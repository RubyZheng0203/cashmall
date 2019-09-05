<?php 
class CheckUserAction extends HCommonAction{

    /**
     * 请求贷前决策树
     * @param 会员编号 $uid
     * @param 借款ID $borrowId
     * @param 数据集 $data
     * @return $flg 1：通过 0：拒绝
     */
    public function requestApi($uid,$borrowId,$categoryId,$data=array()) {
       $url = C('RISK_URL'); //风控请求接口
       if (empty($data)){
           $memberInfo  = M("member_info")->field(true)->where("uid = $uid")->find();
            
           $companyInfo = M("member_company")->field(true)->where("uid = $uid ")->find();
            
           $bank_info   = M("member_bank")->field(true)->where("uid = $uid and type = 1 ")->find();
            
           $deviceInfo  = M("member_device")->field(true)->where("uid = $uid")->find();
            
           $borrowInfo  = M("borrow_apply")->field(true)->where("uid = $uid and id = $borrowId ")->find();
            
           $members     = M("members")->field(true)->where("id = $uid")->find();
            
           $statusInfo  = M("member_status")->field(true)->where("uid = $uid and pending = 0 and id = $borrowId ")->find();
           $data['categoryId']  = $categoryId;
           $data['borrowId']    = $borrowId;
           $data['idNumber']    = $memberInfo['id_card'];//身份证
           $data['mobileAuth']  = "Accept";//手机认证
           $data['nameAuth']    = "Accept";//实名认证
           $data['fmBlackList'] = array(
               'iphone'     => $members['iphone'],//手机
               'email'      => $memberInfo['email'],//邮箱
               'idCard'     => $memberInfo['id_card'],//身份证
               'mmDevice'   => $deviceInfo['maxent_id'],//猛犸设备ID
               'isPass'     => "false"//是否放行
           );
           $data['tdLoan'] = array(
               'accountName'   => $memberInfo['real_name'],//姓名
               'idNumber'      => $memberInfo['id_card'],//身份证
               'accountMobile' => $members['iphone'],//手机号
               'accountEmail'  => $memberInfo['email'],//邮箱
               'qqNumber'      => $memberInfo['qq_code'],//QQ
               'organization'  => $companyInfo['company_name'],//工作单位
               'annualIncome'  => $companyInfo['year_income'],//年收入
               'houseType'     => $memberInfo['house_type'],//房产类型
               'houseProperty' => $memberInfo['house'],//房产情况
               'loanAmount'    => $borrowInfo['money'],//借款金额
               'loanPurpose'   => $borrowInfo['purpose'],//借款用途
               'homeAddress'   => $memberInfo['address'],
               'organizationAddress'   => $companyInfo['company_address'],
               'registeredAddress'     => get_province($memberInfo['register_province']).get_city($memberInfo['register_city']).$memberInfo['register_address'],
               'isPass'        => "false"
           );
           $data['zm']  = array(
               'certNo'    => $memberInfo['id_card'],//身份证
               'certType'  => "IDENTITY_CARD",//证件类型
               'name'      => $memberInfo['real_name'],//姓名
               'mobile'    => $members['iphone'],//手机号码
               'email'     => $memberInfo['email'],//邮箱
               'bankCard'  => $bank_info['bank_card'],//银行卡号
               'isPass'        => "false"
           );
           $data['bqsLog'] = array(
               'account'   => $members['iphone'],//手机号
                'name'     => $memberInfo['real_name'],//姓名
                'email'    => $memberInfo['email'],//邮箱
                'mobile'   => $members['iphone'],//手机号
                'certNo'   => $memberInfo['id_card'],//身份证
                'tokenKey' => session_id(),
                'ip'       => get_client_ip(),
               'isPass'    => "true"
           );
           //白骑士贷款策略参数
           $data['bqsLoan']  = array(
               'account'   => $members['iphone'],//手机号
               'name'      => $memberInfo['real_name'],//姓名
               'email'     => $memberInfo['email'],//邮箱
               'mobile'    => $members['iphone'],//手机号
               'certNo'    => $memberInfo['id_card'],//身份证
               'bankCardNo'=> $bank_info['bank_card'], //银行卡卡号
               //'bankCardName'=> "",//银行户名
               //'bankCardMobile'=> "",//银行预留手机号
               'amount'    => $borrowInfo['money'], //借款金额
               'zmOpenId'  => $members['zhima_openid'],//芝麻openId
               //'contactsName'           => "",//用户联系人姓名
               //'contactsMobile'         => "",//用户联系人手机号
               //'creditCardNo'           => "",//信用卡卡号
               //'creditCardName'         => "",//信用卡户名
               //'creditCardMobile'       => "",//信用卡预留手机号
               //'platform'               => "",//平台 h5/web/ios/android
               'address'                => get_province($memberInfo['province']).get_city($memberInfo['city']).$memberInfo['address'],//用户住址
               'addressCity'            => get_province($memberInfo['province']).get_city($memberInfo['city']),//用户所在城市
               //'contactsNameSec'        => "",//第二联系人姓名
               //'contactsMobileSec'      => "",//第二联系人手机号
               //'organization'           => "",//用户工作单位
               'organizationAddress'    => get_province($companyInfo['company_province']).get_city($companyInfo['company_city']).$companyInfo['company_address'],//用户工作单位地址
               //'education'              => "",//学历
               //'graduateCity'           => "",//毕业院校城市
               'marriage'               => "",//是否已婚
               'residence'              => get_province($memberInfo['register_province']).get_city($memberInfo['register_city']).$memberInfo['register_address'],//户籍所在地
               'tokenKey'               => session_id(),
               'ip'                     => get_client_ip(),
               'isPass'                 => 'false'
           );
           //白骑士贷款策略参数
           $data['bqsGrey'] = array(
               'name'      => $memberInfo['real_name'],//姓名
               'mobile'    => $members['iphone'],//手机号
               'certNo'    => $memberInfo['id_card'],//身份证
               'isPass'    => "false"
           );
           
           wqbLog("baiqishisession--------".session_id());
           $data['zmCreScore']  = array(
               'openId'    => $members['zhima_openid'],
               'tokenKey'  => session_id(),
               'isPass'    => "true"
           );
           $data['callData'] = array(
               'mobile'    => $members['iphone'],
               'isPass'    => "false"
           );
           $data['tdFqz'] = array(
               'loanAmount'     => $borrowInfo['money'],
               'loanTerm'       => $borrowInfo['duration'],
               'loanTermUnit'   => "DAY",
               'loanDate'       => date('Y-m-d',$borrowInfo['add_time']),
               'idNumber'       => $memberInfo['id_card'],//身份证
               'mobile'         => $members['iphone'],
               'name'           => $memberInfo['real_name'],
               'isPass'         => "false"
           );
           $data['ipValidate'] = array(
               'regIpCity'   => $members['reg_address'],//注册ip城市
               'loginIpCity' => get_ipAddress(get_client_ip()),//登陆ip城市
               'perAddr'     => get_province($memberInfo['province']).get_city($memberInfo['city']).$memberInfo['address'],//常住地址 
               'companyAddr' => get_province($companyInfo['company_province']).get_city($companyInfo['company_city']).$companyInfo['company_address'],//公司地址
               'mobile'      => $members['iphone'],//手机号码
               'isPass'      => 'false'
           );
           $data['regAudit']   = "Accept";//注册审核
           $data['accountVer'] = "Accept";//账号验证
           $data['logAudit']   = "Accept";//登录审核
           $data['loanAudit']  = "Accept";//
           if ($statusInfo['id_verify'] == 2){
               $data['zmht']       = "Reject";//芝麻活体
           }else {
               $data['zmht']       = "Accept";//芝麻活体
           }
       }
       $res = http_request($url,json_encode($data));
       wqbLog($data);
       $flg = 0;
       wqbLog("贷款决策树结果---".$res);
       if (stripos($res,'reject') !== false){
           wqbLog("AA---".$res);
           $arr = explode(':', $res);
           $result = $this->dealBlackUser($arr[1]);
           if ($result){
                   switch ($arr[1]){
                       case '3':
                          $data['fmBlackList']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                       case '6':
                          $data['tdLoan']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                       case '7':
                          $data['zm']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                       case '21':
                          $data['bqsLog']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                       case '14':
                          $data['bqsLoan']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                       case '13':
                          $data['zmCreScore']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                       case '15':
                          $data['callData']['isPass'] = "true";
                          self::requestApi($uid,$data);
                          break;
                   }
                   $flg = 1;
               }else {
                   wqbLog("未通过");
                   $flg = 0;
               }          
       }else if (stripos($res, 'error') !== false){
           $arr = explode(':', $res);
           wqbLog("贷款决策树报错---uid:".$uid.",错误code：".$arr[1]);
           $flg = 0; 
       }else if(stripos($res,'accept') !== false){
               
           $flg = 1;
       }else{
           $arr = explode(':', $res);
           wqbLog("贷款决策树请求连接不上");
           $flg = 0;                            
       }
       return $flg;
    }
    
