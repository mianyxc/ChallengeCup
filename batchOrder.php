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

		/*生成随机数组*/
		$sql = "select * from location where id='$location_id'";
		$result = $DB_connect->query($sql);
		$location = $result->fetch_array();
		echo json_encode($location);

		$DB_connect->close();
	}

?>