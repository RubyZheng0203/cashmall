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
<link href="__ROOT__/Style/Swfupload/swfupload.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="__ROOT__/Style/cash/css/viewer.min.css">
<script type="text/javascript" src="__ROOT__/Style/Swfupload/handlers.js"></script>
<script type="text/javascript" src="__ROOT__/Style/Swfupload/swfupload.js"></script>
<style type="text/css">
    .x_member_form{width:1000px; overflow:hidden}
    .x_member_form .form2{height:auto; overflow:auto;}
    .x_member_form .form2 .lineD{overflow:hidden}
    .x_member_form .form2 .lineD dt{width:150px; color:#9CB8CC; font-weight:bold}
    .x_member_form .form2 .lineD dd{width:200px; float:left; margin-left:0px}
    .x_member_form .form2 .lineD dd.xwidth{width:500px;}
    .x_member_form .form2 .lineD dt.title{color:#F00}
    ul li{list-style: none;}
</style> 
<div class="so_main x_member_form">
    <div class="page_tit">待授信的处理</div> 
    <div class="form2">
        <form method="post" action="__URL__/revsave" onsubmit="return subcheck();">
            <input type="hidden" name="bid" value="<?php echo ($bid); ?>" />
            <input type="hidden" name="uid" value="<?php echo ($uid); ?>" />
            <div id="tab_1">
                <dl class="lineD"><dt>处理结果：</dt><dd><?php $i=0;$___KEY=array ( 1 => '通过', 2 => '不处理', ); foreach($___KEY as $k=>$v){ if(strlen("1")==1 && $i==0){ ?><input type="radio" name="status" value="<?php echo ($k); ?>" id="status_<?php echo ($i); ?>" checked="checked" /><?php }elseif(("1"=="key1"&&$_X["_Y"]==$k)||(""=="value"&&$_X["_Y"]==$v)){ ?><input type="radio" name="status" value="<?php echo ($k); ?>" id="status_<?php echo ($i); ?>" checked="checked" /><?php }else{ ?><input type="radio" name="status" value="<?php echo ($k); ?>" id="status_<?php echo ($i); ?>" /><?php } ?><label for="status_<?php echo ($i); ?>"><?php echo ($v); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php $i++; } ?></dd></dl>
        		<dl class="lineD"><dt>原因：</dt><dd><textarea name="reason"  id="reason" /></textarea></dd></dl>
                <div class="page_btm">
                    <input type="submit" class="btn_b" value="提交" />
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript">
        var cansub = true;
        function subcheck() {
            if (!cansub) {
                alert("请不要重复提交，如网速慢，请耐心等待！");
                return false;
            }
            cansub = false;
            return true;

        }
    </script>
    <script src="__ROOT__/Style/js/viewer.min.js"></script>
    <script>
        var viewer = new Viewer(document.getElementById('jq22'), {
            url: 'data-original'
        });
    </script>
</div>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>