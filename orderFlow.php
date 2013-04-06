<?php
	
	require_once("DB_config.php");

	$DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
	$DB_connect->query("set names utf8");
	$sql_waiting = "select * from orders where state=0 order by time desc";
	$sql_dealing = "select * from orders where state=1 order by time desc";
	$sql_dealed = "select * from orders where date(time)=date(now()) and state=2 order by time desc";
	$waiting = $DB_connect->query($sql_waiting);
	$dealing = $DB_connect->query($sql_dealing);
	$dealed = $DB_connect->query($sql_dealed);
	$temp;
	echo "<script type='text/javascript'>newLocation = []</script>";
	while($temp=$waiting->fetch_array()) {
		echo "<li class='message'><a class='title' href='#'><span class='btn btn-warning'><i class='icon-time'></i>";
		echo $temp['amount'];
		echo "</span>";
		echo $temp['location'];
		echo "</a><span class='description'>";
		echo $temp['time']." - 尚未处理</span>";
		echo "<div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>";
		echo "<script type='text/javascript'>newLocation.push(".$temp['lng'].",".$temp['lat'].")</script>";
	}
	while($temp=$dealing->fetch_array()) {
		echo "<li class='message'><a class='title' href='#'><span class='btn btn-info'><i class='icon-shopping-cart'></i>";
		echo $temp['amount'];
		echo "</span>";
		echo $temp['location'];
		echo "</a><span class='description'>";
		echo $temp['time']." - 正在配送</span>";
		echo "<div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>";
	}
	while($temp=$dealed->fetch_array()) {
		echo "<li class='message'><a class='title' href='#'><span class='btn btn-success'><i class='icon-check'></i>";
		echo $temp['amount'];
		echo "</span>";
		echo $temp['location'];
		echo "</a><span class='description'>";
		echo $temp['time']." - 订单已完成</span>";
		echo "<div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>";
	}
	echo "<script type='text/javascript'>
		//alert(newLocation.toString());
		if(orderLocation.toString() !== newLocation.toString()) {
			//alert('OK');
			orderLocation = newLocation.concat();
			showOrder();
		}
	</script>";


?>
