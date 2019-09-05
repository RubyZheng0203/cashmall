<?php
use App\Library\Weiqianbao\Weiqianbao;
use App\Library\Weiqianbao\Protocol\CreateActiveUser\Request as CreateActiveUserRequest ;
use App\Library\Weiqianbao\Protocol\CreateActiveUser\Response as CreateActiveUserResponse;
use App\Library\Weiqianbao\Protocol\BindingVerify\Request as BindingVerifyRequest;
use App\Library\Weiqianbao\Protocol\BindingVerify\Response as BindingVerifyResponse;
use App\Library\Weiqianbao\Protocol\SetRealName\Request as SetRealNameRequest;
use App\Library\Weiqianbao\Protocol\SetRealName\Response as SetRealNameResponse;
use App\Library\Weiqianbao\Protocol\CreateBidInfo\Request as CreateBidInfoRequest;
use App\Library\Weiqianbao\Protocol\CreateBidInfo\Response as CreateBidInfoResponse;
use App\Library\Weiqianbao\Protocol\QueryBidInfo\Request as QueryBidInfoRequest;
use App\Library\Weiqianbao\Protocol\QueryBidInfo\Response as QueryBidInfoResponse;
use App\Library\Weiqianbao\Protocol\QueryBankCard\Request as QueryBankCardRequest;
use App\Library\Weiqianbao\Protocol\QueryBankCard\Response as QueryBankCardResponse;
use App\Library\Weiqianbao\Protocol\SetPayPassword\Request as SetPayPasswordRequest;
use App\Library\Weiqianbao\Protocol\SetPayPassword\Response as SetPayPasswordResponse;
use App\Library\Weiqianbao\Protocol\ModifyPayPassword\Request as ModifyPayPasswordRequest;
use App\Library\Weiqianbao\Protocol\ModifyPayPassword\Response as ModifyPayPasswordResponse;
use App\Library\Weiqianbao\Protocol\FindPayPassword\Request as FindPayPasswordRequest;
use App\Library\Weiqianbao\Protocol\FindPayPassword\Response as FindPayPasswordResponse;
use App\Library\Weiqianbao\Protocol\QueryIsSetPayPassword\Request as QueryIsSetPayPasswordRequest;
use App\Library\Weiqianbao\Protocol\QueryIsSetPayPassword\Response as QueryIsSetPayPasswordResponse;
use App\Library\Weiqianbao\Protocol\CreateSingleHostingPayToCardTrade\Request as CreateSingleHostingPayToCardTradeRequest;
use App\Library\Weiqianbao\Protocol\CreateSingleHostingPayToCardTrade\Response as CreateSingleHostingPayToCardTradeResponse;
use App\Library\Weiqianbao\Protocol\UnbindingBankCard\Request as UnbindingBankCardRequest;
use App\Library\Weiqianbao\Protocol\UnbindingBankCard\Response as UnbindingBankCardResponse;
use App\Library\Weiqianbao\Protocol\UnbindingBankCardAdvance\Request as UnbindingBankCardAdvanceRequest;
use App\Library\Weiqianbao\Protocol\UnbindingBankCardAdvance\Response as UnbindingBankCardAdvanceResponse;
use App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade\Request as CreateHostingCollectTradeRequest;
use App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade\Response as CreateHostingCollectTradeResponse;
use App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade\Request as CreateSingleHostingPayTradeRequest;
use App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade\Response as CreateSingleHostingPayTradeResponse;
use App\Library\Weiqianbao\PayMethod\Balance;
use App\Library\Weiqianbao\PayMethod\Extend\BalanceExtend;

require APP_PATH."Common/Lib.php";
require APP_PATH."Common/DataSource.php";

function acl_get_key(){
	empty($model)?$model=strtolower(MODULE_NAME):$model=strtolower($model);
	empty($action)?$action=strtolower(ACTION_NAME):$action=strtolower($action);
	
	$keys = array($model,'data','eqaction_'.$action);
	require C('APP_ROOT')."Common/acl.inc.php";
	$inc = $acl_inc;
	
	$array = array();
	foreach($inc as $key => $v){
			if(isset($v['low_leve'][$model])){
				$array = $v['low_leve'];
				continue;
			}
	}//找到acl.inc中对当前模块的定义的数组
	
	$num = count($keys);
	$num_last = $num - 1;
	$this_array_0 = &$array;
	$last_key = $keys[$num_last];
	
	for ($i = 0; $i < $num_last; $i++){
		$this_key = $keys[$i];
		$this_var_name = 'this_array_' . $i;
		$next_var_name = 'this_array_' . ($i + 1);        
		if (!array_key_exists($this_key, $$this_var_name)) {            
			break;       
		}        
		$$next_var_name = &${$this_var_name}[$this_key];    
	}    
	/*取得条件下的数组  ${$next_var_name}得到data数组 $last_key即$keys = array($model,'data','eqaction_'.$action);里面的'eqaction_'.$action,所以总的组成就是，在acl.inc数组里找到键为$model的数组里的键为data的数组里的键为'eqaction_'.$action的值;*/
	$actions = ${$next_var_name}[$last_key];//这个值即为当前action的别名,然后用别名与用户的权限比对,如果是带有参数的条件则$actions是数组，数组里有相关的参数限制
	if(is_array($actions)){
		foreach($actions as $key_s => $v_s){
			$ma = true;
			if(isset($v_s['POST'])){
				foreach($v_s['POST'] as $pkey => $pv){
					switch($pv){
						case 'G_EMPTY';//必须为空
							if( isset($_POST[$pkey]) && !empty($_POST[$pkey]) ) $ma = false;
						break;
					
						case 'G_NOTSET';//不能设置
							if( isset($_POST[$pkey]) ) $ma = false;
						break;
					
						case 'G_ISSET';//必须设置
							if( !isset($_POST[$pkey]) ) $ma = false;
						break;
					
						default;//默认
							if( !isset($_POST[$pkey]) || strtolower($_POST[$pkey]) != strtolower($pv) ) $ma = false;
						break;
					}
				}
			}
			
			if(isset($v_s['GET'])){
				foreach($v_s['GET'] as $pkey => $pv){
					switch($pv){
						case 'G_EMPTY';//必须为空
							if( isset($_GET[$pkey]) && !empty($_GET[$pkey]) ) $ma = false;
						break;
					
						case 'G_NOTSET';//不能设置
							if( isset($_GET[$pkey]) ) $ma = false;
						break;
					
						case 'G_ISSET';//必须设置
							if( !isset($_GET[$pkey]) ) $ma = false;
						break;
					
						default;//默认
							if( !isset($_GET[$pkey]) || strtolower($_GET[$pkey]) != strtolower($pv) ) $ma = false;
						break;
					}
					
				}
			}
			if($ma)	return $key_s;
			else $actions="0";
		}//foreach
	}else{
		return $actions;
	}
}

