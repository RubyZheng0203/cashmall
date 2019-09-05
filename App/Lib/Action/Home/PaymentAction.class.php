<?php
define("BAOFOO_ENCRYPT_LEN", 32);
class PaymentAction extends HCommonAction{
    
    private $private_key;
    private $public_key;
    
    
    public function requestApi($uid,$bid,$type,$money,$bankId){
        $borrow_detail  = M("borrow_detail")->field(true)->where("uid = {$uid} and borrow_id = {$bid}")->find();
        $borrowInfo     = M("borrow_apply")->field(true)->where("id = {$bid}")->find();
        $mem            = M("member_info")->field(true)->where("uid = {$uid}")->find();
        $bank           = M("member_bank")->field(true)->where("uid = {$uid} and id = {$bankId}")->find();
        if ($type == 2){
            $patType      = "XQ";
        }else {
            $patType      = "HK";
        }
        $txnAmt = $money*100;
        
        //请求宝付支付代扣接口
        $res = baofoo($uid,$bid,$money,$type,$bank['bank_code'],$bank['bank_card'],$mem['id_card'],$mem['real_name'],$mem['iphone'],$txnAmt,$patType);
        if($res==1){
            return true; 
        }else{
        	if($res===0){
        		return false;
        	}else{
            	return $res;
        	}
        }
        
    }
    