    public function requestBackApi() {
        $uid        = intval($_POST['uid']);
        $borrowId   = intval($_POST['bid']);
        $categoryId = intval($_POST['cid']);
        $data       = array();
        $url = C('RISK_URL'); //风控请求接口
        if (empty($data)){
            $memberInfo  = M("member_info")->field(true)->where("uid = $uid")->find();
    
            $companyInfo = M("member_company")->field(true)->where("uid = $uid ")->find();
    
            $bank_info   = M("member_bank")->field(true)->where("uid = $uid and type = 1 ")->find();
    
            $deviceInfo  = M("member_device")->field(true)->where("uid = $uid")->find();
    
            $borrowInfo  = M("borrow_apply")->field(true)->where("uid = $uid and id = $borrowId ")->find();
    
            $members     = M("members")->field(true)->where("id = $uid")->find();
    
            $statusInfo  = M("member_status")->field(true)->where("uid = $uid and pending = 0 and id = $borrowId ")->find();
            $data['categoryId']  = $categoryId;
            $data['borrowId']    = $borrowId;
            $data['idNumber']    = $memberInfo['id_card'];//身份证
            $data['mobileAuth']  = "Accept";//手机认证
            $data['nameAuth']    = "Accept";//实名认证
            $data['fmBlackList'] = array(
                'iphone'     => $members['iphone'],//手机
                'email'      => $memberInfo['email'],//邮箱
                'idCard'     => $memberInfo['id_card'],//身份证
                'mmDevice'   => $deviceInfo['maxent_id'],//猛犸设备ID
                'isPass'     => "false"//是否放行
            );
            $data['tdLoan'] = array(
                'accountName'   => $memberInfo['real_name'],//姓名
                'idNumber'      => $memberInfo['id_card'],//身份证
                'accountMobile' => $members['iphone'],//手机号
                'accountEmail'  => $memberInfo['email'],//邮箱
                'qqNumber'      => $memberInfo['qq_code'],//QQ
                'organization'  => $companyInfo['company_name'],//工作单位
                'annualIncome'  => $companyInfo['year_income'],//年收入
                'houseType'     => $memberInfo['house_type'],//房产类型
                'houseProperty' => $memberInfo['house'],//房产情况
                'loanAmount'    => $borrowInfo['money'],//借款金额
                'loanPurpose'   => $borrowInfo['purpose'],//借款用途
                'homeAddress'   => $memberInfo['address'],
                'organizationAddress'   => $companyInfo['company_address'],
                'registeredAddress'     => get_province($memberInfo['register_province']).get_city($memberInfo['register_city']).$memberInfo['register_address'],
                'isPass'        => "false"
            );
            $data['zm']  = array(
                'certNo'    => $memberInfo['id_card'],//身份证
                'certType'  => "IDENTITY_CARD",//证件类型
                'name'      => $memberInfo['real_name'],//姓名
                'mobile'    => $members['iphone'],//手机号码
                'email'     => $memberInfo['email'],//邮箱
                'bankCard'  => $bank_info['bank_card'],//银行卡号
                'isPass'        => "false"
            );
            $data['bqsLog'] = array(
                'account'   => $members['iphone'],//手机号
                'name'     => $memberInfo['real_name'],//姓名
                'email'    => $memberInfo['email'],//邮箱
                'mobile'   => $members['iphone'],//手机号
                'certNo'   => $memberInfo['id_card'],//身份证
                'tokenKey' => session_id(),
                'ip'       => get_client_ip(),
                'isPass'    => "true"
            );
            //白骑士贷款策略参数
            $data['bqsLoan']  = array(
                'account'   => $members['iphone'],//手机号
                'name'      => $memberInfo['real_name'],//姓名
                'email'     => $memberInfo['email'],//邮箱
                'mobile'    => $members['iphone'],//手机号
                'certNo'    => $memberInfo['id_card'],//身份证
                'bankCardNo'=> $bank_info['bank_card'], //银行卡卡号
                //'bankCardName'=> "",//银行户名
                //'bankCardMobile'=> "",//银行预留手机号
                'amount'    => $borrowInfo['money'], //借款金额
                'zmOpenId'  => $members['zhima_openid'],//芝麻openId
                //'contactsName'           => "",//用户联系人姓名
                //'contactsMobile'         => "",//用户联系人手机号
                //'creditCardNo'           => "",//信用卡卡号
                //'creditCardName'         => "",//信用卡户名
                //'creditCardMobile'       => "",//信用卡预留手机号
                //'platform'               => "",//平台 h5/web/ios/android
                'address'                => get_province($memberInfo['province']).get_city($memberInfo['city']).$memberInfo['address'],//用户住址
                'addressCity'            => get_province($memberInfo['province']).get_city($memberInfo['city']),//用户所在城市
                //'contactsNameSec'        => "",//第二联系人姓名
                //'contactsMobileSec'      => "",//第二联系人手机号
                //'organization'           => "",//用户工作单位
                'organizationAddress'    => get_province($companyInfo['company_province']).get_city($companyInfo['company_city']).$companyInfo['company_address'],//用户工作单位地址
                //'education'              => "",//学历
                //'graduateCity'           => "",//毕业院校城市
                'marriage'               => "",//是否已婚
                'residence'              => get_province($memberInfo['register_province']).get_city($memberInfo['register_city']).$memberInfo['register_address'],//户籍所在地
                'tokenKey'               => session_id(),
                'ip'                     => get_client_ip(),
                'isPass'                 => 'false'
            );
            //白骑士贷款策略参数
            $data['bqsGrey'] = array(
                'name'      => $memberInfo['real_name'],//姓名
                'mobile'    => $members['iphone'],//手机号
                'certNo'    => $memberInfo['id_card'],//身份证
                'isPass'    => "false"
            );
             
            wqbLog("baiqishisession--------".session_id());
            $data['zmCreScore']  = array(
                'openId'    => $members['zhima_openid'],
                'tokenKey'  => session_id(),
                'isPass'    => "true"
            );
            $data['callData'] = array(
                'mobile'    => $members['iphone'],
                'isPass'    => "false"
            );
            $data['tdFqz'] = array(
                'loanAmount'     => $borrowInfo['money'],
                'loanTerm'       => $borrowInfo['duration'],
                'loanTermUnit'   => "DAY",
                'loanDate'       => date('Y-m-d',$borrowInfo['add_time']),
                'idNumber'       => $memberInfo['id_card'],//身份证
                'mobile'         => $members['iphone'],
                'name'           => $memberInfo['real_name'],
                'isPass'         => "false"
            );
            $data['ipValidate'] = array(
                'regIpCity'   => $members['reg_address'],//注册ip城市
                'loginIpCity' => get_ipAddress(get_client_ip()),//登陆ip城市
                'perAddr'     => get_province($memberInfo['province']).get_city($memberInfo['city']).$memberInfo['address'],//常住地址
                'companyAddr' => get_province($companyInfo['company_province']).get_city($companyInfo['company_city']).$companyInfo['company_address'],//公司地址
                'mobile'      => $members['iphone'],//手机号码
                'isPass'      => 'false'
            );
            $data['regAudit']   = "Accept";//注册审核
            $data['accountVer'] = "Accept";//账号验证
            $data['logAudit']   = "Accept";//登录审核
            $data['loanAudit']  = "Accept";//
            if ($statusInfo['id_verify'] == 2){
                $data['zmht']       = "Reject";//芝麻活体
            }else {
                $data['zmht']       = "Accept";//芝麻活体
            }
        }
        $res = http_request($url,json_encode($data));
        wqbLog($data);
        $flg = 0;
        wqbLog("贷款决策树结果---".$res);
        if (stripos($res,'reject') !== false){
            $arr = explode(':', $res);
            $result = $this->dealBlackUser($arr[1]);
            if ($result){
                switch ($arr[1]){
                    case '3':
                        $data['fmBlackList']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                    case '6':
                        $data['tdLoan']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                    case '7':
                        $data['zm']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                    case '21':
                        $data['bqsLog']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                    case '14':
                        $data['bqsLoan']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                    case '13':
                        $data['zmCreScore']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                    case '15':
                        $data['callData']['isPass'] = "true";
                        self::requestApi($uid,$data);
                        break;
                }
                $flg = 1;
                ajaxmsg("决策树通过",1);
            }else {
                $flg = 0;
                ajaxmsg("决策树未通过",0);
            }
        }else if (stripos($res, 'error') !== false){
            $arr = explode(':', $res);
            wqbLog("贷款决策树报错---uid:".$uid.",错误code：".$arr[1]);
            $flg = 0;
            ajaxmsg("贷款决策树报错",0);
        }else if(stripos($res,'accept') !== false){
            $flg = 1;
            ajaxmsg("决策树通过",1);
        }else{
            $arr = explode(':', $res);
            $flg = 0;
            ajaxmsg("贷款决策树请求连接不上",0);
        }
        
    }
        