// 第三方支付
//* 移动支付使用该方法
//获取客户端ip地址
//注意:如果你想要把ip记录到服务器上,请在写库时先检查一下ip的数据是否安全.
//*
function getIp() {
    if (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP'); 
	}
	elseif (getenv('HTTP_X_FORWARDED_FOR')) { //获取客户端用代理服务器访问时的真实ip 地址
			$ip = getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif (getenv('HTTP_X_FORWARDED')) { 
			$ip = getenv('HTTP_X_FORWARDED');
	}
	elseif (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR'); 
	}
	elseif (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
	}
	else if(!empty($_SERVER["REMOTE_ADDR"])){
			$cip = $_SERVER["REMOTE_ADDR"];  
	}else{
			$cip = "unknown";  
	}
	return $ip;
}

/**
 * 更新邀请码
 * @param 借款会员编号 $uid
 */
function set_member_invite_code($uid)
{
	$member = M('members')->field('id,iphone')->where("id={$uid}")->find();
	if ($member) {
		$invite_code = substr($member['iphone'], -8);
		$res = M('members')->field('id,iphone')->where("invite_code='{$invite_code}'")->find();
		if ($res) {
			$invite_code = 'a'.$invite_code;
		}
		$member['invite_code'] = (string) $invite_code;
		M('members')->where("id={$uid}")->save($member);
		return true;
	}
	return false;
}

/**
 * 获取借款会员邀请码
 * @param 借款会员编号  $uid
 * @return 借款会员邀请码
 */
function get_member_invite_code($uid)
{
	$invite_code = '';
	$member = M('members')->field('id,invite_code')->where("id={$uid}")->find();
	if ($member) {
		$invite_code = trim($member['invite_code']);
	}
	return $invite_code;
}

/**
 * https请求方法
 * @param unknown $url
 * @param string $data
 */
 function http_request($url,$data=NULL){
     $header = array(
         'Content-Type: application/json;',
     );
     
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * https请求方法
 * @param unknown $url
 * @param string $data
 */
function http_request_zh($url,$data=NULL){
    $header = array(
        "content-type: application/x-www-form-urlencoded; charset=UTF-8"
    );
     
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

/**
 * 创建猛犸设备信息
 * @param 借款用户UID  $uid
 * @param 猛犸tick    $tick
 * @param 事件ID      $event
 * @param 事件状态         $status 0：失败 1：成功
 */
function creatDevice($uid,$tick,$event,$status) {
    $url = "https://www.id-linking.com/api/v1/5g0hau6ige6ndtwx6fg9a9gyxbhcgjg4/tick/".$tick;
    $res = http_request($url);
    $arr = json_decode($res,true);
    
    $data['uid']         = $uid;
    $data['event']       = $event;
    $data['status']      = $status;
    $data['maxent_id']   = $arr['data']['maxent_id'];
    $data['campaign_id'] = $arr['data']['campaign_id'];
    $data['session_id']  = $arr['data']['session_id'];
    $data['tick_id']     = $arr['data']['tick'];
    $data['ip']          = $arr['data']['ip'];
    $data['os']          = $arr['data']['os'];
    $data['device']      = $arr['data']['device'];
    $data['imei']        = $arr['data']['did']['imei'];
    $data['aid']         = $arr['data']['did']['aid'];
    $data['mac']         = $arr['data']['did']['mac'];
    $data['agent']       = $arr['data']['user_agent'];
    $data['add_time']    = time();
    $res = M("member_device")->add($data);
    
    return $res;
}

/**
 * 白骑士芝麻授权接口
 * @param 身份证 $id_card
 * @param 姓名 $real_name
 * @param 授权页面 $channel apppc:pc app：手机
 * @param 授权成功返回路径 $callbackUrl
 * @return authInfoUrl 授权页面的URL 为空表示请求失败
 */
function bqszhima($id_card,$real_name,$channel,$callbackUrl) {
    $done               = false;
    $config             = require(APP_PATH . "/Conf/baiqishi.php");
    $linkedMerchantId   = $config["linkedMerchantId"];
    $productId          = $config["productId"];
    $identityType       = $config["identityType"];
    $partnerId          = $config["partnerId"];
    $verifyKey          = $config["verifyKey"];

    $data = "{
    \"linkedMerchantId\":\"$linkedMerchantId\",
    \"productId\":\"$productId\",
    \"extParam\":{
    \"certNo\":\"$id_card\",
    \"identityType\":\"$identityType\",
    \"name\":\"$real_name\",
    \"channel\":\"$channel\",
    \"callbackUrl\":\"$callbackUrl\"
    },
    \"partnerId\":\"$partnerId\",
    \"verifyKey\":\"$verifyKey\"
    }";
    $url= $config['getway'];

    $res   = http_request($url,$data);
    $zhima =  json_decode($res,true);
    return $zhima['resultData']['authInfoUrl'];
}

/**
 * 白骑士淘宝授权接口
 * @param 身份证 $id_card
 * @param 姓名 $real_name
 * @param 授权页面 $channel apppc:pc app：手机
 * @param 授权成功返回路径 $callbackUrl
 * @return authInfoUrl 授权页面的URL 为空表示请求失败
 */
function bqstaobao($id_card,$real_name,$mobile) {
    $config             = require(APP_PATH . "/Conf/baiqishi.php");
    $partnerId          = $config["partnerId"];
    $tb                 = tb;
    $userName           = '15256381560';
    $pwd                = 'liu231938';
     $data = "{
        \"partnerId\":\"$partnerId\",
        \"certNo\":\"$id_card\",
        \"mobile\":\"$mobile\",
        \"name\":\"$real_name\",
        \"loginType\":\"tb\",
        \"userName\":\"$userName\",
        \"pwd\":\"$pwd\",
        \"smsCode\":\"169296\",
        \"reqId\":\"3c20d279359c4e72a6c60cededcd4484\",
         }";
//     $data = "{
//         \"partnerId\":\"fumi\",
//         \"certNo\":\"320826199411205629\",
//         \"mobile\":\"18217786712\",
//         \"name\":\"曾雅云\",
//         \"loginType\":\"tb\",
//         \"userName\":\"陌路誓言zyy\",
//         \"pwd\":\"1120yunzi\"https://credit.baiqishi.com/clweb/api/zm/getoriginala60088f545134b13beb1304632b1bc2a
// }";
     
    $url= "https://credit.baiqishi.com/clweb/api/zm/login";
    $res   = http_request($url,$data);
    $taobao  =  json_decode($res,true);
    var_dump($taobao);
}
function bqsjifen($id_card,$real_name,$mobile){
    $config             = require(APP_PATH . "/Conf/baiqishi.php");
    $partnerId          = $config["partnerId"];
    $verifyKey          = $config["verifyKey"];
    $data = "{
    \"partnerId\":\"$partnerId\",
    \"certNo\":\"$id_card\",
    \"mobile\":\"$mobile\",
    \"name\":\"$real_name\",
    \"verifyKey\":\"$verifyKey\",
    }";
    $url= "https://credit.baiqishi.com/clweb/api/zm/getoriginal";
    $res   = http_request($url,$data);
    $taobao  =  json_decode($res,true);
    var_dump($taobao);
}
/**
 * 白骑士芝麻查询授权接口
 * @param openid $openId
 * @return 授权状态 false：失败 success：成功
 */
function bqszhimaSearch($openId) {
    $done               = false;
    $config             = require(APP_PATH . "/Conf/baiqishi.php");
    $linkedMerchantId   = $config["linkedMerchantId"];
    $productId          = $config["productIdS"];
    $identityType       = $config["identityTypeS"];
    $partnerId          = $config["partnerId"];
    $verifyKey          = $config["verifyKey"];

    $data = "{
    \"linkedMerchantId\":\"$linkedMerchantId\",
    \"productId\":\"$productId\",
    \"extParam\":{
    \"openId\":\"$openId\",
    \"identityType\":\"$identityType\"
    },
    \"partnerId\":\"$partnerId\",
    \"verifyKey\":\"$verifyKey\"
    }";
    $url= $config['getway'];

    $res   = http_request($url,$data);
    $zhima =  json_decode($res,true);
    return $zhima['resultData']['authorized'];
}

/**
 * 白骑士芝麻数据反馈文件名
 */
function createzhimaOrder()
{
    return "ZM" . date("YmdHis") . mt_rand(10000, 99999);
}

/**
 * 白骑士芝麻数据反馈接口
 * @param 借款ID $borrowid
 * @param 借款用户 $uid
 * @param 场景状态 $scene_status  0-分期还款 1-逾期未还款 2-用户全部还款  3-用户取消  4-放款拒绝  5-放款通过
 * @param 备注 $memo
 * @return 反馈状态  true
 */
function bqszhimaOrder($borrowid,$uid,$scene_status,$memo) {
    $done               = false;
    $config             = require(APP_PATH . "/Conf/baiqishi.php");
    $linkedMerchantId   = $config["linkedMerchantId"];
    $productId          = "102003";
    $identityType       = $config["identityTypeS"];
    $partnerId          = $config["partnerId"];
    $verifyKey          = $config["verifyKey"];

    $today    = date('Y-m-d',time());
    $mem      = M("member_info")->field(" id_card, real_name ")->where(" uid =".$uid)->find();
    $borrow   = M("borrow_apply")->field(true)->where(" id = {$borrowid} ")->find();
    $idcard   = $mem['id_card'];
    $realname = $mem['real_name'];
    $order_no = "Mall".$borrowid;

    //场景状态 0-履约（分期的按时还款）1-违约（逾期未还款）2-结清（用户还款）3-用户放弃（用户取消）4-审批拒绝（放款拒绝）5-审批通过（放款通过）
    //金额：场景状态为0、1、2时：填放款金额；场景状态为3、4时：填申请金额；场景状态为5时：填授信金额；场景状态为6时：填放款金额；
    //业务阶段日期场景状态 0-履约（应还日期）1-违约（应还日期）2-结清（应还日期）3-用户放弃（取消日期）4-审批拒绝（拒绝日期）5-审批通过（通过日期）
	$ovd_date = '';
	$due_amt  = 0;
	$due_day  = get_due_day($borrow['deadline']);
    switch ($scene_status) {
        case 0:
            $amt      = $borrow['loan_money'];
            $ins_date = date('Y-m-d',$borrow['deadline']);
            break;
        case 1:
            $amt      = $borrow['loan_money'];
            $ins_date = date('Y-m-d',$borrow['deadline']);
			$due_fee  = 0;
            $late_fee = 0;
            if($due_day>0){
                $due_fee  = get_due_fee($borrow['money'], $borrow['item_id'],$due_day);
                $late_fee = get_late_fee($borrow['money'], $borrow['item_id'],$due_day);
            }

            $due_amt  = getFloatValue($borrow['money']+$borrow['interest']+$due_fee+$late_fee,2);
			//$due_date = strtotime("+1 day",strtotime(date("Y-m-d",$borrow['deadline'])." 00:00:00"));
			//$ovd_date = date('Y-m-d',$due_date);
			$ovd_date = date('Y-m-d',$borrow['deadline']);
            break;
        case 2:
            $amt      = $borrow['loan_money'];
            if($due_day>0){
                $ins_date = $today;
            }else{
                $ins_date = date('Y-m-d',$borrow['deadline']);
            }
            break;
        case 3:
            $amt      = $borrow['money'];
            $ins_date = date('Y-m-d',time());
            break;
        case 4:
            $amt      = $borrow['money'];
            $ins_date = date('Y-m-d',$borrow['refuse_time']);
            break;
        case 5:
            $amt      = $borrow['money'];
            $ins_date = date('Y-m-d',$borrow['len_time']);
            break;
        default :
            $amt      = $borrow['loan_money'];
            $ins_date = date('Y-m-d',$borrow['len_time']);
            break;
    }

    $json = array(
        'biz_date'=> $today,
        'linked_merchant_id'=>$linkedMerchantId,
        'user_credentials_type'=>0,
        'user_credentials_no'=>$idcard,
        'user_name'=>$realname,
        'order_no'=>$order_no,
        'scene_type'=>1,
        'scene_desc'=>'现金借款',
        'scene_status'=>$scene_status,
        'create_amt'=>$amt,
        'installment_due_date'=>$ins_date,
        'overdue_amt'=>$due_amt,
        'gmt_ovd_date'=>$ovd_date,
        'rectify_flag'=>0,
        'memo'=>$memo
    );
	wqbLog("zhifubaofankui------------------".$borrowid);
    wqbLog($json);
    $data = Array($json);
    $data = array(
        'records' => $data
    );


    //把PHP数组转成JSON字符串
    $json_string = json_encode($data);
    $filename = createzhimaOrder();
    file_put_contents('d:/json/'.$filename.'.json', base64_encode($json_string));
    $file = file_get_contents('d:/json/'.$filename.'.json');
    $data = "{
    \"partnerId\":\"$partnerId\",
    \"verifyKey\":\"$verifyKey\",
    \"linkedMerchantId\":\"$linkedMerchantId\",
    \"productId\":\"$productId\",
    \"extParam\":{
    \"fileCharset\":\"UTF-8\",
    \"records\":\"100\",
    \"primaryKeyColumns\":\"$order_no\",
    \"bizExtParams\":{\"extParam1\":\"value1\",},
    \"file\":\"$file\",
    \"columns\":\"biz_date, linked_merchant_id, user_credentials_type, user_credentials_no, user_name, order_no, scene_type, scene_desc, scene_status, create_amt, installment_due_date, overdue_amt, gmt_ovd_date, rectify_flag, memo\"
},
}";
    $url   = $config['getway'];
    $res   = http_request($url,$data);
    $zhima = json_decode($res,true);
    $success = $zhima['resultData']['success'];
    wqbLog("芝麻数据反馈:--- taskId:".$zhima['resultData']['taskId']."scene---".$scene_status."--ddddd--".$success);
    return $zhima['resultData']['success'];
}

