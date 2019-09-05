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
.alertDiv { margin: 0px auto; background-color: #FEFACF; border: 1px solid green; line-height: 25px; background-image: url(__ROOT__/Style/M/images/info/001_30.png); background-position: 20px 4px; background-repeat: no-repeat; }
.alertDiv li { margin: 5px 0; list-style-type: decimal; color: #005B9F; padding: 0px; line-height: 20px; }
.alertDiv ul { text-align: left; list-style-type: decimal; display: block; padding: 0px; margin: 0px 0px 0px 75px; }
</style>

<div class="so_main">
  <div class="page_tit">通知信息接口管理</div>
  <div class="page_tab"><span data="tab_1" class="active">短信参数</span></div>
  <div class="form2">
    <form method="post" action="__URL__/save" >
      <div id="tab_1">
        <!-- <div class="alertDiv">
          <ul>
            <li>当您需要短信充值时，可直接联系工作人员开通短信账户并充值！</li>
            <li>当您所选择的短信平台出现故障时，您可以选择”关闭短信平台服务”来暂时停止向会员发送短信的服务,以保证您系统其他操作的正常使用！</li>
          </ul>
        </div>-->
        <dl class="lineD">
          <dt>请选择短信服务：</dt>
          <dd>
            <?php $i=0;foreach($onoff_list as $k=>$v){ if(strlen("key1")==1&&$i==0){ ?><input type="radio" name="msg[sms][onoff]" value="<?php echo ($k); ?>" id="msg[sms][onoff]_<?php echo ($i); ?>" checked="checked" /><?php }elseif("key1"=="key1"&&$k==$sms_config["onoff"]){ ?><input type="radio" name="msg[sms][onoff]" value="<?php echo ($k); ?>" id="msg[sms][onoff]_<?php echo ($i); ?>" checked="checked" /><?php }elseif("key1"=="value1"&&$v==$sms_config["onoff"]){ ?><input type="radio" name="msg[sms][onoff]" value="<?php echo ($k); ?>" id="msg[sms][onoff]_<?php echo ($i); ?>" checked="checked" /><?php }else{ ?><input type="radio" name="msg[sms][onoff]" value="<?php echo ($k); ?>" id="msg[sms][onoff]_<?php echo ($i); ?>" /><?php } ?><label for="msg[sms][onoff]_<?php echo ($i); ?>"><?php echo ($v); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php $i++;} ?>
          </dd>
        </dl>
        <dl class="lineD">
          <dt>请选择短信供应商：</dt>
          <dd>
            <?php $i=0;foreach($type_list as $k=>$v){ if(strlen("key1")==1&&$i==0){ ?><input type="radio" name="msg[sms][type]" value="<?php echo ($k); ?>" id="msg[sms][type]_<?php echo ($i); ?>" checked="checked" /><?php }elseif("key1"=="key1"&&$k==$sms_config["type"]){ ?><input type="radio" name="msg[sms][type]" value="<?php echo ($k); ?>" id="msg[sms][type]_<?php echo ($i); ?>" checked="checked" /><?php }elseif("key1"=="value1"&&$v==$sms_config["type"]){ ?><input type="radio" name="msg[sms][type]" value="<?php echo ($k); ?>" id="msg[sms][type]_<?php echo ($i); ?>" checked="checked" /><?php }else{ ?><input type="radio" name="msg[sms][type]" value="<?php echo ($k); ?>" id="msg[sms][type]_<?php echo ($i); ?>" /><?php } ?><label for="msg[sms][type]_<?php echo ($i); ?>"><?php echo ($v); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php $i++;} ?>
          </dd>
        </dl>
        <div id="close">
          <dl class="lineD">
            <dt>当前短信服务状态:</dt>
            <dd>关闭</dd>
          </dl>
          <dl class="lineD">
            <dt>友情提示:</dt>
            <dd>当您停止短信服务时，系统中所有的操作都将不再向会员发送短信通知！</dd>
          </dl>
        </div>
        <div id="zucp">
          <dl class="lineD">
            <dt>短信充值请联系:</dt>
            <dd> <font style="color:green;font_size:14px">电话:13263413312   &nbsp;|&nbsp; QQ:2851336116 &nbsp;&nbsp;进行购买</font></dd>
          </dl>
          <dl class="lineD">
            <dt>当前剩余短信条数:</dt>
            <dd> <?php echo (($zucp)?($zucp):"0"); ?></dd>
          </dl>
          <dl class="lineD">
            <dt>帐号：</dt>
            <dd>
              <input name="msg[sms][user2]" id="msg[sms][user2]"  class="input" type="text" value="<?php echo ($sms_config["user2"]); ?>" >
            </dd>
          </dl>
          <dl class="lineD">
            <dt>密码：</dt>
            <dd>
              <input type="password" name="msg[sms][pwd2]" id="pwd2" value="<?php echo ($sms_config["pwd2"]); ?>" class="input" />
            </dd>
          </dl>
        </div>
        <div id="dahan">
          <dl class="lineD">
            <dt>帐号：</dt>
            <dd>
              <input name="msg[sms][user1]" id="msg[sms][user1]"  class="input" type="text" value="<?php echo ($sms_config["user1"]); ?>" >
            </dd>
          </dl>
          <dl class="lineD">
            <dt>密码：</dt>
            <dd>
              <input type="password" name="msg[sms][pwd1]" id="pwd1" value="<?php echo ($sms_config["pwd1"]); ?>" class="input" />
            </dd>
          </dl>
        </div>
        
      </div>
      <!--tab2-->
      <div class="page_btm">
        <input type="submit" class="btn_b" value="确定" />
        <span style="color:#CCCCCC">(所有方式修改提交一次即可)</span> </div>
    </form>
  </div>
  <script language=javascript type="text/javascript" >
$(document).ready(function() {
	var b_onoff = $(":input[name='msg[sms][onoff]']:checked").val();
	var b_type  = $(":input[name='msg[sms][type]']:checked").val();
	if(b_onoff==0){
		if(b_type==2){
			$("#zucp").show(); 
			$("#dahan").hide(); 
			$("#close").hide();
		}
		if(b_type==1){
			$("#zucp").hide(); 
			$("#dahan").show(); 
			$("#close").hide();
		}
	}else{
		$("#zucp").hide();
		$("#dahan").hide();
		$("#close").show();
	}
});
$(function(){
	$(":input[name='msg[sms][onoff]']").click(function(){
		var b_type  = $(":input[name='msg[sms][type]']:checked").val();
	  	if($(this).attr("value")=="0"){
	  		if(b_type==2){
	  			$("#zucp").show(); 
	  			$("#dahan").hide();
				$("#close").hide();
	  		}else{
	  			$("#zucp").hide();
	  			$("#dahan").show(); 
				$("#close").hide();
	  		}
			
	  	}else{
			$("#zucp").hide();
			$("#dahan").hide();
			$("#close").show();
	  	}
	});
	
	$(":input[name='msg[sms][type]']").click(function(){
		var b_onoff  = $(":input[name='msg[sms][onoff]']:checked").val();
	  	if($(this).attr("value")=="2"){
	  		if(b_onoff==0){
	  			$("#zucp").show(); 
	  			$("#dahan").hide();
				$("#close").hide();
	  		}else{
	  			$("#zucp").hide();
				$("#dahan").hide();
				$("#close").show();
	  		}
	  	}else{
	  		if(b_onoff==0){
	  			$("#zucp").hide();
	  			$("#dahan").show(); 
				$("#close").hide();
	  		}else{
				$("#zucp").hide();
				$("#dahan").hide();
				$("#close").show();
	  		}
	  	}
	});
});
</script>
</div>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>