    /**
     * 处理返回黑名单用户
     */
    private function dealBlackUser($api_id){  
        $flg = 0;
        $map['api_id'] = $api_id;
        $map['status'] = 1;
        $now           = time();
        $info = M("random_setup")->field('interval,num,flag,lasttime')->where($map)->find();
        wqbLog($now);
        wqbLog($info['lasttime']);
        wqbLog($info['interval']);
        wqbLog(($now - $info['lasttime']));
        if (!empty($info)){
            if (($now - $info['lasttime']) > $info['interval']){
                if ($info['num'] > 0){//黑名单放行通过
                    $sdata['lasttime'] = $now;
                    $sdata['num']      = $info['num'] - 1;
                    M("random_setup")->where($map)->save($sdata);
                    wqbLog("黑名单放行通过");
                    $flg = 0;
                }else {//黑名单放行次数用完
                    wqbLog("黑名单放行次数用完");
                    $flg = 0;
                }
            }else {//使用时间间隔未到
                wqbLog("使用时间间隔未到 ");
                $flg = 0;
            }
        }else {
            wqbLog("放行未通过");
            $flg = 0;
        }
        return  $flg;    
    }
    
    /**
     * 请求注册决策树
     * @param 用户编号 $uid
     */
    public function requestRegistApi($uid){
        $url = C('RISK_URL'); //风控请求接口
        $memberInfo = M("member_info")->field(true)->where("uid = $uid")->find();
        $deviceInfo = M("member_device")->field(true)->where("uid = $uid")->find();
        $bank_info  = M("member_bank")->field(true)->where("uid = $uid")->find();
        $members     = M("members")->field(true)->where("id = $uid")->find();
        $map['uid'] = $uid;
        $map['status'] = array('not in','5,96,97,98,99');
        $borrowInfo = M("borrow_apply")->field(true)->where($map)->find();
        $data['categoryId']  = "1";
        $data['borrowId']= $borrowInfo['id'];
        $data['idNumber']= $memberInfo['id_card'];
        $data['mobileAuth'] = "Accept";
        $data['nameAuth']   = "Accept";
        $data['regAudit']   = "Accept";//注册审核
        $data['fmBlackList'] = array(
            'iphone'     => $members['iphone'],//手机
            'email'      => $memberInfo['email'],//邮箱
            'idCard'     => $memberInfo['id_card'],//身份证
            'mmDevice'   => $deviceInfo['maxent_id'],//猛犸设备ID
            'isPass'     => "false"//是否放行
        );
        $data['tdReg']  = array(
            'accountLogin'  => $members['iphone'],
            'accountMobile' => $members['iphone'],
            'ipAddress'     => get_client_ip(),
            'tokenId'       =>session_id(),
            'isPass'        => 'false',
        );
        $data['zm']  = array(
            'certNo'    => $memberInfo['id_card'],//身份证
            'certType'  => "IDENTITY_CARD",//证件类型
            'name'      => $memberInfo['real_name'],//姓名
            'mobile'    => $members['iphone'],//手机号码
            'email'     => $memberInfo['email'],//邮箱
            'bankCard'  => $bank_info['bank_card'],//银行卡号
            'isPass'    => "false"
        );
        $data['bqsReg'] = array(
            'account'   => $members['iphone'],//手机号
            'name'      => $memberInfo['real_name'],//姓名
            'email'     => $memberInfo['email'],//邮箱
            'mobile'    => $members['iphone'],//手机号
            'certNo'    => $memberInfo['id_card'],//身份证
            'tokenKey'  => session_id(),
            'ip'        => get_client_ip(),
            'isPass'    => 'false'
        );
        $res = http_request($url,json_encode($data));
        wqbLog("uid:".$uid.",调用注册策略结果".$res);
    }