/**
 *
 * @param 福米上标日志$message
 */
function fumiLog($message)
{
    if ( is_array($message) ){
        $message = json_encode($message);
    }
    error_log("[".date('Y-m-d H:i:s')."] ".$message."\r\n", 3, "d:/logs/fumbid.log");
}

/**
 *
 * @param 安卓日志$message
 */
function appLog($message)
{
    if ( is_array($message) ){
        $message = json_encode($message);
    }
    error_log("[".date('Y-m-d H:i:s')."] ".$message."\r\n", 3, "d:/logs/exception.log");
}


/**********************************新浪支付Start*********************************************/
/**
 * 
 * @param 新浪支付日志 $message
 */
function wqbLog($message)
{
    if ( is_array($message) ){
        $message = json_encode($message);
    }
    error_log("[".date('Y-m-d H:i:s')."] ".$message."\r\n", 3, "d:/logs/fumi.log");
}

/**
 *
 * @param 安卓抓取数据 $message
 */
function androidLog($message)
{
    if ( is_array($message) ){
        $message = json_encode($message);
    }
    error_log("[".date('Y-m-d H:i:s')."] ".$message."\r\n", 3, "d:/logs/android.log");
}

/**
 * 托管代收交易订单号(直连模式)
 * @return string
 */

function createCollectTradeOrderSn()
{
    return "MLCT" . date("YmdHis") . mt_rand(10000, 99999);
}

function createPayTradeOrderSn()
{
    return "MLPT" . date("YmdHis") . mt_rand(10000, 99999);
}

/**
 * 创建托管代收交易(直连模式)
 * @param 用户编号 $uid
 * @param 代收摘要 $summary
 * @param 金额 $amount
 */
function sinaHostingCollectionTradeDirect($uid, $summary, $amount)
{
    $amount = number_format($amount, 2, ".", "");
    if ($amount<=0) return;

    $extend = new BalanceExtend();
    $extend->accountType = "BASIC";
    $payMethod = new Balance($extend);
    $payMethod->setAmount($amount);
    $payMethodStr = $payMethod->toString();
    $wqbRequest = new CreateHostingCollectTradeRequest();
    $wqbResponse = new CreateHostingCollectTradeResponse();
    $wqbRequest->out_trade_no = $sn = createCollectTradeOrderSn();
    $wqbRequest->out_trade_code = "1001";
    $wqbRequest->summary = $summary;
    $wqbRequest->payer_id = "fumi".$uid;
    $wqbRequest->payer_identity_type = "UID";
    $wqbRequest->pay_method = $payMethodStr;
    $wqbRequest->payer_ip = get_client_ip();
    $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
    $weiqianbao->fire();

    $data = array(
        "uid"    => $uid,
        "amount" => $amount,
        "sn"     => $sn,
    );
    wqbLog("sinapay collect trade request: 新浪直连代收请求发出  ".json_encode($data));
    wqbLog("sinapay collect trade response: ".json_encode($wqbResponse->getRawData()));
    if($wqbResponse->getRawData()['response_code']=="APPLY_SUCCESS"){
        $rescode = 1;
    }else if($wqbResponse->getRawData()['response_code']=="AUTH_EMPTY"){
        $rescode = 2;
    }else if($wqbResponse->getRawData()['response_code']=="PAY_FAILED"){
        $rescode = 3;
    }else if($wqbResponse->getRawData()['response_code']=="AUTH_QUOTA_FAIL"){
        $rescode = 4;
    }else if($wqbResponse->getRawData()['response_code']=="AUTH_DAY_QUOTA_FAIL"){
        $rescode = 5;
    }else{
        $rescode = 6;
    }

    return $rescode;
}

/**
 * 中间帐户转账
 * @param 新浪帐户ID $uid fumi or mall
 * @param 转账金额  $amount
 */
function wqbHostingPayTradeToUID($uid, $amount,$summary)
{
    return wqbHostingPayTrade($uid, $amount,$summary);//存钱罐
}


/**
 * 创建托管代付交易
 * @param $identifyId
 * @param $identifyType
 * @param $amount
 */
function wqbHostingPayTrade($uid, $amount, $summary)
{
    $amount = number_format($amount, 2, ".", "");
    if ($amount<=0) return false;

    $wqbRequest  = new CreateSingleHostingPayTradeRequest();
    $wqbResponse = new CreateSingleHostingPayTradeResponse();
    $wqbRequest->out_trade_no = $sn = createPayTradeOrderSn();
    $wqbRequest->out_trade_code = "2001";
    $wqbRequest->payee_identity_id = $uid;
    $wqbRequest->payee_identity_type = "UID";
    $wqbRequest->account_type = "BASIC";
    $wqbRequest->amount = $amount;
    $wqbRequest->summary = "代付X:".$summary;
    $wqbRequest->user_ip = get_client_ip();
    $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
    $weiqianbao->fire();

    $i = 0;
    while (!$wqbResponse->success()) {
        if ($i > 2) {
            break;
        } else {
            $i++;
            sleep(2);
            $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
            $weiqianbao->fire();
        }
    }

    $data = array(
        "uid"          => $uid,
        "account_type" => "UID",
        "amount"       => $amount,
        "sn"           => $sn,
    );
    wqbLog("续期转账 request: ".json_encode($data));
    wqbLog("续期转账 response: ".json_encode($wqbResponse->getRawData()));
    return $wqbResponse;
}

/**
 * 新浪创建激活会员
 * @param 用户编号 $newid
 * @return boolean
 */
function sinaCreatMember($newid){
    $request  = new CreateActiveUserRequest();
    $response = new CreateActiveUserResponse();
    
    $request->identity_id   = "mall".$newid;
    $request->identity_type = "UID";
    $request->member_type   = 1;
    $request->client_ip     = get_client_ip();
    $weiqianbao = new Weiqianbao($request, $response);
    $weiqianbao->fire();
    wqbLog("创建激活会员".$response->error());
    if (!$response->success()) {
        wqbLog("创建激活会员:--".$newid."创建失败。失败原因：".$response->error());
        $flg = false;
    }else{
        $flg = true;
    }
    return $flg;
}


/**
 * 新浪认证会员
 * @param 用户编号 $newid
 * @param 手机号码 $phone
 * @return boolean
 */
function sinaBindingVerify($newid,$phone){
    $wqbRequest = new BindingVerifyRequest();
    $wqbResponse = new BindingVerifyResponse();
    
    $wqbRequest->identity_id    = "mall".$newid;
    $wqbRequest->identity_type  = "UID";
    $wqbRequest->verify_type    = "MOBILE";
    $wqbRequest->verify_entity  = $phone;
    $wqbRequest->client_ip      = get_client_ip();
    $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
    $weiqianbao->fire();
    if (!$wqbResponse->success()) {
        wqbLog("手机认证:--".$phone."认证失败。失败原因：".$wqbResponse->error());
        $flg = false;
    }else{
        wqbLog("手机认证:--".$phone."认证成功。");
        $flg = true;
    }
    return $flg;
}

/**
 * 新浪实名认证
 * @param 用户编号 $newid
 * @param 用户姓名 $name
 * @param 身份证号码 $cert
 * @return boolean
 */
function sinaNameVerify($newid,$name,$cert){
    $wqbRequest = new SetRealNameRequest();
    $wqbResponse = new SetRealNameResponse();
    $wqbRequest->identity_id    = "mall".$newid;
    $wqbRequest->identity_type  = "UID";
    $wqbRequest->real_name      = $name;
    $wqbRequest->cert_type      = "IC";
    $wqbRequest->cert_no        = $cert;
    $wqbRequest->need_confirm   = "Y";
    $wqbRequest->client_ip      = get_client_ip();
    $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
    $weiqianbao->fire();
    if (!$wqbResponse->success() && $wqbResponse->errno() != "DUPLICATE_VERIFY") {
        wqbLog("uid:{$newid},实名认证:--".$name."认证失败。失败原因：".$wqbResponse->error());
        $flg = false;
    } else {
        wqbLog("uid:{$newid},实名认证:--".$name."成功。");
        $flg = true;
    }
    return $flg;
}

 /**
  * 新浪标的录入
  * @param 申请借款数据表 $list
  * @return boolean
  */
