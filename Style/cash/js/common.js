/**
 * 网站通用js
 */

//倒计时
function timing(){
	var timeNumber = 90;
	$("#timing").removeAttr("onclick");	
	$("#timing").unbind().css("background","#ccc");
	clearInterval(timer);
	var timer = setInterval(function(){
	$("#timing").html(timeNumber+"s");
		if(!timeNumber){
			clearInterval(timer);
			$("#timing").unbind().css("background","#39c");
			$("#timing").html("获取");
			$("#timing").click(function(){
				requestApi();		
			});
		}
		timeNumber--;
	},1000);	
};

//请求运营商登录
function requestApi(){
	layer.open({type: 2,shadeClose: false,content:'获取中'});
	$.ajax({
		url:'/CheckPhone/requestJavaApi',
		type:'post',
		success:function(response){
			var response = $.parseJSON(response);
			if(response.status){
				layer.open({
				    content: '信息已提交，等待审核'
				    ,skin: 'msg'
				    ,time: 2 
				  });
			}else{
				layer.closeAll();
				if (response.message == 'sms'){					
					timing();
				}else{
					layer.open({
					    content: response.message
					    ,shadeClose: false,skin: 'msg'
					    ,time: 2 //2秒后自动关闭
					  });							
				}
			}
		}
	})
}


