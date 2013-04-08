<?php

	require_once("DB_config.php");

	if($_POST) {
		$temp = $_POST['cache'];
		$cache = json_decode($temp, true);


		$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
		$DB_connect->query("set names utf8");

		foreach ($cache as $pair) {
			$distance = $pair['distance'];
			if($distance != 0) {
				$start_id = $pair['start']['location_id'];
				$end_id = $pair['end']['location_id'];
				$sql_check = "select * from cache where start='$start_id' and end='$end_id'";
				$resp = $DB_connect->query($sql_check);
				if(!($resp->fetch_array())) {
					$sql_write = "insert into cache (start,end,distance) values ('$start_id','$end_id','$distance')";
					$DB_connect->query($sql_write);
				}
			}
		}

		$DB_connect->close();

		echo "end";
	}

?>