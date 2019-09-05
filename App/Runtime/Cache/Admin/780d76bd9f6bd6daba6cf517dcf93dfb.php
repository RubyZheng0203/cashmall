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
</script>
<div class="so_main">
	<div class="page_tit">会员奖励</div>
	<!--搜索/筛选会员-->
	  <div id="search_div" style="display:none">
  	<div class="page_tit"><script type="text/javascript">document.write(searchName);</script> [ <a href="javascript:void(0);" onclick="dosearch();">隐藏</a> ]</div>
	
	<div class="form2">
	<form method="get" action="__URL__/index">
    <dl class="lineD">
      <dt>会员编号：</dt>
      <dd>
        <input name="uid" style="width:190px" id="title" type="text" value="<?php echo ($search["uid"]); ?>">
        <span>不填则不限制</span>
      </dd>
    </dl>
	<dl class="lineD"><dt>优惠券开始时间(开始)：</dt><dd><input onclick="WdatePicker({maxDate:'#F{$dp.$D(\'end_time\')||\'2020-10-01\'}',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true});" name="start_time" id="start_time"  class="input Wdate" type="text" value="<?php echo (mydate('Y-m-d H:i:s',$search["start_time"])); ?>"><span id="tip_start_time" class="tip">只选开始时间则查询从开始时间往后所有</span></dd></dl>
	<dl class="lineD"><dt>优惠券开始时间(结束)：</dt><dd><input onclick="WdatePicker({minDate:'#F{$dp.$D(\'start_time\')}',maxDate:'2020-10-01',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true});" name="end_time" id="end_time"  class="input Wdate" type="text" value="<?php echo (mydate('Y-m-d H:i:s',$search["end_time"])); ?>"><span id="tip_end_time" class="tip">只选结束时间则查询从结束时间往前所有</span></dd></dl>

    <div class="page_btm">
      <input type="submit" class="btn_b" value="确定" />
    </div>
	</form>
  </div>
  </div>
	<!--搜索/筛选会员-->
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
		<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选</span></a>
		<a onclick="send()" class="btn_a" href="javascript:void(0);"><span>发送优惠券 </span></a>
	</div>
	<div class="list">
		<table id="area_list" width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
			    <th style="width:30px;">
			        <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
			        <label for="checkbox"></label>
			    </th>
			    <th class="line_l">ID</th>
				<th class="line_l">借款人UID</th>
				<th class="line_l">优惠券金额</th>
				<th class="line_l">标题</th>
				<th class="line_l">类型</th>
				<th class="line_l">内容</th>
				<th class="line_l">状态</th>
			    <th class="line_l">开始时间</th>
			    <th class="line_l">结束时间</th>
			    <th class="line_l">操作</th>
			</tr>
			<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr overstyle='on' id="list_<?php echo ($vo["id"]); ?>">
		        <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
		        <td><?php echo ($vo["id"]); ?></td>
				<td><a onclick="loadUser(<?php echo ($vo["uid"]); ?>,'<?php echo ($vo["uid"]); ?>')" href="javascript:void(0);"><?php echo ($vo["uid"]); ?></a></td>
				<td><?php echo ($vo["money"]); ?></td>
				<td><?php echo ($vo["title"]); ?></td>
				<td><?php echo ($vo["type_name"]); ?></td>
				<td><?php echo ($vo["memo"]); ?></td>
		        <td>
		        <?php if($vo["status"] == 0): ?>未使用<?php endif; ?>
		        <?php if($vo["status"] == 1): ?>已使用<?php endif; ?>
		        <?php if($vo["status"] == 2): ?>已禁用<?php endif; ?>
		        </td>
		        <td><?php echo (date("Y-m-d H:i",$vo["start_time"])); ?></td>
		        <td><?php echo (date("Y-m-d H:i",$vo["end_time"])); ?></td>
		        <td>
					<?php if($vo["status"] == 0): ?><a href="javascript:;" onclick="disabled(<?php echo ($vo["id"]); ?>)">[失效]</a>&nbsp;&nbsp; 
						<a href="javascript:;" onclick="del_coupon(<?php echo ($vo["id"]); ?>)">[删除]</a><?php endif; ?>
        		</td>
       		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</table>
	</div>
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
  		<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选</span></a>
  		<a onclick="send()" class="btn_a" href="javascript:void(0);"><span>发送优惠券</span></a>
	</div>
</div>
<script type="text/javascript">
	function showurl(url,Title){
		ui.box.load(url, {title:Title});
	}
	
    function send()
    {
    	location.href="/admin/coupon/send";
        return true;
    }
    
    function disabled(id){
    	if(confirm("确定要失效此条优惠券记录？")){
    		$.ajax({
    			url:'__URL__/disabled',
    			type:'post',
    			data:'id='+id,
    			success:function(response){
    				var response=$.parseJSON(response);
    				if(!response.status){
    					alert("操作失败");
    				}else{
    					alert("操作成功");
    	                location.href="__URL__/index";
    	                
    				}
    			}
    		})
    	}
    }
    
    function del_coupon(id){
    	if(confirm("确定要删除此条优惠券记录？")){
    		$.ajax({
    			url:'__URL__/deleteCoupon',
    			type:'post',
    			data:'id='+id,
    			success:function(response){
    				var response=$.parseJSON(response);
    				if(!response.status){
    					alert("删除失败");
    				}else{
    					alert("删除成功");
    	                location.href="__URL__/index";
    	                
    				}
    			}
    		})
    	}
    }
	
</script>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>