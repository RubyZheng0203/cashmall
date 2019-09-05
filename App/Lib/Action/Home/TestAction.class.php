<?php
// 本类由系统自动生成，仅供测试用途
require_once APP_PATH.'/Common/Umeng.php';
class TestAction extends HCommonAction {
    
    public function  dull(){
        echo "start<br/>";
        //$model    = new PaymentAction();
        //$res      = $model->requestApi($borrowInfo['uid'], $borrowInfo['id'], 1,$total ,$_POST['bankId']);
        $uid            = "9999";
        $payCode        = "BOC";
        $accNo          = "6217880800007258033";
        $idCard         = "41272619930213142X";
        $idHolder       = "陈春燕";
        $mobile         = "15618539929";
        $txnAmt         = "1000000";
        $additionalInfo = "TEST";
        //echo "NAME------".urlencode($idHolder)."<br/>";
        baofoo($uid,$payCode,$accNo,$idCard,$idHolder,$mobile,$txnAmt,$additionalInfo);
        //getppc($idcard,$mobile,$idHolder);
        echo "END";
    }
    
    public function  sendwechat(){
        //$wxInfo = M("wechat_msg")->field(true)->where(" scene = 11 ")->find();
        $tempLateData = array(
            'touser'       =>"oItly1TLtOmdXTtKxVKq8XbHvK7A",
            'template_id'  =>"kbW0OlbCLOCxuGKkaN12NP0FdIdqk8jfvACxu9DDQWc",
            'url'          =>"https://www.cashmall.com.cn/member/invite_regist?invite_code=21031142",
            'topcolor'=>"#777777",
            'data'=>array(
                'first'=>array('value'=>"恭喜您获得邀请好友资格！",
                        'color'=>"#777777"),
                'keyword1'=>array('value'=> date("Y-m-d",$v['add_time']),
                    'color'=>"#777777"),
                'keyword2'=>array('value'=> "现贷猫",
                    'color'=>"#777777"),
                'remark'=>array('value'=>"成功邀请好友即可获得10-50元现金奖励，并有机会提高借款成功机率！",
                    'color'=>"#777777"),
            )
        );
        $res = WxSendTemplateMsg($tempLateData,1);
        return $res;
    }
    
    public function rmb(){
         $param = array(
             "uid"          => "13,5",
             "bid"          => 11342,//多个推送的时候为0，单个推送的时候需要填写申请单号
             "chaining"     => "http://www.cashmall.com.cn/Activity/invite",
             "ticker"       => "邀好友拿奖励最高500元现金",  //通知栏提示文字
             "title"        => "点击有好礼",//通知标题
             "text"         => "您有一份50元奖励尚未GET！赶紧领取吧",//通知文字描述
             "after_open"   => "go_url",//点击通知的后续行为
             "url"          => "http://www.cashmall.com.cn/Activity/invite",//go_url时跳转到URL
             "activity"     => "",//go_activity时打开特定的activity
             "tokens"       => "AqLrwdem67wSdl6UfaUF5FIhH2-qC2dt02TmxWE1893B,Av7nCqCRtXe5sUk1gunPd4O8fHReUZ_R4y7RPzh4Db_D" //发送对象的token
        );
        
        //ad_unicast($param);//单个推送
        ad_listcast($param);//多个推送
        
    }
    public function getbank(){

				$uid			='49524';
				$bankName       ='中国银行';
				$bankCard		='6217567500012101026';
				$phone			='17373492227';
				$province		='湖南省';
				$city			='衡阳市';
			 
            $bankCode = C("SINA_BANK_CODE");
            $code = array_search($bankName, $bankCode);

			wqbLog("APP绑卡--------UID----------".$uid."----code-------".urldecode($code)."----bankCard----".$bankCard."--phone--".$phone."----province---".urldecode($province)."---city---".urldecode($city));
            /*$tick = sinaGetBindBankCode($uid, $code, $bankCard, $phone,urldecode($arrayprovince), urldecode($city));
			if ($tick['status'] == 1){
				exit(json_encode(array('message'=>'已发送','code'=>200,'result'=>array('bankTicket'=>$tick))));
            }else{
				if($tick['message'] =="验签未通过"){
				    exit(json_encode(array('message'=>'网络有异常请重试一下','code'=>401)));
				}else{
				    exit(json_encode(array('message'=>$tick['message'],'code'=>401)));
				}
            }  */
	}
    
     public function creat(){
         $bid = 98420;
         $deadline = 1509954099;
         $info = M("borrow_apply")->field(true)->where("id = $bid ")->find();
         createRepayOrder($info,$deadline);
     }
    
