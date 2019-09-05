function setIdCard() {
		var real_name = $('#real_name').val();
		var idcard = $('#idcard').val();
		var isValidForm = true;
		if ($.trim(real_name) == '') {
			isValidForm = false;
			$('#realnameErr').html('请输入您的真实姓名。');
		}else{
			$('#realnameErr').html('');
		}
		
		if ($.trim(idcard) == '') {
			isValidForm = false;
			$('#idcardErr').html('请输入您的身份证号码。');
		}
		else {
			var idcartValidResult = testIdcard($.trim(idcard));
			if (idcartValidResult.indexOf('通过') == -1) {
				isValidForm = false;
				$('#idcardErr').html(idcartValidResult);
			}
		}
		if (isValidForm) {
			$('#realnameErr').html('');
			$('#idcardErr').html('');
		}
		else {
			return;
		} 
		$.ajax({
			url: "/member/verify/wqbverify/",
			type: "post",
			dataType: "json",
			data: {"real_name":real_name,"idcard":idcard},
			success: function(result) {
				if (result.status == 0) {
					$('#idcardErr').html(result.message);
				}
				else {
					$.jBox.tip("身份信息认证成功！");
					setTimeout('myrefresh1()',2000); //指定2秒刷新
				}
			},
			complete:function(XMLHttpRequest, textStatus){
				
			}
		});
	}
	function myrefresh1()
	{
	       window.location.reload();
		   window.location.href="/member/verify?id=1#fragment-1";
	}
	//验证身份证号方法
	var testIdcard = function(idcard) {
		var Errors = new Array("验证通过!", "身份证号码位数不对!", "身份证号码出生日期超出范围!", "身份证号码校验错误!", "身份证地区非法!");
		var area = { 11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "xinjiang", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外" }
		var idcard, Y, JYM;
		var S, M;
		var idcard_array = new Array();
		idcard_array = idcard.split("");
		if (area[parseInt(idcard.substr(0, 2))] == null) return Errors[4];
		switch (idcard.length) {
			case 15:
				if ((parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0 || ((parseInt(idcard.substr(6, 2)) + 1900) % 100 == 0 && (parseInt(idcard.substr(6, 2)) + 1900) % 4 == 0)) {
					ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}$/; //测试出生日期的合法性 
				}
				else {
					ereg = /^[1-9][0-9]{5}[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}$/; //测试出生日期的合法性 
				}
				if (ereg.test(idcard))
					return Errors[0];
				else
					return Errors[2];
				break;
			case 18:
				if (parseInt(idcard.substr(6, 4)) % 4 == 0 || (parseInt(idcard.substr(6, 4)) % 100 == 0 && parseInt(idcard.substr(6, 4)) % 4 == 0)) {
					ereg = /^[1-9][0-9]{5}[0-9]{4}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}[0-9Xx]$/; //闰年出生日期的合法性正则表达式 
				}
				else {
					ereg = /^[1-9][0-9]{5}[0-9]{4}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|1[0-9]|2[0-8]))[0-9]{3}[0-9Xx]$/; //平年出生日期的合法性正则表达式 
				}
				if (ereg.test(idcard)) {
					S = (parseInt(idcard_array[0]) + parseInt(idcard_array[10])) * 7 + (parseInt(idcard_array[1]) + parseInt(idcard_array[11])) * 9 + (parseInt(idcard_array[2]) + parseInt(idcard_array[12])) * 10 + (parseInt(idcard_array[3]) + parseInt(idcard_array[13])) * 5 + (parseInt(idcard_array[4]) + parseInt(idcard_array[14])) * 8 + (parseInt(idcard_array[5]) + parseInt(idcard_array[15])) * 4 + (parseInt(idcard_array[6]) + parseInt(idcard_array[16])) * 2 + parseInt(idcard_array[7]) * 1 + parseInt(idcard_array[8]) * 6 + parseInt(idcard_array[9]) * 3;
					Y = S % 11;
					M = "F";
					JYM = "10X98765432";
					M = JYM.substr(Y, 1);
					if (M == idcard_array[17])
						return Errors[0];
					else
						return Errors[3];
				}
				else
					return Errors[2];
				break;
			default:
				return Errors[1];
				break;
		}
	}
	//修改密码js
	var newTitle = '{$glo.web_name}提醒您：';
	function UpdatePwd() {
		var oldpwd = $("#oldpassword").val();
		var newspwd1 = $("#newpassword").val();
		var newspwd2 = $("#newpassword1").val();
		
		clearErr();
		hideErr();
		if (oldpwd == '') {
			addErr('原密码必须填写！');
		}
		if (newspwd1.length <6) {
			addErr('新密码必须大于等于6位！');
		}
		if (newspwd2 == '') {
			addErr('确认新密码必须填写！');
		}
		if (newspwd2 != newspwd1) {
			addErr('两次密码不一致！');
		}
		if (hasErr()) {
			showErr();
			return;
		}
		else {
			$.ajax({
				url: "/member/user/changepass/",
				type: "post",
				dataType: "json",
				data: {"oldpwd":oldpwd,"newpwd1":newspwd1,"newpwd2":newspwd2},
				success: function(d) {
					if (d.status == "2") {
						addErr('原密码错误，请重新输入！');
						showErr();
						return;
					} else if (d.status == "1") {
						$.jBox.tip('恭喜，密码修改成功！','success');
						$("input").attr("value","");
					} else {
						$.jBox.tip('对不起，原密码与新密码相同或者操作失败，请联系客服！','fail');
					}
				}
			})
		}
	}
	function showErr() {
		$(".alertDiv").css("display", "");
	}
	function clearErr() {
		$(".alertDiv ul").html("");
	}
	function addErr(err) {
		$(".alertDiv ul").append("<li>" + err + "</li>");
	}
	function hideErr() {
		$(".alertDiv").css("display", "none");
	}
	function hasErr() {
		return $(".alertDiv ul li").length > 0;
	}
	//修改密码结束
	
	//修改交易密码
	function showErr_pin() {
		$(".alertDiv_pin").css("display", "");
	}
	function clearErr_pin() {
		$(".alertDiv_pin ul").html("");
	}
	function addErr_pin(err) {
		$(".alertDiv_pin ul").append("<li>" + err + "</li>");
	}
	function hideErr_pin() {
		$(".alertDiv_pin").css("display", "none");
	}
	function hasErr_pin() {
		return $(".alertDiv_pin ul li").length > 0;
	}
	var newTitle = '{$glo.web_name}提醒您：';

	function UpdatePwd_pin() {
		var oldpwd = $("#oldpassword_pin").val();
		var newspwd1 = $("#newpassword_pin").val();
		var newspwd2 = $("#newpassword_pin1").val();
		clearErr_pin();
		hideErr_pin();
		if (oldpwd == '') {
			addErr_pin('原支付密码必须填写！');
		}
		if (newspwd1 == '') {
			addErr_pin('新支付密码必须填写！');
		}
		if (newspwd2 == '') {
			addErr_pin('确认新支付密码必须填写！');
		}
        var pat = new RegExp("^.{6,20}$", "i");
		if (!pat.test(newspwd1) || !pat.test(newspwd2) ) {
		//格式正确
		addErr_pin('支付密码必须6-20位！');
		}
		if (newspwd2 != newspwd1) {
			addErr_pin('两次支付密码不一致！');
		}
		if (hasErr_pin()) {
			showErr_pin();
			return;
		}else {
			$.ajax({
				url: "/member/user/changepin/",
				type: "post",
				dataType: "json",
				data: {"oldpwd":oldpwd,"newpwd1":newspwd1,"newpwd2":newspwd2},
				success: function(d) {
					if (d.status == "2") {
						addErr_pin(d.message);
						showErr_pin();
						return;
					} else if (d.status == "1") {
						$.jBox.tip('恭喜，支付密码修改成功！','success');	
						setTimeout("location.href=document.referrer;", 3000 )
					} else {
						$.jBox.tip(d.message,'fail');
					}
				}
			})
		}
	}
	//`mxl 20150121`
	function getPinByPhone(){
		if (confirm("原来的支付密码将被更改,新的临时支付密码将发送到您的手机,确定要继续操作吗?")==false){ return; }
		$.ajax({
			url: "/member/user/getPassByPhone",
			data: {"code":"pin_pass"},
			timeout: 5000,
			cache: false,
			type: "get",
			dataType: "json",
			success: function (d, s, r) {
				if(d){
					if(d.status==1){
						$.jBox.tip(d.message);
						$.jBox.close();
					}
					else if(d.status==2){ $.jBox.tip(d.message, "warning"); }
					else{ $.jBox.tip(d.message,"error"); }
				}
			}
		});
	}
	//`mxl 20150121`
	//修改交易密码结束
	
	//邮箱绑定
	function sendValidEmail(){
		var email = $("#email").val();
		if(email==""){
			$.jBox.tip('邮箱地址不能为空！','info');
			return;
		}else{
			var emailreg = new RegExp("^[\\w-]+(\\.[\\w-]+)*@[\\w-]+(\\.[\\w-]+)+$", "i");
			if(!emailreg.test(email)){
				$.jBox.tip('请输入正确的邮箱地址','tip');
				return;
			}else{
				AsyncEmail(email);
			}
		}
	}
	function AsyncEmail(email) {
		$.jBox.tip("正在检测电子邮件地址……",'loading');
		$.ajax({
	            type: "post",
	            async: false,
				dataType: "json",
	            url: "/member/verify/ckemail/",
	            data: {"Email":email},
	            //timeout: 3000,
	            success: function (d, s, r) {
	              	if(d){
						if(d.status==1){
							send_email(email);
						}else{
							$.jBox.tip('邮箱已经在本站注册！','info');
							return;
						}
					}
				}
	        });
		}
		function send_email(email){
			$.jBox.tip("邮件发送中......",'loading');
	        $.ajax({
	            url: "/member/verify/emailvsend/",
				data: {"email":email},
	            timeout: 8000,
				cache: false,
				type: "post",
				dataType: "json",
	            success: function (d, s, r) {
						if(d.status==1){
							$.jBox.tip(d.message,"success");
						}else if(d.status==2){
							$.jBox.tip(d.message,"fail");
						}else{
							$.jBox.tip(d.message,"fail");
						}
	            },
				complete:function(XMLHttpRequest, textStatus){
						setTimeout('myrefresh()',1000); //指定2秒刷新
				}
	        });
		}
		//邮箱绑定结束
		//上传资料
		$('.ajaxpagebar a').click(function(){
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
		              	if(d) $("#"+id).html(d.content);//更新客户端竞拍信息 作个判断，避免报错
		            }
		        });
			}catch(e){};
			return false;
		})
		function delfile(id){
	if(!confirm("删除后不可恢复，确定要删除吗?")) return;
        $.ajax({
            url: "/member/verify/delfile",
            data: {"id":id},
            timeout: 5000,
            cache: false,
            type: "post",
            dataType: "json",
            success: function (d, s, r) {
              	if(d){
					if(d.status==1){
						$.jBox.tip("删除成功",'success');
						$("#xf_"+id).remove();
					}else{
						$.jBox.tip(d.message,'fail');
					}
				}
            }
        });
}
		function upfile()
		{
			$("#loading_makeclub").ajaxStart(function(){	$(this).css("visibility","visible");	}).ajaxComplete(function(){	$(this).css("visibility","hidden");	});
			var name = $("#filetxt").val();
			var fname = $("#uploadFile").val();
			var data_type = $("#data_type").val();
			if(fname==""){
				$.jBox.tip("请先选择要上传的文件",'info');
				return;
			}
			if(data_type==""){
				$.jBox.tip("请选择资料分类",'info');
				return;
			}
			if(name=="文件名称" || name==""){
				$.jBox.tip("请输入此上传文件的文件名",'info');
				return;
			}
			
			$.jBox.tip("上传中......","loading");
			$.ajaxFileUpload({
					url:'/member/verify/editdataup/?name='+name+'&data_type='+data_type,
					secureuri:false,
					fileElementId:'uploadFile',
					dataType: 'json',
					success: function (data, status)
					{
						if(data.status==1){
							$("#uploadFile").val('');
							$("#filetxt").val('');
							$.jBox.tip(data.message,'success');
							updatedata();
						}
						else  $.jBox.tip(data.message,'fail');
					},
					complete:function(XMLHttpRequest, textStatus){
						setTimeout('myrefresh()',3000); //指定3秒刷新
					}
				})
		}
		
		function myrefresh()
		{
		       window.location.href="/member/verify";
			   window.location.reload();
		}

		function updatedata(){
		        $.ajax({
		            url: "/member/verify/editdata/",
		            data: {},
		            timeout: 5000,
		            cache: false,
		            type: "get",
		            dataType: "json",
		            success: function (d, s, r) {
		              	if(d) $("#fragment-7").html(d.html);//更新客户端信息 作个判断，避免报错
		            }
		        });
		}
		