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
	var editUrl = '__URL__/edit';
	var editTitle = '编辑产品信息';
	var isSearchHidden = 1;
	var searchName = "搜索/筛选";
</script>
<div class="so_main">
	<div class="page_tit">借款产品Ⅰ类</div>
	<!--搜索/筛选会员-->
	
	<!--搜索/筛选会员-->
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
		<!-- <a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选</span></a>-->
		<a onclick="additem()" class="btn_a" href="javascript:void(0);"><span>添加</span></a>
	</div>
	<div class="list">
		<table id="area_list" width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
			    <th style="width:30px;">
			        <input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="0">
			        <label for="checkbox"></label>
			    </th>
			    <th class="line_l">ID</th>
				<th class="line_l">产品金额（元）</th>
				<th class="line_l">年化利率（%）</th>
				<th class="line_l">利息（元）</th>
				<th class="line_l">借款期限</th>
				<th class="line_l">贷后管理费（元）</th>
				<th class="line_l">认证费（元）</th>
				<th class="line_l">账户管理费（元）</th>
				<th class="line_l">支付服务费（元）</th>
				<th class="line_l">续期服务费（元）</th>
				<th class="line_l">逾期利息（%）</th>
				<th class="line_l">逾期管理费（%）</th>
				<th class="line_l">是否可以续期</th>
				<th class="line_l">可续期天数</th>
			    <th class="line_l">操作</th>
			</tr>
			<?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr overstyle='on' id="list_<?php echo ($vo["id"]); ?>">
		        <td><input type="checkbox" name="checkbox" id="checkbox2" onclick="checkon(this)" value="<?php echo ($vo["id"]); ?>"></td>
		        <td><?php echo ($vo["id"]); ?></td>
				<td><?php echo ($vo["money"]); ?></td>
		        <td><?php echo ($vo["rate"]); ?>%</td>
				<td><?php echo ($vo["interest"]); ?>元</td>
				<td><?php echo ($vo["duration"]); echo ($vo["type"]); ?></td>
		        <td><?php echo ($vo["audit_rate"]); ?>元</td>
		        <td><?php echo ($vo["created_rate"]); ?>元</td>
		        <td><?php echo ($vo["enabled_rate"]); ?>元</td>
		        <td><?php echo ($vo["pay_fee"]); ?>元</td>
		        <td><?php echo ($vo["renewal_fee"]); ?>元</td>
		        <td><?php echo ($vo["due_rate"]); ?>%</td>
		        <td><?php echo ($vo["late_rate"]); ?>%</td>
		        <td>
			        <?php if($vo['is_xuqi'] == 1): ?>是
			        	<?php else: ?>
			        		否<?php endif; ?>
		    	</td>
		        <td><?php echo ($vo["renewal_day"]); ?></td>
		        <td>
		        	<a href="javascript:;" onclick="edit('?id=<?php echo ($vo["id"]); ?>')">[编辑]</a>&nbsp;&nbsp; 
					<?php if($vo["is_on"] == 0): ?><a href="javascript:;" onclick="changeOn(<?php echo ($vo["id"]); ?>,1)">[上架]</a>&nbsp;&nbsp; 
					<?php else: ?>
						<a href="javascript:;" onclick="changeOn(<?php echo ($vo["id"]); ?>,0)">[下架]</a>&nbsp;&nbsp;<?php endif; ?>
        		
        		</td>
       		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		</table>
	</div>
	<div class="Toolbar_inbox">
		<div class="page right"><?php echo ($pagebar); ?></div>
  		<!--<a onclick="dosearch();" class="btn_a" href="javascript:void(0);"><span class="search_action">搜索/筛选</span></a>-->
  		<a onclick="additem()" class="btn_a" href="javascript:void(0);"><span>添加</span></a>
	</div>
</div>
<script type="text/javascript">
	function showurl(url,Title){
		ui.box.load(url, {title:Title});
	}
	
	function additem()
    {
    	location.href="/admin/item/add?type=1";
        return true;
    }
	
    function changeOn(id,status){
    	if(confirm("确定要操作？")){
    		$.ajax({
    			url:'__URL__/changeOn',
    			type:'post',
    			data:'id='+id+"&status="+status,
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
</script>
<script type="text/javascript" src="__ROOT__/Style/A/js/adminbase.js"></script>
</body>
</html>