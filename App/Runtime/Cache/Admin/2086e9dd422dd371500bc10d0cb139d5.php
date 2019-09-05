<?php if (!defined('THINK_PATH')) exit();?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>public</title>
<link rel="stylesheet" href="css/base.css" type="text/css" />
<link rel="stylesheet" href="__ROOT__/Style/A/css/style.css">
<link rel="stylesheet" href="__ROOT__/Style/A/css/frame.css">  
<script type="text/javascript" src="__ROOT__/Style/A/js/jquery.js"></script>
<script type="text/javascript" src="__ROOT__/Style/A/js/common.js"></script>
<script type="text/javascript" src="__ROOT__/Style/A/js/tbox/box.js"></script>
<script type="text/javascript" src="__ROOT__/Style/A/js/leftmenu.js"></script>
<?php
echo "<script language='javascript'>var curopenItem = '$openitem';</script>\r\n"; ?>
<script language="javascript" type="text/javascript">
/* 按下F5时仅刷新iframe页面 */
function inactiveF5(e) {
	return ;
	e=window.event||e;
	var key = e.keyCode;
	if (key == 116){
		parent.MainIframe.location.reload();
		if(document.all) {
			e.keyCode = 0;
			e.returnValue = false;
		}else {
			e.cancelBubble = true;
			e.preventDefault();
		}
	}
}
function nof5() {
    return ;
	if(window.frames&&window.frames[0]) {
		window.frames[0].focus();
		for (var i_tem = 0; i_tem < window.frames.length; i_tem++) {
			if (document.all) {
				window.frames[i_tem].document.onkeydown = new Function("var e=window.frames[" + i_tem + "].event; if(e.keyCode==116){parent.MainIframe.location.reload();e.keyCode = 0;e.returnValue = false;};");
			}else {
				window.frames[i_tem].onkeypress = new Function("e", "if(e.keyCode==116){parent.MainIframe.location.reload();e.cancelBubble = true;e.preventDefault();}");
			}
		} //END for()
	} //END if()
}

function refresh() {
	parent.MainIframe.location.reload();
}
document.onkeydown=inactiveF5;

function txxt(){
	ui.box.load("/admin/common/sms", {title:"通讯系统"});
}
</script> 
<style>
div {
	padding:0px;
	margin:0px;
}
body {
	padding:0px;
	margin:auto;
	text-align:center;
	background-color:#103D67;
	padding-left:3px;
	overflow:scroll;
	overflow-x:hidden;
	overflow-y:hidden;
	scrollbar-face-color: #103D67;
	scrollbar-shadow-color: #103D67;
	scrollbar-highlight-color: #103D67;
	scrollbar-3dlight-color: #103D67;
	scrollbar-darkshadow-color: #103D67;
	scrollbar-base-color: #103D67;
	scrollbar-track-color: #103D67;
	scrollbar-arrow-color: #103D67;
	
}
dl.bitem {
	clear:both;
	width:140px;
	margin:0px 0px 5px 12px;
	background:url(/Style/A/images/menunewbg.gif) repeat-x;
}
li.bitem {
	clear:both;
	width:140px;
	margin:0px 0px 5px 12px;
	background:url(/Style/A/images/menunewbg.gif) repeat-x;
}


dl.bitem2 {
	clear:both;
	width:140px;
	margin:0px 0px 5px 12px;
	background:url(/Style/A/images/menunewbg2.gif) repeat-x;
}
dl.bitem dt, dl.bitem2 dt {
	height:25px;
	line-height:25px;
	padding-left:10px;
	cursor:pointer;
}
dl.bitem dt b, dl.bitem2 dt b {
	color:#243E4D;
	font-size:14px;
}
dl.bitem dd, dl.bitem2 dd {
	padding:3px 3px 3px 10px;
	background-color:#EAF0F3;
}


div.items {
	clear:both;
	padding:0px;
	height:0px;
}
.fllct {
	float:left;
	width:85px;
}