function sinaCreateBidInfo($list){
    $apply = M("borrow_apply")->where( "id = {$list['id']}")->find();
    if($apply['sinabid_flg'] == 0){
        $uid = $list['uid'];
        $mem  = M("members")->field(true)->where(" id=".$uid )->find();
        if($list['purpose']==""){
            $purpose = "其他个人消费";
        }else{
            $purpose = $list['purpose'];
        }
        if($mem['sina_id'] == '0'){
            $info = "mall".$uid."~UID~".$list['money']."~".$purpose."~".$mem['iphone'];
        }else{
            $info = $mem['sina_id']."~UID~".$list['money']."~".$purpose."~".$mem['iphone'];
        }
        $wqbRequest  = new CreateBidInfoRequest();
        $wqbResponse = new CreateBidInfoResponse();
        
        $wqbRequest->out_bid_no             = "orderml".$list['id'];
        $wqbRequest->web_site_name          = "福米金融";
        $wqbRequest->bid_name               = "保证赢".$list['id'];
        $wqbRequest->bid_type               = "CREDIT";;
        $wqbRequest->bid_amount             = $list['money'];
        $wqbRequest->bid_year_rate          = $list['rate'];
        $wqbRequest->bid_duration           = $list['duration'];
        $wqbRequest->repay_type             = "REPAY_CAPITAL_WITH_INTEREST";
        $wqbRequest->protocol_type          = ""; //可空
        $wqbRequest->bid_product_type       = ""; //可空
        $wqbRequest->recommend_inst         = ""; //可空
        $wqbRequest->limit_min_bid_copys    = ""; //可空
        $wqbRequest->limit_per_copy_amount  = ""; //可空
        $wqbRequest->limit_max_bid_amount   = ""; //可空
        $wqbRequest->limit_min_bid_amount   = ""; //可空
        $wqbRequest->summary                = ""; //可空
        $wqbRequest->url                    = ""; //可空0
        $wqbRequest->begin_date             = date("YmdHis",$list['add_time']);
        $wqbRequest->term                   = date("YmdHis",$list['deadline']);
        $wqbRequest->guarantee_method       = "银行担保";
        $wqbRequest->extend_param           = ""; //可空
        $wqbRequest->borrower_info_list     = $info;
        $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
        $weiqianbao->fire();
        if (!$wqbResponse->success()){
            wqbLog("标order".$list['id']."-------录入失败。失败原因：".$wqbResponse->error());
            $flg = false;
            $data['sinabid_flg'] = 3;
            $upate = M("borrow_apply")->where( "id = {$list['id']}")->save($data);
        }else{
            wqbLog("标order".$list['id']."-------录入成功。");
            $data['sinabid_flg'] = 1;
            $upate = M("borrow_apply")->where( "id = {$list['id']}")->save($data);
            $flg = true;
        }
    }else{
        $flg = true;
    }
    
    return $flg;
}

/**
 * 新浪标的录入
 * @param 申请借款数据表 $list
 * @return boolean
 */
function sinaQueryBidInfo($bid){
    $wqbRequest  = new QueryBidInfoRequest();
    $wqbResponse = new QueryBidInfoResponse();
    
    $wqbRequest->out_bid_no     = "order".$bid;
    $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
    $weiqianbao->fire();
    wqbLog($wqbResponse);
}

/**
 * 创建新浪代付到提现卡交易的单号
 *
 * @return string
 */
function createPayToCardTradeOrderSn()
{
    return "PB" . date("YmdHis") . mt_rand(10000, 99999);
}


/**
 * 新浪单笔代付到提现卡交易
 * @param 借款申请数据集 $list
 * @param 绑定银行卡ID $bankid
 */
function sinaCreateSingleHostingPayToCardTrade($list,$bankid){
    $mem =  M("members")->field('id,sina_id')->where("id = ".$list['uid'])->find();
    $uid = '';
    if($mem['sina_id'] != '0'){
        $uid = $mem['sina_id'];
    }else{
        $uid = 'mall'.$list['uid'];
    }
    $wqbRequest  = new CreateSingleHostingPayToCardTradeRequest();
    $wqbResponse = new CreateSingleHostingPayToCardTradeResponse();
    $wqbRequest->out_trade_no       = $sn = createPayToCardTradeOrderSn();
    $wqbRequest->out_trade_code     = "2001";
    $wqbRequest->collect_method     = "binding_card^".$uid.",UID,".$bankid;
    $wqbRequest->amount             = $list['loan_money'];
    if($list['purpose']==""){
        $wqbRequest->summary        = "其他个人消费";
    }else{
        $wqbRequest->summary        = $list['purpose'];
    }
    $wqbRequest->payto_type         = "FAST";
    $wqbRequest->extend_param       = "";
    $wqbRequest->goods_id           = "orderml".$list['id'];
    $wqbRequest->creditor_info_list = "";
    $wqbRequest->user_ip            = get_client_ip();
    
    $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
    $weiqianbao->fire();
    addSinaOrders($uid, $sn, "", $list['loan_money'], $list['id'], 3);
    if (!$wqbResponse->success()){
        wqbLog("交易单号：--------".$sn."代付到提现卡交易失败。失败原因：".$wqbResponse->error());
        $flg = false;
        $data['loan_error'] = $wqbResponse->error();
        $upate = M("borrow_apply")->where( "id = {$list['id']}")->save($data);
        updateSinaOrders(2, $sn, $uid);
    }else{
        wqbLog("交易单号：--------".$sn."代付到提现卡录入成功。");
        updateSinaOrders(1, $sn, $uid);
        $flg = true;
    }
    return $flg;
}