    /**
     * 请求登录决策树
     * @param 用户编号 $uid
     */
    public function requestLoginApi($uid){
        $url = C('RISK_URL'); //风控请求接口
        $memberInfo = M("member_info")->field(true)->where("uid = $uid")->find();
        $deviceInfo = M("member_device")->field(true)->where("uid = $uid")->find();
        $bank_info  = M("member_bank")->field(true)->where("uid = $uid")->find();
        $members     = M("members")->field(true)->where("id = $uid")->find();
        $map['uid'] = $uid;
        $map['status'] = array('not in','5,96,97,98,99');
        $borrowInfo = M("borrow_apply")->field(true)->where($map)->find();
        $borrowId = empty($borrowInfo) ? 0 : $borrowInfo['id'];
        $data['categoryId']  = "2";
        $data['borrowId']= $borrowId;
        $data['idNumber']= $memberInfo['id_card'];
        $data['accountVer'] = "Accept";
        $data['logAudit']   = "Accept";
        $data['fmBlackList'] = array(
            'iphone'     => $members['iphone'],//手机
            'email'      => $memberInfo['email'],//邮箱
            'idCard'     => $memberInfo['id_card'],//身份证
            'mmDevice'   => $deviceInfo['maxent_id'],//猛犸设备ID
            'isPass'     => "false"//是否放行
        );
        $data['tdLogin']  = array(
            'accountLogin'  => $members['iphone'],
            'accountMobile' => $members['iphone'],
            'state'         => 0,
            'ipAddress'     => get_client_ip(),
            'tokenId'       => session_id(),
            'isPass'        => 'false',
        );
        $data['zm']  = array(
            'certNo'    => $memberInfo['id_card'],//身份证
            'certType'  => "IDENTITY_CARD",//证件类型
            'name'      => $memberInfo['real_name'],//姓名
            'mobile'    => $members['iphone'],//手机号码
            'email'     => $memberInfo['email'],//邮箱
            'bankCard'  => $bank_info['bank_card'],//银行卡号
            'isPass'    => "true"
        );
        $data['bqsLog'] = array(
            'account'   => $members['iphone'],//手机号
            'name'      => $memberInfo['real_name'],//姓名
            'email'     => $memberInfo['email'],//邮箱
            'mobile'    => $members['iphone'],//手机号
            'certNo'    => $memberInfo['id_card'],//身份证
            'tokenKey'  => session_id(),
            'ip'        => get_client_ip(),
            'isPass'    => 'false'
        );
        $res = http_request($url,json_encode($data));
        wqbLog("uid:".$uid.",调用登录策略结果".$res);
    }
    
