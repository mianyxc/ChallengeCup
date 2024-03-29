<!DOCTYPE html>
<?php

	require_once("DB_config.php");



?>

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
			<div class="input-append">
			  	<input class="span2" id="orderNum" type="text">
			  	<button class="btn" type="button" id="new">新的需求点</button>
			  	<button class="btn" type="button" id="old">现有需求点</button>
			  	<button class="btn" type="button" id="clearOrder">清除需求点</button>
			  	<button class="btn" type="button" id="clearCache">清除缓存</button>
			</div>
			<div style="margin:50px;display:none;" id="animation">
  				<img src="MetroUI/images/preloader-w8-cycle-black.gif" />
  			</div>
  			<div style="margin:10px;display:none;" id="success">
  				<img src="source/checkmark.png" />
  			</div>
  			<div id="message">
  			</div>
		</div>
  	</body>
</html>

<script type="text/javascript">
	var count = 0;
	$(document).ready(function(){
		$("#new").click(function(){
			var orderNum = $("#orderNum").val();
			$("#success").css("display","none");
			$("#animation").css("display","block");
			var gc = new BMap.Geocoder();
			var i = 0;
			var flag = true;
			var interval = setInterval(function(){
				if(i >= orderNum) {
					clearInterval(interval);
				} else {
					if(flag) {
						flag = false;
						var lng = lngRandom();
						var lat = latRandom();
						var point = new BMap.Point(lng, lat);
						var amount = Math.floor(Math.random()*250+50);
						gc.getLocation(point, function(resp){
							var addComp = resp.addressComponents;
				        	var location = addComp.city + addComp.district + addComp.street + addComp.streetNumber;
							$.post("newOrder.php",{
								user_id: "1",
								username: "清华大学",
								location: location,
								amount: amount,
								lng: lng,
								lat: lat,
								phone: "15201410992"
							},function(res){
								if(res=="success") count++;
								$("#message").html("已生成"+count+"个订单");
								if(count==orderNum) {
									$("#animation").css("display","none");
									$("#success").css("display","block");
									$("#message").html("成功添加"+i+"个新订单");
								}
							});
							flag = true;
							i++;
						});
					}
				}
			},200)
		})

		$("#clearOrder").click(function(){
			$._messengerDefaults = {
				extraClasses: 'messenger-fixed messenger-theme-future messenger-on-top'
			}
			$.post("clearOrder.php",function(resp){
				if(resp=="success"){
					$.globalMessenger().post("现有订单已清除！");
				}
			});
		})

		$("#clearCache").click(function(){
			$._messengerDefaults = {
				extraClasses: 'messenger-fixed messenger-theme-future messenger-on-top'
			}
			$.post("clearCache.php",function(resp){
				if(resp=="success"){
					$.globalMessenger().post("现有缓存已清除！");
				}
			});
		})

		$("#old").click(function(){
			$("#animation").css("display","block");
			$("#message").html("正在生成订单……");
			$.post("batchOrder.php",{num:$("#orderNum").val()},function(resp){
				if(resp=="success") {
					$("#animation").css("display","none");
					$("#success").css("display","block");
					$("#message").html("成功添加"+$("#orderNum").val()+"个新订单");
				}
			})
		})
	})

	var lngRandom = function() {
		var westLimit = 116.205;
		var eastLimit = 116.57;
		var lng = Math.random() * (eastLimit - westLimit) + westLimit;
		return lng.toFixed(6);
	}
	var latRandom = function() {
		var northLimit = 40.04;
		var southLimit = 39.8;
		var lat = Math.random() * (northLimit - southLimit) + southLimit;
		return lat.toFixed(6);
	}
</script>