/**
 * 新浪绑卡第一步
 */
 function sinaGetBindBankCode($uid,$bankCode,$bankCard,$phone,$province,$city){
     $memberInfo = M("members")->field('sina_id')->where("id = $uid")->find();
     $wqbRequest = new App\Library\Weiqianbao\Protocol\BindingBankCard\Request();
     $wqbResponse = new App\Library\Weiqianbao\Protocol\BindingBankCard\Response();
     $wqbRequest->request_no = "MALL".$uid.time();
     if ($memberInfo['sina_id'] === '0'){
         $wqbRequest->identity_id = "mall" . $uid;
     }else {
         $wqbRequest->identity_id = $memberInfo['sina_id'];
     }
     $wqbRequest->identity_type = "UID";
     $wqbRequest->bank_code = $bankCode;
     $wqbRequest->bank_account_no = $bankCard;
     $wqbRequest->card_type = "DEBIT";
     $wqbRequest->verify_mode = "SIGN";
     $wqbRequest->card_attribute = "C";
     $wqbRequest->phone_no = $phone;
     $wqbRequest->province = $province;
     $wqbRequest->city = $city;
     $wqbRequest->client_ip = get_client_ip();
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     if ( !$wqbResponse->success() ) {
         wqbLog(array(
             "uid" => $wqbRequest->identity_id,
             "bankcode" => $wqbRequest->bank_code,
             "bank_num" => $wqbRequest->bank_account_no,
             "response" => $wqbResponse->getRawData(),
         ));
         wqbLog($wqbResponse->error());
         $data['status'] = 0;
         $data['message'] = $wqbResponse->error();
         return $data;
     }else {
         $data['status'] = 1;
         $data['message'] = $wqbResponse->ticket;
         $tick = $wqbResponse->ticket;
         return $data;
     }
 }
 
 /**
  * 新浪绑卡第二步
  */
 function sinaBindBankCard($ticket,$code){ 
     $wqbRequest = new App\Library\Weiqianbao\Protocol\BindingBankCardAdvance\Request();
     $wqbResponse = new App\Library\Weiqianbao\Protocol\BindingBankCardAdvance\Response();
     $wqbRequest->ticket = $ticket;
     $wqbRequest->valid_code = $code;
     $wqbRequest->client_ip  = get_client_ip();
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     if (!$wqbResponse->success()){
         wqbLog($wqbResponse->error());
         $message = $wqbResponse->error();
         return $message;
     }else {
         wqbLog($ticket."-----".$code);
         return true;
     }       
 }
 
 /**
  * 获取新浪绑卡id
  */
 function getSinaBindBankCardId($uid){
     //注入点：获得用户绑定银行卡
     $memberInfo = M("members")->field('sina_id')->where("id = $uid")->find();
     if ($memberInfo['sina_id']  === '0'){
         $identifyId = "mall".$uid;
     }else {
         $identifyId = $memberInfo['sina_id'];
     }
     $queryBankCardRequest = new QueryBankCardRequest();
     $queryBankCardResponse = new QueryBankCardResponse();
     $queryBankCardRequest->identity_id = $identifyId;
     $queryBankCardRequest->identity_type = "UID";
     $weiqianbao = new Weiqianbao($queryBankCardRequest, $queryBankCardResponse);
     $weiqianbao->fire();
     if ( !$queryBankCardResponse->success() ) {
         wqbLog("网络错误，请求失败！");
     }
     $cardList = array();
     if ($queryBankCardResponse->card_list) {
         $cardList = explode("|", $queryBankCardResponse->card_list);
         $bankList = require(APP_PATH . "/Conf/bank.php");
         $cardList = array_map(
             function ($item) use ($bankList) {
                 list($cardId, $bankCode, $bankcardNum, $name, $bankcardType, $bankcardAttribute, $isVerify, $createTime, $isSafeCard)
                 = explode("^", $item);
                 $bankName = $bankList[$bankCode]["name"];
                 $tailNum = substr($bankcardNum, -4);
                 return compact(
                     "cardId",
                     "bankCode",
                     "bankcardNum",
                     "name",
                     "bankcardType",
                     "bankcardAttribute",
                     "isVerify",
                     "createTime",
                     "isSafeCard",
                     "bankName",
                     "tailNum"
                 );
             },
             $cardList
         );
     }
     $vobank = $cardList ? $cardList[0] : array();
     wqbLog($vobank);
     return $vobank['cardId'];
 }
 
 /**
  * 新浪解绑银行卡第一步
  */
 function sinaUnBindBankCard($uid,$cardId){
     $memberInfo = M("members")->field('sina_id')->where("id = $uid")->find();
     $wqbRequest = new UnbindingBankCardRequest();
     $wqbResponse = new UnbindingBankCardResponse();
     if ($memberInfo['sina_id']  === '0'){
         $wqbRequest->identity_id = "mall".$uid;
     }else {
         $wqbRequest->identity_id = $memberInfo['sina_id'];
     }     
     $wqbRequest->identity_type = "UID";
     $wqbRequest->card_id = $cardId;
     $wqbRequest->advance_flag = "Y";
     $wqbRequest->client_ip    = get_client_ip();
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     if (!$wqbResponse->success()) {
         $code = $wqbResponse->errno();
         if ( $code == "UNBINDING_SECURITY_CARD_FORBIDDING" ) {
             $error = "余额为0才可以解绑";
         }else {
             $error = $wqbResponse->error();
         }
         $data = array(
             "message"  => $error,
             "code"     =>$code,
             "status"   => false
         );
         return $data;
     }else {
         $data = array(
             "ticket" => $wqbResponse->ticket,
             "status" => true
         );
         return $data;
     }
 }

 /**
  * 新浪解绑银行卡第二步
  */
 function unBindSinaBankCard($ticket,$code,$uid){
     $memberInfo = M("members")->field('sina_id')->where("id = $uid")->find();
     $wqbRequest = new UnbindingBankCardAdvanceRequest();
     $wqbResponse = new UnbindingBankCardAdvanceResponse();
     if ($memberInfo['sina_id']  === '0'){
         $wqbRequest->identity_id = "mall".$uid;
     }else {
         $wqbRequest->identity_id = $memberInfo['sina_id'];
     }
     $wqbRequest->identity_type = "UID";
     $wqbRequest->ticket = $ticket;
     $wqbRequest->valid_code = $code;
     $wqbRequest->client_ip  = get_client_ip();
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     if (!$wqbResponse->success()) {
         $error = $wqbResponse->error();
         wqbLog($error);
         return false;
     }else{       
         return true;
     }
 }
 
 /**
  * 设置新浪支付密码
  * @param 用户编号 $uid
  * @param 使用场景 $device_type
  */
 function sinaSetPayPassword($uid){
     $wqbRequest                 = new SetPayPasswordRequest();
     $wqbResponse                = new SetPayPasswordResponse();
     $return_url                 = "http://" . $_SERVER['HTTP_HOST'] . "/Borrow/msgCheck";
     $wqbRequest->identity_id    = 'mall'.$uid;
     $wqbRequest->identity_type  = "UID";
     $wqbRequest->setBaseParam("return_url", $return_url);
 
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     redirect($wqbResponse->redirect_url);
 }
 
 function sinaSetPayPassword2($uid){ 
     $wqbRequest                 = new SetPayPasswordRequest();
     $wqbResponse                = new SetPayPasswordResponse();
     $return_url                 = "http://" . $_SERVER['HTTP_HOST'] . "/Activity/appPassword";
     $wqbRequest->identity_id    = 'mall'.$uid;
     $wqbRequest->identity_type  = "UID";
     $wqbRequest->setBaseParam("return_url", $return_url);
 
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     return $wqbResponse->redirect_url;
 }
 
 
 /**
  * 修改新浪支付密码
  * @param 用户编号 $uid
  * @param 使用场景 $device_type
  */
 function sinaModifyPayPassword($uid){
     $wqbRequest                 = new ModifyPayPasswordRequest();
     $wqbResponse                = new ModifyPayPasswordResponse();
     $return_url                 = "http://" . $_SERVER['HTTP_HOST'] . "/Borrow/msgCheck";
     $mem =  M("members")->field('id,sina_id')->where("id = ".$uid)->find();
     if($mem['sina_id'] != '0'){
         $wqbRequest->identity_id    = $mem['sina_id'];
     }else{
         $wqbRequest->identity_id    = 'mall'.$uid;
     }
     $wqbRequest->identity_type  = "UID";
     $wqbRequest->setBaseParam("return_url", $return_url);
 
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     redirect($wqbResponse->redirect_url);
 }

 /**
  * 找回新浪支付密码
  * @param 用户编号 $uid
  * @param 使用场景 $device_type
  */
 function sinaFindPayPassword($uid){
     $wqbRequest                 = new FindPayPasswordRequest();
     $wqbResponse                = new FindPayPasswordResponse();
     $return_url                 = "http://" . $_SERVER['HTTP_HOST'] . "/Borrow/msgCheck";
     $mem =  M("members")->field('id,sina_id')->where("id = ".$uid)->find();
     if($mem['sina_id'] != '0'){
         $wqbRequest->identity_id    = $mem['sina_id'];
     }else{
         $wqbRequest->identity_id    = 'mall'.$uid;
     }
     $wqbRequest->identity_type  = "UID";
     $wqbRequest->setBaseParam("return_url", $return_url);
 
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
     redirect($wqbResponse->redirect_url);
 }
 
  /**
  * 查询用户是否设置新浪支付密码
  * @param 用户编号 $uid
  * @return boolean
  */
 function sinaQueryPayPassword($uid){
     $wqbRequest = new QueryIsSetPayPasswordRequest();
     $wqbResponse = new QueryIsSetPayPasswordResponse();
     $mem =  M("members")->field('id,sina_id')->where("id = ".$uid)->find();
     if($mem['sina_id'] != '0'){
        $wqbRequest->identity_id    = $mem['sina_id'];
     }else{
		$wqbRequest->identity_id    = 'mall'.$uid;
     }
     $wqbRequest->identity_type = "UID";
 
     $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
     $weiqianbao->fire();
 
     if($wqbResponse->is_set_paypass == "Y"){
         $flg = true;
     }else {
         $flg = false;
     }
	 return $flg;
 }
 
 /**
  * 查看老用户是否已实名
  */
  function checkUserAuth($uid){
     $uid        = substr($uid, 4);
     $dbms       = C('DB_TYPE_SINA');     //数据库类型
     $host       = C('DB_HOST_SINA');  //数据库主机名
     $dbName     = C('DB_NAME_SINA');     //使用的数据库
     $username   = C('DB_USER_SINA');       //数据库连接用户名
     $passwd     = C('DB_PWD_SINA');           //对应的密码
     $dsn        = "$dbms:host=$host;dbname=$dbName";
     $link = new PDO($dsn, $username, $passwd);
     $sql = "SELECT b.real_name,b.idcard FROM lzh_members_status a LEFT JOIN lzh_member_info b on a.uid = b.uid
     WHERE a.id_status = 1 AND b.uid = $uid;";
     $res = $link->query($sql);
     if ($res){
         foreach ($res as $row) {
             $data['real_name'] = $row['real_name'];
             $data['id_card']   = $row['idcard'];
         }
         return $data;
     }else {
         return false;
     }
 }
 
 /**
  * 获取用户上次操作记录
  */
 function getUserLastOperation($uid,$bid){
    $info = M("user_operation")->field('operation')->where("uid = {$uid} and borrow_id = {$bid}")->find();
    return $info['operation'];
 }
 
 /**
  * 插入借款订单信息
  * @param int $uid 用户uid
  * @param double $money 借款金额
  * @param int $duration 借款期限
  * @param int $coupon_id 优惠券id
  * @param double $coupon_money 优惠券金额
  * @return Ambigous <mixed, boolean, string, unknown, false>|boolean
  */
 function insertCashLoanOrder($uid,$money,$duration,$coupon_id,$coupon_money){
     $model = M("borrow_apply");
     $model->startTrans();
     $itemInfo  = M("borrow_item")->field('*')->where("duration = $duration and money = $money and is_on = 1")->find();//查询借款产品信息
     $where['uid']           = $uid;
     $where['status']        = 5;
     $binfo = M("borrow_apply")->field('id')->where($where)->find();//查询用户是否借过款
     $data['uid']            = $uid;
     $data['money']          = $money;//借款金额
     $data['duration']       = $duration;//借款期限
     $data['repayment_type'] = 1;//还款类型
     $data['rate']           = $itemInfo['rate'];//利率
     $data['item_id']        = $itemInfo['id'];//产品id
     $data['interest']       = round($money*$itemInfo['rate']/100*$duration/360,2);//利息
     $data['audit_fee']      = round($money*$itemInfo['audit_rate']/100*$duration,2);//贷后管理费
     $data['created_fee']    = empty($binfo) ? $itemInfo['created_rate'] : 0;//技术服务费
     $data['enabled_fee']    = $itemInfo['enabled_rate'];//账户管理费
     $data['pay_fee']        = $itemInfo['pay_fee'];//支付服务费
     $data['coupon_id']      =  0;
     $data['coupon_amount']  =  0;
     //$data['coupon_id']      = empty($coupon_id) ? 0 : $coupon_id;
     //$data['coupon_amount']  = empty($coupon_money) ? 0 : $coupon_money;
     $data['status']         = 0;
     $data['add_time']       = time();
     $data['loan_money']     = $money;
     $data['is_new']         = 1;
     $result = $model->add($data);
     wqbLog($data);
     if ($result){
         $model->commit();
         session('bid',$data['id']);
         return $result;
     }else {
         $model->rollback();
         return false;
     }
 }
 
 /**
  * 放款并生成账单信息以及更新借款状态 
  * @param int $borrow_id 借款订单id
  * @param bool $orRenemal 是否是续期
  * @return boolean
  */
 function lending($borrow_id,$orRenemal=FALSE){
     $model = M("borrow_detail");
     $model->startTrans();
     $now = time();
     $borrow_info = M("borrow_apply")->field('id as bid,uid,money,interest,duration,renewal_fee,renewal_id,deadline,purpose,rate,add_time,loan_money')->where("id = $borrow_id")->find();
     $data['uid']           = $borrow_info['uid'];
     $data['borrow_id']     = $borrow_id;
     $data['capital']       = $borrow_info['money'];
     $data['interest']      = $borrow_info['interest'];
     $data['sort_order']    = 1;
     $data['total']         = 1;
     $data['status']        = 0;
     $data['add_time']      = $now;
     $data['renewal_fee']   = empty($borrow_info['renewal_fee']) ? 0 : $borrow_info['renewal_fee'];
     $data['renewal_id']    = empty($borrow_info['renewal_id']) ? 0 : $borrow_info['renewal_id'];
     if ($orRenemal){
         $data['deadline']      = $borrow_info['deadline'];
     }else {
         $data['deadline']      = $now+($borrow_info['duration']+1)*3600*24;
     }
     $data['hope_charge_time']  = $data['deadline'];
     $result = $model->add($data);
     if ($result) {
         if ($orRenemal){
             return true;
         }else {
             $sdata['status']       = 4;
             $sdata['audit_status'] = 5;
             $sdata['len_time']     = $now;
             $sdata['deadline']     = $data['deadline'];
             $sdata['id']           = $borrow_id;
             $status = M("borrow_apply")->save($sdata);
             if ($status !== false){
                 $b_result = sinaCreateBidInfo($borrow_info);
                 if ($b_result){
                     $bankInfo = M("member_bank")->field('bank_id')->where("uid = {$borrow_info['uid']}")->find();
                     $loanResult = sinaCreateSingleHostingPayToCardTrade($borrow_info, $bankInfo['bank_id']);
                     if ($loanResult){
                         $model->commit();
                         return true;
                     }else {
                         return false;
                     }
                 }else {
                    return false; 
                 }
                 return true;
             }else {
                 return false;
             }
         }
     }else {
         $model->rollback();
         return false;
     }
 }
 
 /**
  * 续期并重新生成订单和账单
  * @param int $borrow_info 需还款借款订单
  * @param int $borrow_detail 还款账单
  * @param int $duration 续期天数
  * @param double $amount 需付续期费用
  * @param int $coupon_id 还款or续期优惠券id
  * @param double $coupon_money 优惠券金额
  * @return boolean
  */
 function renewal($borrow_info, $borrow_detail, $itemInfo, $newArr){
     $model = M("borrow_apply");
     $now   = time();
     
     //更新当期账单
     $datab['repayment_time'] = $now;
     $datab['status']         = 1;
     $datab['due_fee']        = $newArr['due_fee'];
     $datab['late_fee']       = $newArr['late_fee'];
     if($newArr['tickId']>0){
         $datab['coupon_id']  = $newArr['tickId'];
     }
     $updetail = M('borrow_detail')->where("id={$borrow_detail['id']} ")->save($datab);
     if($updetail){
         //所有期数都还款完成
         if($borrow_detail['sort_order']==$borrow_detail['total']){
             //更新借款申请表状态
             $dataapply['status']         = 5;
             $dataapply['repayment_time'] = $now;
             $dataapply['due_fee']        = $borrow_info['due_fee']+$newArr['due_fee'];
             $dataapply['late_fee']       = $borrow_info['late_fee']+$newArr['late_fee'];
             $upapply = M('borrow_apply')->where(" id={$borrow_info['id']}  and uid = {$borrow_info['uid']} ")->save($dataapply);
         }
     }
     
     //插入新的订单信息
     $data['deadline']       = $now+$newArr['duration']*3600*24;
     $data['uid']            = $borrow_info['uid'];
     $data['money']          = $borrow_info['money'];//借款金额
     $data['purpose']        = $borrow_info['purpose'];
     $data['duration']       = $newArr['duration'];//借款期限
     $data['repayment_type'] = 1;//还款类型
     $data['rate']           = $itemInfo['rate'];//利率
     $data['item_id']        = $itemInfo['id'];//产品id
     $data['interest']       = $newArr['interest'];//利息
     $data['audit_fee']      = $newArr['audit_fee'];//贷后管理费
     $data['created_fee']    = 0;//技术服务费
     $data['enabled_fee']    = $newArr['enabled_fee'];//账户管理费
     $data['pay_fee']        = $newArr['pay_fee'];//支付服务费
     $data['renewal_fee']    = $newArr['money'];//扣款金额
     $data['renewal_id']     = $borrow_info['id'];
     $data['is_new']         = $newArr['is_new'];
     $data['status']         = 4;
     $data['audit_status']   = 5;
     $data['add_time']       = $now;
     $data['len_time']       = $now;   
     $data['loan_money']     = $borrow_info['money'];
     $data['is_new']         = 1;
     if ($updetail!==false && $upapply!==false){
         $result = $model->add($data);
         $orRenewal = true;
         $bool = lending($result,$orRenewal);
         if ($result && $bool!==false){
             session('bid',$result);
             return $bool;
         }else {
             return false;
         }
     }else {
         return false;
     }
      
 }