    /**
     * 后台请求APP风控审核    老的方法
     * @param 用户编号 $uid
     */
    public function appRiskAuditOld($uid,$moblie,$borrowId){
        $uid        = intval($_GET['uid']);
        $moblie     = $_GET['iphone'];
        $borrowId   = intval($_GET['id']);
        $categoryId = 8;
        $userinfo   = M("ad_contact")->field('id,device_id,name,mobile')->where("uid = {$uid}")->limit(3)->order('add_time desc')->select();
        $moblielist = $userinfo[0]['mobile'].'/'.$userinfo[1]['mobile'].'/'.$userinfo[2]['mobile']; //手机号
        $namelist   = urlencode($userinfo[0]['name']).'/'.urlencode($userinfo[1]['name']).'/'.urlencode($userinfo[2]['name']) ;  //通讯录用户名
        $device_id  = $userinfo[0]['device_id']; //设备ID
        //通讯录分析  
        $addrList =  $this->addrList($uid,$moblie, $device_id, $moblielist, $namelist);
        if (strpos('分析成功',$addrList) !== false){
             //安装App分析
             $appControl  =   $this->appControl($uid,$moblie);
             if (strpos("分析成功",$appControl) !== false){
                 //地址分析
                 $memberinfo = M("member_info")->field('address')->where("uid = {$uid}")->find();
                 $loginIp = $regIp = $loginGps = $regGps = $perAddr = urlencode($memberinfo['address']) ;
                 $comAddr = M("member_company")->field('company_address')->where("uid = {$uid}")->find();
                 $comAddr = urlencode($comAddr['company_address']);
                 $addressControl = $this->addressControl($moblie, $loginIp, $regIp, $loginGps, $regGps, $perAddr, $comAddr);
                 if (strpos('分析成功',$addressControl) !== false){
                     //短信分析
                     $smsControl = $this->smsControl($moblie);
                     if (strpos("分析成功",$smsControl) !== false){
                         // 通话记录分析
                         $userinfos =M("ad_callrecord")->field('id,device_id,name,mobile')->where("uid = {$uid}")->limit(3)->order('add_time desc')->select();
                         $call_moblielist = $userinfos[0]['mobile'].'/'.$userinfos[1]['mobile'].'/'.$userinfos[2]['mobile']; //手机号
                         $call_namelist   = urlencode($userinfos[0]['name']).'/'.urlencode($userinfos[1]['name']).'/'.urlencode($userinfos[2]['name']);  //通讯录用户名
                         $callControl = $this->callControl($uid,$moblie, $device_id, $call_moblielist, $call_namelist);
                          if (strpos("分析成功",$callControl) !== false){
                             $flg =   $this->appApi($uid, $borrowId, $categoryId,$device_id);
                             if ($flg == 1){
                                 $this->success("App风控审核通过");
                             }else{
                                 switch ($flg){
                                     case '2':
                                         $this->error("APP端贷款决策树未通过");
                                     case '3':
                                         $this->error("APP端贷款决策树报错");
                                     case '4':
                                         $this->error("APP端贷款决策树请求连接不上");
                                 }  
                             }
                         }else {
                             wqbLog("通话记录分析失败---");
                             $this->error('通话记录'.$callControl);
                         }
                     }else{
                         wqbLog("短信分析失败---");
                         $this->error('短信'.$smsControl);
                     }
                 }else {
                     wqbLog("地址分析失败---");
                     $this->error('地址'.$addressControl);
                 }
             }else {
                 wqbLog("App分析失败---");
                 $this->error('App'.$appControl);
             }
            
         }else {
             wqbLog("通讯录分析失败---");
             $this->error('通讯录'.$addrList);
         }
    }
    
