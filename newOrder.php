<?php

	require_once("DB_config.php");

	if($_POST) {
		$user_id = $_POST['user_id'];
		$username = $_POST['username'];
		$location = $_POST['location'];
		$amount = $_POST['amount'];
		$lng = $_POST['lng'];
		$lat = $_POST['lat'];
		$location_id;
		$phone = $_POST['phone'];


		$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
		$DB_connect->query("set names utf8");

		$sql_check = "select * from location where lng='$lng' and lat='$lat'";
		
		$result = $DB_connect->query($sql_check);
		$temp;
		if($temp = $result->fetch_array()) {
			$location_id = $temp['id'];
		} else {
			$sql_newLocation = "insert into location (name,lng,lat) values ('$location','$lng','$lat')";
			$DB_connect->query($sql_newLocation);
			$location_id = mysqli_insert_id($DB_connect);
		}

		$sql_newOrder = "insert into orders (user_id,username,location_id,location,phone,amount,lng,lat) values ('$user_id','$username','$location_id','$location','$phone',$amount,'$lng','$lat')";
		if($DB_connect->query($sql_newOrder)) echo "success";

		$DB_connect->close();
	}

?>