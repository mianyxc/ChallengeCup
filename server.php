<!DOCTYPE html>

<?php
  
  require_once("DB_config.php");

  $DB_connect = new mysqli($DB_url, $DB_username, $DB_password, $DB_name);
  $DB_connect->query("set names utf8");
  $sql_depot = "select * from location where id=0";
  $depot_result = $DB_connect->query($sql_depot);
  $depot_location = $depot_result->fetch_array();
  $depot = $depot_location['lng'].",".$depot_location['lat'];

  $DB_connect->close();

?>


<html>
  <head>
    <title>物流配送系统</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <!-- Bootstrap -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="server.css" rel="stylesheet" type="text/css">
    <script src="jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="http://api.map.baidu.com/api?v=1.4"></script>
    <script type="text/javascript" src="TextIconOverlay.js"></script>
    <script type="text/javascript" src="MarkerClusterer.js"></script>
  </head>
  <body>
    <div id="left">
      <div id="control" align='center' class="well sidebar-nav">
        <div class="input-prepend">
            <span class="add-on">车辆总数</span>
            <input class="span2" id="vehicle" type="text" placeholder="输入数字">
        </div>
        <div class="input-prepend">
            <span class="add-on">准载重量</span>
            <input class="span2" id="capacity" type="text" placeholder="输入数字">
        </div>
        <div class="input-prepend">
            <span class="add-on">质量参数</span>
            <input class="span2" id="parameter" type="text" placeholder="输入数字">
        </div>
        <div>
          <button class="btn btn-large" id="go" disabled="true">规划配送方案</button>
        </div>
        <div>
          <button class="btn btn-large" id="cluster">附近点合并/不合并</button>
        </div>
      </div>
      <div id="order" class="pane-message">
        <div id="main-page" style="display: block; ">
          <div class="category-heading">当前订单</div>
          <ol id="orderList" class="message-list">
            
          </ol>
        </div>
      </div>
    </div>
    <div id="map"></div>
  </body>
</html>

<script type="text/javascript">
  var current_responce = "";
  var current_json;
  var waiting;
  var dealing;
  var dealed;

  $(document).ready(function(){

    window.setInterval(function(){
      $.get("queryOrders.php", function(resp){
        if(resp != current_responce) {
          current_responce = resp;
          current_json = JSON.parse(current_responce);
          waiting = current_json.waiting;
          dealed = current_json.dealed;
          dealing = current_json.dealing;
          listOrders();
          showOrders();
          checkCache();
        }
      })
    }, 5000);

    $("#go").click(function(){
      window.open("route.php?vehicle="+$("#vehicle").val()+"&capacity="+$("#capacity").val()+"&parameter="+$("#parameter").val());
    })

    $("#cluster").click(function(){
      cluster = !cluster;
      showOrders();
    })
    
  })

  var listOrders = function(){
    $("#orderList").html("");
    for(var temp in waiting) {
      $("#orderList").append("<li class='message'><a class='title' href='#'><span class='btn btn-warning'><i class='icon-time'></i>"+waiting[temp].amount+"</span>"+waiting[temp].location+"</a><span class='description'>"+waiting[temp].time+" - 尚未处理</span><div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>");

    }
    for(var temp in dealing) {
      $("#orderList").append("<li class='message'><a class='title' href='#'><span class='btn btn-info'><i class='icon-shopping-cart'></i>"+dealing[temp].amount+"</span>"+dealing[temp].location+"</a><span class='description'>"+dealing[temp].time+" - 正在配送</span><div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>");
      
    }
    for(var temp in dealed) {
      $("#orderList").append("<li class='message'><a class='title' href='#'><span class='btn btn-success'><i class='icon-check'></i>"+dealed[temp].amount+"</span>"+dealed[temp].location+"</a><span class='description'>"+dealed[temp].time+" - 订单已完成</span><div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>");
      
    }
  }

  var checkCache = function(){
    $.post("checkCache.php",function(resp){
      if(resp == "nice!") {
        $('#go').removeAttr("disabled");
      } else {
        $('#go').attr('disabled',"true");
        cacheFlag = true;
        cache(resp);
      }
    })
  }
</script>

<script type="text/javascript">
  var map = new BMap.Map("map");
  map.centerAndZoom("北京");
  map.enableScrollWheelZoom();

  var depot = new BMap.Point(<?php echo $depot; ?>);
  var depotMarker = new BMap.Marker(depot);
  map.addOverlay(depotMarker);

  var orderPoints = [];
  var markers = [];
  var cluster = true;

  var showOrders = function(){
    map.clearOverlays();
    map.addOverlay(depotMarker);
    orderPoints = [];
    markers = [];
    orderPoints.push(depot);
    
    for(var temp in waiting) {
      var newPoint = new BMap.Point(waiting[temp].lng,waiting[temp].lat);
      orderPoints.push(newPoint);
      var newMarker = new BMapLib.TextIconOverlay(newPoint,waiting[temp].amount);
      //var newMarker = new BMap.Marker(newPoint,waiting[temp].amount);
      markers.push(newMarker);
      //map.addOverlay(newMarker);
    }
    showCluster();
  }

  var showCluster = function(){
    if(cluster) {
      var markerClusterer = new BMapLib.MarkerClusterer(map, {markers:markers});
    } else {
      for(var i in markers) {
        map.addOverlay(markers[i]);
      }
    }
  }

  var driving_cache = new BMap.DrivingRoute(map);

  var toBeCached = [];

  var cacheFlag = true;

  var cache_interval;

  var cache = function(resp) {
    toBeCached = [];
    var json_resp = JSON.parse(resp);
    var pairs = json_resp.data;
    for(var temp in pairs) {
      var starttemp = new BMap.Point(pairs[temp].start.lng, pairs[temp].start.lat);
      var endtemp = new BMap.Point(pairs[temp].end.lng, pairs[temp].end.lat);
      var startPoint = new Object();
      var endPoint = new Object();
      startPoint.location_id = pairs[temp].start.location_id;
      startPoint.point = starttemp;
      endPoint.location_id = pairs[temp].end.location_id;
      endPoint.point = endtemp;
      var pair = new Object();
      pair.start = startPoint;
      pair.end = endPoint;
      pair.distance = 0;
      toBeCached.push(pair);
    }

    driving_cache.setSearchCompleteCallback(function(callback){
      var distance = callback.getPlan(0).getDistance(false);
      toBeCached[currentIndex].distance = distance;
      currentIndex++;
      console.log(currentIndex);
      if(currentIndex >= toBeCached.length) {
        clearInterval(cache_interval);
        //console.log(toBeCached);
        sendCache();
      }
      cacheFlag = true;
    })

    var currentIndex = 0;
    var timeWaited = 0;
    cache_interval = setInterval(function(){
      if(cacheFlag) {
        cacheFlag = false;
        timeWaited = 0;
        driving_cache.search(toBeCached[currentIndex].start.point, toBeCached[currentIndex].end.point);
      }
      timeWaited += 200;
      if(timeWaited >= 20000) {
        clearInterval(cache_interval);
        sendCache();
      }
    },200)
  }

  var failed = 0;

  var sendCache = function(){
    if(toBeCached[0].distance == 0) {
      failed++;
      if(failed >= 2) {
        window.location.href="server.php";
      }
    } else {
      failed = 0;
    }
    var cacheToSend = JSON.stringify(toBeCached);
    //console.log(cacheToSend);
    console.log("Send to cache.");
    $.post("cache.php",{cache: cacheToSend}, function(res){
      checkCache();
    });
  }

</script>