#include <iostream>
#include <fstream>
#include <vector>
#include <string>
#include <algorithm>
#include <cmath>
#include <ctime>
#include <stdlib.h>
#include <list>


#define GEN 100
#define POP 200
#define CROSSPROB 0.5
#define MUPROB 0.1
#define FITPARA 1
#define MAXNODES 500

using namespace std;



int dist[MAXNODES][MAXNODES];
int need[MAXNODES];
int numNodes;
int vehicle;
int capacity;
int loop;

int random() {
	return abs(rand())%numNodes;
}

double random01() {
	return (double)rand()/(RAND_MAX+1);
}


class Route{
public:
	int route[MAXNODES];
	int totalDistance;
	double fitness;
	
	Route() {
		int i, j;
		for(i = 0; i < numNodes; i++) {
			j = 0;
			route[i] = random();
			while(j < i) {
				if(route[i] == route[j]) {
					route[i] = random();
					j = 0;
				}
				else j++;
			}

		}
		normalize();
		getDistance();
		//optimize();
		//cout << fitness << endl;
	}

	Route(int* r) {
		for(int i = 0; i < numNodes; i++) {
			route[i] = r[i];
		}
		normalize();
		getDistance();
	}
	/*
	void pre_PMX(int begin, int end, int* mate) {
		for(int i = 0; i < numNodes; i++) {
			if(i >= begin && i <= end) continue;
			for (int j = begin; j <= end; j++) {
				if(route[i] == mate[j]) {
					int flag = true;
					for(int k = begin; k <= end; k++) {
						if(route[j] == mate[k]) {
							flag = false;
							break;
						}
					}
					if(flag){
						route[i] = route[j];
					}
					break;
				}
			}
		}
	}
	*/

	void normalize() {
		int depotNo;
		int temp[MAXNODES];
		for(int i = 0; i < numNodes; i++) {
			if(route[i] == 0) {
				depotNo = i;
				break;
			}
		}
		if(depotNo==0) return;
		for(int i = 0; i < numNodes; i++) {
			temp[i] = route[i];
		}
		for(int i = 0; i < numNodes; i++) {
			route[i] = temp[(depotNo+i)%numNodes];
		}
	}

	void getDistance() {
		int currentCapacity = capacity;
		totalDistance = 0;
		for(int i = 1; i < numNodes; i++) {
			currentCapacity -= need[i];
			if(currentCapacity < 0) {
				currentCapacity = capacity;
				totalDistance += dist[route[i-1]][0];
				totalDistance += dist[0][route[i]];
			} else {
				totalDistance += dist[route[i-1]][route[i]];
			}
		}
		totalDistance += dist[route[numNodes-1]][route[0]];
		//cout << totalDistance <<endl;

	}

	void mutation() {/*
		int begin = random();
		int end = random();
		int temp;
		if(begin > end) {
			temp = begin;
			begin = end;
			end = temp;
		}
		for(int i = 0; i < (end-begin)/2; i++) {
			temp = route[begin+i];
			route[begin+i] = route[end-i];
			route[end-i] = temp;
		}
		normalize();
		getDistance();*/
		optimize();
	}

	void printRoute() {
		/*
		for(int i = 0; i < numNodes; i++) {
			cout << "<" << i << ">" << route[i] << "\t";
		}
		cout << endl;
		*/
		cout << totalDistance << "  ";
	}

	void getFitness(int best) {
		//cout << best << endl;
		//fitness = MAXNODES/(double)(totalDistance - best + FITPARA);
		//cout << fitness << endl;
		fitness = best/(double)(totalDistance - best + best/50);
	}

	void optimize() {
		int currentDistance;
		int temp;
		getDistance();
		for(int i = 1; i < numNodes; i++) {
			for(int j = 1; j < numNodes; j++) {
				if(j == i) continue;
				currentDistance = totalDistance;
				temp = route[i];
				route[i] = route[j];
				route[j] = temp;
				getDistance();
				if(totalDistance < currentDistance) {
					i = 0;
					break;
				} else {
					temp = route[i];
					route[i] = route[j];
					route[j] = temp;
					getDistance();
				}
			}
		}
	}

};


class Group{
public:
	Route routes[POP];
	double cumuProb[POP];
	double totalFitness;
	Route best;

	Group() {
		Route temp;
		best = temp;
		routes[0] = temp;
		for(int i = 1; i < POP; i++) {
			Route temp;
			routes[i] = temp;
			if(routes[i].totalDistance < best.totalDistance) {
				best = routes[i];
				//best.printRoute();
			}
		}
		//cout << "BEST: " << best.totalDistance << endl;
		refreshFitness();
	}

	void naturalSelect(){
		//cout << "naturalSelect" << endl;
		refreshFitness();
		getProbability();
		Route newRoutes[POP];
		double r;
		for(int i = 0; i < POP; i++) {
			r = random01();
			//cout << r << endl;
			for(int j = 0; j < POP; j++) {
				if(r < cumuProb[j]) {
					//cout << cumuProb[j-1] << "  " << r << "  " << cumuProb[j] << endl;
					newRoutes[i] = routes[j];
					break;
				}
			}
			//cout << newRoutes[i].fitness << endl;
		}
		for(int i = 0; i < POP; i++) {
			routes[i] = newRoutes[i];
			//routes[i].printRoute();
		}
	}