.fllct  a{
	color:#48575A;}
.flrct {
	padding-top:3px;
	float:left;
}
.sitemu li {
	padding:0px 0px 0px 3px;
	line-height:25px;
	height:25px;
}
.sitemu li a{
	color:#32556B;
	display:block;
	background:url(/Style/A/images/aaa.gif) no-repeat left center;
	padding-left:7px;}
	
	.sitemu li a:visited{
		color:#32556B;}
	
	.sitemu li a:hover{
		display:block;
		color:#FFB436;
		font-weight:bold;
		text-decoration:underline;
		/**background:url(/Style/A/images/aaa2.gif) no-repeat left center;**/}
		
	
ul {
	padding-top:3px;
}
li {
	height:22px;
}
a.mmac div11 {
	background:url(/Style/A/images/leftbg1.gif) no-repeat;
	height:auto!important;
	padding:5px 4px 4px 10px;
	word-wrap: break-word;
	word-break : break-all;
	font-weight:bold;
	color:#fff;
	margin:0px 0;
	overflow:hidden;
	
}
 
a.mm div {
	background:url(/Style/A/images/leftmbg123.gif) no-repeat;
	height:auto!important;
	padding:5px 4px 5px 10px;
	word-wrap: break-word;
	word-break : break-all;
	color:#666;
	cursor:pointer;
	overflow:hidden;
	font-weight:bold;
        margin-top: 3px;
        line-height:22px;
}
table a.mm div:hover{
	background:url(/Style/A/images/leftbg1.gif) no-repeat;
	height:auto!important;
	padding:5px 4px 5px 10px;
	word-wrap: break-word;
	word-break : break-all;
	color:#fff;
	margin:0px 0;
	overflow:hidden;
	font-weight:bold;
        margin-top: 3px;
        line-height:22px;
}
a.mmac div {
	background:url(/Style/A/images/leftbg1.gif) no-repeat;
	height:auto!important;
	padding:5px 4px 5px 10px;
	word-wrap: break-word;
	word-break : break-all;
	color:#fff;
	margin:0px 0;
	overflow:hidden;
	font-weight:bold;
        margin-top:3px;
        line-height:22px;
}


.mmf {
	height:1px;
	padding:5px 7px 5px 7px;
}
#mainct {
	background: url(/Style/A/images/idnbg1.gif) repeat-y;
}
</style>
<link href="images/style/style.css" rel="stylesheet" type="text/css" />
<base target="main" />
</head>
       
<body target="main" onLoad="CheckOpenMenu();">
<table width="180" align="left" border='0' cellspacing='0' cellpadding='0' style="text-align:left;font-size: 13px; " >
  <tr>
    <td valign='top' style="padding-top:38px;width:22px;padding-right:1px;">
	<!--<a id='link1' class='mmac'><div onClick="ShowMainMenu(1)">全局</div></a> -->
	<?php foreach($menu_left as $keyt => $menu_1) {if($menu_1[2]==0) continue; ?>
	<a href="javascript:void(0);" target="_self"   class='mm' id="link<?php echo $keyt; ?>" onClick="switchChannel('<?php echo $keyt; ?>');" hidefocus="true" style="outline:none;"><div><?php echo $menu_1[0]; ?></div></a>
	<?php } ?>
      <div class='mmf'></div>
    </td>
      
    <td  height="100%" valign="top" id="FrameTitle" background="__ROOT__/Style/A/images/idnbg1.gif">
        <div><img src='/Style/A/images/idnbgfoot2.jpg' border="0" usemap="#Map" /></div>
  	<div class="LeftMenu">
            <?php $iterator = 1; $home_url = ''; $j = 1 ; ?>
  	<!-- 第一级菜单，即大频道 -->
            <?php foreach($menu_left as $key => $menu_1) { ?>
                <ul class="MenuList" id="root_<?php echo ($key); ?>" style="display:none;">
                        <!-- 第二级菜单 -->	
                    <?php foreach($menu_1['low_title'] as $key2 => $menu_2) { if($menu_2[2]==0) continue;?>
                    <li class="treemenu" id="munu_qh">
                    <a id="root_<?php echo ($key2); echo ($iterator); ?>" class="actuator"  onClick="switch_root_menu('<?php echo ($key2); echo ($iterator); ?>');" hidefocus="true" style="outline:none;cursor:pointer;"><b><?php echo $menu_2[0]; ?></b></a>
                        <ul id="tree_<?php echo ($key2); echo ($iterator); ?>" class="submenu">
                        <!-- 第三级菜单 -->
                            <?php foreach($menu_1[$key2] as $key3 => $menu_3) { if($menu_3[2]==0) continue;?>
                            <!--<?php $home_url = empty($home_url) ? $menu_3_url : $home_url; ?>-->
                                <li class="popo"><a id="menu_<?php echo ($j); ?>"  onClick="switch_sub_menu('<?php echo ($j); ?>', '<?php echo ($menu_3['1']); ?>');" class="submenuA" hidefocus="true" style="outline:none;cursor:pointer;"><?php echo ($menu_3['0']); ?></a></li>
                            <?php $j++ ;} ?>
                            <!-- 第三级菜单 -->
                        </ul>
                    </li>			
                    <?php } ?>
                    <!-- 第二级菜单 -->
                    </ul>
	<?php ++ $iterator;} ?>
  	<!-- 第一级菜单，即大频道 -->
	</div>
    </td>
	
	
  </tr>
  <tr>
    <td width='26'></td>
    <td width='160' valign='top'><img src='/Style/A/images/idnbgfoot.gif' /></td>
  </tr>
</table>
 
<map name="Map" id="Map">
    <area shape="rect" coords="2,2,79,36" />
    <area shape="rect" coords="84,1,158,35" />
</map>
</body>

<script type="text/javascript">
	var current_channel   = null;
	var current_menu_root = null;
	var current_menu_sub  = null;
	var viewed_channel	  = new Array();
	
	$(document).ready(function(){
		switchChannel('0');
	});
	//切换频道（即头部的tab）
	function switchChannel(channel) {
		if(current_channel == channel) return false;
		$('#link'+channel).removeClass('mm');
		$('#link'+channel).addClass('mmac');
                
                $('#link'+current_channel).removeClass('mmac');
                $('#link'+current_channel).addClass('mm');
              
              
		$('#root_'+current_channel).css('display', 'none');
		$('#root_'+channel).css('display', 'block');
		var tmp_menulist = $('#root_'+channel).find('a');
		tmp_menulist.each(function(i, n) {
			// 防止重复点击ROOT菜单
			if( i == 0 && $.inArray($(n).attr('id'), viewed_channel) == -1 ) {
				$(n).click();
				viewed_channel.push($(n).attr('id'));
			}
			if ( i == 1 ) {
				$(n).click();
			}
		});
		current_channel = channel;  
	}
	function switch_root_menu(root) {
		root = $('#tree_'+root);
		if (root.css('display') == 'block') {
			root.css('display', 'none');
			root.parent().css('backgroundImage', 'url(__ROOT__/Style/A/images/y2.png )');
		}else {
			root.css('display', 'block');
			root.parent().css('backgroundImage', 'url(__ROOT__/Style/A/images/x3.png )');
		}
	}
	
	function switch_sub_menu(sub, url) {
		if(current_menu_sub) {
			$('#menu_'+current_menu_sub).attr('class', 'submenuA');
		}
		$('#menu_'+sub).attr('class', 'submenuB');
		current_menu_sub = sub;
		parent.MainIframe.location = url;
		
	}

</script>
</html>