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
	.sel_fs{width:110px}
</style>
<link href="__ROOT__/Style/Swfupload/swfupload.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="__ROOT__/Style/Swfupload/handlers.js"></script>
<script type="text/javascript" src="__ROOT__/Style/Swfupload/swfupload.js"></script>

<div class="so_main">
	<div class="page_tit">查询借款进度</div>
	<div class="form2">
		<div id="tab_5" >
	        <dl class="lineD">
	        	<dt>手机号码：</dt>
	        	<dd>
		        	<input name="mobile" id="mobile" style="width:150px;" class="input" type="text" value="" >
	        	</dd>
	        	<dt>会员编号：</dt>
	        	<dd>
		        	<input name="uid" id="uid" style="width:150px;" class="input" type="text" value="" >
	        	</dd>
	        	<dd>
		        	<input type="button" onclick="dolist()" id="submit" value="查询" />&nbsp;&nbsp;&nbsp;
	        	</dd>
	        </dl>
	    </div>
		<div class="list">
			<div id="searchList"></div>
		</div>
	</div>
</div>
<script type="text/javascript">
	function dolist(id){
		$("#searchList").html("");
		var mobile = $("#mobile").val();
		var uid    = $("#uid").val();
		if (mobile == "" && uid == "") {
			alert("请输入手机号码或者会员编号");
			return false;
		}
		var data = {'mobile':mobile,'uid':uid};
		$.post("__URL__/doapply", data, function(res){
			$("#searchList").html(res.message);
		}, 'json');
		
	}
</script>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>