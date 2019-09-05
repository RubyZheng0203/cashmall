$(function(){
	(function(){
		if(sessionStorage.getItem("risk")){

		}else{
			var on = sessionStorage.setItem("risk",true);
			pop();
		}
		function pop(){
			var str = '<div class="risk" style="">'+
				'<div class="risk_content">'+
					'<h4>放假公告</h4>'+
					'<p>亲爱的用户：</p>'+

				    '<div>感谢您一直以来对现贷猫的支持与厚爱。现贷猫春节假日安排如下：</div>'+

				    '<div>一．放假安排：从2018年2月15日至2月21日放假，2月22日（初七）正常上班。</div>'+

				    '<div>二．客服安排：2月15日-2月21日期间，如有问题可联系18101812991、18101816772</div>'+

				     '<div>感谢您一直以来的信任与支持，现贷猫恭祝您新春大吉，阖家幸福！</div>'+
					'<button id="btn">我知道了</button>'+
				'</div>'+
			'</div>';
			$("body").append(str);
			$("#btn").click(function(){
				$(".risk").hide();
			});
		}
	})();
})