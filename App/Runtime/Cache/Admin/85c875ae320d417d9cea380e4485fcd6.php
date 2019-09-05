<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo ($ts['site']['site_name']); ?>管理后台</title>
<link href="__ROOT__/Style/A/css/style.css" rel="stylesheet" type="text/css">
<link href="__ROOT__/Style/A/js/tbox/box.css" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" href="__ROOT__/Style/JBox/Skins/Blue/jbox.css"/><!-- `mxl:teamreward` --><!-- 2014.10.13增补 -->
<script type="text/javascript" src="__ROOT__/Style/A/js/jquery.js"></script>
<script type="text/javascript" src="__ROOT__/Style/A/js/common.js"></script>
<script type="text/javascript" src="__ROOT__/Style/A/js/tbox/box.js"></script>
<script type="text/javascript" src="/Style/My97DatePicker/WdatePicker.js" language="javascript"></script>
<script  src="__ROOT__/Style/JBox/jquery.jBox.min.js" type="text/javascript"></script><!-- `mxl:teamreward` -->
<script  src="__ROOT__/Style/JBox/jquery.jBoxConfig.js" type="text/javascript"></script><!-- `mxl:teamreward` -->
</head>
<body>
<style type="text/css">
.quxiantu{ margin-top:30px;}
.qleft{ float:left; width:50%; text-align:left;}
.qright{ float:right; width:50%; text-align:right;}

.ssx a{height:30px; line-height:30px}
.lf{
    float:left;
    width:48%; border:1px solid #c7d8ea; margin: 10px;
}
.lf h6{
    border-bottom: 1px solid #c7d8ea;
    color: #3a6ea5;
    height: 26px;
    line-height: 28px;
    padding: 0 10px;
    font-size: 13px;
}
.lf .content{
    padding: 9px 10px;
    line-height: 22px;
}
.lf .content a{
    color:red;
    
}
</style>
<script type="text/javascript" src="__ROOT__/Style/Js/highcharts.js"></script>
<script type="text/javascript" src="__ROOT__/Style/Js/exporting.js"></script>
<div class="so_main">
	<div class="page_tit">欢迎页</div>
  	<!--列表模块-->
  	<div class="Toolbar_inbox">
    	<div class="page right">
			当前时间<span id="clock"></span>
    	</div>
    	<a href="javascript:;" class="btn_a"><span>欢迎登陆</span></a></div>
		<script>
			function changeClock()
			{
				var d = new Date();
				document.getElementById("clock").innerHTML = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + ":" + d.getSeconds();
			}
			window.setInterval(changeClock, 1000);
		</script>  
	<div class="lf">
	    <h6>个人信息</h6>
	    <div class="content">
	        您好，<?php echo ($user["user_name"]); ?>
	        <br />
	        所属角色：<?php echo ($user["groupname"]); ?> 
	        <br />
	        上次登录时间：<?php echo (date('Y-m-d H:i:s',$user["last_log_time"])); ?>
	        <br />
	        上次登录IP：<?php echo ($user["last_log_ip"]); ?>   
	    </div>
	</div>
	<div class="lf">
		<h6>系统信息</h6>
		<div class="content">
	    	<div style="float: left; width:300px;">
	        	福米金融现金猫管理系统
	     	</div>
	    	<div style="float: left;">
	        	操作系统：<?php echo ($service["service_name"]); ?> 
	     	</div>
	     	<br />
			<div style="float: left; width:300px;">
	       		服务器软件：<?php echo ($service["service"]); ?>
	     	</div>
	    	<div style="float: left;">
	        	MySQL 版本：<?php echo ($service["mysql"]); ?>
	     	</div>
	     	<br />
		 	<div style="float: left; width:300px;">
	     		服务器协议：<?php echo ($_SERVER['SERVER_PROTOCOL']); ?>
	     	</div>
	    	<div style="float: left;">
	      		服务器名称：<?php echo ($_SERVER['SERVER_NAME']); ?>
	     	</div>
	     	<br />
		 	<div style="float: left; width:300px;">
	      		PHP运行方式：<?php echo strtoupper(php_sapi_name())?>
	     	</div>
	    	<div style="float: left;">
	      		PHP版本：<?php echo PHP_VERSION?>
	     	</div>
			<br />
		 </div>
	</div>
	<!-- <div class="lf">
    	<h6>网站运行数据</h6>
    	<div class="content">
	    	<div style="float: left; width:300px;">
	         	注册用户总数：<?php if($count > 0): ?><a href="__APP__/admin/members/daCount" ><?php echo ($count); ?></a><?php else: ?> 0<?php endif; ?>个
	     	</div>
	     	<div style="float: left; width:300px;">
	         	申请借款用户总数：<?php if($countBorrow > 0): ?><a href="__APP__/admin/members/daCount" ><?php echo ($countBorrow); ?></a><?php else: ?> 0<?php endif; ?>个
	     	</div>
	     	<br />
    		<div style="float: left; width:300px;">
      			放款总金额：<?php if($loanMoney > 0): ?><a href="__APP__/admin/borrow/waitverify2.html"><?php echo (fmoney($loanMoney)); ?></a><?php else: ?> 0.00<?php endif; ?>元
     		</div>
	 		<div style="float: left; width:300px;">  
      		 	回款到账总金额：<?php if($repayment > 0): ?><a href="__APP__/admin/vipapply/index?status=0"><?php echo (fmoney($repayment)); ?></a><?php else: ?> 0.00<?php endif; ?>元
     		</div>
	    </div>
	</div>-->
	<div class="lf">
	    <h6>现金猫管理系统</h6>
	    <div class="content">
	    	版权所有：福米互联网金融信息服务(上海)有限公司
	        <br />    
	       	 官方网站：<a href="http://www.cashmall.com.cn" target="_blank">http://www.cashmall.com.cn</a> 
	    </div>
	</div>
</div>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>