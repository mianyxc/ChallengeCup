<!DOCTYPE html>

<?php
  
  require_once("DB_config.php");
  //require_once("depotLocation.php");

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
        <!--
        <form class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputVehicle">车辆总数</label>
            <div class="controls">
              <input type="text" id="inputVehicle" placeholder="" class="input-small">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputCapacity">载重量</label>
            <div class="controls">
              <input type="text" id="inputCapacity" placeholder="" class="input-small">
            </div>
          </div>
          <div class="control-group">
            <div class="controls">
              <label class="checkbox">
                <input type="checkbox"> Remember me
              </label>
              <button type="submit" class="btn">Sign in</button>
            </div>
          </div>
        </form>-->
        <div class="input-prepend">
            <span class="add-on">车辆数</span>
            <input class="span2" id="vehicle" type="text" placeholder="输入数字">
        </div>
        <div class="input-prepend">
            <span class="add-on">载重量</span>
            <input class="span2" id="capacity" type="text" placeholder="输入数字">
        </div>
        <button class="btn btn-large">规划配送方案</button>
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
  $(document).ready(function(){
    window.setInterval(function(){
      $.get("orderFlow.php", function(resp){
        $("#orderList").html(resp);
      })
    }, 5000);
    
    $("#go").click(function(){
      //alert("OK");
      showRoute([0,1,3,2]);
      showRoute([0,4,6,5,7]);
    })
    
  })
</script>

<script type="text/javascript">
  var map = new BMap.Map("map");
  map.centerAndZoom("北京");
  map.enableScrollWheelZoom();

  var depot = new BMap.Point(<?php echo $depot; ?>);
  var depotMarker = new BMap.Marker(depot);
  map.addOverlay(depotMarker);

  var orderLocation = [];
  var newLocation = [];
  var orderPoints = [];

  var showOrder = function(){
    //alert("OK")
    map.clearOverlays();
    map.addOverlay(depotMarker);
    //map.addOverlay(depotMarker);
    orderPoints = [];
    orderPoints.push(depot);
    for(var i=0; i<orderLocation.length; i+=2) {
      //alert(orderLocation[p])
      var temp = new BMap.Point(orderLocation[i],orderLocation[i+1]);
      orderPoints.push(temp);
      var newMarker = new BMap.Marker(temp);
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