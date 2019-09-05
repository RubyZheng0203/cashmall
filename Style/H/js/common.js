function plus(id){
	var cnum = parseInt($("#tnum_"+id).val());
	var tnumTransTransfer = parseInt($("#tnumTransfer").val());
	cnum++;
	$("#tnum_"+id).val(cnum);
	$("#tnumInvest").val(tnumTransTransfer*cnum);
	var testreuslt = true;
	if (testreuslt) {
		showChineseAmount22();
	}
	return testreuslt;
	
}
function minus(id){
	var cnum = parseInt($("#tnum_"+id).val());
	var tnumTransTransfer = parseInt($("#tnumTransfer").val());
	cnum=(cnum-1)>0?(cnum-1):1;
	$("#tnum_"+id).val(cnum);
	$("#tnumInvest").val(cnum*tnumTransTransfer);
	var testreuslt = true;
	if (testreuslt) {
		showChineseAmount22();
	}
	return testreuslt;
}

function enterToChinese(id){
	
	var cnum = parseInt($("#enter_value").val());
	var tnumshouyi = parseFloat($("#tnumshouyi").val());
	if(cnum !=0 && !isNaN(cnum)){
		var shouyi = ((tnumshouyi/100000)*cnum+cnum).toFixed(2);
	} else {
		var shouyi = 0;
	}
	
	$("#shouyiId").html(shouyi+"元");
	var testreuslt = true;
	if (testreuslt) {
		showChineseAmount33();
	}
	return testreuslt;
	
}

function showChineseAmount33() {
	var regamount = /^(([1-9]{1}[0-9]{0,})|([0-9]{1,}\.[0-9]{1,2}))$/;
	var reg = new RegExp(regamount);
	if (reg.test($("#enter_value").val())) {
		var amstr = $("#enter_value").val();
		var leng = amstr.toString().split('.').length;
		if (leng == 1) {
			$("#enter_value").val($("#enter_value").val() + ".00");
		}
		
		$("#d_money").html(Arabia_to_Chinese($("#enter_value").val()));
		$("#d_money").css("display", "");
		$("#d_money").css("color", "red");
		$("#d_money").removeClass("reg_wrong");
	} else {
		$("#d_money").html("");
	}
}

function tnumToChinese(id){
	var cnum = parseInt($("#tnum").val());
	var tnumTransTransfer = parseInt($("#tnumTransfer").val());
	var tnumshouyi = parseFloat($("#tnumshouyi").val());
	if(cnum !=0 && !isNaN(cnum)){
		var shouyi = ((tnumshouyi/100000)*cnum+cnum).toFixed(2);
	} else {
		var shouyi = 0;
	}
	var mod = cnum%tnumTransTransfer;
	if(mod > 0){
		$("#errorsId").html("投资金额需为起投金额的倍数");
		$("#tnum").val("");
		$("#tnum_"+id).val("");
		$("#shouyiId").html("");
	} else {
		$("#tnum_"+id).val(parseInt(cnum/tnumTransTransfer));
		$("#shouyiId").html(shouyi+"元");
	}
	var testreuslt = true;
	if (testreuslt) {
		showChineseAmountNew();
	}
	return testreuslt;
	
}
function showChineseAmountNew() {
	var regamount = /^(([1-9]{1}[0-9]{0,})|([0-9]{1,}\.[0-9]{1,2}))$/;
	var reg = new RegExp(regamount);
	if (reg.test($("#tnum").val())) {
		var amstr = $("#tnum").val();
		var leng = amstr.toString().split('.').length;
		if (leng == 1) {
			$("#tnum").val($("#tnum").val() + ".00");
		}
		$("#d_money").html(Arabia_to_Chinese($("#tnum").val()));
		$("#d_money").css("display", "");
		$("#d_money").css("color", "red");
		$("#d_money").removeClass("reg_wrong");
		
	} else {
		$("#d_money").html("");
	}
}


