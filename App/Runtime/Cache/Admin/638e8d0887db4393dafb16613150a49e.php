<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/Style/A/css/adminlogin.css" rel="stylesheet" type="text/css" />
<title>管理员登陆</title>
 <style type="text/css">
#gg { 
position:fixed;
text-align:center;
color:#666;
width:100%;
opacity:.99; 
filter:alpha(opacity=99);
line-height:30px;
 _width:100%;
 _position:absolute; 
_top:expression(eval(document.documentElement.scrollTop+document.documentElement.clientHeight-this.offsetHeight-
(parseInt(this.currentStyle.marginTop, 10)||0)-(parseInt(this.currentStyle.marginBottom, 10)||0)));
}
#gg a { color:#333; font-size:13px; margin:0px auto; }
*{margin:0; padding:0}
ul,ol{ list-style:none}

body{
	margin:0; 
	padding:0;
	background:url(/Style/A/images/body_bg.gif) 50% top;
	MARGIN:0px 0px;
 }
.content_ht{
	width:1000px;
	margin:0px auto;
	height:100%;
}
.content_right{ width:500px;}
.content_left{
	float:left;
	width:500px;
	background:url(/Style/A/images/left_line.gif) no-repeat right center;
	height:460px;}
.left_weizi{
	width:500px; 
	margin:20% auto 0px auto; 
	height:300px;background:url(/Style/A/images/login_bg1.png) no-repeat right center;}

.LOGONEW{
	background:url(/Style/A/images/) no-repeat 50% top; 
	width:500px; 
	padding:80px 0 0 50px; 
	height:300px
}


.LOGONEW1{ width:260px; float:left}
.admin_bg{font-weight:bold;font-size:16px;background:url(/Style/A/images/admin_bg.gif) no-repeat left center;padding-left:25px;height:22px;line-height:22px;margin-bottom:10px;}
.bodybox ul li{height:37px;line-height:37px;}
.LOGONEW1 li input{ border:0px solid #008000}
.bodybox{ margin-left:20px;}
.logn2 img{cursor:pointer; margin-bottom:-5px;}
.lognAD4{ float:left;background:url(/Style/A/images/user_botton00.gif); height:83px; width:83px; padding:0;margin-top:60px;}
.unpa{height:22px; line-height:22px; font-size:16px; font-weight:bold; font-family:Arial, Helvetica, sans-serif;  width:146px; border:none;background: url(/Style/A/images/inputbg.gif) no-repeat;padding-left:4px;}
.code{height:22px; line-height:22px; font-size:16px; font-weight:bold; font-family:Arial, Helvetica, sans-serif; padding-left:4px; width:79px;
background: url(/Style/A/images/b_inputbg.gif) no-repeat;margin-right:10px;}
.lognAD4 a{text-indent:-10000px; display:block;cursor: pointer;  width:83px; height:83px; overflow:hidden; }
.logn3 img{margin-left:25px; margin-top:4px;}
.footer_bg{margin:0px auto;text-align:center; width:100%}
.footer_img{height:50px;font-size:12px;color:#fff;padding-top:10px;background:url(/Style/A/images/footer_010.gif) no-repeat bottom right;}
*html .logn3{padding:13px 143px 0px 300px;}
*html .logn4{padding:20px 460px 0 20px;}
</style>
</head>

<body>

<div style="width:100%; height:100%; background:url(/Style/A/images/bg-login.jpg) repeat-x 50% top; margin:0 auto;"> 
	<!--<div class="content_ht" style="background:url(/Style/A/images/logo_login1..png)  no-repeat;width:450px;height:160px;">-->
		   <div class="content_ht" style="width:450px;height:160px;">
		</div>
	
	
		<center>
		<div class="content_right">
			<div class="LOGONEW">
				<form method="post" action="/admin/index/login" name="form" id="form"> 
					<div class="bodybox">  	
						<ul class="LOGONEW1">
						<li class="admin_bg" >管理员登录</li> 
							<li>用户名：<input type="text" class="unpa" id="admin_name" name="admin_name" /></li>            
							<li>密&#12288;码：<input type="password" class="unpa" id="admin_pass" name="admin_pass" /></li>
							<li>口&#12288;令：<input type="text" class="unpa" id="user_word" name="user_word" /></li>
							<li class="logn2">验证码：<input type="text" class="code" id="code" name="code" maxlength="4" />
							<img  src="/admin/index/verify" onclick="javascript:this.src='/admin/index/verify?'+new Date().getTime()" />
							</li>            
						</ul>
						<div class="lognAD4"><a id="btnReg" onclick="javascript:subform();" onfocus="this.blur();" title="登陆">登陆</a></div>
					</div>
				</form>
			</div>
		
		</div>
		</center>



<script type="text/javascript">
	function subform(){
		var frm = document.forms['form'];
			frm.submit();
	}

	function keyUp(e) {  
           var currKey=0,e=e||event; 
           currKey=e.keyCode||e.which||e.charCode;
 		   if(currKey==13){
 			  document.getElementById("btnReg").click();
		 }
    } 

   document.getElementById("form").onkeydown = keyUp;
   </script>

	  
    <span id="gg">
       
COPYRIGHT @fumi88.com All Rights Reserved. 福米互联网金融信息服务(上海)有限公司 版权所有
 
      
	  
    </span>
	
</div>
</body>