<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="现贷猫、贷款、网贷、身份证贷款、现金贷、随心贷、小额贷款、信用金、信用贷款、个人贷款、无抵押贷款、网上借钱、大学生贷款、快速审批、极速到账" />
	<link href="favicon.ico" type="image/x-icon"/>
	<link rel="stylesheet" type="text/css" href="__ROOT__/Style/cash/css/style.css">
	<title>现贷猫</title>
	<script type="text/javascript" src="__ROOT__/Style/cash/js/jquery-1.12.0.min.js"></script>
	<script type="text/javascript" src="https://api.map.baidu.com/api?v=2.0&ak=kuxAUkRzHD3wzlDxCPQtY4IK&s=1"></script>
	<!-- 百度统计 Start-->
	 <?php echo get_baidu();?>
	<!-- 百度统计 End-->
	<!-- 白骑士JS Start -->
	<?php echo get_baiqishi_fir(); echo session_id(); echo get_baiqishi_sec();?>
	<!-- 白骑士JS  End-->
	<!-- 同盾JS Start -->
	<?php echo get_tongdun_fir(); echo session_id(); echo get_tongdun_sec();?>
	<!-- 同盾JS  End-->
<style>
	#index_link:hover{
		text-decoration:underline;
	}
</style>
</head>
<body>
	<!-- <div class="pc_top">
		<div class="top_content">
			<div class="top_left">logo</div>
			<div class="top_right">
				<ul>
					<li><a href="#pc_content2">产品介绍</a></li>
					<li><a href="#pc_content4">快速关注</a></li>
					<li><a href="#pc_content3">业务流程</a></li>
					<li><a href="#pc_content5">联系我们</a></li>
				</ul>
			</div>
		</div>
	</div> -->
	<div class="top1_all" id="pc_nav">
		<div class="clearfix top1">
			<div class="top1_left">
				<div class="top1_d1" style="cursor:pointer;"><img title="现贷猫、贷款、网贷、身份证贷款、现金贷、随心贷、小额贷款、信用金、信用贷款、个人贷款、无抵押贷款、网上借钱、大学生贷款、快速审批、极速到账" src="/style/cash/images/logo2.png" alt=""></div>
			</div>
			<div class='top1_right'>
				<ul id="top_ul1">
					<li><a href="javascript:;">产品介绍</a></li>
					<li><a href="javascript:;">借款流程</a></li>
					<li><a href="javascript:;">快速关注</a></li>
					<li><a href="javascript:;">联系我们</a></li>
				</ul>
				<ul class="pc_line">
					<li></li>
					<li></li>
					<li></li>
					<li></li>
				</ul>
			</div>
		</div>
	</div>
	<div style="height:88px;display:none" id="top_none">
		
	</div>
	<div class="pc_content0">
		
	</div>
	<div id="pc_content2">
		<div class="content2_content" style="height:500px">
			<div>
				<div class="content2_title">我们的优势</div>
				<div class="content2_line"></div>
			</div>
			<div>
				<ul class="content_ul1">
					<li>
						<div><img src="/Style/cash/images/pc_img1.png" alt=""></div>
						<div>快速</div>
						<div>快速审批，极速到账</div>
					</li>
					<li>
						<div><img src="/Style/cash/images/pc_n_2.png" alt=""></div>
						<div>低门槛</div>
						<div>身份证+手机号码</div>
					</li>
					<li>
						<div><img src="/Style/cash/images/pc_n_3.png" alt=""></div>
						<div>无抵押</div>
						<div>无需抵押 无需担保</div>
					</li>
					<li>
						<div><img src="/Style/cash/images/pc_n_4.png" alt=""></div>
						<div>隐私保护</div>
						<div>高级别数据安保确保安全</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div id="pc_content3" style="-background:#f5f5f5;">
		<div class="content2_content" style="padding-bottom:27px;">
			<div>
				<div class="content2_title" style="color:#1dacfb;">借款流程</div>
				<div class="content2_line" style="background:#1dacfb;"></div>
			</div>
			<div>
				<ul class="content_ul2 clearfix">
					<li>
						<div><img src="/Style/cash/images/lc1.png" alt=""></div>
						<div>注册登录</div>
					</li>
					<li>
						<div><img src="/Style/cash/images/lc2.png" alt=""></div>
						<div>选择借款</div>
					</li>
					<li>
						<div><img src="/Style/cash/images/lc3.png" alt=""></div>
						<div>身份认证</div>
					</li>
					<li>
						<div><img src="/Style/cash/images/lc4.png" alt=""></div>
						<div>审核放款</div>
					</li>
				</ul>
			</div>
		</div>
	</div>
	<div id="pc_content4" style="height:490px;">
		<div class="content2_content">
			<div>
				<div class="content2_title">快速关注</div>
				<div class="content2_line"></div>
			</div>
			<div style="text-align:center;margin-top:40px;" class="clearfix">
				<div style="width:298px;height:298px;float:left;margin-left:200px;">
					<img width="100%" src="/Style/cash/images/pc_img2.png" alt="">
					<p style="color:#fff">扫码关注“现贷猫”微信公众号</p>
				</div>
				<div style="width:298px;height:298px;margin-right:200px;float:right">
					<img width="100%" src="/Style/cash/images/app_down.jpg" alt="">
					<p style="color:#fff">扫码下载“现贷猫”安卓版app</p>
				</div>
				<!-- <div class="content4_text1">现在微信扫一扫，即刻关注现贷猫微信公众号体验吧！</div> -->
			</div>
		</div>
	</div>
	<div id="pc_content5" style="background:#fff;">
		<div class="content2_content">
			<div>
				<div class="content2_title" style="color:#1dacfa;">联系我们</div>
				<div class="content2_line" style="background:#1dacfa;"></div>
			</div>
			<div class="content5_text2" style="margin-bottom:40px;">您的每一个疑问，我们都认真对待！</div>
			<div class="clearfix">
				<div class="content_left" style="float:left;">
					<ul class="content5_ul1">
						<li>
							<div><img src="/style/cash/images/pc_img4.png" alt=""></div>
							<div>客服：021-50818123</div>
						</li>
						<!-- <li>
							<div><img src="/style/cash/images/pc_img5.png" alt=""></div>
							<div>邮箱：service@fumi88.com</div>
						</li> -->
						<li>
							<div><img src="/style/cash/images/pc_img6.png" alt=""></div>
							<div>地址：上海市浦东南路379号金穗大厦8楼H座</div>
						</li>
					</ul>
				</div>
				<div style="width:349px;height:252px;border:#ccc solid 1px;margin-right:70px;float:right" id="map"></div>
			</div>
			<div align="center" style="padding-top:20px;font-size:12px;">
				<p><span id="index_link" onclick="window.open('http://www.miibeian.gov.cn/')" style="cursor: pointer;">沪ICP备15025008号-3</span></p>
				<script language="javascript" type="text/javascript" src="https://seal.wosign.com/tws.js"></script>
			</div>
		</div>
	</div>
	<div id="back_top">
		<img style="width:54px;height:54px;" src="/style/cash/images/back_top1.png" alt="返回顶部" title="返回顶部"/>
	</div>