function tnumOnchange(id){
	var cnum = parseInt($("#tnum_"+id).val());
	var tnumTransTransfer = parseInt($("#tnumTransfer").val());
	$("#tnumInvest").val(cnum*tnumTransTransfer);
	var testreuslt = true;
	if (testreuslt) {
		showChineseAmount22();
	}
	return testreuslt;
	
}

function showChineseAmount22() {
	var regamount = /^(([1-9]{1}[0-9]{0,})|([0-9]{1,}\.[0-9]{1,2}))$/;
	var reg = new RegExp(regamount);
	if (reg.test($("#tnumInvest").val())) {
		var amstr = $("#tnumInvest").val();
		var leng = amstr.toString().split('.').length;
		if (leng == 1) {
			$("#tnumInvest").val($("#tnumInvest").val() + ".00");
		}
		$("#d_money_2").html(Arabia_to_Chinese($("#tnumInvest").val()));
		$("#d_money_2").css("display", "");
		$("#d_money_2").css("color", "red");
		$("#d_money_2").removeClass("reg_wrong");
		
	} else {
		$("#d_money_2").html("");
	}
}

function round2(floatData,i){
	var i=i+1;
	var floatStr = (floatData)+"";
	var index = floatStr.indexOf(".");
	if(index!=-1){
		return floatStr.substring(0,(index+i));	
	}
	else
		return floatStr;
}


/*企业直投流程*/

var T_transfer_num = 0;
var T_month_min = 0;
var T_month_max = 0;
function Transfer(id){
	var moneys=parseFloat($("#tnum").val());
	var per=parseFloat($("#perMoney").val());
	if(moneys%per!=0){
		alert("请输入起投金额的整数倍");
		$("#tnum").val(per);
		return false;
	}	
	var hb=document.getElementsByName('hb');
	var hb_str='';
	var hbtype='';
	for(var i = 0; i < hb.length; i++){
        if(hb[i].checked){
        	if(hb_str == ''){
        		hb_str = "'"+hb[i].value+"'";
        	}else{
        		hb_str = hb_str+","+"'"+hb[i].value+"'";
        	}
        	hbtype=$("#hbtype_"+hb[i].value).val();
        }
    }
	var jxq=document.getElementsByName('jxq');
	var jxq_str='';
	for(var i = 0; i < jxq.length; i++){
        if(jxq[i].checked){
        	if(jxq_str == ''){
        		jxq_str = "'"+jxq[i].value+"'";
        	}else{
        		jxq_str = jxq_str+","+"'"+jxq[i].value+"'";
        	}
        }
    }
	var exp=document.getElementsByName('eg');
	var exp_str='';
	for(var i = 0; i < exp.length; i++){
        if(exp[i].checked){
        	if(exp_str == ''){
        		exp_str = "'"+exp[i].value+"'";
        	}else{
        		exp_str = exp_str+","+"'"+exp[i].value+"'";
        	}
        	
        }
    }
	var per=$("#perMoney").val();
	var cnum = $("#tnum").val();
	var num=cnum/per;
	$.jBox("get:"+Transfer_invest_url+"/ajax_invest?id="+id+"&num="+num+"&cnum="+cnum+"&hb="+hb_str+"&jxq="+jxq_str+"&exp="+exp_str+"&hbtype="+hbtype, {title: "支付",buttons: {}});
}
function FTransfer(id){
	if($("#status").val()==0){
		notice.setSinaPass();
		return false;
	}
	var moneys=parseFloat($("#tnum").val());
	var per=parseFloat($("#perMoney").val());
	if(moneys%per!=0){
		alert("请输入起投金额的整数倍");
		$("#tnum").val(per);
		return false;
	}	
	var hb=document.getElementsByName('hb');
	var hb_str='';
	var hbtype='';
	for(var i = 0; i < hb.length; i++){
        if(hb[i].checked){
        	if(hb_str == ''){
        		hb_str = "'"+hb[i].value+"'";
        	}else{
        		hb_str = hb_str+","+"'"+hb[i].value+"'";
        	}
        	hbtype=$("#hbtype_"+hb[i].value).val();
        }
    }
	
	var jxq=document.getElementsByName('jxq');
	var jxq_str='';
	for(var i = 0; i < jxq.length; i++){
		if(jxq[i].checked){
        	if(jxq_str == ''){
        		jxq_str = "'"+jxq[i].value+"'";
        	}else{
        		jxq_str = jxq_str+","+"'"+jxq[i].value+"'";
        	}
        }
    }
	
	var exp=document.getElementsByName('eg');
	var exp_str='';
	for(var i = 0; i < exp.length; i++){
        if(exp[i].checked){
        	if(exp_str == ''){
        		exp_str = "'"+exp[i].value+"'";
        	}else{
        		exp_str = exp_str+","+"'"+exp[i].value+"'";
        	}
        	
        }
    }
	//var chooseWay = $("input[name='radios']:checked").val();
	var chooseWay = 5; //按月付息
	/*if(chooseWay==null){
		alert("请选择利息使用方式！");
    	return;
	}*/
	var per=$("#perMoney").val();	
	var cnum = $("#tnum").val();
	var num=cnum/per;
	$.jBox("get:"+Transfer_invest_url+"/ajax_invest?id="+id+"&num="+num+"&cnum="+cnum+"&chooseWay="+chooseWay+"&hb="+hb_str+"&jxq="+jxq_str+"&exp="+exp_str+"&hbtype="+hbtype, {
		title: "支付",
		width: "auto",
		buttons: {}
	});
}

