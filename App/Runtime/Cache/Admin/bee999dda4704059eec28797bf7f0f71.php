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
	var isSearchHidden = 1;
	var searchName = "搜索/筛选";
	var editUrl = '__URL__/showreview';
	var editTitle = '待复审的借款';
</script>
<div class="so_main">
	<div class="page_tit">待复审的借款</div>
	<!--搜索/筛选-->
	  <div id="search_div" style="display:none">
  	<div class="page_tit"><script type="text/javascript">document.write(searchName);</script> [ <a href="javascript:void(0);" onclick="dosearch();">隐藏</a> ]</div>
	<div class="form2">
		<form method="get" action="__URL__/review">
			<dl class="lineD">
		      <dt>借款申请编号：</dt>
		      <dd>
		        <input name="borrowid" style="width:190px" id="title" type="text" value="<?php echo ($search["borrowid"]); ?>">
		        <span>不填则不限制</span>
		      </dd>
		    </dl>
		    <dl class="lineD">
		      <dt>借款会员编号：</dt>
		      <dd>
		        <input name="uid" style="width:190px" id="title" type="text" value="<?php echo ($search["uid"]); ?>">
		        <span>不填则不限制</span>
		      </dd>
		    </dl>
		    <dl class="lineD">
		      <dt>借款会员手机号：</dt>
		      <dd>
		        <input name="iphone" style="width:190px" id="title" type="text" value="<?php echo ($search["iphone"]); ?>">
		        <span>不填则不限制</span>
		      </dd>
		    </dl>
		    <dl class="lineD">
		      <dt>查询时间提示：</dt>
		      <dd>
		        <span style="color:red;">开始时间和结束时间均不填，则查询四天前00:00往后所有</span>
		      </dd>
		    </dl>
			<dl class="lineD"><dt>授信缴费时间(开始)：</dt><dd><input onclick="WdatePicker({maxDate:'#F{$dp.$D(\'end_time\')||\'2020-10-01\'}',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true});" name="start_time" id="start_time"  class="input Wdate" type="text" value="<?php echo (mydate('Y-m-d H:i:s',$search["start_time"])); ?>"><span id="tip_start_time" class="tip">只选开始时间则查询从开始时间往后所有</span></dd></dl>
			<dl class="lineD"><dt>授信缴费时间(结束)：</dt><dd><input onclick="WdatePicker({minDate:'#F{$dp.$D(\'start_time\')}',maxDate:'2020-10-01',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true});" name="end_time" id="end_time"  class="input Wdate" type="text" value="<?php echo (mydate('Y-m-d H:i:s',$search["end_time"])); ?>"><span id="tip_end_time" class="tip">只选结束时间则查询从结束时间往前所有</span></dd></dl>
		    <div class="page_btm">
		      <input type="submit" class="btn_b" value="确定" />
		    </div>
		</form>
  	</div>
  </div>
	<!--搜索/筛选-->
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
		<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选</span></a>
	</div>
	<div class="list">
		<table id="area_list" width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
			    <th style="width:30px;">
			        <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
			        <label for="checkbox"></label>
			    </th>
			    <th class="line_l">借款申请ID</th>
				<th class="line_l">借款人编号</th>
				<th class="line_l">借款人手机号</th>
				<th class="line_l">借款人姓名</th>
				<th class="line_l">借款金额（元）</th>
				<th class="line_l">借款利息（元）</th>
				<th class="line_l">年化率（%）</th>
				<th class="line_l">借款期限</th>
				<th class="line_l">授信缴费时间</th>
			    <th class="line_l">操作</th>
			</tr>
			
			<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr overstyle='on' id="list_<?php echo ($vo["id"]); ?>">
			        <td><input type="checkbox" value="<?php echo ($vo["id"]); ?>" id="checkbox_<?php echo ($vo["id"]); ?>" name="checkbox"  onclick="checkon(this)"></td>
			        <td><?php echo ($vo["id"]); ?></td>
			        <td><a onclick="loadUser(<?php echo ($vo["uid"]); ?>,'<?php echo ($vo["uid"]); ?>')" href="javascript:void(0);"><?php echo ($vo["uid"]); ?></a></td>
			        <td><?php echo ($vo["iphone"]); ?></td>
			        <td><?php echo ($vo["real_name"]); ?></td>
					<td><?php echo ($vo["money"]); ?></td>
					<td><?php echo ($vo["interest"]); ?></td>
					<td><?php echo ($vo["rate"]); ?></td>
					<td><?php echo ($vo["duration"]); echo ($vo["type"]); ?></td>
					<td><?php echo (date("Y-m-d H:i",$vo["recheck_time"])); ?></td>
			        <td>
						<a href="javascript:;" onclick="edit('?uid=<?php echo ($vo["uid"]); ?>&id=<?php echo ($vo["id"]); ?>')">[审核]</a>
						<a href="javascript:;" onclick="checkid(<?php echo ($vo["uid"]); ?>,<?php echo ($vo["id"]); ?>)">[决策树]</a>
			        	
			        </td>
	       		</tr>
	       		<input type="hidden" value="<?php echo ($vo["duration"]); ?>" id="duration_<?php echo ($vo["id"]); ?>" name="duration_<?php echo ($vo["id"]); ?>">
	       		<input type="hidden" value="<?php echo ($vo["rate"]); ?>" id="rate_<?php echo ($vo["id"]); ?>" name="rate_<?php echo ($vo["id"]); ?>"><?php endforeach; endif; else: echo "" ;endif; ?>
		</table>
	</div>
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
  		<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选</span></a>
	</div>
</div>
<script type="text/javascript">
	function showurl(url,Title){
		ui.box.load(url, {title:Title});
	}
	function checkid(uid,bid){
    	url = "../../Home/CheckUser/requestBackApi";
		$.ajax({
				url: url,
				data: {"uid":uid,"bid":bid,"cid":5},
				type: "post",
				dataType: "json",
				success: function (d, s,  r) {
					if(d){
						alert(d.message);
					}
				}
		});
	}
</script>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>