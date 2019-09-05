<?php if (!defined('THINK_PATH')) exit();?><html>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<body>
<style type="text/css">
/*成功 | 错误 提示*/
* { word-wrap:break-word }
html{width:100%;height:100%;}
body {position:relative;font:12px Microsoft YaHei,Arial,Helvetica,sans-serif,Simsun; text-align:center; color:#333;width:100%;height:100%;}
body, div, dl, dt, dd, ul, ol, li, pre, form, fieldset, blockquote, h1, h2, h3, h4, h5, h6,p{ padding:0; margin:0 }
h1, h2, h3, h4, h5, h6 { font-weight: normal; }
table, td, tr, th { font-size:12px }
li { list-style-type:none }
table { margin:0 auto }
img { border:none }
ol, ul { list-style:none }
caption, th { text-align:left }
.Prompt_top, .Prompt_btm, .Prompt_ok, .Prompt_x { background:url(/Style/tip/images/message.gif) no-repeat; display:inline-block }
.Prompt { width:640px; margin:100px auto 180px; text-align:left; }
.Prompt_top { background-position:0 0; height:15px; width:100%; }
.Prompt_con { width:100%; border-left:1px solid #E7E7E7; border-right:1px solid #E7E7E7; background:#fff; overflow:hidden;}
.Prompt_btm { background-position:0 -27px; height:6px; width:100%; overflow:hidden; }
.Prompt_con dl { overflow:hidden;border-left:1px solid #E7E7E7; border-right:1px solid #E7E7E7; background:#fff;}
.Prompt_con dt { width:100%; font-size:18px; padding:15px; border-bottom:2px solid #E7E7E7;font-weight: bold;_height:20px;}
.Prompt_con dd { float:left; display:block; padding:15px; }
.Prompt_con dd h2 { font-size:14px; line-height:30px; }
.Prompt_ok { background-position:-72px -39px; width:68px; height:68px; }
.Prompt_x { background-position:0 -39px; width:68px; height:68px; }
.Prompt_con a.a { color:#fff; padding:0 15px; line-height:30px; background-color:#307ba0; display:inline-block; font-size:14px; margin:20px 0px; }
/*2016.8新改版*/
.prompPage{
  position:absolute;
  top:50%;
  left:50%;
  margin-top:-150px;
  margin-left:-350px;
  width:700px;
  height:300px;
  color:#67676b;
}
.prompPage:after{
  content:"";
  display:block;
  clear:both;
  visibility:hidden;
}
.prompPage .left{
  float:left;
}
.prompPage .right{
  float:right;
  margin-top:30px;
}
.prompPage .home{
  vertical-align:text-bottom;
}
.prompPage .index{
  color:#ff6622;
  text-decoration:none;
}
.prompPage h1{
  font-weight:bold;
  font-size:24px;
  text-align:left;
}
.prompPage p{
  margin-top:20px;
  font-size:16px;
}
.prompPage p img{
  vertical-align:text-bottom;
}
.prompPage_m{
  position:absolute;
  top:50%;
  left:50%;
  transform:translate(-50%,-50%);
  -webkit-transform:translate(-50%,-50%);
  -o-transform:translate(-50%,-50%);
  -ms-transform:translate(-50%,-50%);
  -moz-transform:translate(-50%,-50%);
  width:90%;
  color:#67676b;
}
.prompPage_m:after{
  content:"";
  display:block;
  clear:both;
  visibility:hidden;
}
.prompPage_m .left{
  /*float:left;*/
}
.prompPage_m .right{
  /*float:right;*/
  margin-top:30px;
}
.prompPage_m .left img{
  width:50%;
}
.prompPage_m .home{
  vertical-align:text-bottom;
}
.prompPage_m .index{
  color:#ff6622;
  text-decoration:none;
}
.prompPage_m h1{
  font-weight:bold;
  font-size:16px;
  text-align:center;
}
.prompPage_m p{
  margin-top:20px;
  font-size:14px;
}
.prompPage_m p img{
  vertical-align:text-bottom;
}
</style>
<script>
function Jump(){
    window.location.href = '<?php echo ($jumpUrl); ?>';
}
document.onload = setTimeout("Jump()" , <?php echo ($waitSecond); ?>* 1000);
</script>
<base target="_self" />

<?php if(($status) == "1"): ?><div class="Prompt">
  <div class="Prompt_top"></div>
  <div class="Prompt_con">
    <dl>
      <dt>提示信息</dt>
      <dd><span class="Prompt_ok"></span></dd>
      <dd>
        <h2><?php echo ($message); ?></h2>
        <?php if(isset($closeWin)): ?><p>系统将在 <span style="color:blue;font-weight:bold"><?php echo ($waitSecond); ?></span> 秒后自动关闭，如果不想等待,直接点击 <A HREF="<?php echo ($jumpUrl); ?>">这里</A> 关闭</p><?php endif; ?>
        <?php if(!isset($closeWin)): ?><p>系统将在 <span style="color:blue;font-weight:bold"><?php echo ($waitSecond); ?></span> 秒后自动跳转,如果不想等待,直接点击 <A HREF="<?php echo ($jumpUrl); ?>">这里</A> 跳转</p><?php endif; ?>
      </dd>
    </dl>
    <div class="c"></div>
    </div>
    <div class="Prompt_btm"></div>
  </div><?php endif; ?>
<?php if(($status) == "0"): if(isset($closeWin)): ?><div class="Prompt">
        <div class="Prompt_top"></div>
        <div class="Prompt_con">
          <dl>
            <dt>提示信息</dt>
            <dd><span class="Prompt_x"></span></dd>
            <dd>
              <h2 style="color:red"><?php echo ($error); ?></h2>
              <p>系统将在 <span style="color:blue;font-weight:bold"><?php echo ($waitSecond); ?></span> 秒后自动关闭，如果不想等待,直接点击 <A HREF="<?php echo ($jumpUrl); ?>">这里</A> 关闭</p>
            </dd>
          </dl>
          <div class="c"></div>
        </div>
        <div class="Prompt_btm"></div>
      </div><?php endif; ?>
  <?php if(!isset($closeWin)): if(stripos($error,'非法操作，请与管理员联系') == '0'): ?><div class="Prompt">
	        <div class="Prompt_top"></div>
	        <div class="Prompt_con">
	          <dl>
	            <dt>提示信息</dt>
	            <dd><span class="Prompt_x"></span></dd>
	            <dd>
	              <h2 style="color:red"><?php echo ($error); ?></h2>
	              <p>系统将在 <span style="color:blue;font-weight:bold"><?php echo ($waitSecond); ?></span> 秒后自动跳转,如果不想等待,直接点击 <A HREF="<?php echo ($jumpUrl); ?>">这里</A> 跳转<br/>
					  或者 <a href="__ROOT__/">返回首页</a></p>
	            </dd>
	          </dl>
	          <div class="c"></div>
	        </div>
	        <div class="Prompt_btm"></div>
	      </div>
	  <?php else: ?>
	  	<div class="prompPage">
	        <div class="left">
	          <img src="/Style/pc/images/prompt-QR.jpg" alt="">
	        </div>
	        <div class="right">
	          <h1>来福米的朋友真是太多了，有点忙不过来了</h1>
	          <h1>大家不要着急，可以先去<a href="__ROOT__/" class="index">&nbsp;<img src="/Style/pc/images/prompt-index.jpg" alt="" class="home">&nbsp;首页</a>逛逛</h1>
	          <p>如有任何疑问，请致电客服热线：400-788-5018
	          <br />或联系福米在线客服
	              <a target="_blank" href="http://wpa.qq.com/msgrd?v=3&uin=1051815029&site=qq&menu=yes"><img border="0" src="http://wpa.qq.com/pa?p=2:1051815029:51" alt="点击这里联系我们" title="点击这里联系我们"/></a>
	          </p>
	        </div>
	    </div>
	    <!-- 加油霸移动端 -->
	    <!-- <div class="prompPage_m">
	        <div class="left">
	          <img src="/Style/tip/images/oil-tip-QR.jpg" alt="">
	        </div>
	        <div class="right">
	          <h1>来加油霸的朋友真是太多了，<br />有点忙不过来了</h1>
	          <h1>大家不要着急，<br />可以先去<a href="__ROOT__/" class="index">&nbsp;<img src="/Style/pc/images/prompt-index.jpg" alt="" class="home">&nbsp;首页</a>逛逛</h1>
	          <p>如有任何疑问，<br />请致电客服热线：400-788-5018
	          </p>
	        </div>
	    </div> --><?php endif; endif; endif; ?>
</body>
</html>