function HTransfer(id){
	if($("#status").val()==0){
		notice.setSinaPass();
		return false;
	}
	var moneys=parseFloat($("#tnum").val());
	var per=parseFloat($("#perMoney").val());
	if(moneys%per!=0){
		alert("请输入起投金额的整数倍");
		$("#tnum").val(per);
		return false;
	}	
	var chooseWay = 5; //按月付息
	/*if(chooseWay==null){
		alert("请选择利息使用方式！");
    	return;
	}*/
	var per=$("#perMoney").val();	
	var cnum = $("#tnum").val();
	var num=cnum/per;
	$.jBox("get:"+Transfer_invest_url+"/ajax_invest?id="+id+"&num="+num+"&cnum="+cnum+"&chooseWay="+chooseWay, {
		title: "支付",
		width: "auto",
		buttons: {}
	});
}
function tanchu(id,ziduan){
	
	$.jBox("get:"+Transfer_invest_url+"/ajax_tanchu?id="+id+"&ziduan="+ziduan, {title: "详情",buttons: {}});
}
function sumTMoney(obj){
	obj.value=obj.value.replace(/[^0-9]/g,'');
	var tnum = parseInt($("#transfer_invest_num").val());
	var per = parseInt($("#per_transfer").val());
	var total = tnum*per;
		total = isNaN(total)?0:total;
	$("#total_transfer_money").html(total);
}

