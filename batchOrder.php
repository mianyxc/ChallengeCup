<?php

	require_once("DB_config.php");

	if($_POST) {
		$num = $_POST['num'];


		$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
		$DB_connect->query("set names utf8");

		$sql_interval = "select max(id) as maxid from location";
		$result = $DB_connect->query($sql_interval);
		$temp = $result->fetch_array();
		$maxID = $temp['maxid'];
		$ids = array();
		$idSet = "";

		for($i = 0; $i < $num; $i++) {
			$tempID = rand(1, $maxID);
			for($j = 0; $j < $i; $j++) {
				if($tempID == $ids[$j]) {
					$tempID = rand(1, $maxID);
					$j = -1;
				}
			}
			$ids[$i] = $tempID;
			if($i == 0) {
				$idSet = "".$tempID;
			} else {
				$idSet = $idSet.",".$tempID;
			}
		}

		$sql = "select * from location where id in ($idSet)";
		$result = $DB_connect->query($sql);

		$temp;
		$locations = array();
		while($temp = $result->fetch_array()) {
			$locations[] = $temp;
		}

		foreach($locations as $location) {
			$location_id = $location['id'];
			$location_name = $location['name'];
			$amount = rand(50,300);
			$lng = $location['lng'];
			$lat = $location['lat'];
			$sql_newOrder = "insert into orders (user_id,username,location_id,location,phone,amount,lng,lat) values (1,'清华大学','$location_id','$location_name','15201410992',$amount,'$lng','$lat')";
			$DB_connect->query($sql_newOrder);
		}

		echo "success";


		$DB_connect->close();
	}


?>