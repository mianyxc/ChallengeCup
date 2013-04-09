<?php

	$vehicle = $_GET['vehicle'];
  	$capacity = $_GET['capacity'];

	require_once("DB_config.php");

	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
  	$DB_connect->query("set names utf8");

  	$sql_depot = "select * from location where id=0";
	$result_depot = $DB_connect->query($sql_depot);
	$depot = $result_depot->fetch_array();

	$orders = array();

  	$sql_orders = "select * from orders where state=0 order by location_id";
  	$result = $DB_connect->query($sql_orders);
	
	while($temp = $result->fetch_array()) {
		$orders[] = $temp;
	}

	$distance = array();

	$temp_distance = array();
	$temp_distance[] = 0;
	foreach ($orders as $order) {
		$start = $depot['id'];
		$end = $order['location_id'];
		$sql_distance = "select * from cache where start='$start' and end='$end'";
		$result_cache = $DB_connect->query($sql_distance);
		$cache = $result_cache->fetch_array();
		$temp_distance[] = $cache['distance'];
	}
	$distance[] = $temp_distance;

	foreach ($orders as $order) {
		$temp_distance = array();
		$start = $order['location_id'];
		$end = $depot['id'];
		$sql_distance = "select * from cache where start='$start' and end='$end'";
		$result_cache = $DB_connect->query($sql_distance);
		$cache = $result_cache->fetch_array();
		$temp_distance[] = $cache['distance'];
		foreach ($orders as $dest) {
			$end = $dest['location_id'];
			if($start != $end) {
				$sql_distance = "select * from cache where start='$start' and end='$end'";
				$result_cache = $DB_connect->query($sql_distance);
				$cache = $result_cache->fetch_array();
				$temp_distance[] = $cache['distance'];
			} else {
				$temp_distance[] = 0;
			}
		}
		$distance[] = $temp_distance;
	}

	$data = "".$vehicle."\n".$capacity."\n".(count($orders)+1)."\n";

	foreach ($distance as $row) {
		foreach ($row as $temp) {
			$data = $data.$temp." ";
		}
		$data = $data."\n";
	}

  	$DB_connect->close();

  	$file=fopen("distance.txt","w");

  	fwrite($file, $data);

  	fclose($file);


?>