function showTMoney(rate,reward_rate,increase_rate,month1){
	var tnum = parseInt($("#transfer_invest_num").val());
	var per = parseInt($("#per_transfer").val());
	var month = parseInt($("#transfer_invest_month").val());
		month = isNaN(month)?0:month;
	var total = tnum*per;
		total = isNaN(total)?0:total;
	
	var interest_rate = parseFloat(rate)+month*parseFloat(increase_rate);
	var interest = parseFloat(interest_rate)*total*month/(12*100);
	var reward = parseFloat(reward_rate)*total/100;
	$("#year_interest").html(interest_rate);
	$("#except_income").html("￥"+round2((interest+reward),2));
	$("#interest_income").html("￥"+round2(interest,2));
	$("#reward_income").html("￥"+round2(reward,2));
}
function showFMoney(rate,reward_rate,increase_rate,chooseway,shouyi4,shouyi6){
	var tnum = parseInt($("#transfer_invest_num").val());
	var per = parseInt($("#per_transfer").val());
	var month = parseInt($("#transfer_invest_month").val());
		month = isNaN(month)?0:month;
	var total = tnum*per;
		total = isNaN(total)?0:total;
	var chooseway = parseInt(chooseway)
	//var mujiqi = parseFloat(total*leftday*(rate/365)/100);
	
	var interest_rate = parseFloat(rate);
	//var interest = parseFloat(interest_rate)*total*month/(12*100);
	var interest;
	if(chooseway == 4){
	    interest = parseFloat(tnum*shouyi4);
	}else if(chooseway == 6){
	    interest = parseFloat(tnum*shouyi6);
	}
	var reward = parseFloat(reward_rate)*total/100;
	$("#year_interest").html(interest_rate);
	//$("#except_income").html("￥"+round2((interest+mujiqi+reward),2));
	$("#except_income").html("￥"+round2((interest+reward),2));
	$("#interest_income").html("￥"+round2(interest,2));
	$("#reward_income").html("￥"+round2(reward,2));
}
function T_PostData() {
	
	var tnum = parseInt($("#transfer_invest_num").val());
	var per = parseInt($("#per_transfer").val());
	var amount = tnum*per;
	var month = parseInt($("#transfer_invest_month").val());
	if(tnum<1){
		$.jBox.tip("购买份数必须大于等于1份！");  
		return false;
	}
	var total = tnum*per;
		tendValue = isNaN(total)?0:total;
	var pin = $("#T_pin").val();
	var borrow_id = $("#T_borrow_id").val();
	if(pin==""){
		$.jBox.tip("请输入支付密码");  
		return false;
	}
	if(tnum>T_transfer_num){
		$.jBox.tip("本标还能认购最大份数为"+T_transfer_num+"份，请重新输入认购份数");  
		return false;
	}else if(T_month_max<month){
		$.jBox.tip("本标最多只能认购"+T_month_max+"个月");  
		return false;
	}
	var hb_id = $('#hb').val();
	var hb_jxq_id = $('#jxq').val();
	var hbType = $('#hbtype').val();
	var exp = $('#exp').val();
	
	if((hb_id!="" && typeof(hb_id) != "undefined") || (hb_jxq_id!="" &&typeof(hb_jxq_id) != "undefined") || (exp!="" && typeof(exp) != "undefined")){
		
		if(amount<100){
			alert("投资金额至少100元！");
			return;
		}
	}
	$("#buyid").css('display','none');
	$("#payImg").css('display','block');
	$.ajax({
		url: Transfer_invest_url+"/investcheck",
		type: "post",
		dataType: "json",
		data: {"tnum":tnum,"month":month,'pin':pin,'borrow_id':borrow_id,'hb_id':hb_id,'hb_jxq_id':hb_jxq_id,'hbType':hbType,'exp_id':exp},
		success: function(d) {
			if (d.status == 1) {
				investmoney = tendValue;
				document.forms.investForm.submit();
				//$.jBox.confirm(d.message, "会员投标提示", isinvest, { buttons: { '确认投标': true, '暂不投标': false},top:'40%' });
			//} else if(d.status == 2){// 无担保贷款多次提醒 
				//var content = '<div class="jbox-custom"><p>'+ d.message +'</p><div class="jbox-custom-button"><span onclick="$.jBox.close()">取消</span><span onclick="gocharge(true,\''+d.ext+'\')">去充值</span></div></div>';
				//$.jBox(content, {title:'会员投标提示'});
			//}else if(d.status == 3){// 无担保贷款多次提醒
			//	$.jBox.alert(d.message, '会员投标提示',{top:'40%'});
			}else{
				$("#buyid").css('display','block');
				$("#payImg").css('display','none');
				$.jBox.tip(d.message);  
			}
		}
	});
}