    /**
     * 后台请求APP设备通讯录分析
     * @param 用户编号 $uid
     */
    public function adAddrAudit($uid,$moblie,$borrowId){
        $uid        = intval($_GET['uid']);
        $moblie     = $_GET['iphone'];
        $userinfo   = M("ad_contact")->field('id,device_id,name,mobile')->where("uid = {$uid}")->limit(3)->order('add_time desc')->select();
        $moblielist = $userinfo[0]['mobile'].'/'.$userinfo[1]['mobile'].'/'.$userinfo[2]['mobile']; //手机号
        $namelist   = urlencode($userinfo[0]['name']).'/'.urlencode($userinfo[1]['name']).'/'.urlencode($userinfo[2]['name']) ;  //通讯录用户名
        $device_id  = $userinfo[0]['device_id']; //设备ID
        $addrList   = $this->addrList($uid,$moblie, $device_id, $moblielist, $namelist);
        if (strpos('分析成功',$addrList) !== false){
            ajaxmsg("通讯录分析成功！",1);
        }else {
            ajaxmsg("通讯录分析".$addrList,0);
        }
    
    }
    
    /**
     * 后台请求安装APP分析
     * @param 用户编号 $uid
     */
    public function adAppAudit($uid,$moblie){
        $uid        = intval($_GET['uid']);
        $moblie     = $_GET['iphone'];
        $appControl = $this->appControl($uid,$moblie);
        if (strpos("分析成功",$appControl) !== false){
            ajaxmsg("应用分析成功！",1);
        }else {
            ajaxmsg("应用分析".$addrList,0);
        }
    }
    
    /**
     * 后台请求APP地址分析
     * @param 用户编号 $uid
     */
    public function adAddressAudit($uid,$moblie){
        $uid        = intval($_GET['uid']);
        $moblie     = $_GET['iphone'];
        $memberinfo = M("member_info")->field('address')->where("uid = {$uid}")->find();
        $loginIp    = $regIp = $loginGps = $regGps = $perAddr = urlencode($memberinfo['address']) ;
        $comAddr    = M("member_company")->field('company_address')->where("uid = {$uid}")->find();
        $comAddr    = urlencode($comAddr['company_address']);
        $addressControl = $this->addressControl($moblie, $loginIp, $regIp, $loginGps, $regGps, $perAddr, $comAddr);
        if (strpos('分析成功',$addressControl) !== false){
            ajaxmsg("地址分析成功！".$addrList,1);
        }else {
            ajaxmsg("地址分析".$addrList,0);
        }
    }
    
    /**
     * 后台请求APP短信分析
     * @param 用户编号 $uid
     */
    public function adSmsAudit($moblie){
        $moblie     = $_GET['iphone'];
        $smsControl = $this->smsControl($moblie);
        if (strpos("分析成功",$smsControl) !== false){
            ajaxmsg("短信分析成功！",1);
        }else{
            ajaxmsg("短信分析".$addrList,0);
        }
    }
    
    /**
     * 后台请求APP通话记录
     * @param 用户编号 $uid
     */
    public function adCallAudit($uid,$moblie){
        $uid             = intval($_GET['uid']);
        $moblie          = $_GET['iphone'];
        $userinfo        = M("ad_setup")->field('device_id')->where("uid = {$uid}")->order('add_time desc')->find();
        $device_id       = $userinfo['device_id'];//设备ID
        $userinfos       = M("ad_callrecord")->field('id,device_id,name,mobile')->where("uid = {$uid}")->limit(3)->order('add_time desc')->select();
        $call_moblielist = $userinfos[0]['mobile'].'/'.$userinfos[1]['mobile'].'/'.$userinfos[2]['mobile']; //手机号
        $call_namelist   = urlencode($userinfos[0]['name']).'/'.urlencode($userinfos[1]['name']).'/'.urlencode($userinfos[2]['name']);  //通讯录用户名
        $callControl     = $this->callControl($uid,$moblie, $device_id, $call_moblielist, $call_namelist);
        if (strpos("分析成功",$callControl) !== false){
            ajaxmsg("通话记录分析成功！",1);
        }else {
            ajaxmsg("通话记录分析".$addrList,0);
        }
    }

