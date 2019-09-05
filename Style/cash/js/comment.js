new function (){
            var _self = this;
            _self.width = 640;//设置默认最大宽度
            _self.fontSize = 100;//默认字体大小
            _self.widthProportion = function(){var p = document.getElementsByTagName("html")[0].offsetWidth/_self.width;return p>1?1:p<0.5?0.5:p;};
            _self.changePage = function(){
                document.getElementsByTagName("html")[0].setAttribute("style","font-size:"+_self.widthProportion()*_self.fontSize+"px !important");
            };
            _self.changePage();
            window.addEventListener("resize",function(){_self.changePage();},false);
};
/*$(function(){
		function ismobile(test){
		    var u = navigator.userAgent, app = navigator.appVersion;
		    if(/AppleWebKit.*Mobile/i.test(navigator.userAgent) || (/MIDP|SymbianOS|NOKIA|SAMSUNG|LG|NEC|TCL|Alcatel|BIRD|DBTEL|Dopod|PHILIPS|HAIER|LENOVO|MOT-|Nokia|SonyEricsson|SIE-|Amoi|ZTE/.test(navigator.userAgent))){
		     if(window.location.href.indexOf("?mobile")<0){
		      try{
		       if(/iPhone|mac|iPod|iPad/i.test(navigator.userAgent)){
		        return '0';
		       }else{
		        return '1';
		       }
		      }catch(e){}
		     }
		    }else if( u.indexOf('iPad') > -1){
		        return '0';
		    }else{
		        return '1';
		    }
		};
		var  a = ismobile();
		var url = location.href.toLowerCase();
		if(url.indexOf("android") > 0){
			$(".cash_title,.question,#offline_app").hide();
		}
		if(a == 0){

			$('.cash_title').hide();

		}else if(url.indexOf("android") > 0){

			$(".cash_title,.question,#offline_app").hide();
			
		}else{

			var url = window.location.href.toLowerCase().indexOf("/activity/index");

			if(url < 0){

				window.location.href = "/activity/index";
			}
			
		}
});
*/
$(document).ready(function(){
		if($(document.body).height() < $(window).height()){
			// $("#fd").show(); 
			$(".cash_footer").css({"visibility":"visible"});
		}
		else{
			$(".cash_footer").css({"visibility":"hidden"});
		}
		$(window).scroll(function(){
	    	if($(document).scrollTop()>=$(document).height()-$(window).height()){
	    		// $("#fd").show();
	    		$(".cash_footer").css({"visibility":"visible"});
			}
	    	else{
	    		// $("#fd").hide();
	    		$(".cash_footer").css({"visibility":"hidden"});
	    	}
		});
		$("input").focus(function(){
			$(".cash_footer").hide();
			$(".cash_view").css("padding-bottom","100px");
		});
		$("input").blur(function(){
			$(".cash_footer").show();
			$(".cash_view").css("padding-bottom","70px");
		});
		//关闭
		$(".pop_sign_btn").click(function(){
			$(".cash_pop").hide();
		});
});
//添加顶部的title的js
$(function(){
	(function(){
		var json = {
			"borrow":{
				"index":"小额消费借款",
				"verifyuserstatus":"验证身份",
				"userbaseinfo":"基本信息",
				"verifyphone":"验证手机",
				"msgcheck":"现贷猫",
				"sign":"签约",
				"ticket":"注册成功",
				"addcard":"添加银行卡",
				"bindbankcard":"确认银行卡",
				"idfalse":"验证失败",
				"idsure":"验证成功",
				"checkresult":"处理中",
				"calm":"冷静期"
			},
			"index":{
				"borrowagreement":"借款协议",
				"borwser":"现贷猫",
				"commission":"授权书",
				"serviceagreement":"服务协议",
				"webserviceterms":"网站服务条款",
				"borrowmanage":"逾期处理措施"
			},
			"member":{
				"regist":"现贷猫",
				"index":"我的账户",
				"borrow":"我的账户",
				"currborrow":"小额消费借款",
				"ticket":"我的账户",
				"details":"借款详情"
			},
			"repayment":{
				"editbank":"其他银行卡",
				"index":"",
				"renewalinfo":"还款/续期",
				"renewalpay":"还款/续期",
				"repayauto":"还款/续期",
				"repaymentinfo":"还款/续期",
				"repaymenttype":"还款/续期",
				"repaymentoffline":"手动还款"
			},
			"wechat":{
				"getopenld":""
			},
			"question":{
				"index":"常见问题"
			},
			"activity":{
				"appdown":"现贷猫",
				"index":"现贷猫APP下载"
			}
		}
		var key1 = location.href.split("/")[3].toLocaleLowerCase();
		var key2 = location.href.split("/")[4].toLocaleLowerCase();
		var key3 = key2.split("?")[0];
		$("title").html(json[key1][key3]);
		$("#cash_title1").html(json[key1][key3]);

	})();
	//app头文件
	(function(){
		var url = location.href.toLowerCase();
		if(url.indexOf("android") > 0){
			$(".cash_title,.question,#offline_app").hide();
		}
	})();
})
function check_name(id){
	var str2 = "赵钱孙李周吴郑王冯陈褚卫蒋沈韩杨朱秦尤许何吕施张孔曹严华金魏陶姜戚谢邹喻柏水窦章云苏潘葛奚范彭郎鲁韦昌马苗凤花方俞任袁柳酆鲍史唐费廉岑薛雷贺倪汤滕殷罗毕郝邬安常乐于时傅皮卞齐康伍余元卜顾孟平黄和穆萧尹姚邵湛汪祁毛禹狄米贝明臧计伏成戴谈宋茅庞熊纪舒屈项祝董梁杜阮蓝闵席季麻强贾路娄危江童颜郭梅盛林刁锺徐邱骆高夏蔡田樊胡凌霍虞万支柯昝管卢莫经房裘缪干解应宗丁宣贲邓郁单杭洪包诸左石崔吉钮龚程嵇邢滑裴陆荣翁荀羊於惠甄麴家封芮羿储靳汲邴糜松井段富巫乌焦巴弓牧隗山谷车侯宓蓬全郗班仰秋仲伊宫宁仇栾暴甘钭历戎祖武符刘景詹束龙叶幸司韶郜黎蓟溥印宿白怀蒲邰从鄂索咸籍赖卓蔺屠蒙池乔阳郁胥能苍双闻莘党翟谭贡劳逄姬申扶堵冉宰郦雍却璩桑桂濮牛寿通边扈燕冀僪浦尚农温别庄晏柴瞿阎充慕连茹习宦艾鱼容向古易慎戈廖庾终暨居衡步都耿满弘匡国文寇广禄阙东欧殳沃利蔚越夔隆师巩厍聂晁勾敖融冷訾辛阚那简饶空曾毋沙乜养鞠须丰巢关蒯相查后荆红游竺权逮盍益桓公万俟司马上官欧阳夏侯诸葛闻人东方赫连皇甫尉迟公羊澹台公冶宗政濮阳淳于单于太叔申屠公孙仲孙轩辕令狐钟离宇文长孙慕容司徒司空召有舜叶赫那拉丛岳寸贰皇侨彤竭端赫实甫集象翠狂辟典良函芒苦其京中夕之章佳那拉冠宾香果依尔根觉罗依尔觉罗萨嘛喇赫舍里额尔德特萨克达钮祜禄他塔喇喜塔腊讷殷富察叶赫那兰库雅喇瓜尔佳舒穆禄爱新觉罗索绰络纳喇乌雅范姜碧鲁张廖张简图门太史公叔乌孙完颜马佳佟佳富察费莫蹇称诺来多繁戊朴回毓鉏税荤靖绪愈硕牢买但巧枚撒泰秘亥绍以壬森斋释奕姒朋求羽用占真穰翦闾漆贵代贯旁崇栋告休褒谏锐皋闳在歧禾示是委钊频嬴呼大威昂律冒保系抄定化莱校么抗祢綦悟宏功庚务敏捷拱兆丑丙畅苟随类卯俟友答乙允甲留尾佼玄乘裔延植环矫赛昔侍度旷遇偶前由咎塞敛受泷袭衅叔圣御夫仆镇藩邸府掌首员焉戏可智尔凭悉进笃厚仁业肇资合仍九衷哀刑俎仵圭夷徭蛮汗孛乾帖罕洛淦洋邶郸郯邗邛剑虢隋蒿茆菅苌树桐锁钟机盘铎斛玉线针箕庹绳磨蒉瓮弭刀疏牵浑恽势世仝同蚁止戢睢冼种涂肖己泣潜卷脱谬蹉赧浮顿说次错念夙斯完丹表聊源姓吾寻展出不户闭才无书学愚本性雪霜烟寒少字桥板斐独千诗嘉扬善揭祈析赤紫青柔刚奇拜佛陀弥阿素长僧隐仙隽宇祭酒淡塔琦闪始星南天接波碧速禚腾潮镜似澄潭謇纵渠奈风春濯沐茂英兰檀藤枝检生折登驹骑貊虎肥鹿雀野禽飞节宜鲜粟栗豆帛官布衣藏宝钞银门盈庆喜及普建营巨望希道载声漫犁力贸勤革改兴亓睦修信闽北守坚勇汉练尉士旅五令将旗军行奉敬恭仪母堂丘义礼慈孝理伦卿问永辉位让尧依犹介承市所苑杞剧第零谌招续达忻六鄞战迟候宛励粘萨邝覃辜初楼城区局台原考妫纳泉老清德卑过麦曲竹百福言第五佟爱年笪谯哈墨连南宫赏伯佴佘牟商西门东门左丘梁丘琴后况亢缑帅微生羊舌海归呼延南门东郭百里钦鄢汝法闫楚晋谷梁宰父夹谷拓跋壤驷乐正漆雕公西巫马端木颛孙子车督仉司寇亓官三小鲜于锺离盖逯库郏逢阴薄厉稽闾丘公良段干开光操瑞眭泥运摩伟铁迮";
	var name = id;
    if(!/^[\u4e00-\u9fa5]+$/gi.test($(name).val())){
    	return "只能输入汉字";
    }else{
    	var str = $(name).val();
        var arr = str.split("");
        var str3 = "";
        len = arr.length;
        if(len > 1 && len < 5 ){
        	if(str2.indexOf(arr[0]) == -1){
            	return "不在姓氏里面";
            }else{
            	return false;
            }
        }else{
        	return "字数不对！";
        }
        
    }
        
}
//判断是否是app
function app(){
	var url = location.href.toLowerCase();
	if(url.indexOf("android") > 0){
		return true;
	}else{
		return false;
	}
}
//url后面加参数
function url_rewrite(url){
	var date = new Date();
	var num = date.getTime();
	return url+"?v="+num;
}
