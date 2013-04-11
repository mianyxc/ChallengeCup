<?php
	
	require_once("DB_config.php");

	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
	$DB_connect->query("set names utf8");
	$sql_waiting = "select * from orders where state=0 order by time desc";
	$sql_dealing = "select * from orders where state=1 order by time desc";
	$sql_dealed = "select * from orders where DATEDIFF(time,NOW())=0 and state=2 order by time desc";
	$waiting = $DB_connect->query($sql_waiting);
	$dealing = $DB_connect->query($sql_dealing);
	$dealed = $DB_connect->query($sql_dealed);
	$resp = array();
	while($temp = $waiting->fetch_array()) {
		$resp['waiting'][] = $temp;
	}
	while($temp = $dealing->fetch_array()) {
		$resp['dealing'][] = $temp;
	}
	while($temp = $dealed->fetch_array()) {
		$resp['dealed'][] = $temp;
	}
	echo json_encode($resp);
	$DB_connect->close();

?>