    /**
     * 后台请求APP风控审核
     * @param 用户编号 $uid
     */
    public function appRiskAudit($uid,$moblie,$borrowId){
        $uid        = intval($_GET['uid']);
        $moblie     = $_GET['iphone'];
        $borrowId   = intval($_GET['id']);
        $categoryId = 8;
        $userinfo   = M("ad_setup")->field('device_id')->where("uid = {$uid}")->order('add_time desc')->find();
        $device_id  = $userinfo['device_id']; //设备ID
        
        $flg        =   $this->appApi($uid, $borrowId, $categoryId,$device_id);
        if ($flg == 1){
            ajaxmsg("App风控决策申通通过",1);
        }else{
            switch ($flg){
                case '2':
                    ajaxmsg("App风控贷款决策树未通过",0);
                case '3':
                    ajaxmsg("App风控贷款决策树报错",0);
                case '4':
                    ajaxmsg("App风控贷款决策树请求连接不上",0);
            }
        }
    }
    
    
    /**
     * APP端风控决策树
     * @param 用户id $uid
     * @param 订单id $borrowId
     * @param 默认为7 详情见risk_test库下decision_tree表 $categoryId
     * @param 默认空   $data
     * @param 用户设备ID  $device_id
     */
    public function appApi($uid,$borrowId,$categoryId,$device_id,$data=array()) {
        $url = C('RISK_URL'); //风控请求接口
        if (empty($data)){
            $memberInfo =  M("member_info")->field('id_card,email,real_name')->where("uid = {$uid}")->find();
            $members    =  M("members")->field('iphone')->where("id = {$uid}")->find();
            $bank_info  =  M("member_bank")->field('bank_card')->where("uid = {$uid}")->find();
            $data['categoryId']  = $categoryId;
            $data['borrowId']    = $borrowId;
            $data['idNumber']    = $memberInfo['id_card'];//身份证
            $data['mobileAuth']  = "Accept";//手机认证
            $data['nameAuth']    = "Accept";//实名认证
            
            $data['appBehavioral'] = array(
                'mobile'     => $members['iphone'],//手机
                'deviceId'   => $device_id,//客户端设备
                'isPass'     => "false"//是否放行
            );
            $data['appTel'] = array(
                'mobile'     => $members['iphone'],//手机
                'deviceId'   => $device_id,//客户端设备
                'isPass'     => "false"//是否放行
            );
            $data['appCount']  = array(
                'mobile'     => $members['iphone'],//手机
                'deviceId'   => $device_id,//设备号
                'uid'        => $uid,
                'isPass'     => "false"
            );
            $data['appAddr'] = array(
                'mobile'   => $members['iphone'],//手机号
                'isPass'     => $memberInfo['real_name'],//姓名
                'isPass'   => "true"
            );
            $data['appSms'] = array(
                'mobile'     => $members['iphone'],//手机号
                'isPass'     => "false"
            );
            $data['appSet'] = array(
                'mobile'     => $members['iphone'],//手机号
                'isManual'   => 1,//是否人手操作  是：1 否：0
                'isPass'     => "false"
            );
            $data['appMobile'] = array(
                'mobile'     => $members['iphone'],//手机号
                'reasonable' => 1,//是否人手操作  是：1 否：0
                'isPass'     => "false"
            );
            $data['bqsGrey'] = array(
                'name'       => $memberInfo['real_name'], // 用户姓名
                'mobile'     => $members['iphone'],//手机号
                'certNo'     => $memberInfo['id_card'],//身份证号
                'isPass'     => "false"
            );
            $data['appCall'] = array(
                'mobile'     => $members['iphone'],//手机号
                'deviceId'   => $device_id,//用户设备号
                'isPass'     => "false"
            );
        }
        
        $res = http_request($url,json_encode($data));
        wqbLog($data);
        $flg = 0;
        wqbLog("APP端贷款决策树结果---".$res);
        if (stripos($res,'reject') !== false){
            wqbLog("AA---".$res);
            $arr = explode(':', $res);
            $sdata['tree']       = 2;
            $sdata['tree_time'] = time();
            M("member_status")->where("uid = {$uid}")->save($sdata);
            wqbLog("APP端贷款决策树未通过---".$res);
            $flg = 2;
        }else if (stripos($res, 'error') !== false){
            $sdata['tree']       = 2;
            $sdata['tree_time'] = time();
            M("member_status")->where("uid = {$uid} and borrow_id = {$borrowId} ")->save($sdata);
            $arr = explode(':', $res);
            wqbLog("APP端贷款决策树报错---uid:".$uid.",错误code：".$arr[1]);
            $flg = 3;
        }else if(stripos($res,'accept') !== false){
            if($categoryId !== 5){
                $sdata['tree']       = 1;
                $sdata['tree_time'] = time();
                M("member_status")->where("uid = {$uid}  and borrow_id = {$borrowId} ")->save($sdata);
                wqbLog("APP端贷款决策树通过---".$res);
            }
            $flg = 1;
        }else{
            $sdata['tree']       = 2;
            $sdata['tree_time'] = time();
            M("member_status")->where("uid = {$uid}  and borrow_id = {$borrowId} ")->save($sdata);
            $arr = explode(':', $res);
            wqbLog("APP端贷款决策树请求连接不上");
            $flg = 4;
        }
        return $flg;
    }
    