    /*public  function  aa(){
            //$operator_mh = C('OPERATOR_MH_URL'); //分析运营商数据
            $url = "http://192.168.1.158:8080/zm/creScore";
            $openid = "268814619744585338469376492";
            $data['zm'] = array(
                
                'account'   => "",//手机号
                'name'      => "",//姓名
                'email'     => "",//邮箱
                'mobile'    =>"",//手机号
                'certNo'    => "",//身份证
                'bankCardNo'=> "", //银行卡卡号
                'amount'    => "", //借款金额
                'zmOpenId'  => $openid,//芝麻openId
                'address'                => "",//用户住址
                'addressCity'            => "",//用户所在城市
                'organizationAddress'    =>"",//用户工作单位地址
                'marriage'               => "",//是否已婚
                'residence'              => "",//户籍所在地
                'tokenKey'               => "",
                'ip'                     => "",
                'isPass'                 => 'false'
            );
           
            
			echo "AAAAAAAAAAAAA".json_encode($data);
            $res = http_request($url,json_encode($data));
            echo "DDDDDDD";
            echo $res;
    } */
    
    public  function  bb(){
        //$aa = 1899;
        //$uid = 767;
        $name = "支付宝aaaa";
        //echo getAppType($name);
        $aa = 1881;
        $uid = 758;
        /*$model = new  CheckUserAction();
        echo "AAAAAA";
        wqbLog("开始贷款决策树---------");
        $checkRes = $model->requestApi($uid,$aa,3);
        wqbLog("结束贷款决策树---------".$checkRes);*/
        $mobile_no = "15618539929";
        //echo "DD----".mallZhima($uid);
        //echo "DAY----".isRunCarrier($mobile_no);
        /*$mobile_no = $_GET['iphone'];
        $mem    = M("members")->field('id')->where("iphone = '{$mobile_no}'")->find();
        
        if($mem['id']> 0){
            //sinaModifyPayPassword($mem['id']);
            //sinaSetPayPassword($mem['id']);
        }*/
        
        /*$uid = 536;
        $idCardNo =  "41272619930213142X";
        $openid = "268814619744585678453951821";
        $data=array();
        $url    = " http://192.168.1.158:8080/bqs/queryscore";
        $data['zmCreScore']  = array(
            'openId'    => $members['zhima_openid'],
            'tokenKey'  => session_id(),
            'isPass'    => "false"
        );
        $res = http_request($url,json_encode($data));*/
         
        //echo "qizhafen".$res;
        
        
        //$mobile = "15618539929";
        //$mobile = "13233790047";
        /*$mobile = "15021031142";
        $name = "陈春燕";
        //$res = mallRisk($uid);
        //$res = getZhongan($uid,$idCardNo,$mobile,$name);
        //$res = getAttribution($uid,$mobile);
        $res = findqizha($uid,$openid);
        echo "CCCCC--------".$res;*/
        
    }
    public function bqs(){
        $borrowid       = 1647;
        $uid            = 710;
        $scene_status   = 3;
        $memo = "";
        $res =  bqszhimaOrder($borrowid,$uid,$scene_status,$memo);
        
        $datag        = get_global_setting();
        $zhima        = $datag['zhima_data'];
        if($zhima == 1){
            echo "SSSSSSS";
        }else{
            echo "AAAAAAAAAA";
        }
    }
    public function uid(){
        $model = new  CheckUserAction();
        $uid = 436;
        $borrow = 741;
        //$checkRes = $model->requestApi($uid,$borrow);
        //$checkRes = $model->requestRegistApi($uid,$borrow);
        $checkRes = $model->requestLoginApi($uid,$borrow);
        //echo (get_baiqishi_fir().session_id().get_baiqishi_sec());
        /*$id_card   = "321084198402033825";
        $real_name = "郑迎春";
        $channel = "apppc";
        $uid = 1;
        $callbackUrl = "www.cashmall.com/baiqishiId/".$uid.".html";
        $zhima = bqszhima($id_card,$real_name,$channel,$callbackUrl);
        echo $zhima;*/
        //$openid = "268814619744585759735961760";
        //$zhima = bqszhimaSearch($openid);
        //echo $zhima;
        //print_r($zhima);
        //echo($zhima['resultData']['authInfoUrl']);
        
        //echo $resultData;
        //print_r(json_encode($aa));
        /*$phone = '15021031142';
        $uid   = checkSinaUid($phone);
        if($uid != ''){
            $uid = 'fumi'.$uid;
        }
        echo $uid;*/
        $bid = "656";
        $bankid = "195795";
        //$list = M("borrow_apply")->field(true)->where(" id = ".$bid )->find();
        /*$ddd       = date("YmdHis",$list['add_time']);
        echo $ddd;*/
        $newid = 93;
        $phone = "13657458747";
        //sinaQueryBidInfo($bid);
        //sinaCreateBidInfo($list);
        //sinaCreateSingleHostingPayToCardTrade($list,$bankid);
        //sinaCreatMember($newid);
        //sinaBindingVerify($newid,$phone);
        
        $uid = 2652;
        $bankid = "163417";
        $amount = 800.00;
        $money = 1000;
        $itemid = 11;
        $day = 6;
        $deadline = 1502596320;//1502418600;//1499045356;//1499131756;
        //echo (get_late_fee($money,$itemid,$day));
        //echo (get_due_day($deadline));
        //sinaCreateSingleHostingPayToCardTrade($uid,$bid,$amount,$bankid);
        //echo strtotime(date("Y-m-d",time())." 00:00:00");
        $openid = 'oItly1TLtOmdXTtKxVKq8XbHvK7A';
        echo "AA";
        //sendWxTempleteMsg14($openid);
        $scene = 9;
        //echo getWechatmsg1($openid,$scene);
    }
    
  
    function phone(){
        /*$ressms    = addToSms
        (
            $mem['iphone'],
            str_replace(
                array("#DATE#", "#MONEY#", "#BANK#","#BANKNAME#", "#AMOUNT#"), 
                array(date("Y-m-d",$v['add_time']),$apply['money'],substr($bank['bank_card'],-4),$bank['bank_name'],$money),
                $smsTxt['repayment_before']
                )
        );*/
        $smsTxt = FS("Webconfig/smstxt");
        $time = '1501516800';
        $money = '500';
        $bank_card ='343253542324232323';
        $bank_name ='民生银行';
        $aa = str_replace(
            array("#DATE#", "#MONEY#", "#BANK#","#BANKNAME#", "#AMOUNT#"),
            array(date("Y-m-d",$time),$money,substr($bank_card,-4),$bank_name,$money),$smsTxt['repayment_before']
        );
        echo $aa;
        
        /*$phone = "02160433459";
        $txt    = getlink('https://www.so.com/s?q='.$phone);
        $info   = get_tag("class","mohe-mobileInfoContent",$txt,"td");
    
        
        $where  = get_tag("class","gclearfix mh-detail",$info[0],"div");
        if(count($where)==0){//不是骚扰电话
            echo "HHHHHHHHHHHH";
            $where=get_tag("class","mh-detail",$info[0],"p");
        }
        $info_txt   = strip_tags ($where[0]);
        $info_array = explode("  ",$info_txt);
        $phone_t    = $info_array[0];
        $where_t    = $info_array[1];
        $cmcc_t     = $info_array[2];
        $type=get_tag("class","mohe-ph-mark",$info[0],"span");
        if(count($type)!=0){
            $type_t=$type[0]; 
        }
        
        $result=new Result();
        $result->phone=$phone_t;
        $result->where=$where_t;
        $result->cmcc=$cmcc_t;
        $result->type=$type_t;
         
        $json=json_encode($result,JSON_UNESCAPED_UNICODE);
        wqbLog($json);
        echo $json;*/
    }
    
    function kkk(){
        $uid      = 982;
        $fuiou_id ="13206150012";
        userQuery($uid,$fuiou_id);
        /*$address = "半岛科技园";
        $city = "上海";
        echo "AAAAAAAAAAA";
        get_Address($address,$city);*/
        /*$uid     = 917;
        //echo isPpc($uid);
        $bid     = 50010;
        $money   = 1;
        $type    = 1;
        $scene   = 1;
        $item_id = 0;
        //$img = wechatPay($uid,$bid,$item_id,$money,$scene,$type);
        //echo $img;
        $order    = "563SX2018121811554117711";
        $noncestr = "pqwKEtlY8WB4qHI1";
        
        /*$list     = M()->query(" SELECT * from ml_transfer_order_pay where uid >840  ");
        if(count($list) > 0){
            foreach($list as $key=>$v){
                $res = queryOrder($v['outer_orderId'],$v['noncestr']);
                echo $v['outer_orderId'].",".$res['payResult']."<br/>";
            }
        }*/
        /*$out_trade_no = "3175JYW2019010809495780512";
        if(strpos($out_trade_no,'JYA') !== false){
            echo "OK";
        }else{
            echo "NG";
        }
        */
       
    }
} 
