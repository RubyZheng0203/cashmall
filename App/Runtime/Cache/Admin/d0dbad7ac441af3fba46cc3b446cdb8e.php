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
<script type="text/javascript" src="__ROOT__/Style/My97DatePicker/WdatePicker.js" language="javascript"></script>

<script type="text/javascript">
	var editUrl = '__URL__/black';
	var editTitle = '设置黑白灰金名单';
	var isSearchHidden = 1;
	var searchName = "搜索/筛选会员";
</script>
<div class="so_main">
	<div class="page_tit">会员列表</div>
	<!--搜索/筛选会员-->
	  <div id="search_div" style="display:none">
  	<div class="page_tit"><script type="text/javascript">document.write(searchName);</script> [ <a href="javascript:void(0);" onclick="dosearch();">隐藏</a> ]</div>
	
	<div class="form2">
	<form method="get" action="__URL__/index">
    <?php if($search["customer_id"] > 0): ?><input type="hidden" name="customer_id" value="<?php echo ($search["customer_id"]); ?>" /><?php endif; ?>
    <dl class="lineD">
      <dt>是否黑名单：</dt>
      <dd>
       <?php $i=0;$___KEY=array ( 'yes' => '是', 'no' => '否', ); foreach($___KEY as $k=>$v){ if(strlen("1key")==1 && $i==0){ ?><input type="radio" name="is_black" value="<?php echo ($k); ?>" id="is_black_<?php echo ($i); ?>" checked="checked" /><?php }elseif(("key1"=="key1"&&$search["is_black"]==$k)||("key"=="value"&&$search["is_black"]==$v)){ ?><input type="radio" name="is_black" value="<?php echo ($k); ?>" id="is_black_<?php echo ($i); ?>" checked="checked" /><?php }else{ ?><input type="radio" name="is_black" value="<?php echo ($k); ?>" id="is_black_<?php echo ($i); ?>" /><?php } ?><label for="is_black_<?php echo ($i); ?>"><?php echo ($v); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php $i++; } ?><span id="tip_is_black" class="tip">不填则不限制</span>
        <span>不填则不限制</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>是否白名单：</dt>
      <dd>
       <?php $i=0;$___KEY=array ( 'yes' => '是', 'no' => '否', ); foreach($___KEY as $k=>$v){ if(strlen("1key")==1 && $i==0){ ?><input type="radio" name="is_white" value="<?php echo ($k); ?>" id="is_white_<?php echo ($i); ?>" checked="checked" /><?php }elseif(("key1"=="key1"&&$search["is_white"]==$k)||("key"=="value"&&$search["is_white"]==$v)){ ?><input type="radio" name="is_white" value="<?php echo ($k); ?>" id="is_white_<?php echo ($i); ?>" checked="checked" /><?php }else{ ?><input type="radio" name="is_white" value="<?php echo ($k); ?>" id="is_white_<?php echo ($i); ?>" /><?php } ?><label for="is_white_<?php echo ($i); ?>"><?php echo ($v); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php $i++; } ?><span id="tip_is_white" class="tip">不填则不限制</span>
        <span>不填则不限制</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>是否注销会员：</dt>
      <dd>
       <?php $i=0;$___KEY=array ( 'yes' => '是', 'no' => '否', ); foreach($___KEY as $k=>$v){ if(strlen("1key")==1 && $i==0){ ?><input type="radio" name="is_logoff" value="<?php echo ($k); ?>" id="is_logoff_<?php echo ($i); ?>" checked="checked" /><?php }elseif(("key1"=="key1"&&$search["is_logoff"]==$k)||("key"=="value"&&$search["is_logoff"]==$v)){ ?><input type="radio" name="is_logoff" value="<?php echo ($k); ?>" id="is_logoff_<?php echo ($i); ?>" checked="checked" /><?php }else{ ?><input type="radio" name="is_logoff" value="<?php echo ($k); ?>" id="is_logoff_<?php echo ($i); ?>" /><?php } ?><label for="is_logoff_<?php echo ($i); ?>"><?php echo ($v); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<?php $i++; } ?><span id="tip_is_logoff" class="tip">不填则不限制</span>
        <span>不填则不限制</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>会员编号：</dt>
      <dd>
        <input name="uid" style="width:190px" id="title" type="text" value="<?php echo ($search["uid"]); ?>">
        <span>不填则不限制</span>
      </dd>
    </dl>
   <dl class="lineD">
      <dt>手机号：</dt>
      <dd>
        <input name="iphone" style="width:190px" id="title" type="text" value="<?php echo ($search["iphone"]); ?>">
        <span>不填则不限制</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>真实姓名：</dt>
      <dd>
        <input name="realname" style="width:190px" id="title" type="text" value="<?php echo ($search["realname"]); ?>">
        <span>不填则不限制</span>
      </dd>
    </dl>
 
	<dl class="lineD">
      <dt>注册来源：</dt>
      <dd>
        <input name="promotion_code" style="width:190px" id="title" type="text" value="<?php echo ($search["promotion_code"]); ?>">
        <span>不填则不限制</span>
      </dd>
    </dl>
    <dl class="lineD">
      <dt>查询时间提示：</dt>
      <dd>
        <span style="color:red;">开始时间和结束时间均不填，则查询当天00:00往后所有</span>
      </dd>
    </dl>
	<dl class="lineD"><dt>注册时间(开始)：</dt><dd><input onclick="WdatePicker({maxDate:'#F{$dp.$D(\'end_time\')||\'2020-10-01\'}',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true});" name="start_time" id="start_time"  class="input Wdate" type="text" value="<?php echo (mydate('Y-m-d H:i:s',$search["start_time"])); ?>"><span id="tip_start_time" class="tip">只选开始时间则查询从开始时间往后所有</span></dd></dl>
	<dl class="lineD"><dt>注册时间(结束)：</dt><dd><input onclick="WdatePicker({minDate:'#F{$dp.$D(\'start_time\')}',maxDate:'2020-10-01',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true});" name="end_time" id="end_time"  class="input Wdate" type="text" value="<?php echo (mydate('Y-m-d H:i:s',$search["end_time"])); ?>"><span id="tip_end_time" class="tip">只选结束时间则查询从结束时间往前所有</span></dd></dl>

    <div class="page_btm">
      <input type="submit" class="btn_b" value="确定" />
    </div>
	</form>
  </div>
  </div>
	<!--搜索/筛选会员-->
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
		<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选会员</span></a>
	</div>
	<div class="list">
		<table id="area_list" width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
			    <th style="width:30px;">
			        <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
			        <label for="checkbox"></label>
			    </th>
			    <th class="line_l">ID</th>
				<th class="line_l">借款人手机</th>
			    <th class="line_l">真实姓名</th>
				<th class="line_l">推荐人</th>
				<th class="line_l">注册IP</th>
				<th class="line_l">注册地址</th>
				<th class="line_l">登录IP</th>
				<th class="line_l">登录地址</th>
				<th class="line_l">是否黑名单</th>
				<th class="line_l">是否白名单</th>
				<th class="line_l">是否灰名单</th>
				<th class="line_l">是否金名单</th>
				<th class="line_l">是否注销</th>
			    <th class="line_l">注册时间</th>
			    <th class="line_l">注册来源</th>
			    <th class="line_l">操作</th>
			</tr>
			<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr overstyle='on' id="list_<?php echo ($vo["id"]); ?>">
		        <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
		        <td><?php echo ($vo["id"]); ?></td>
		        <td><a onclick="loadUser(<?php echo ($vo["id"]); ?>,'<?php echo ($vo["iphone"]); ?>')" href="javascript:void(0);"><?php echo ($vo["iphone"]); ?></a></td>
		        <td><?php echo (($vo["real_name"])?($vo["real_name"]):"&nbsp;"); ?></td>
				<td><a onclick="loadUser(<?php echo ($vo["recommend_id"]); ?>,'<?php echo ($vo["recommend_name"]); ?>')" href="javascript:void(0);"><?php echo ($vo["recommend_name"]); ?></a></td>
				<td><?php echo ($vo["reg_ip"]); ?></td>
				<td><?php echo ($vo["reg_address"]); ?></td>
				<td><?php echo ($vo["last_ip"]); ?></td>
				<td><?php echo ($vo["last_address"]); ?></td>
		        <td><?php if($vo["is_black"] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
		        <td><?php if($vo["is_white"] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
		        <td><?php if($vo["is_gray"] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
		        <td><?php if($vo["is_gold"] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
		        <td><?php if($vo["is_logoff"] == 1): ?>是<?php else: ?>否<?php endif; ?></td>
		        <td><?php echo (date("Y-m-d H:i",$vo["reg_time"])); ?></td>
		        <td><?php echo ($vo["promotion_code"]); ?></td>
		        <td>
		        	<?php if( $vo["is_black"] == 0 ): ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=1&status=1')">[设置黑名单]</a>&nbsp;&nbsp; <?php else: ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=1&status=2')">[取消黑名单]</a><?php endif; ?>
        			<?php if( $vo["is_white"] == 0 ): ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=2&status=1')">[设置白名单]</a>&nbsp;&nbsp; <?php else: ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=2&status=2')">[取消白名单]</a><?php endif; ?>
        			<?php if( $vo["is_gray"] == 0 ): ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=3&status=1')">[设置灰名单]</a>&nbsp;&nbsp; <?php else: ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=3&status=2')">[取消灰名单]</a><?php endif; ?>
        			<?php if( $vo["is_gold"] == 0 ): ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=4&status=1')">[设置金名单]</a>&nbsp;&nbsp; <?php else: ?><a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>&type=4&status=2')">[取消金名单]</a><?php endif; ?>
        		</td>
       		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</table>
	</div>
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
  		<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选会员</span></a>
	</div>
</div>
<script type="text/javascript">
	function showurl(url,Title){
		ui.box.load(url, {title:Title});
	}
</script>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>