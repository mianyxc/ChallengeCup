<?php
	
	require_once("DB_config.php");

	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
	$DB_connect->query("set names utf8");
	$sql = "select * from orders where state=0 order by id";
	$result = $DB_connect->query($sql);
	$orders = array();
	while($temp = $result->fetch_array()) {
		$orders[] = $temp;
	}
	$sql_depot = "select * from location where id=0";
	$result_depot = $DB_connect->query($sql_depot);
	$depot = $result_depot->fetch_array();
	$checked = array();
	$resp = array();
	$state = "nothing";
	foreach ($orders as $order) {
		$start = $order['location_id'];
		$end = $depot['id'];
		$sql_check = "select * from cache where start='$start' and end='$end'";
		$temp = $DB_connect->query($sql_check);
		if(!($temp->fetch_array())) {
			$toBeChecked = array();
			$toBeChecked['start']['location_id'] = $order['location_id'];
			$toBeChecked['start']['lng'] = $order['lng'];
			$toBeChecked['start']['lat'] = $order['lat'];
			$toBeChecked['end']['location_id'] = $depot['id'];
			$toBeChecked['end']['lng'] = $depot['lng'];
			$toBeChecked['end']['lat'] = $depot['lat'];
			$resp[] = $toBeChecked;
			$state = "complete";
		}
		$start = $depot['id'];
		$end = $order['location_id'];
		$sql_check = "select * from cache where start='$start' and end='$end'";
		$temp = $DB_connect->query($sql_check);
		if(!($temp->fetch_array())) {
			$toBeChecked = array();
			$toBeChecked['start']['location_id'] = $depot['id'];
			$toBeChecked['start']['lng'] = $depot['lng'];
			$toBeChecked['start']['lat'] = $depot['lat'];
			$toBeChecked['end']['location_id'] = $order['location_id'];
			$toBeChecked['end']['lng'] = $order['lng'];
			$toBeChecked['end']['lat'] = $order['lat'];
			$resp[] = $toBeChecked;
			$state = "complete";
		}
		foreach ($checked as $checkedOrder) {
			$start = $order['location_id'];
			$end = $checkedOrder['location_id'];
			$sql_check = "select * from cache where start='$start' and end='$end'";
			$temp = $DB_connect->query($sql_check);
			if(!($temp->fetch_array())) {
				$toBeChecked = array();
				$toBeChecked['start']['location_id'] = $order['location_id'];
				$toBeChecked['start']['lng'] = $order['lng'];
				$toBeChecked['start']['lat'] = $order['lat'];
				$toBeChecked['end']['location_id'] = $checkedOrder['location_id'];
				$toBeChecked['end']['lng'] = $checkedOrder['lng'];
				$toBeChecked['end']['lat'] = $checkedOrder['lat'];
				$resp[] = $toBeChecked;
				$state = "complete";
			}
			$start = $checkedOrder['location_id'];
			$end = $order['location_id'];
			$sql_check = "select * from cache where start='$start' and end='$end'";
			$temp = $DB_connect->query($sql_check);
			if(!($temp->fetch_array())) {
				$toBeChecked = array();
				$toBeChecked['start']['location_id'] = $checkedOrder['location_id'];
				$toBeChecked['start']['lng'] = $checkedOrder['lng'];
				$toBeChecked['start']['lat'] = $checkedOrder['lat'];
				$toBeChecked['end']['location_id'] = $order['location_id'];
				$toBeChecked['end']['lng'] = $order['lng'];
				$toBeChecked['end']['lat'] = $order['lat'];
				$resp[] = $toBeChecked;
				$state = "complete";
			}
			if(count($resp)>=100) {
				$state = "incomplete";
				break;
			}
		}
		$checked[] = $order;
		if($state == "incomplete") {
			break;
		}
	}
	$result = array();
	$result['state'] = $state;
	$result['data'] = $resp;

	if($state != "nothing") {
		echo json_encode($result);
	} else {
		echo "nice!";
	}
	
	$DB_connect->close();

?>
