$(document).ready(function(){
	// -------------产品购买页-------------
	//增减金额
	var fraction = 100; //每份多少钱
	var money,expecReturn;//投入的金额,预期收益
	money = parseInt($('#money').val());
	var rate = parseInt($('#rate').text());//年化收益率
	var duration = parseInt($('#duration').text());//投资期限
	var limit = $('#limit').text();//限购份额
	var moneyPerFra = parseInt($('#moneyPerFra').text());//起投金额
	var limitSum = limit * moneyPerFra;
    expectedReturn();

	$('#more').click(function(){
		money = parseInt($('#money').val());
		money += fraction;
		if (money > limitSum) {
			money = limitSum;
		}
		$('#money').val(money);
		expectedReturn();
	});

	$('#less').click(function(){
		money = parseInt($('#money').val());
		money -= fraction;
		if (money < 0) {
			money = 0;

		}
		$('#money').val(money);
		expectedReturn();
	});

	$('#money').blur(function(){
		money = parseInt($(this).val());
		if (money > limitSum) {
			money = limitSum;
		}
		if (money < 0) {
			money = 0;
		}
		$('#money').val(money);
		expectedReturn();
	});
	//计算预期收益
	function expectedReturn(){
		expecReturn = money * rate / 100 /365 * duration;
    	expecReturn = expecReturn.toFixed(2);
    	$('#expecReturn').text(expecReturn);
	}
	//立即抢购按钮
	
});