<script>
 //创建和初始化地图函数：
 $(function(){
 	$("#back_top").click(function(){
 		$('body,html').animate({scrollTop:0},1000);
 	});
 	(function(){

 		var h = $(window).height() - 104;
 		var h2 = $(window).height() - 208;
 		$(".pc_content0").css("height",h);
  		var active = 0;
  		var arr = [0,527,1098,1588];
  		var b = $(window).height();
  		$(window).scroll(function(){
  			var a = $(window).scrollTop();
  			if(a > 0 && a < Math.floor(h2+arr[1])){
  				$('.pc_line li').css("width","0");
 				$('.pc_line li:eq(0)').css("width","78px");
 				active = 0;
  			}else if(a >= Math.floor(h2+arr[1]) && a < Math.floor(h2+arr[2])){
  				$('.pc_line li').css("width","0");
 				$('.pc_line li:eq(1)').css("width","78px");
 				active = 1;
  			}else if(a >= Math.floor(h2+arr[2]) && a < Math.floor(h2+arr[3])){
  				$('.pc_line li').css("width","0");
 				$('.pc_line li:eq(2)').css("width","78px");
 				active = 2;
  			}else if(a > Math.floor(h2+arr[3])){
  				$('.pc_line li').css("width","0");
 				$('.pc_line li:eq(3)').css("width","78px");
 				active = 3;
  			}
  		});
  		
  		$("#top_ul1 li a").each(function(index){
  			$(this).click(function(){	
				var top = h + arr[index];
				$('body,html').animate({scrollTop:top},500);
  			});
  		});
 		$('.pc_line li:eq(0)').css("width","78px");
 		$("#top_ul1 a").each(function(index){
			$(this).hover(function(){
				if(active == index){

				}else{
					$('.pc_line li:eq('+index+')').stop();
					$('.pc_line li:eq('+index+')').animate({width:78},200);
				}
				
			},function(){
				if(active == index){

				}else{
					$('.pc_line li:eq('+index+')').stop();
					$('.pc_line li:eq('+index+')').animate({width:0},200);
				}
			});
 		});
 	})();
 	$(window).scroll(function(){
 		if($(window).scrollTop() > 0){
 			$("#top_none").show();
 			$("#back_top").show();
 			$("#pc_nav").css(
 			{
 				"position":"absolute",
 				"left":"0",
 				"top":$(window).scrollTop(),
 				"box-shadow":"0 0 10px #888"
 			}
 				);

 		}else{
 			$("#back_top").hide();
 			$("#top_none").hide();
 			$("#pc_nav").css(
 			{
 				"position":"static",
 			}
 				);
 		}
 		
 	})
 	// $("#pc_nav").css
 })
    function initMap(){
      createMap();//创建地图
      setMapEvent();//设置地图事件
      addMapControl();//向地图添加控件
      addMapOverlay();//向地图添加覆盖物
    }
    function createMap(){ 
      map = new BMap.Map("map"); 
      map.centerAndZoom(new BMap.Point(121.517032,31.245012),16);
    }
    function setMapEvent(){
      map.enableScrollWheelZoom();
      map.enableKeyboard();
      map.enableDragging();
      map.enableDoubleClickZoom()
    }
    function addClickHandler(target,window){
      target.addEventListener("click",function(){
        target.openInfoWindow(window);
      });
    }
    function addMapOverlay(){
      var markers = [
        {content:"",imageOffset: {width:-46,height:-21},position:{lat:31.245612,lng:121.517032}}
      ];
      for(var index = 0; index < markers.length; index++ ){
        var point = new BMap.Point(markers[index].position.lng,markers[index].position.lat);
        var marker = new BMap.Marker(point,{icon:new BMap.Icon("http://api.map.baidu.com/lbsapi/createmap/images/icon.png",new BMap.Size(20,25),{
          imageOffset: new BMap.Size(markers[index].imageOffset.width,markers[index].imageOffset.height)
        })});
        /* var label = new BMap.Label(markers[index].title,{offset: new BMap.Size(25,5)}); */
        var opts = {
          width: 200,
          title: markers[index].title,
          enableMessage: false
        };
        var infoWindow = new BMap.InfoWindow(markers[index].content,opts);
        marker.setLabel(label);
        addClickHandler(marker,infoWindow);
        map.addOverlay(marker);
      };
      var labels = [
      ];
      for(var index = 0; index < labels.length; index++){
        var opt = { position: new BMap.Point(labels[index].position.lng,labels[index].position.lat )};
        var label = new BMap.Label(labels[index].content,opt);
        map.addOverlay(label);
      };
    }
    //向地图添加控件
    function addMapControl(){
      var scaleControl = new BMap.ScaleControl({anchor:BMAP_ANCHOR_BOTTOM_LEFT});
      scaleControl.setUnit(BMAP_UNIT_IMPERIAL);
      map.addControl(scaleControl);
      var navControl = new BMap.NavigationControl({anchor:BMAP_ANCHOR_TOP_LEFT,type:1});
      map.addControl(navControl);
      var overviewControl = new BMap.OverviewMapControl({anchor:BMAP_ANCHOR_BOTTOM_RIGHT,isOpen:false});
      map.addControl(overviewControl);
    }
    var map;
      initMap();
</script>
</body>
</html>