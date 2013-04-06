<?php

	require_once("DB_config.php");

	$sql_clearOrder = "update orders set state=2 where state<>2";
	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
  	$DB_connect->query("set names utf8");
  	if($DB_connect->query($sql_clearOrder)) echo "success";
  	$DB_connect->close();

?>