    /**
     * 通讯录分析（数据分析）
     * $moblie 用户手机号
     * $device_id APP客户端的设备号
     * $moblielist 第一二三联系人的手机（/分隔组合，例如“15022221111/15033331111/15044441111”）
     * $namelist  第一二三联系人的姓名 （/分隔组合，例如“小王/小李/小张”）
     */
    public function addrList($uid,$moblie, $device_id, $moblielist, $namelist){
        $url   = C('RISK_WEB'); //风控请求接口
        $host  = $url."/app/addrList";
        $host .= '/'.$uid.'/'.$moblie.'/'.$device_id.'/'.$namelist.'/'.$moblielist;  
        $data  = http_request_zh($host);
        $arr   = explode(":",$data);
        if($arr[0]=="error"){
            switch ($arr[1]) {
                case "00":
                    return "其他错误";
                    break;
                case "01":
                    return "缺少参数";
                    break;
                case "02":
                    return "分析失败";
                    break;
                default :
                    return "网络错误";
                    break;
            }
        }else{
            return "分析成功";
            
        }
        
    }

    /**
     * 通话记录分析（数据分析）
     * @param 手机号码 $moblie
     * @param 用户id $uid
     * @param 设备ID $device_id
     * @param 联系人手机 $moblielist  第一二三联系人的手机（/分隔组合，例如“15022221111/15033331111/15044441111”）
     * @param 联系人姓名 $namelist 第一二三联系人的姓名 （/分隔组合，例如“小王/小李/小张”）
     * @return string
     */
    public function callControl($uid,$moblie, $device_id, $moblielist, $namelist){
        $url   = C('RISK_WEB'); //风控请求接口
        $host  = $url."/app/call";
        $host .= '/'.$uid.'/'.$moblie.'/'.$device_id.'/'.$namelist.'/'.$moblielist;
        $data  = http_request_zh($host);
        $arr   = explode(":",$data);
        if($arr[0]=="error"){
            switch ($arr[1]) {
                case "00":
                    return "其他错误";
                    break;
                case "01":
                    return "缺少参数";
                    break;
                case "02":
                    return "分析失败";
                    break;
                default :
                    return "网络错误";
                    break;
            }
        }else{
            return "分析成功";
        }
    }
    
    /**
     * 短信分析（数据分析）
     * @param 手机号码  $moblie
     * @return boolean
     */
    public function smsControl($mobile){
        $url    = C('RISK_WEB'); //风控请求接口
        $host   = $url."/app/sms/";
        $host  .= $mobile;
        $data   = http_request_zh($host);;
        $arr    = explode(":",$data);
        if($arr[0]=="error"){
            switch ($arr[1]) {
                case "00":
                    return "其他错误";
                    break;
                case "01":
                    return "缺少参数";
                    break;
                case "02":
                    return "分析失败";
                    break;
                default :
                    return "网络错误";
                    break;
            }
        }else{
            return "分析成功";
    
        }
    }
    
    
    /**
     * 地址分析（只分析 city，数据分析）
     * @param 手机号 $moblie
     * @param 注册地址 $loginIp
     * @param 登录地址 $regIp
     * @param 注册GPS地址 $loginGps
     * @param 登录GPS地址 $regGps
     * @param 常住地址 $perAddr
     * @param 公司地址 $comAddr
     */
    public function addressControl($moblie,$loginIp,$regIp,$loginGps,$regGps,$perAddr,$comAddr){
        $url   = C('RISK_WEB'); //风控请求接口
        $host  = $url."/app/addr";
        $host .= '/'.$moblie.'/'.$loginIp.'/'.$regIp.'/'.$loginGps.'/'.$regGps.'/'.$perAddr.'/'.$comAddr;
        $data  = http_request_zh($host);
        $arr   = explode(":",$data);
        if($arr[0]=="error"){
            switch ($arr[1]) {
                case "00":
                    return "其他错误";
                    break;
                case "01":
                    return "缺少参数";
                    break;
                case "02":
                    return "分析失败";
                    break;
                default :
                    return "网络错误";
                    break;
            }
        }else{
            return "分析成功";
        }
    }
    
    /**
     * 安卓安装APP分析（数据分析）
     * @param 用户id $uid
     * @param 用户手机号码 $mobile
     * @return string
     */
    public function appControl($uid,$mobile){
        $url   = C('RISK_WEB'); //风控请求接口
        $host  = $url."/app/appAna";
        $host .= '/'.$uid.'/'.$mobile;
        $data  = http_request_zh($host);
        $arr   = explode(":",$data);
        if($arr[0]=="error"){
            switch ($arr[1]) {
                case "00":
                    return "其他错误";
                    break;
                case "01":
                    return "缺少参数";
                    break;
                case "02":
                    return "分析失败";
                    break;
                default :
                    return "网络错误";
                    break;
            }
        }else{
            return "分析成功";
        }
    }
    
}
?>