/**********************************新浪支付End*********************************************/
 
 /**
  *
  * @param 新浪支付日志 $message
  */
 function wechatLog($message)
 {
     if ( is_array($message) ){
         $message = json_encode($message);
     }
     error_log("[".date('Y-m-d H:i:s')."] ".$message."\r\n", 3, "d:/logs/wechat.log");
 }
 
 /**
  * 微信公众号access_token的获取
  * @param  $type 1：加油霸  2：福米金融 3:福米钱袋
  */
 function getWxtoken($type){
     $access_token = '';
     $token = M('wechat_access_token')->where("type = 3")->find();
	 
     if(time()-$token['addtime']>7100){
         $weixin  = C("WEIXIN");
         $appid      = $weixin['app_id'];
         $appsecret  = $weixin['app_secret'];
         $url    = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appsecret}";
         $res    = http_request($url);

         $result = json_decode($res,true);
         $updata['token']    = $result['access_token'];
         $updata['addtime']  = time();
         $xid = M('wechat_access_token')->where("type = 3")->save($updata);
         $access_token = $result['access_token'];
     }else{
         $access_token = $token['token'];
     }
	 
     return $access_token;
 }
 
 /**
  * 微信发送模板信息
  * @param unknown $data
  */
 function WxSendTemplateMsg($data,$type){
     $access_token = getWxtoken($type);
     $msgUrl       = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
     $res          = http_request($msgUrl,json_encode($data));
     return json_decode($res,true);
 }
 
 /**
  * 提前两天提醒用户还款的微信模板
  * @param 微信openId $openid
  * @param 姓名 $name
  * @param 还款日期 $date
  * @param 还款金额 $money
  * @param 银行卡 $card
  * @param 银行 $card_name
  * @return
  */
 function sendWxTempleteMsg6($openid,$name,$date,$money,$card,$card_name){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene= 6 ")->find();
     wqbLog(str_replace(array("#NAME#"), array($name), $wxInfo['first']));
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>str_replace(array("#NAME#"), array($name), $wxInfo['first']),
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $date,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=>$money."元",
                 'color'=>"#777777"),
             'remark'=>array('value'=>str_replace(array("#CARD#", "#CNAME#"), array($card,$card_name), $wxInfo['remark']),
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 当天提醒用户还款的微信模板
  * @param 微信openId $openid
  * @param 还款日期 $date
  * @param 还款金额 $money
  * @param 银行卡 $card
  * @param 银行 $card_name
  * @return
  */
 function sendWxTempleteMsg7($openid,$date,$money,$card,$card_name){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene= 7 ")->find();
     wqbLog(str_replace(array("#NAME#"), array($name), $wxInfo['first']));
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $date,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=>$money."元",
                 'color'=>"#777777"),
             'remark'=>array('value'=>str_replace(array("#CARD#", "#CNAME#"), array($card,$card_name), $wxInfo['remark']),
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 还款成功微信模板
  * @param 微信openId $openid
  * @param 借款日期 $date
  * @param 借款金额 $money
  * @param 借款期限 $day
  * @param 应还金额 $repayment
  * @return
  */
 function getWechatmsg8($openid,$date,$money,$day,$repayment){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene= 8")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>"尊敬的用户，您{$date}在现贷猫借款{$money}元{$day}，已成功还款。",
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> str_replace(array("#REPAYMENT#"), array($repayment), $wxInfo['keyword1']),
                 'color'=>"#777777"),
             'keyword2'=>array('value'=>date("Y-m-d H:i",time()),
                 'color'=>"#777777"),
             'keyword3'=>array('value'=>$repayment."元",
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 
 /**
  * 还款失败后催收微信模板
  * @param 微信openId $openid
  * @param 姓名 $name
  * @param 借款日期 $date
  * @param 借款金额 $money
  * @param 借款期限 $day
  * @param 应还金额 $repayment
  * @return 
  */
 function sendWxTempleteMsg9($openid,$name,$date,$money,$day,$repayment){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene= 9")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>str_replace(array("#NAME#", "#DATE#",  "#MONEY#", "#DAY#"), array($name, $date,$money,$day), $wxInfo['first']),
             'color'=>"#777777"),
             'keyword1'=>array('value'=> str_replace(array("#REPAYMENT#"), array($repayment), $wxInfo['keyword1']),
             'color'=>"#777777"),
             'keyword2'=>array('value'=>$wxInfo['keyword2'],
             'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
             'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 

 /**
  * 逾期的微信模板(不超过7天)
  * @param 微信openId $openid
  * @param 姓名 $name
  * @param 借款日期 $date
  * @param 借款金额 $money
  * @param 借款期限 $day
  * @param 应还日期 $deadline
  * @param 应还金额 $repayment
  * @param 逾期天数 $due_day
  * @param 逾期罚息 $due_fee
  * @param 逾期管理费 $late_fee
  * @return 
  */
 function sendWxTempleteMsg10($openid,$name,$date,$money,$day,$deadline,$repayment,$due_day,$due_fee,$late_fee){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 10 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>str_replace(array("#NAME#", "#DATE#",  "#MONEY#", "#DAY#"), array($name, $date,$money,$day), $wxInfo['first']),
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $deadline,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $due_day."天",
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> str_replace(array("#REPAYMENT#", "#DUELEE#",  "#LATELEE#"), array($repayment,$due_fee,$late_fee), $wxInfo['keyword3']),
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 逾期的微信模板(不超过7天)
  * @param 微信openId $openid
  * @param 姓名 $name
  * @param 借款日期 $date
  * @param 借款金额 $money
  * @param 借款期限 $day
  * @param 应还日期 $deadline
  * @param 应还金额 $repayment
  * @param 逾期天数 $due_day
  * @param 逾期罚息 $due_fee
  * @param 逾期管理费 $late_fee
  * @param 亲人电话 $tel1
  * @param 朋友电话$tel2
  * @return 
  */
 function sendWxTempleteMsg11($openid,$name,$date,$money,$day,$deadline,$repayment,$due_day,$due_fee,$late_fee,$tel1,$tel2){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 11 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>str_replace(array("#NAME#", "#DATE#",  "#MONEY#", "#DAY#"), array($name, $date,$money,$day), $wxInfo['first']),
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $deadline,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $due_day."天",
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> str_replace(array("#REPAYMENT#", "#DUELEE#",  "#LATELEE#"), array($repayment,$due_fee,$late_fee), $wxInfo['keyword3']),
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 
 /**
  * 验证手机后推送
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg1($openid,$money,$date){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 1 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $money."元",
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> date('Y-m-d',$date),
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 
 /**
  * 用户被拒绝推送
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg2($openid,$money,$date){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 2 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $wxInfo['keyword1'],
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $wxInfo['keyword2'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 
 /**
  * 初审通过推送
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg3($openid,$money,$date){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 3 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $money."元",
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> date('Y-m-d',$date),
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 
 /**
  * 贷款取消
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg4($openid,$money,$date,$duration){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 4 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> "CASHLOAN".$date,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $money."元",
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> $duration."天",
                 'color'=>"#777777"),
             'keyword4'=>array('value'=> "弃单",
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 
 /**
  * 放款推送
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg5($openid,$money,$date,$loanDate,$loanMoney){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 5 ")->find();
     $day = date('Y年m月d日',$date);
     $fee = getFloatValue(($money-$loanMoney),2);
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>"尊敬的用户，您{$day}在现贷猫借款{$money}元，已成功汇到您的银行卡。",
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> "放款",
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> date('Y年m月d日 H:i',$loanDate),
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> "借款".$money."元，服务费用".$fee."元。",
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 
 
 /**
  * 主动还款推送
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg8($openid,$money,$date,$duration,$repayTime){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 8 ")->find();
     $day = date('Y年m月d日',$date);
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>"尊敬的用户，您{$day}在现贷猫借款{$money}元{$duration}天，已成功还款。",
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $wxInfo['keyword1'],
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> date('Y年m月d日 H:i',$repayTime),
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> $money."元",
                  'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 
 /**
  * 续期成功推送
  * @param unknown $openid
  * @param unknown $money
  * @param unknown $date
  * @return unknown
  */
 function sendWxTempleteMsg12($openid,$money,$date,$phone,$renewalDay){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 12 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>"尊敬的{$phone}，你已成功续期{$money}元{$renewalDay}天。",
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> date('Y-m-d',$date),
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $wxInfo['keyword2'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wqbLog($res);
 }
 

 /**
  * 自动还款时还款失败提醒
  * @param 微信ID $openid
  * @param 借款日期 $date
  * @param 借款金额 $money
  * @param 借款期限 $day
  * @param 还款日期 $deadline
  * @param 还款金额 $repayment
  * @param 扣款银行尾号 $bank
  * @param 扣款银行名称 $bankname
  * @return boolean
  */
  function sendWxTempleteMsg13($openid,$date,$money,$day,$deadline,$repayment,$bank,$bankname){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 13 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>str_replace(array("#DATE#",  "#MONEY#", "#DAY#"), array($date,$money,$day), $wxInfo['first']),
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $deadline,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $repayment."元",
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> "尾号".$bank."（".$bankname."）",
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 
 /**
  * 管理员不通过通知
  * @param 微信ID $openid
  * @return boolean
  */
 function sendWxTempleteMsg14($openid){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 14 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $wxInfo['keyword1'],
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $wxInfo['keyword2'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#d73d3d"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 人脸识别失败通知
  * @param 微信ID $openid
  * @return boolean
  */
 function sendWxTempleteMsg19($openid){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 19 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $wxInfo['keyword1'],
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $wxInfo['keyword2'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#d73d3d"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 决策树失败通知
  * @param 微信ID $openid
  * @return boolean
  */
 function sendWxTempleteMsg20($openid){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 20 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $wxInfo['keyword1'],
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $wxInfo['keyword2'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#d73d3d"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
  /**
  * 复审通过通知
  * @param 微信ID $openid
  * @return boolean
  */
 function sendWxTempleteMsg21($openid,$borrow_money,$time){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 21 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $borrow_money."元",
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $time,
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#d73d3d"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 复审或人脸失败通知
  * @param 微信ID $openid
  * @return boolean
  */
 function sendWxTempleteMsg22($openid){
     $wxInfo = M("wechat_msg")->field(true)->where(" scene = 22 ")->find();
     $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $wxInfo['keyword1'],
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $wxInfo['keyword2'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#d73d3d"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     return $res;
 }
 
 /**
  * 生成还款账单
  */
 function createRepayOrder($borrow,$deadline){
     $model = M("borrow_detail");
     $model->startTrans();
     $data['uid']               = $borrow['uid'];
     $data['borrow_id']         = $borrow['id'];
     $data['capital']           = $borrow['money'];
     $data['interest']          = $borrow['interest'];
     $data['sort_order']        = 1;
     $data['total']             = 1;
     $data['renewal_fee']       = $borrow['renewal_fee'];
     $data['renewal_id']        = $borrow['renewal_id'];
     $data['due_fee']           = $borrow['due_fee'];
     $data['late_fee']          = $borrow['late_fee'];
     $data['status']            = 0;
     $data['add_time']          = time();
     $data['deadline']          = $deadline;
     $data['hope_charge_time']  = $deadline;
     $res = M("borrow_detail")->add($data);
     if ($res){
         $model->commit();
         return $res;
     }else {
         $model->rollback();
         return false;
     }
 }
 
 /**
  * 随机放款
  * @param unknown $uid
  * @param unknown $bid
  */
 function randomLoans($uid,$bid){
     $loans         = M("random_lending")->field('*')->where("status = 0 and type = 1")->order("id")->limit('1')->find();
     if (!empty($loans)){
         $itemInfo      = M("borrow_item")->field('*')->where("money = {$loans['money']}")->find();
         $where['uid']           = $uid;
         $where['status']        = 5;
         $binfo = M("borrow_apply")->field('id')->where($where)->find();//查询用户是否借过款
         $data['id']             = $bid;
         $data['uid']            = $uid;
         $data['money']          = $itemInfo['money'];//借款金额
         $data['duration']       = $itemInfo['duration'];//借款期限
         $data['repayment_type'] = 1;//还款类型
         $data['rate']           = $itemInfo['rate'];//利率
         $data['item_id']        = $itemInfo['id'];//产品id
         $data['interest']       = round($itemInfo['money']*$itemInfo['rate']/100*$itemInfo['duration']/360,2);//利息
         $data['audit_fee']      = round($itemInfo['money']*$itemInfo['audit_rate']/100*$itemInfo['duration'],2);//信息审核费
         $data['created_fee']    = $itemInfo['created_rate'];//账户建立费
         $data['enabled_fee']    = $itemInfo['enabled_rate'];//账户动用费
         $data['coupon_id']      =  0;
         $data['coupon_amount']  =  0;
         $data['is_random']      =  1;
         $data['add_time']       = time();
         $data['deadline']       = time()+$itemInfo['duration']*3600*24;
         $data['loan_money']     = round($itemInfo['money']-$data['audit_fee']-$data['enabled_fee']-$data['created_fee']+$data['coupon_amount'],2);
         $res = M("borrow_apply")->save($data);
         if ($res !== false) {
             $ldata['status'] = 1;
             $ldata['id']     = $loans['id'];
             M("random_lending")->save($ldata);
         }
     }else {
         $smsTxt = FS("Webconfig/smstxt");
         $mem = M(' members ')->field('iphone')->where(" id = {$uid} ")->find();
         $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
         sendWxTempleteMsg20($wxInfo['openid']);
         addToSms($mem['iphone'], $smsTxt['loan_ad']);
         $appdata['status']       = 97;
         $appdata['audit_status'] = 3;
         M("borrow_apply")->where("id = {$bid}")->save($appdata);
         delUserOperation($uid);
     }
 }
 
 /**
  * 删除用户操作记录
  * @param unknown $uid
  */
 function delUserOperation($uid,$bid){
     M("user_operation")->where("uid = {$uid} and borrow_id = {$bid}")->delete();
     wqbLog("已删除用户:{$uid},操作记录。");
 }
 
/**
 * 添加交易订单信息
 * @param int $uid 用户id
 * @param int $orderNum 订单号
 * @param int $orderNo 流水号
 * @param double $amount 交易金额
 * @param int $bank_id 银行卡id
 * @param int $borrow_id 账单id
 */
 function addSinaOrders($uid,$orderNum,$orderNo,$amount,$borrow_id,$type){
     $data['uid']           = $uid;
     $data['outer_orderId'] = $orderNum;
     $data['amount']        = $amount;
     $data['pay_amount']    = $amount;
     $data['user_fee']      = 0;
     $data['status']        = 0;
     $data['type']          = $type;
     $data['addtime']       = time();
     $data['finishedtime']  = "";
     $data['notify_id']     = $orderNo;
     $data['notify_time']   = "";
     $data['borrow_id']     = $borrow_id;
     $res = M("sina_order_pay")->add($data);
 }

 /**
  * 更改订单信息状态
  * @param int $status 订单状态
  * @param int $borrow_id 账单id
  * @param int $orderNum 订单号
  * @param int $uid 用户uid
  */
 function updateSinaOrders($status,$orderNum,$uid){
     $data['status']        = $status;
     $data['finishedtime']  = time();
     $data['notify_time']   = time();
     $map['outer_orderId']  = $orderNum;
     $map['uid']            = $uid;
     $res = M("sina_order_pay")->where($map)->save($data);
 }
 
 /**
  * 发送用户优惠券
  */
 function sendUserCoupon($uid){
     $global = get_global_setting();
     $ticArr = explode('|', $global['coupon_register']);
     $ticData['uid']  = $uid;
     $ticData['money'] = $ticArr[0];
     $ticData['title'] = "还款用优惠券";
     $ticData['type']  = '2';
     $ticData['status'] = 0;
     $ticData['add_time'] = time();
     $ticData['start_time'] = time();
     $ticData['end_time'] = time()+3600*24*$ticArr[1];
     $res = M("member_coupon")->add($ticData);
     if ($res) {
         return true;
     }else {
         return false;
     }
 }
 
 /**
  * 添加用户微信绑定信息
  */
 function addUserWxBindInfo($uid,$openid,$access_token,$refresh_token,$wx_nickname,$wx_image){
     $wx['uid']             = $uid;
     $wx['openid']          = $openid;
     $wx['access_token']    = $access_token;
     $wx['refresh_token']   = $refresh_token;
     $wx['nickname']        = $wx_nickname;
     $wx['headimg']         = $wx_image;
     $wx['wechat_type']     = 'xdm';
     $info = M("member_wechat_bind")->field('*')->where("uid = '{$uid}'")->find();
     if(empty($info)){
         $res = M("member_wechat_bind")->add($wx);
		 if($res){
			 M("member_wechat_bind")->commit();
		 }else{
			 M("member_wechat_bind")->rollback();
		 }
     }else{
		 M("member_wechat_bind")->where("uid = $uid")->save($wx);
	 }
 }
 
 /**
  * 获取用户芝麻分
  * @param 用户ID $uid
  * @param openid $openid
  * @return string|结果
  */
 function findZhima($uid,$openid){
     $web    = C('RISK_WEB'); //风控请求接口
     $url    = $web."/zm/creScore/{$openid}";
     $res    = http_request($url);
     $arr    = explode(":",$res);
     if($arr[0]=="error"){
         switch ($arr[1]) {
         case "00":
           return "其他错误";
            break;
        case "01":
            return "缺少参数";
            break;
        case "02":
            return "白骑士接口调用失败";
            break;    
        default :
            return "网络错误";
            break;
         }
     }else{
         $data['uid']        = $uid;
         $data['openid']     = $openid;
         $data['score']      = $arr[1];
         $data['score_time'] = time();
         $update =  M("zhima_score")->add($data);
         if($update){
             return "芝麻分已经取得";
         } else{
             return "芝麻分保存有误";
         } 
     }
     
 }

 /**
  * 获取众安等级
  * @param 身份证 $idCardNo
  * @param 电话号码 $mobile
  * @param 姓名 $name
  * @return string|结果  等于100表示有错误，其他数字表示风险等级
  */
 function getZhongan($idCardNo,$mobile,$name){
     $name   = urlencode($name);
     $web    = C('RISK_WEB'); //风控请求接口
     $url    = $web."/zhongan/type/{$idCardNo}/{$mobile}/{$name}";
     $res    = http_request_zh($url);
     $arr    = explode(":",$res);
     if($arr[0]=="error"){
         $flg = 100;
         switch ($arr[1]) {
             case "00":
                 wqblog("众安接口调用其他错误");
                 return $flg;
                 break;
             case "01":
                 wqblog( "众安接口调用时缺少参数");
                 return $flg;
                 break;
             case "02":
                 wqblog("众安接口调用失败");
                 return $flg;
                 break;
             default :
                 wqblog( "众安接口调用时网络错误");
                 return $flg;
                 break;
         }
     }else{
         wqblog( "众安数据已经取得");
         return $res;
     } 
 }
 
/**
  * 现贷猫上传福米发标
  * @param 借款ID $id
 *  @param 是否发送福米金融推送 $send_wechat 1发送 0不发送
  * @return boolean  
  */
 function createFumiBid($id,$send_wechat){
     $flg    = true;
     $web    = C('BID_WEB'); //上标请求接口
     $url    = $web."/bidup/created";

     $apply  = M('borrow_apply')->field("id,uid,duration,money,rate,purpose")->where(" id = ".$id )->find();
     $mem    = M('member_info')->field("id, id_card,real_name,iphone")->where(" uid = ".$apply['uid'] )->find();
     //成功借款次数
     $borrowCount     = M('borrow_detail')->where(" uid = {$apply['uid']}")->group('borrow_id')->count("id");
     //逾期还款次数
     $dueCount        = M('borrow_detail')->where(" uid = {$id} and status = 1 and repayment_time > deadline ")->count("id");
     $sex_a  = substr($mem['id_card'],16,1);
     if($sex_a%2==1){
         $sex = '男';
     }else if($sex_a%2==0){
         $sex = '女';
     }
     $today  = date('Y-m-d H:i:s',time());
     $date   = date("Y",strtotime($today));
     $age    = $date - substr($mem['id_card'],6,4);
     $idcard = substr($mem['id_card'],0,8);
     $borrow_use  = "<ul class='fn-clear'><li>性别：".$sex."</li><li>年龄：{$age}</li><li>身份证号：<span class='hui-id'>{$idcard}********</span></li><li>借款用途：".$apply['purpose']."</li><li>还款来源：个人收入</li><li>成功借款：{$borrowCount}笔</li><li>逾期次数：{$dueCount}次</li><li>信用评级：A</li></ul>";
     $data = array(
               'id'               => $id,
               'id_card'         => $mem['id_card'],
               'real_name'       => $mem['real_name'],
               'iphone'          => $mem['iphone'],
               'uid'             => $apply['uid'],
               'use'             => $borrow_use,
               'duration'       => $apply['duration'],
               'invest_uid'     => $loan_account,
               'rate'            => $apply['rate'],
               'money'           => $apply['money'],
               'total_money'    => $apply['money'],
               'send_wechat'    => $send_wechat
           ); 
     $res = http_request($url,json_encode($data));
     $res = ltrim(rtrim($res, '"'),'"');
     $array = explode(':',$res);
     if($array[0] == 200){
         $save['up_bid']        = $array[1];
         $updata = M('borrow_apply')->where("id={$id}")->save($save);
     }else{
         $flg = false;
     }
     return $flg;
 }
 
 
 /**
  * 现贷猫批量上传福米发标
  * @param 借款ID $bid（英文逗号分隔的借款ID） 
  * @param 借款申请列表 $apply 所有借款申请的集合
  * @param 年化率 $borrowrate
  * @param 上标金额合计 $money
  * @param 是否发送福米金融推送 $send_wechat 1发送 0不发送
  * @return boolean
  */
 function batchFumiBid($bid,$apply,$borrowrate,$money,$send_wechat){
     $flg        = true;
     $web        = C('BID_WEB'); //上标请求接口
     $url        = $web."/bidup/createdBatch";
     if($borrowrate>0){
         $rate = $borrowrate;
     }else{
         $rate = $apply[0][rate];
     }
     foreach($apply as $key=>$v){
         $mem    = M('member_info')->field("id, id_card,real_name,iphone")->where(" uid = ".$v['uid'] )->find();
         $sex_a  = substr($mem['id_card'],16,1);
         if($sex_a%2==1){
             $sex = '男';
         }else if($sex_a%2==0){
             $sex = '女';
         }
         $today                         = date('Y-m-d H:i:s',time());
         $date                          = date("Y",strtotime($today));
         $age                           = $date - substr($mem['id_card'],6,4);
         $idcard                        = substr($mem['id_card'],0,8);
         $data['id']                    = $v['id'];
         $data['id_card']               = $mem['id_card'];
         $data['real_name']             = $mem['real_name'];
         $data['iphone']                = $mem['iphone'];
         $data['uid']                   = $v['uid'];
         $data['use']                   = "<ul class='fn-clear'><li>性别：".$sex."</li><li>年龄：{$age}</li><li>身份证号：{$idcard}********</li><li>借款用途：".$v['purpose']."</li><li>还款来源：个人收入</li></ul>";
         $data['invest_uid']            = $loan_account;
         $data['rate']                  = $rate;
         $data['money']                 = $v['money'];
         $data['duration']              = $v['duration'];
         $data['total_money']           = $money;
         $data['send_wechat']           = $send_wechat;
         $datas[] = $data;
     }
     $res = http_request($url,json_encode($datas));
     $res = ltrim(rtrim($res, '"'),'"');
     $array = explode(':',$res);
     if($array[0] == 200){
         M()->query("update ml_borrow_apply set up_bid = {$array[1]}  where id in ($bid) ");
     }else{
         $flg = false;
     }
     
     return $flg;
 }

/*******************Face++接口 START**************************************/
 /**
  * face++ https请求方法
  * @param 接口地址 $url
  * @param 传值参数 $data
  * @param 请求方式 $type POST or GET
  */
 function http_request_face($url,$data,$type){
     $curl = curl_init();
     curl_setopt_array($curl, array(
         CURLOPT_URL => $url,
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_ENCODING => "",
         CURLOPT_SSL_VERIFYPEER => true,
         CURLOPT_SSL_VERIFYHOST => true,
         CURLOPT_MAXREDIRS => 10,
         CURLOPT_TIMEOUT => 30,
         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
         CURLOPT_CUSTOMREQUEST => $type,
         CURLOPT_POSTFIELDS => $data,
         CURLOPT_HTTPHEADER => array(
             "cache-control: no-cache",
         ),
     ));
     $response = curl_exec($curl);
     $err      = curl_error($curl);
     return $response;
 }
 
 /**
  * Face++ 获得一个用于实名验证的 token（token唯一且只能使用一次）。接口同时还能帮助完成人脸比对，并在完成验证后自动将人脸比对结果返回
  * @param 我司请求单号 $biz_no
  * @param 真实姓名 $real_name
  * @param 身份证 $id_card
  * @return unknown
  */
 function faceGetToken($biz_no,$real_name,$id_card,$borrow_id){
     $config                = require(APP_PATH . "/Conf/face.php");
     $api_key               = $config["api_key"];
     $api_secret            = $config["api_secret"];
     $return_url            = $config["return_url"];
     $notify_url            = $config["notify_url"];
     
     $url                   = "https://api.megvii.com/faceid/lite/get_token";
     $data = [
         'api_key'          => $api_key,
         'api_secret'       => $api_secret,
         'scene_id'         => $scene_id,
         'return_url'       => $return_url,
         'notify_url'       => $notify_url,
         'biz_no'           => $biz_no,
         'procedure_type'   => 'video',//刷脸活体验证流程 微信默认用视频
         'comparison_type'  => 1,//刷脸活体验证 用有源比对
         'idcard_mode'      => 0,//不拍摄身份证，传数值
         'idcard_name'      => $real_name,
         'idcard_number'    => $id_card,
     ];
     
     $res   = http_request_face($url,$data,"POST");
     $res   = json_decode($res,true);
     return $res;
 }

 /**
  * Face++ 活体结果反查功能，可以以biz_id为索引对 FaceID Lite 验证结果进行反查
  * @param 活体业务编号 $biz_id
  * @return unknown
  */
 function faceGetResult($biz_id){
     $config                = require(APP_PATH . "/Conf/face.php");
     $api_key               = $config["api_key"];
     $api_secret            = $config["api_secret"];
    
     $url  = "https://api.megvii.com/faceid/lite/get_result";
     $data = [
         'api_key'          => $api_key,
         'api_secret'       => $api_secret,
         'biz_id'           => $biz_id,
     ];
     
     $res   = http_request_face($url,$data,"GET");
     $res   = json_decode($res,true);
     return $res;
 }
 /*******************Face++接口 END**************************************/