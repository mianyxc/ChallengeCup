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
  </head>
  <body>
    <div id="left">
      <div id="control" align='center' class="well sidebar-nav">
        <div class="input-prepend">
            <span class="add-on">车辆数</span>
            <input class="span2" id="vehicle" type="text" placeholder="输入数字">
        </div>
        <div class="input-prepend">
            <span class="add-on">载重量</span>
            <input class="span2" id="capacity" type="text" placeholder="输入数字">
        </div>
        <button class="btn btn-large" id="go">规划配送方案</button>
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
        }
      })
    }, 5000);
    
    $("#go").click(function(){
      showRoute([0,1,3,2]);
      showRoute([0,4,6,5,7]);
    })
    
  })

  var listOrders = function(){
    $("#orderList").html("");
    for(var temp in waiting) {
      $("#orderList").append("<li class='message'><a class='title' href='#'><span class='btn btn-warning'><i class='icon-time'></i>"+waiting[temp].amount+"</span>"+waiting[temp].location+"</a><span class='description'>"+waiting[temp].time+" - 尚未处理</span><div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>");

    }
    for(var temp in dealing) {
      $("#orderList").append("<li class='message'><a class='title' href='#'><span class='btn btn-info'><i class='icon-shopping-cart'></i>"+waiting[temp].amount+"</span>"+waiting[temp].location+"</a><span class='description'>"+waiting[temp].time+" - 正在配送</span><div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>");
      
    }
    for(var temp in waiting) {
      $("#orderList").append("<li class='message'><a class='title' href='#'><span class='btn btn-success'><i class='icon-time'></i>"+waiting[temp].amount+"</span>"+waiting[temp].location+"</a><span class='description'>"+waiting[temp].time+" - 订单已完成</span><div class='toolbar'><a class='handin-link' href='#'>查看订单详情</a></div></li>");
      
    }
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

  var showOrders = function(){
    map.clearOverlays();
    map.addOverlay(depotMarker);
    orderPoints = [];
    orderPoints.push(depot);
    
    for(var temp in waiting) {
      var newPoint = new BMap.Point(waiting[temp].lng,waiting[temp].lat);
      orderPoints.push(newPoint);
      var newMarker = new BMap.Marker(newPoint);
      map.addOverlay(newMarker);
    }
  }

  var driving = new BMap.DrivingRoute(map);
  
  driving.setSearchCompleteCallback(function(results){
    
    var pts = results.getPlan(0).getRoute(0).getPath();
    var line = new BMap.Polyline(pts);
    map.addOverlay(line);
      
    
  });



  function showRoute(route) {

    for(var i=0; i<route.length; i++) {
      driving.search(orderPoints[route[i]], orderPoints[route[(i+1)%route.length]]);
    }

  }

</script>