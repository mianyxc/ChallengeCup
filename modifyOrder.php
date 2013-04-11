<?php

	require_once("DB_config.php");

	if($_POST){
		$id = $_POST['id'];
		$state = $_POST['state'];
		$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
	  	$DB_connect->query("set names utf8");

	  	$sql = "update orders set state=$state where id=$id";
	  	if($DB_connect->query($sql)) echo "success";

	  	$DB_connect->close();
  	}

?>