function H_PostData() {
	var tnum = parseInt($("#transfer_invest_num").val());
	var per = parseInt($("#per_transfer").val());
	var amount = tnum*per;
	var month = parseInt($("#transfer_invest_month").val());
	if(tnum<1){
		$.jBox.tip("购买份数必须大于等于1份！");  
		return false;
	}
	var total = tnum*per;
		tendValue = isNaN(total)?0:total;
	var pin = $("#T_pin").val();
	var borrow_id = $("#T_borrow_id").val();
	if(pin==""){
		$.jBox.tip("请输入支付密码");  
		return false;
	}
	if(tnum>T_transfer_num){
		$.jBox.tip("本标还能认购最大份数为"+T_transfer_num+"份，请重新输入认购份数");  
		return false;
	}else if(T_month_max<month){
		$.jBox.tip("本标最多只能认购"+T_month_max+"个月");  
		return false;
	}
	$("#buyid").css('display','none');
	$("#payImg").css('display','block');
	$.ajax({
		url: Transfer_invest_url+"/investcheck",
		type: "post",
		dataType: "json",
		data: {"tnum":tnum,"month":month,'pin':pin,'borrow_id':borrow_id},
		success: function(d) {
			if (d.status == 1) {
				investmoney = tendValue;
				document.forms.investForm.submit();
			}else{
				$("#buyid").css('display','block');
				$("#payImg").css('display','none');
				$.jBox.tip(d.message);  
			}
		}
	});
}
//充值
function gocharge(d,money){
	if(d===true) location.href='/member/charge?money='+money+'#fragment-1';
}
function ischarge(d){
	if(d===true) window.location.href="/member/charge#fragment-1";
}
function isinvest(d){
	if(d===true) document.forms.investForm.submit();
}
/*企业直投流程*/
function bindpagebar(){
	$('.ajaxpagebar a').unbind().click(function(){
		try{	
			var geturl = $(this).attr('href');
			var id = $(this).parent().attr('data');
			var x={};
			$.ajax({
				url: geturl,
				data: x,
				timeout: 5000,
				cache: false,
				type: "get",
				dataType: "json",
				success: function (d, s, r) {
					if(d) $("#"+id).html(d.html);//更新客户端竞拍信息 作个判断，避免报错
				}
			});
		}catch(e){};
		return false;
	})
}

//红包选择器
function checkHb(){
	var id=document.getElementsByName('hb');
	var hbtype='';
	for(var i = 0; i < id.length; i++){
        if(id[i].checked){
        	var type=$("#hbtype_"+id[i].value).val();
        	if(hbtype==''){
        		hbtype=type;
        	}
        	if(hbtype!=type){
        		alert('请选择同类型红包');
        		$("#hb_"+id[i].value).attr('checked',false);
        		return false;
        	}
        	if(type==6){
        		var nums=$("#tnum").val();
        		if(parseFloat(nums)<100){
            		alert('使用分享红包，投资金额必须大于100元');
            		$("#hb_"+id[i].value).attr('checked',false);
            		return false;
            	}
        	}
        }
       }
}
//加息券选择器
function checkJxq(){
	var id=document.getElementsByName('jxq');
	var jxq_val=0;
	for(var i = 0; i < id.length; i++){
        if(id[i].checked){
        	var val=$("#jxqid_"+id[i].value).val();
        	alert(val);
        	jxq_val+=parseFloat(val);
        	if(jxq_val>1){
        		alert('加息券每次最多只能累计到1%使用');
        		$("#jxq_"+id[i].value).attr('checked',false);
        		return false;
        	}
        }
    }
}
//体验金选择
function checkEg(){
	var id=document.getElementsByName('eg');
	var nums=$("#tnum").val();
	for(var i = 0; i < id.length; i++){
        if(id[i].checked){
        	if(parseFloat(nums)<100){
        		alert('使用体验金，投资金额必须大于100元');
        		$("#eg_"+id[i].value).attr('checked',false);
        		return false;
        	}
        }
    }
}