    /**
     * 授信费用支付
     * @param 借款会员编号 $uid
     * @param 借款申请编号 $bid
     * @param 授信费用 $money
     * @param 银行ID $bankId
     */
    public function creditApi($uid,$bid,$money,$bankId){
        $mem            = M("member_info")->field('id_card,real_name,iphone')->where("uid = {$uid}")->find();
        $bank           = M("member_bank")->field('bank_code,bank_card')->where("uid = {$uid} and id = {$bankId}")->find();
        $patType        = "SX";
        $txnAmt         = $money*100;
        $type           = 4;
        //请求宝付支付代扣接口
        $res = baofoo($uid,$bid,$money,$type,$bank['bank_code'],$bank['bank_card'],$mem['id_card'],$mem['real_name'],$mem['iphone'],$txnAmt,$patType);
        if($res==1){
            return true;
        }else{
            if($res===0){
                return false;
            }else{
                return $res;
            }
        }
    }
    
    
    /**
     * 宝付代扣(废弃)
     * @param int $uid 用户uid 
     * @param int $bid 用户借款id
     * @param int $type 付款类型1：还款 , 2：续期付款 
     * @param double $money 代扣金额
     * @param int $bankId 用户添加银行卡id
     */
    /*public function requestApi($uid,$bid,$type,$money,$bankId){
        $borrow_detail = M("borrow_detail")->field('*')->where("uid = $uid and borrow_id = $bid")->find();
        $borrowInfo = M("borrow_apply")->field('*')->where("id = $bid")->find();
        $info  = M()->query("SELECT a.iphone,a.real_name,a.id_card,b.bank_card,b.bank_code,b.type FROM ml_member_info a 
                            INNER JOIN ml_member_bank b ON a.uid = b.uid WHERE  a.uid = {$uid} and b.id = $bankId");
        $info  = $info[0];
        $public_key_path        = APP_PATH."/key/baofu_public.cer";//公钥
        $private_key_path       = APP_PATH."/key/baofu_private.pfx";//私钥
        $private_key_password   = "051811"; //私钥密码
        $request_url            = "https://public.baofoo.com/cutpayment/api/backTransRequest";//api地址
        $version                = "4.0.0.0";//版本号
        $terminal_id            = "35004";//终端号
        $txn_type               = "0431";//交易类型
        $txn_sub_type           = "13";//交易子类
        $member_id              = "1174846";//商户号
        $data_type              = "json";//加密数据类型    
        if ($type == 2){
            $patType      = "XUQI";
        }else {
            $patType      = "HUANKUAN";
        }
        $data_arr     = array(
            "txn_sub_type"      => "13",
            "biz_type"          => "0000",//接入类型
            "terminal_id"       => $terminal_id,
            "member_id"         => $member_id,
            "trans_id"          => "XIANDAIMAO".time(),//商户订单号
            "trans_serial_no"   => "XIANDAIMAO".time()."NUM",//商户流水号
            "pay_code"          => $info['bank_code'],//银行编码
            "pay_cm"            => "2",//安全标示1:不进行信息严格验证2:对四要素（身份证号、持卡人姓名、银行卡绑定手机、卡号）进行严格校验，默认2            
            "acc_no"            => $info['bank_card'],//卡号
            "id_card_type"      => "01",//身份证类型
            "id_card"           => $info['id_card'],//身份证
            "id_holder"         => $info['real_name'],//持卡人姓名
            "mobile"            => $info['iphone'],//银行卡绑定手机
            "valid_date"        => "",//卡有效期
            "valid_no"          => "",//卡安全码
            "trade_date"        => date('YmdHis',time()),//订单日期
            "txn_amt"           => $money*100,//交易金额
            "additional_info"   => $patType,//附加字段
            "req_reserved"      => "bb"//请求方保留域
        );
        //addSinaOrders($uid, $data_arr['trans_id'], $data_arr['trans_serial_no'], $money, $bid, $info['type']);
        $Encrypted_string = str_replace("\\/", "/",json_encode($data_arr));//转JSON
        wqbLog($data_arr);
        $this->checkRsa($private_key_path,$public_key_path,$private_key_password);
        $Encrypted = $this->encryptedByPrivateKey($Encrypted_string);
        $PostArry = array(
            "version"       => $version,
            "terminal_id"   => $terminal_id,
            "txn_type"      => $txn_type,
            "txn_sub_type"  => $txn_sub_type,
            "member_id"     => $member_id,
            "data_type"     => $data_type,
            "data_content"  => $Encrypted            
        );
        //$return = $this->requestPost($PostArry, $request_url);      
        $return = "97816ab8e8a8b3aa59f34857a073daa8a5d5c34f7e13db529b020a254899d4728bf3ac1bad4fad461cbaf66e1598e9529583a3d6f0ae611176b30384a0d930424a6cd644d782fd5cdc3e32a9700495ef6f3843df1a0f2293160e527b05e247616fb3cb949d05cc14e9d7997381315d067beb45224b9ff68601ae365391694a6a";
        if(empty($return)){
            wqbLog("返回为空，确认是否网络原因！");
        }
        $return_decode = $this->decryptByPublicKey($return);//解密返回的报文
        wqbLog("解密结果：".$return_decode);
        $endata_content = array();
        if(!empty($return_decode)){//解析XML、JSON
            $endata_content = json_decode($return_decode,TRUE);
            if(is_array($endata_content) && (count($endata_content)>0)){
                if(array_key_exists("resp_code", $endata_content)){
                    if($endata_content["resp_code"] == "0000"){
                        $return_decode = "订单状态码：".$endata_content["resp_code"].", 商户订单号：".$endata_content["trans_id"].", 返回消息：".$endata_content["resp_msg"];
                        updateSinaOrders(1, $endata_content["trans_id"], $uid);
                        wqbLog($return_decode);//输出
                        return true;
                    }else{
                        //错误或失败其他状态
                        $str = "BF00101,BF00102,BF00103,BF00104,BF00107,BF00110,BF00140,BF00141,BF00146,BF00232,BF00234,BF00235,BF00237,BF00342,
                               BF00331,BF08704,BF00350,BF00415,BF00351,BF00372,BF08704,BF00343,BF00344,BF00345,BF00346,BF00322";
                        $return_decode = "订单状态码：".$endata_content["resp_code"].", 商户订单号：".$endata_content["trans_id"].", 返回消息：".$endata_content["resp_msg"];
                        updateSinaOrders(2, $endata_content["trans_id"], $uid);
                        wqbLog($return_decode);//输出
                        if (stripos($str, $endata_content["resp_code"]) !== false){
                            return $endata_content["resp_msg"];
                        }else {                         
                            return false;
                        }                      
                    }
                }else{
                    wqbLog("[resp_code]返回码不存在!");
                }
            }
        }  else {
           wqbLog("请求出错，请检查网络");
        }
    }*/
    
    /**
     * 检验密钥
     * @param unknown $private_key_path
     * @param unknown $public_key_path
     * @param unknown $private_key_password
     */
    function checkRsa($private_key_path,$public_key_path,$private_key_password){
        // 初始化商户私钥
        $pkcs12 = file_get_contents($private_key_path);
        $private_key = array();
        openssl_pkcs12_read($pkcs12, $private_key, $private_key_password);
        $res = empty($private_key) == true ? '不可用':'可用';
        $str = "私钥是否可用:".$res;
        $this->private_key = $private_key["pkey"];
        	
        //宝付公钥
        $str2 = "公钥路径：".$public_key_path;
        $keyFile = file_get_contents($public_key_path);
        $this->public_key = openssl_get_publickey($keyFile);
        $res2 = empty($this->public_key) == true ? '不可用':'可用';
        $str3 = "宝付公钥是否可用:".$res2;
    }
    
