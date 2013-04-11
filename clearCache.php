<?php

	require_once("DB_config.php");

	$sql_clearCache = "delete from cache";
	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
  	$DB_connect->query("set names utf8");
  	if($DB_connect->query($sql_clearCache)) echo "success";
  	$DB_connect->close();

?>