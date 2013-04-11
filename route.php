<!DOCTYPE html>
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

  	$orders_json = json_encode($orders);
  	$depot_json = json_encode($depot);

  	//echo $orders_json;

?>


<html>
  	<head>
	    <title>路径规划结果</title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta charset="utf-8">
	    <!-- Bootstrap -->
	    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	    <link href="HubSpot/build/css/messenger.css" rel="stylesheet" media="screen">
	    <link href="HubSpot/build/css/messenger-theme-future.css" rel="stylesheet" media="screen">
		<link href="route.css" rel="stylesheet" type="text/css">
	  	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
	  	<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
	  	<script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
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
  		<div style="display:none;" id="orders_json">
  			<?php echo $orders_json;?>
  		</div>
  		<div style="display:none;" id="depot_json">
  			<?php echo $depot_json;?>
  		</div>

  		<div class="container">
			<div style="margin:50px;" id="animation">
  				<img src="MetroUI/images/preloader-w8-line-black.gif" />
  			</div>
  			<div id="message">
  				路径规划中……
  			</div>
		</div>
		<div id="left" style="display:none;">
	      <div id="routes">
	        <div id="main-page" style="display: block; ">
	          <!--<ol id="orderList" class="message-list"></ol>-->
	          <div id="accordion">
	          </div>
	        </div>
	      </div>
	    </div>
		<div id="map" style="display:none;">
		</div>
  	</body>

</html>
<script type="text/javascript">
	var orders_json = $("#orders_json").html();
	var depot_json = $("#depot_json").html();
	var orders = JSON.parse(orders_json);
	var depot = JSON.parse(depot_json);
	var routes;
	//alert(orders_json);
</script>
<script type="text/javascript">
	$(document).ready(function(){
		$.post("GA.php", function(resp){
			$(".container").css('display','none');
			$("#left").css('display','block');
			$("#map").css('display','block');
			routes = JSON.parse(resp);
			for(var routeNo in routes) {
				var routeString = "<h3>车辆编号："+routeNo+"</h3><div><ul class='sortable'>";
				var route = routes[routeNo];
				for(var nodeNo in route) {
					var node = route[nodeNo]-1;
					var nodeString = "<li class='node'>" + orders[node].location + "</li>"
					routeString += nodeString;
				}
				routeString += "</ul></div>";
				$("#accordion").append(routeString);
			}
			$("#accordion").accordion();
			$("#accordion").accordion('destroy');
			$("#accordion").accordion({active:false,collapsible:true});
			$(".sortable").sortable();
			$(".sortable").disableSelection();
		})
	})
</script>