	void crossover() {
		double r;
		for(int i = 0; i < POP; i+=2) {
			r = random01();
			//cout << r << "  ";
			if(r < CROSSPROB) {
				cross(i, i+1);
			}
			routes[i].normalize();
			routes[i+1].normalize();
			//routes[i].optimize();
			//routes[i+1].optimize();
			if(routes[i].totalDistance < best.totalDistance) {
				best = routes[i];
				//best.printRoute();
			}
			if(routes[i+1].totalDistance < best.totalDistance) {
				best = routes[i+1];
				//best.printRoute();
			}
		}
	}

	void mutate() {
		double r;
		for(int i = 0; i < POP; i++) {
			r = random01();
			if(r < MUPROB) {
				routes[i].mutation();
			}
			if(routes[i].totalDistance < best.totalDistance) {
				best = routes[i];
				//best.printRoute();
			}
		}
	}



private:
	void getProbability() {
		totalFitness = 0;
		for(int i = 0; i < POP; i++) {
			totalFitness += routes[i].fitness;
		}
		cumuProb[0] = routes[0].fitness/totalFitness;
		for(int i = 1; i < POP; i++) {
			cumuProb[i] = cumuProb[i-1] + routes[i].fitness/totalFitness;
			//cout << cumuProb[i] << endl;
		}
	}
	
	void cross(int i, int j) {
		int begin = random();
		int end = random();
		int temp;
		if(begin > end) {
			temp = begin;
			begin = end;
			end = temp;
		}
		
		//routes[i].printRoute(); cout << endl;
		//routes[j].printRoute(); cout << endl;
		
		/*
		routes[i].pre_PMX(begin, end, routes[j].route);
		routes[j].pre_PMX(begin, end, routes[i].route);
		
		for(int k = begin; k <= end; k++) {
			temp = routes[i].route[k];
			routes[i].route[k] = routes[j].route[k];
			routes[j].route[k] = temp;
		}*/

		vector<int> temp1, temp2;

		int k;
		//routes[i].printRoute();
		//routes[j].printRoute();

		for(k=begin; k <= end; k++) {
			temp1.push_back(routes[i].route[k]);
			temp2.push_back(routes[j].route[k]);
		}

		do {
			if(k == numNodes) k = 0;
			if(check(routes[i].route, routes[j].route[k], begin, end)) temp1.push_back(routes[j].route[k]);
			if(check(routes[j].route, routes[i].route[k], begin, end)) temp2.push_back(routes[i].route[k]);
			k++;
		} while(k != end+1);

		int m = begin, n = 0;
		while(n < numNodes) {
			if(m==numNodes) m = 0;
			routes[i].route[m] = temp1[n];
			routes[j].route[m] = temp2[n];
			m++;
			n++;
		}

		routes[i].getDistance();
		routes[j].getDistance();
		/*
		routes[i].printRoute();
		routes[j].printRoute();
		cout << endl;
		*/
		/*
		for(int k = 0; k < numNodes; k++) {
			for(int m = k+1; m < numNodes; m++) {
				if(routes[i].route[k] == routes[i].route[m] || routes[j].route[k] == routes[j].route[m]) {
					routes[i].printRoute(); cout << endl;
					routes[j].printRoute(); cout << endl;
					cout << begin << "\t" << end << endl;
					cout << endl;
					system("pause");
				}
			}
		}
		*/

	}

	bool check(int* a, int x, int begin, int end) {
		for(int i = begin; i <= end; i++) {
			if(a[i] == x) {
				return false;
			}
		}
		return true;
	}

	void refreshFitness() {
		//cout << "best: " << best.totalDistance << endl;
		for(int i = 0; i < POP; i++) {
			routes[i].getFitness(best.totalDistance);
		}
	}

};



int main(){
	srand(unsigned(time(0)));
	ifstream fin("distance.txt");
	fin >> numNodes;
	fin >> vehicle;
	fin >> capacity;
	fin >> loop;
	int temp;
	need[0] = 0;
	for(int i = 1; i < numNodes; i++) {
		fin >> temp;
		need[i] = temp;
	}
	for(int i = 0; i < numNodes; i++) {
		for(int j = 0; j < numNodes; j++) {
			fin >> temp;
			dist[i][j] = temp;
		}
	}
	fin.close();

	Route bestBest;

	for(int l = 0; l < loop; l++) {
		Group group;
	
		for(int i = 0; i < GEN; i++) {
			group.naturalSelect();
			group.crossover();
			group.mutate();
		}

		group.best.optimize();

		//cout << group.best.totalDistance << endl;

		if(group.best.totalDistance < bestBest.totalDistance) {
			bestBest = group.best;
		}
	}

	//cout << bestBest.totalDistance << endl;
	
	int currentCapacity = capacity;
	for(int i = 1; i < numNodes; i++) {
		currentCapacity -= need[bestBest.route[i]];
		if( i == 1) {cout << "[[\"" << bestBest.route[i] << "\"";}
		else if(currentCapacity < 0) {
			currentCapacity = capacity;
			cout << "],";
			cout << "[\"" << bestBest.route[i] << "\"";
		} else {
			cout << ",\"" << bestBest.route[i] << "\"";
		}
	}
	cout << "]]";


	return 0;

}