    /**
     * 私钥加密
     * @param unknown $data_content
     * @return string
     */
    function encryptedByPrivateKey($data_content){
        $data_content = base64_encode($data_content);
        $encrypted = "";
        $totalLen = strlen($data_content);
        $encryptPos = 0;
        $encryptData = "";
        for ($i=0;$i<$totalLen;$i++){
            openssl_private_encrypt(substr($data_content, $encryptPos, BAOFOO_ENCRYPT_LEN), $encryptData, $this->private_key);
            $encrypted .= bin2hex($encryptData);
            $encryptPos += BAOFOO_ENCRYPT_LEN;
        }
        return $encrypted;
    }
    
    /**
     * 公钥解密
     * @param unknown $encrypted
     */
    function decryptByPublicKey($encrypted){
        wqbLog($encrypted);
        $decrypt = "";
        $totalLen = strlen($encrypted);
        $decryptPos = 0;
        while ($decryptPos < $totalLen) {
            openssl_public_decrypt(hex2bin(substr($encrypted, $decryptPos, BAOFOO_ENCRYPT_LEN * 8)), $decryptData, $this->public_key);
            $decrypt .= $decryptData;
            $decryptPos += BAOFOO_ENCRYPT_LEN * 8;
        }
		 openssl_public_decrypt($encrypted, $decryptData, $this->public_key);
        $decrypt=base64_decode($decrypt);
        wqbLog($decryptData);
        return $decrypt;
    }

    /**
     * curl请求
     * @param unknown $PostArry
     * @param unknown $request_url
     */
    public function requestPost($PostArry,$request_url){
        $postData = $PostArry;
        $postDataString = http_build_query($postData);//格式化参数

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $request_url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postDataString); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // 设置超时限制防止死循环返回
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
    
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            $tmpInfo = curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }
    
    /**
     * 查询订单信息
     */
    public function checkOrders(){
        $public_key_path  = APP_PATH."/key/baofu_public.cer";//公钥
        $private_key_path = APP_PATH."/key/baofu_private.pfx";//私钥
        $private_key_password = "051811"; //私钥密码
        $request_url = "https://public.baofoo.com/cutpayment/api/backTransRequest";//api地址
        $version      = "4.0.0.0";//版本号
        $terminal_id  = "35004";//终端号
        $txn_type     = "0431";//交易类型
        $txn_sub_type = "31";//交易子类
        $member_id    = "1174846";//商户号
        $data_type    = "json";//加密数据类型
        $data_arr     = array(
            "txn_sub_type"      => $txn_sub_type,
            "biz_type"          => "0000",//接入类型
            "terminal_id"       => $terminal_id,
            "member_id"         => $member_id,
            "orig_trans_id"     => "XIANDAIMAO1499219961",//商户订单号
            "trans_serial_no"   => "XIANDAIMAO1499219961NUM",//商户流水号
            "orig_trade_date"   => "20170608151430",
            "additional_info"   => "fumi",//附加字段
            "req_reserved"      => "bb"//请求方保留域
        );
        $Encrypted_string = str_replace("\\/", "/",json_encode($data_arr));//转JSON
        wqbLog($data_arr);
        $this->checkRsa($private_key_path,$public_key_path,$private_key_password);
        $Encrypted = $this->encryptedByPrivateKey($Encrypted_string);
        $PostArry = array(
            "version"       => $version,
            "terminal_id"   => $terminal_id,
            "txn_type"      => $txn_type,
            "txn_sub_type"  => $txn_sub_type,
            "member_id"     => $member_id,
            "data_type"     => $data_type,
            "data_content"  => $Encrypted);
        $return = $this->requestPost($PostArry, $request_url);
        wqbLog("返回数据：".$return);
        if(empty($return)){
            throw new Exception("返回为空，确认是否网络原因！");
        }
        $return_decode = $this->decryptByPublicKey($return);//解密返回的报文
        wqbLog($return_decode);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}