<?php

	$vehicle = $_GET['vehicle'];
  	$capacity = $_GET['capacity'];
  	$parameter = $_GET['parameter'];

	require_once("DB_config.php");

	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
  	$DB_connect->query("set names utf8");

  	$sql_depot = "select * from location where id=0";
	$result_depot = $DB_connect->query($sql_depot);
	$depot = $result_depot->fetch_array();

	$orders = array();
	$amount = array();

  	$sql_orders = "select * from orders where state=0 order by location_id";
  	$result = $DB_connect->query($sql_orders);
	
	while($temp = $result->fetch_array()) {
		$orders[] = $temp;
		$amount[] = $temp['amount'];
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

	$data = "".(count($orders)+1)."\n"."".$vehicle."\n".$capacity."\n".$parameter."\n";

	foreach ($amount as $need) {
		$data = $data.$need." ";
	}
	$data = $data."\n";

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

<!DOCTYPE html>
<html>
  	<head>
	    <title>随机生成需求</title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta charset="utf-8">
	    <!-- Bootstrap -->
	    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	    <link href="HubSpot/build/css/messenger.css" rel="stylesheet" media="screen">
	    <link href="HubSpot/build/css/messenger-theme-future.css" rel="stylesheet" media="screen">
	    <script src="jquery.min.js"></script>
	    <script src="bootstrap/js/bootstrap.min.js"></script>
	    <script src="HubSpot/build/js/messenger.min.js"></script>
	    <script type="text/javascript" src="http://api.map.baidu.com/api?v=1.4"></script>
	    <style type="text/css">
	    	.container {
	    		text-align:center;
	    		vertical-align: middle;
	    		margin-top: 300px;
	    	}
	    </style>
  	</head>
  	<body>
  		<div class="container">
			<div style="margin:50px;" id="animation">
  				<img src="MetroUI/images/preloader-w8-line-black.gif" />
  			</div>
  			<div id="message">
  				路径规划中……
  			</div>
		</div>
		<div id="map">
		</div>
  	</body>

</html>
<script type="text/javascript">
	$(document).ready(function(){
		$.post("GA.php", function(resp){
			$(".container").css('display','none');
			$("#map").html(resp);
		})
	})
</script>