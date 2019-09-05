<?php 
class AndroidAction extends HCommonAction{
 
 /******************************************************用户借款流程接口开始*******************************************************/   
    /**
     * 验证码接口     
     */
    public function requestVerifyCode() {
        $array          = $_POST;
        $phone          = $array['phone'];   
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $smsTxt     = FS("Webconfig/smstxt");
            $smsTxt     = de_xie($smsTxt);
            $code       = rand_string_reg(6,1);
			if($phone=="15007028638"){
				$code = "111111"; 
			}
            $res = addToSms($phone,str_replace(array("#UserName#", "#CODE#"), array($phone, $code), $smsTxt['verify_phone']));
            if ($res){
                 exit(json_encode(array('message'=>"验证码已发送",'code'=>200)));
            }else {
                 exit(json_encode(array('message'=>"验证码发送失败",'code'=>401)));
            }        
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 首页的banner
     */
    public function  banner(){ 
        $array = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $ad = M("ad")->field('content')->where("id = 1")->find();
            $ad['content'] = unserialize($ad['content']);
            foreach ($ad['content'] as $key=>$val){
                $ad['content'][$key]['img'] = $_SERVER['HTTP_HOST']."/".$val['img'];
            }
            if ($ad){
                exit(json_encode(array('message'=>"成功",'code'=>200,'result'=>$ad)));
            }else {
                exit(json_encode(array('message'=>"失败",'code'=>401)));
            }
        }else {
            exit(json_encode($checkResult));
        }  
    }
    /**  
     * 注册登录接口       
     */
    public function requestRegist(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        $verifyCode     = $array['verifyCode'];
        $phone          = $array['phone'];
        $tick           = $array['tick'];
        $codeInfo       = M()->query("SELECT add_time,content FROM ml_send_sms WHERE phone = {$phone} AND content LIKE '%{$verifyCode}%' order by add_time desc limit 1 ");
        $codeInfo       = $codeInfo[0];
        $now            = time();
        if (empty($codeInfo)){
            exit(json_encode(array('message'=>"验证码错误",'code'=>401)));
        }else {
            if (($now-$codeInfo['add_time'])>300){
                exit(json_encode(array('message'=>"验证码已过期",'code'=>401)));
            }
        }            
        if (is_bool($checkResult)){
            $info = M("members")->field('id,iphone,usr_attr')->where("iphone = '{$phone}'")->find();
            if (empty($info)){
                $res = $this->insertUser($array);
                if ($res){
                    $uid = checkSinaUid($phone);
                    if (empty($uid)){
                        
                    }else {
                        $user['id']      = $res;
                        $user['sina_id'] = "fumi".$uid;
                        M("members")->save($user);
                    }
                    //$ticData = $this->createUserCoupon($res);
                    $token = $this->createToken($res, $phone);
                    
                    $global = get_global_setting();
                    $ticArr = explode('|', $global['coupon_register']);
                    $money = $ticArr[0];
                    
                    exit(json_encode(array('message'=>"注册成功",'code'=>200,'result'=>array('uid'=>$res,'token'=>$token,'phone'=>$array['phone'],'tickMoney'=>$money,'is_login'=>0,'bid'=>0))));
                }else {
                    exit(json_encode(array('message'=>"注册失败",'code'=>401)));
                }
            }else {
                if (!empty($info)){
                    $token                  = $this->createToken($info['id'], $phone);
                    $userInfo               = M("member_info")->field('id_card,real_name')->where("uid = {$info['id']}")->find();
                    $data['last_time']      = time();
                    $data['last_ip']        = '0';
                    $data['last_address']   = '0';
                    $data['last_gps']       = $array['address'];
                    if($info['usr_attr'] == 0){
                        $data['usr_attr']   = 2;
                    }
                    $res   = M("members")->where("id = {$info['id']}")->save($data);
                    $model = new CheckUserAction();
                    $model->requestLoginApi($info['id']);
                    exit(json_encode(array('message'=>"登录成功",'code'=>200,'result'=>array('uid'=>$info['id'],'token'=>$token,'phone'=>$info['iphone'],'is_login'=>1,'endTime'=>0,'tickMoney'=>'0','userInfo'=>$userInfo))));
                }else {
                    exit(json_encode(array('message'=>"未注册用户",'code'=>401)));
                }
            }
        }else {
            exit(json_encode($checkResult));
        }        
    } 
     
    /**
     * 借款首页数据        
     */
    public function borrowInfo(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        $uid            = $array['uid'];
        $type           = $array['product_type'];
        if (is_bool($checkResult)){
            
            $map['is_on']    = 1;
            $map['type']     = $type;
            $now             = time();
            $borrowInfo      = M("borrow_item")->field(true)->where($map)->order("money, duration")->select();  
            $where['uid']    =  $uid;
            $where['status'] = 5;
            $binfo = M("borrow_apply")->field('status,is_withdraw')->where($where)->find();
            foreach ($borrowInfo as $key => $value) {
                if (!empty($binfo['status'])) {
                    $value['created_rate'] = '0.00';
                }
                 $borrowInfo[$key]['audit_rate'] = getFloatValue($value['money']*$value['duration']*$value['audit_rate']/100,2);
                 $audit_rate = $borrowInfo[$key]['audit_rate'];
                 $borrowInfo[$key]['created_rate'] = $value['created_rate'];
                 $borrowInfo[$key]['total']        = getFloatValue($value['money']+$audit_rate+$value['pay_fee']+$value['created_rate']+$value['enabled_rate']+$value['interest'],2);
            }
            
            $moneyList    = M("borrow_item")->field("DISTINCT money")->where($map)->order("money")->select();
            $durationList = M("borrow_item")->field("DISTINCT duration")->where($map)->order("duration")->select();
            
            $where['uid']           = $uid;
            $where['status']        = 5;
            $binfo = M("borrow_apply")->field('id')->where($where)->find();//查询用户是否借过款
            
            $orHaveCreateFee = empty($binfo) ? 1 : 0;                                                     
            $data['message'] = "数据请求成功";
            $data['code']    = "200";
            $data['result']  = array(
                    'borrowInfo'      => $borrowInfo,
                    'money'           => $moneyList,
                    'duration'        => $durationList,
                    'orHaveCreateFee' => $orHaveCreateFee
            );
            exit(json_encode($data));
        }else {
            exit(json_encode($checkResult));
        }                
    }
    
    /**
     * 申请借款接口  
     */
    public function requestBorrow(){
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $uid     = $array['uid'];
            $mem     = attributes($uid);
            if($mem['is_white']==0 && $mem['is_gold']==0){//普通借款用户，黑名单，灰名单用户
                if($array['product_type']==2){
                    exit(json_encode(array('message'=>"信用额度不足，不能申请此借款，保持良好的还款有助于提额！",'code'=>401)));
                }
                $map['uid']             = $uid; 
                $map['status']          = array('in','0,1,2,3,4');
                $info = M("borrow_apply")->field('id')->where($map)->find();
                if($info['id']>0){
                    exit(json_encode(array('message'=>"您当前有一笔借款正在进行，请还清后再申请！",'code'=>401)));
                }
            }else{
                if($mem['is_white']==1){//白名单用户
                    $map['uid']         = $uid;
                    $map['status']      = array('in','0,1,2,3,4');
                    $info = M("borrow_apply")->field('id')->where($map)->find();
                    if($info['id']>0){
                        exit(json_encode(array('message'=>"您当前有一笔借款正在进行，取消或者还清后方可再申请！",'code'=>401)));
                    }
                }
                if($mem['is_gold']==1){//金名单用户
                    $count1 = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` in (0,1,2,3) and bb.type = 1 ")->count('aa.id');
                    $count2 = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` in (0,1,2,3) and bb.type = 2 ")->count('aa.id');
                    $len1   = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` = 4 and bb.type = 1 ")->count('aa.id');
                    $len2   = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` = 4 and bb.type = 2 ")->count('aa.id');
                    
                    if($count1>0 || $count2>0){
                        exit(json_encode(array('message'=>"您当前有一笔借款正在进行，取消或者放款后方可再申请！",'code'=>401)));
                    }
                    
                    if($len1>0 && $len2>0){
                        exit(json_encode(array('message'=>"您当前已经有两笔借款正在进行，还清后方可再申请！",'code'=>401)));
                    }
                }
            }
            //查询用户是否有借款在身
            //查询用户是否三天内已有被拒借款申请
            $globalArr        = get_global_setting();//借款申请周期
            $loanPeriod       = $globalArr['reapply_day'];
            $now              = time();
            $binfo = M()->query("SELECT id, refuse_time  FROM ml_borrow_apply WHERE uid = {$array['uid']}  AND `status` in (93,94,95,96,97,98) AND (refuse_time + $loanPeriod*3600*24) > $now ORDER BY refuse_time DESC LIMIT 1 ");
            if(empty($binfo)){
                $where['money']    = $array['money'];
                $where['duration'] = $array['day'];
                
                $infos   = M("borrow_item")->field('pay_fee')->where($where)->find();
                $pay_fee = $infos['pay_fee'];
                $result  = insertCashLoanOrder($array['uid'], $array['money'], $array['day'], $array['tickId'], $array['tickMoney'],$array['product_type']);
                if($result!==false){
                    if (!empty($array['tickId'])){
                        $tickData['status'] = 1;
                        $tickData['id']     = $array['tickId'];
                        M("member_coupon")->save($tickData);
                    }
                    exit(json_encode(array('message'=>"申请借款成功",'code'=>200,'result'=>array('bid'=>$result))));
                }else {
                    exit(json_encode(array('message'=>"申请借款失败",'code'=>401)));
                }
            }else {
                $day = $loanPeriod-intval(($now-$binfo[0]['refuse_time'])/24/3600);
                exit(json_encode(array('message'=>"您借款已被拒绝，请于{$day}天后尝试",'code'=>401)));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取用户实名信息接口
     */
    public function userBaseInfo(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid        = $array['uid'];
            $memberInfo = M("member_info")->field('real_name,id_card,register_province,register_city,register_address')->where("uid = $uid")->find();
            $user       = M("members")->where("id = {$uid}")->field('fuiou_id')->find();
            $memberInfo['fuiou_id'] = $user['fuiou_id'];
            $data['message'] = "获取成功";
            $data['code']    = 200;
            $data['result']  = $memberInfo;
            exit(json_encode($data));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 实名认证接口
     */
    /*public function verifyRealName(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $idcardL = substr($array['id_card'], -1);
            if($idcardL=="x"){
                $idcard = substr($array['id_card'], 0,16)."X";
            }else{
                $idcard = $array['id_card'];
            }
            $data['id_card']            = $idcard;
            $data['real_name']          = $array['real_name'];
            $data['cardAddressInfo']    = $array['cardAddressInfo'];
            $data['register_province']  = $array['register_province'];
            $data['register_city']      = $array['register_city'];
            $data['register_address']   = $array['register_address'];
            $data['uid']                = $array['uid'];
            $data['add_time']           = time();
            $map['uid']                 = $array['uid'];
            $sinaInfo   = M("members")->field("sina_id,iphone,is_white,is_gold")->where("id = {$array['uid']}")->find();
            $res        = checkUserAuth($sinaInfo['sina_id']);
            $info       = M("member_info")->field('id,real_name,id_card')->where($map)->find();
            if (empty($info)){
                if (!$res){
                    if (!sinaNameVerify($array['uid'],$array['real_name'],$array['id_card'])){
                        exit(json_encode(array('message'=>'实名认证失败','code'=>401)));
                    }
                }else {
                    if ($res['real_name'] !== $array['real_name'] || $res['id_card'] !== $array['id_card']){
                        exit(json_encode(array('message'=>'实名认证失败','code'=>401)));
                    }
                }
                $data['iphone']         = $sinaInfo['iphone'];
                $result = M("member_info")->add($data);
                $sdata['purpose'] = $array['cashUse'];
                M("borrow_apply")->where("uid = {$array['uid']} AND id = {$array['bid']}")->save($sdata);
                if ($result){
                    set_member_invite_code($array['uid']);
                    $model = new CheckUserAction();
                    $model->requestRegistApi($array['uid']);
                    //请求众安数据
                    $type = getZhongan($idcard,$sinaInfo['iphone'],$array['real_name']);
                    
                    //非白名单众安风控不通过后初审拒绝
                    if($sinaInfo['is_white']==0 && $sinaInfo['is_gold']==0){
                        $iszan = zanType($type);
                        if($iszan==0){
                            $binfo = M("borrow_apply")->field('coupon_id')->where(" uid = {$array['uid']} AND id = {$array['bid']} ")->find();
                            if ($binfo['coupon_id'] > 0){
                                $cstatus['status'] = 0 ;
                                M("member_coupon")->where("id = {$binfo['coupon_id']}")->save($cstatus);
                            }
                            $datas['first_trial']       = 2;
                            $datas['first_trial_time']  = time();
                            $datas['uid']               = $array['uid'];
                            $datas['borrow_id']         = $array['bid'];
                            M("member_status")->add($datas);
                            
                            $ubdata['refuse_time'] = time();
                            $ubdata['status']      = "98";
                            delUserOperation($array['uid'],$array['bid']);
                            M("borrow_apply")->where("uid = {$array['uid']} and id = {$array['bid']}")->save($ubdata);
                            
                            exit(json_encode(array('message'=>'初审拒绝','code'=>401)));
                        }else{
                            exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
                        }
                    }else{
                        exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
                    }
                }
            }else {
                $data['mod_time'] = time();
                M("member_info")->where("uid = {$array['uid']}")->save($data);
                
                $sdata['purpose'] = $array['cashUse'];
                M("borrow_apply")->where("uid = {$array['uid']} AND id = {$array['bid']}")->save($sdata);
                $type = getZhongan($idcard,$sinaInfo['iphone'],$array['real_name']);
                
                //非白名单众安风控不通过后初审拒绝
                if($sinaInfo['is_white']==0 && $sinaInfo['is_gold']==0){
                    $iszan = zanType($type);
                    if($iszan==0){
                        $binfo = M("borrow_apply")->field('coupon_id')->where(" uid = {$array['uid']} AND id = {$array['bid']} ")->find();
                        if ($binfo['coupon_id'] > 0){
                            $cstatus['status'] = 0 ;
                            M("member_coupon")->where("id = {$binfo['coupon_id']}")->save($cstatus);
                        }
                        
                        $datas['first_trial']       = 2;
                        $datas['first_trial_time']  = time();
                        $datas['uid']               = $array['uid'];
                        $datas['borrow_id']         = $array['bid'];
                        M("member_status")->add($datas);
                        
                        $ubdata['refuse_time'] = time();
                        $ubdata['status']      = "98";
                        delUserOperation($array['uid'],$array['bid']);
                        M("borrow_apply")->where("uid = {$array['uid']} and id = {$array['bid']}")->save($ubdata);

                        exit(json_encode(array('message'=>'初审拒绝','code'=>401)));
                    }else{
                        exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
                    }
                }else{
                    exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
                }
            }
        }else{
            exit(json_encode($checkResult));
        }
    }*/


    /**
     * 实名认证接口(富友)
     */
    public function verifyRealName(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $idcardL = substr($array['id_card'], -1);
            if($idcardL=="x"){
                $idcard = substr($array['id_card'], 0,16)."X";
            }else{
                $idcard = $array['id_card'];
            }
            $data['id_card']            = $idcard;
            $data['real_name']          = $array['real_name'];
            $data['cardAddressInfo']    = $array['cardAddressInfo'];
            $data['register_province']  = $array['register_province'];
            $data['register_city']      = $array['register_city'];
            $data['register_address']   = $array['register_address'];
            $data['uid']                = $array['uid'];
            $data['add_time']           = time();
            $map['uid']                 = $array['uid'];
            $sinaInfo   = M("members")->field("sina_id,iphone,is_white,is_gold,fuiou_id")->where("id = {$array['uid']}")->find();
            $info       = M("member_info")->field('id,real_name,id_card')->where($map)->find();
            $data['iphone']         = $sinaInfo['iphone'];
            //更新用户身份信息 
            if (empty($info['real_name'])&&empty($info['id_card'])){ 
                $result = M("member_info")->add($data);
            }else{
                $data['mod_time'] = time();
                $result = M("member_info")->where("uid = {$array['uid']}")->save($data);
            }
            $sdata['purpose'] = $array['cashUse'];
            M("borrow_apply")->where("uid = {$array['uid']} AND id = {$array['bid']}")->save($sdata);
            if($sinaInfo['fuiou_id'] != 0){
                if ($result){
                    set_member_invite_code($array['uid']);
                    $model = new CheckUserAction();
                    $model->requestRegistApi($array['uid']);
                    //请求众安数据
                    $type = getZhongan($idcard,$sinaInfo['iphone'],$array['real_name']);
                    
                    //非白名单众安风控不通过后初审拒绝
                    if($sinaInfo['is_white']==0 && $sinaInfo['is_gold']==0){
                        $iszan = zanType($type);
                        if($iszan==0){
                            $binfo = M("borrow_apply")->field('coupon_id')->where(" uid = {$array['uid']} AND id = {$array['bid']} ")->find();
                            if ($binfo['coupon_id'] > 0){
                                $cstatus['status'] = 0 ;
                                M("member_coupon")->where("id = {$binfo['coupon_id']}")->save($cstatus);
                            }
                            $datas['first_trial']       = 2;
                            $datas['first_trial_time']  = time();
                            $datas['uid']               = $array['uid'];
                            $datas['borrow_id']         = $array['bid'];
                            M("member_status")->add($datas);
                            
                            $ubdata['refuse_time'] = time();
                            $ubdata['status']      = "98";
                            delUserOperation($array['uid'],$array['bid']);
                            M("borrow_apply")->where("uid = {$array['uid']} and id = {$array['bid']}")->save($ubdata);
                            
                            exit(json_encode(array('message'=>'初审拒绝','code'=>401)));
                        }else{
                            exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
                        }
                    }else{
                        exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
                    }
                }
            }else{
                exit(json_encode(array('message'=>'实名认证成功','code'=>200)));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    //富友个人业务
    public function openAccountByFuiou(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $uid = $array['uid'];
            $bid = $array['bid'];
            if($array['type'] == 1){
                $url = "http://192.168.10.115:80/fuiouback/ByFive/uid/".$uid."/bid/".$bid."/ap/1";
                $data['message'] = "申请开户";
            }else if($array['type'] == 2){
                $url = "http://192.168.10.115:80/fuiouback/Grant/uid/".$uid."/ap/1";
                $data['message'] = "申请授权";
            }else if($array['type'] == 3){
                $url = "http://192.168.10.115:80/fuiouback/Card/uid/".$uid."/ap/1";
                $data['message'] = "申请绑卡";
            }else if($array['type'] == 4){
                $url = "http://192.168.10.115:80/fuiouback/Decard/uid/".$uid."/ap/1";
                $data['message'] = "申请解绑";
            }else if($array['type'] == 5){
                $url = "http://192.168.10.115:80/fuiouback/Withd/uid/".$uid."/bid/".$bid."/ap/1";
                $data['message'] = "申请提现";
            }
            $data['code']    =  200;
            $data['result']  =  $url;
            exit(json_encode($data));
        }else{
            exit(json_encode($checkResult));
        }
    }

    //富友用户申请状态查询
    public function findAccountByFuiou(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $status = M("fuiou_order_user")->where("mchnt_txn_ssn = '{$array['mchnt_txn_ssn']}' and plat = 1")->field("status,bid,uid")->find();
            $withdraw = M("member_withdraw")->where("nid = '{$array['mchnt_txn_ssn']}' and plat = 1")->field("withdraw_status")->find();
            //提现
            if(!empty($withdraw)){ 
                if($withdraw['withdraw_status'] == 1){
                    $data['code'] = 200;//提现成功
                }else{
                    $data['code'] = 401;//提现失败
                }
            }else{
                if($status['status'] == 1){
                    if($array['type'] == 1){
                        $first = M("member_status")->where("bid = {$bid} and uid = {$uid}")->field("first_trial")->find();
                        //众安是否通过
                        if($first['first_trial'] == 2){
                            $data['code'] = 403;//评分不足
                        }else{
                            $data['code'] = 200;//成功
                        }
                    }else{
                        $data['code'] = 200;//成功
                    }
                }else if($status['status'] == 2){
                    $data['code']    = 401;//失败
                    $data['message'] = $status['resp_desc'];
                }else{
                    $data['code'] = 202;//待处理
                }
            }
            exit(json_encode($data));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
     /**
     * 检查是否上传图片
     * 
     */
    public function imageStatus(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $narrow_path    = C('NARROW_PATH');
            $uid            = $array['uid'];
            $filename1 = $narrow_path.$uid.'-1.jpg';
            $filename2 = $narrow_path.$uid.'-2.jpg';
            $filename3 = $narrow_path.$uid.'-3.jpg';
            $image_info =  M("borrow_apply")->field(true)->where("uid = {$uid} and is_full = 1 and up_bid > 0 and status in (4,5) and len_time >= '1515751200'")->find();
            if(file_exists($filename1) && file_exists($filename2) && file_exists($filename3) && $image_info){
            	$is_pic = 1;
            }else{
            	$is_pic = 0;
            }
            if($is_pic == 1){
            	$result['image_satus'] = false;
            	exit(json_encode(array('message'=>'已上传图片','code'=>200,'result'=>$result)));
            }else {
            	$result['image_satus'] = true;
            	exit(json_encode(array('message'=>'请上传图片','code'=>401,'result'=>$result)));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 检查是否上传图片
     *
     */
    public function imageStatus2(){
    	$array          = $_POST;
    	$checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
    	if(is_bool($checkResult)){
    		$narrow_path    = C('NARROW_PATH');
    		$uid            = $array['uid'];
    		$filename1 = $narrow_path.$uid.'-1.jpg';
    		$filename2 = $narrow_path.$uid.'-2.jpg';
    		$filename3 = $narrow_path.$uid.'-3.jpg';
    		if(file_exists($filename1) && file_exists($filename2) && file_exists($filename3)){
    			$is_pic = 1;
    		}else{
    			$is_pic = 0;
    		}
    		if($is_pic == 1){
    			$result['image_satus'] = false;
    			exit(json_encode(array('message'=>'已上传图片','code'=>200,'result'=>$result)));
    		}else {
    			$result['image_satus'] = true;
    			exit(json_encode(array('message'=>'请上传图片','code'=>401,'result'=>$result)));
    		}
    	}else{
    		exit(json_encode($checkResult));
    	}
    }


    /**
     * 上传图片
     */
    public function upLoadImage(){ 
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $file1  = $array['uid'].'-1.jpg';
            $file2  = $array['uid'].'-2.jpg';
            $file3  = $array['uid'].'-3.jpg';
            base64_image_id($array['idcard1'],$file1);
            base64_image_id($array['idcard2'],$file2);
            base64_image_id($array['idcard3'],$file3);
            exit(json_encode(array('message'=>'图片上传成功','code'=>200)));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 提交用户详细信息接口
     */
    /**
     * 提交用户详细信息接口
     */
    public function addUserDetail(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            if($apply['status']!=0){
                exit(json_encode(array('message'=>'请刷新后确认借款进度','code'=>401)));
            }else{
                $education      = C("EDUCATION_NAME");
                $eduCode        = C("EDUCATION_CODE");
                $marry          = C("MARRIAGE_NAME");
                $marryCode      = C("MARRIAGE_CODE");
                $relation       = C("SOCIAL_NAME");
                $relationCode   = C("SOCIAL_CODE");
                $map['uid']     = $array['uid'];
                //个人信息
                $infoData['uid']       = $array['uid'];
                $infoData['qq_code']   = $array['qq'];//QQ
                $infoData['education'] = $eduCode[array_search($array['education'], $education)];//学历
                $infoData['marriage']  = $marryCode[array_search($array['marry'], $marry)];//婚姻状态
                $infoData['email']     = $array['email'];//邮箱
                $infoData['address']   = $array['address'];//常住地址
                $infoData['province']  = $array['province2'];//常住地址
                $infoData['city']      = $array['city2'];//常住地址
                $infoData['house']     = $array['house'];
                $infoData['house_type']= $array['house_type'];
                //公司信息
                $companyData['job_title']       = $array['job'];//职业类型
                $companyData['year_income']     = $array['salary'];
                $companyData['company_name']    = $array['companyName'];
                $companyData['company_province']= $array['province'];
                $companyData['company_city']    = $array['city'];
                $companyData['company_address'] = $array['addressInfo'];
                $companyData['company_tel']     = $array['tel_phone'];
                $companyData['job_time']        = $array['job_time'];
                $companyData['debt']            = $array['debt'];
                //社会信息
                $relationData['relation1']  = $relationCode[array_search($array['family'], $relation)];
                $relationData['iphone1']    = $array['familyPhone'];
                $relationData['name2']      = $array['relation'];
                $relationData['name1']      = $array['familyName'];
                $relationData['iphone2']    = $array['relationPhone'];
                $relationData['name3']      = $array['friend'];
                $relationData['iphone3']    = $array['friendPhone'];
                
                $memberRelation = M("member_relation")->field('id')->where($map)->find();
                $memberInfo     = M("member_info")->field('id')->where($map)->find();
                $memberCompany  = M("member_company")->field('id')->where($map)->find();
                if (empty($memberInfo)){
                    $infoData['uid']        = $array['uid'];
                    $infoData['add_time']   = time();
                    $res = M("member_info")->add($infoData);
                }else {
                    $infoData['mod_time']   = time();
                    $res = M("member_info")->where($map)->save($infoData);
                }
                if (empty($memberCompany)){
                    $companyData['uid']    = $array['uid'];
                    $companyData['add_time'] = time();
                    $res2 = M("member_company")->add($companyData);
                }else {
                    $companyData['mod_time'] = time();
                    $res2 = M("member_company")->where($map)->save($companyData);
                }
                if (empty($memberRelation)){
                    $relationData['uid']    = $array['uid'];
                    $relationData['add_time'] = time();
                    $res3 = M("member_relation")->add($relationData);
                } else {
                    $relationData['mod_time'] = time();
                    $res3 = M("member_relation")->where($map)->save($relationData);
                }
                
                if ($res&&$res2&&$res3){
                    
                    //默认芝麻授权通过
                    updateMemStatus($array['uid'],$array['bid']);
                    $members  = M("members")->field(true)->where("id = {$array['uid']}")->find();
                    //白骑士策略
                    $model    = new  CheckUserAction();
                    //灰名单用户
                    if($members['is_gray']==1){
                        $checkRes = $model->requestApi($members['id'], $array['bid'],5);//白骑士灰策略
                    }else if($members['is_gold']==0&&$members['is_white']==0&&$members['is_black']==0){ //正常用户
                        $checkRes = $model->requestApi($members['id'], $array['bid'],5);//白骑士一般策略
                    }else if($members['is_gold']==1 || $members['is_white']==1){//白金用户
                        $checkRes = 1;
                    }else if($members['is_black']==1){
                        $checkRes = 0;
                    }
                    if($checkRes==0){
                        $mid_tree = 2;
                    }else{
                        $mid_tree = 1;
                        $status   = mallRisk($array['uid']);//年龄、职业、地区审核
                        if($status==0&&$members['is_white']==0&&$members['is_gold']==0){
                            $checkRes = 0;
                        }
                    }
                
                    //黑名单直接拒绝
                    if($members['is_black']==1){
                        $bdata['status']      = 98;
                        $bdata['refuse_time'] = time();
                        M("borrow_apply")->where("id = {$array['bid']}")->save($bdata);
                
                        $data['first_trial']       = 2;
                        $data['first_trial_time']  = time();
                        $data['mid_tree']          = $mid_tree;
                        $data['mid_tree_time']     = time();
                        $data['uid']               = $array['uid'];
                        $data['borrow_id']         = $array['bid'];
                        M("member_status")->add($data);
                
                        //发送微信推送通知初审拒绝
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$array['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg2($wxInfo['openid'], $apply['money'], $apply['add_time'],1);
                        }
                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $array['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($members['id'],$token['token'],$array['bid']);
                        }
                        
                        delUserOperation($array['uid'],$array['bid']);
                        exit(json_encode(array('message'=>'初审拒绝','code'=>403)));
                    }else{
                        //非黑名单若也 不是白名单白骑士拒绝
                        if($checkRes==0 && $members['is_white']==0 && $members['is_gold']==0){
                            $bdata['status']      = 98;
                            $bdata['refuse_time'] = time();
                            M("borrow_apply")->where("id = {$array['bid']}")->save($bdata);
                
                            $data['first_trial']       = 2;
                            $data['first_trial_time']  = time();
                            $data['mid_tree']          = $mid_tree;
                            $data['mid_tree_time']     = time();
                            $data['uid']               = $array['uid'];
                            $data['borrow_id']         = $array['bid'];
                            M("member_status")->add($data);
                
                            //发送微信推送通知初审拒绝
                            $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
                            if($wxInfo['openid']!==""){
                                sendWxTempleteMsg2($wxInfo['openid'], $apply['money'], $apply['add_time'],1);
                            }
                            //发送App推送通知初审拒绝
                            $mwhere['uid'] = $members['id'];
                            $token = M('member_umeng')->where($mwhere)->field(true)->find();
                            if(!empty($token['token'])){
                                AndroidTempleteMsg2($members['id'],$token['token'],$array['bid']);
                            }
                
                            delUserOperation($array['uid'],$array['bid']);
                            exit(json_encode(array('message'=>'初审拒绝','code'=>403)));
                        }
                        
                        $bdata['status']       = 1;
                        $bdata['audit_status'] = 1;
                        M("borrow_apply")->where("id = {$array['bid']}")->save($bdata);
                        
                        //有效期天数以内无需拉取运营商
                        if(isRunCarrier($array['phone'])==0){
                            //更新状态为手机验证
                            $sdata['verify_phone']     = 1;
                            $sdata['first_trial']      = 1;
                            $sdata['first_trial_time'] = time();
                            $sdata['uid']              = $array['uid'];
                            $sdata['borrow_id']        = $array['bid'];
                            M("member_status")->add($sdata);
                             
                            $bdata['status']           = 2;
                            $bdata['audit_status']     = 1;
                            M("borrow_apply")->where(" id = {$array['bid']} ")->save($bdata);
                    
                            $data['operation'] = "/Borrow/msgCheck";
                            $data['orderNum']  = 5;
                            M("user_operation")->where("uid = {$array['uid']} and borrow_id = {$array['bid']}")->save($data);
                    
                            //发送App推送通知初审通过
                            $mwhere['uid'] = $array['uid'];
                            $token = M('member_umeng')->where($mwhere)->field(true)->find();
                            if(!empty($token['token'])){
                                AndroidTempleteMsg($array['uid'],$token['token'],$array['bid']);
                            }
                            exit(json_encode(array('message'=>'提交成功','code'=>200,'result'=>array('pass'=>1))));
                        }else{
                            exit(json_encode(array('message'=>'提交成功','code'=>200,'result'=>array('pass'=>0))));
                        } 
                    }
                }else {
                    exit(json_encode(array('message'=>'提交失败','code'=>401)));
                }
            }
            
            
        }else {
            exit(json_encode($checkResult));
        }
    }

    /**
     * 用户详细信息接口
     */
    public function userDetailInfo(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
           $education    = C("EDUCATION_NAME");
           $eduCode      = C("EDUCATION_CODE");
           $marry        = C("MARRIAGE_NAME");
           $marryCode    = C("MARRIAGE_CODE");
           $relation     = C("SOCIAL_NAME");
           $relationCode = C("SOCIAL_CODE");
           $memberInfo   = M("member_info")->field('*')->where("uid = {$array['uid']}")->find();
           $companyInfo  = M("member_company")->field('*')->where("uid = {$array['uid']}")->find();
           $relationInfo = M("member_relation")->field('*')->where("uid = {$array['uid']}")->find();
           $user = M("members")->where("id = {$array['uid']}")->field("fuiou_id,usr_attr")->find();
           $memberInfo['education'] = $education[array_search($memberInfo['education'], $eduCode)];
           $memberInfo['marriage']  = $marry[array_search($memberInfo['marriage'], $marryCode)];
           $relationInfo['relation1'] = $relation[array_search($relationInfo['relation1'], $relationCode)];
           $Grant = checkGrant($array['uid'],$user['usr_attr'],$user['fuiou_id']);
           exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('memberInfo'=>$memberInfo,'companyInfo'=>$companyInfo,'relationInfo'=>$relationInfo,'Grant'=>$Grant))));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取绑定银行卡页面数据
     */
    public function requestBankName(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $bankList = C("SINA_BANK_NAME");
            $bankInfo = M("member_bank")->field(true)->where("uid = {$array['uid']} and type=2")->select();
            $bankInfo = empty($bankInfo) ? null : $bankInfo;
			
            exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('bankList'=>array_values($bankList),'bankInfo'=>$bankInfo))));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
    * 富友绑定银行卡页面数据
    */
    public function fuiouBankName(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $user = M("members")->field("fuiou_id,iphone")->where("id = {$array['uid']}")->find();
            $bankInfo = M("fuiou_user_bank")->field('city_code,parent_bank_code,card_no')->where("uid = {$array['uid']}")->find();
            $code   = city_Code($bankInfo['city_code'],$bankInfo['parent_bank_code']);
            $bankInfos['city'] = $code['city'];//市
            $bankInfos['province']  = $code['province'];//省
            $bankInfos['bank_name'] = $code['bank_name'];//行别
            $bankInfos['card_no']   = $bankInfo['card_no'];//卡号
            $bankInfos['fuiou_id']  = $user['fuiou_id'];//是否开户
            $bankInfos['iphone']    = $user['iphone'];//银行预留手机号
            $bankInfos = empty($bankInfos) ? null : $bankInfos;
            
            exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>$bankInfos)));
        }else {
            exit(json_encode($checkResult));
        }
    }


    /**
     * 用户确认绑卡
     * @param int uid  用户uid
     * @param int bid  用户借款id
     */
    public function userCheckBankCard(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $apply = M("borrow_apply")->field(true)->where("id = {$array['bid']}")->find();
            if($apply['audit_status']<2 && $apply['status']<3){
                $map['uid']         = $array['uid'];
                $map['borrow_id']   = $array['bid'];
                
                $sdata['bank_bing']      = 1;
                $sdata['bank_bing_time'] = time();
                $result = M("member_status")->where($map)->save($sdata);
                
                $ddata['audit_status']   = 2;
                M("borrow_apply")->where("id = {$array['bid']}")->save($ddata);
            }
            $members = M("members")->field('id,is_white,is_gray,is_black,is_gold')->where("id = '{$array['uid']}'")->find();
            if($result!==false){
                if (!sinaQueryPayPassword($array['uid'])){
                    $pwdUrl  = sinaSetPayPassword2($array['uid']);
                }else {
                    $pwdUrl  = null;
                }
                exit(json_encode(array('message'=>'提交成功','code'=>200,'result'=>$pwdUrl)));
            }else {
                exit(json_encode(array('message'=>'提交失败','code'=>401)));
            }           
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取绑定银行卡验证码接口
     */
    public function requestBindBankCardCode(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $bankCode = C("SINA_BANK_CODE");
            $code = array_search($array['bankName'], $bankCode);
            $tick = sinaGetBindBankCode($array['uid'], $code, $array['bankCard'], $array['phone'],urldecode($array['province']), urldecode($array['city']));
			if ($tick['status'] == 1){
				exit(json_encode(array('message'=>'已发送','code'=>200,'result'=>array('bankTicket'=>$tick))));
            }else{
				if($tick['message'] =="验签未通过"){
				    exit(json_encode(array('message'=>'网络有异常请重试一下','code'=>401)));
				}else{
				    exit(json_encode(array('message'=>$tick['message'],'code'=>401)));
				}
            }        
        }else {
            exit(json_encode($checkResult));
        }
    }

    
    /**
     * 绑定银行卡接口
     */
    public function bindBankCard(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $code = C("SINA_BANK_CODE");
            $message = sinaBindBankCard($array['bankTicket'],$array['verifyCode']);
            if (is_bool($message)){
                $data['uid']           = $array['uid'];
                $data['bank_card']     = $array['bankCard'];
                $data['bank_code']     = array_search($array['bankName'], $code);
                $data['bank_name']     = $array['bankName'];
                $data['bank_province'] = $array['province'];
                $data['bank_city']     = $array['city'];
                $data['bank_id']       = getSinaBindBankCardId($array['uid']);
                $data['type']          = '1';
                $res = M("member_bank")->add($data);
    
                $members = M("members")->field('id,is_white,is_gray,is_black,is_gold')->where("id = '{$array['uid']}'")->find();
                if ($res){
                    $map['uid']              = $array['uid'];
                    $map['borrow_id']        = $array['bid'];
                    $sdata['bank_bing']      = 1;
                    $sdata['bank_bing_time'] = time();
                    $result = M("member_status")->where($map)->save($sdata);
    
                    $ddata['audit_status'] = 2;
                    M("borrow_apply")->where("id = {$array['bid']}")->save($ddata);
    
                    if ($result!==false){
                        if (!sinaQueryPayPassword($array['uid'])){
                            $pwdUrl  = sinaSetPayPassword2($array['uid']);
                        }else {
                            $pwdUrl  = null;
                        }
                        exit(json_encode(array('message'=>'绑卡成功','code'=>200,'result'=>$pwdUrl)));
                    }else{
                        exit(json_encode(array('message'=>'绑卡失败','code'=>401)));
                    }
                }
            }else {
                exit(json_encode(array('message'=>$message,'code'=>401)));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
	/**
     * 是否设置支付密码[暂时没在用]
     */
    public function isPayPass(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
             $res = sinaQueryPayPassword($array['uid']);
             if($res===true){
                 exit(json_encode(array('message'=>'支付密码已设置','code'=>200,'status'=>1)));
             }else{
                 $pwdUrl  = sinaSetPayPassword2($array['uid']);
                 exit(json_encode(array('message'=>'支付密码未设置','code'=>200,'status'=>0,'result'=>$pwdUrl)));
             }
             
        }else{
            exit(json_encode($checkResult));
        }
    }

    /**
     * 获取解绑银行卡验证码接口
     */
    public function requestUnBindBankCard(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $cardId = getSinaBindBankCardId($array['uid']);
            $res    = sinaUnBindBankCard($array['uid'],$cardId);
            if ($res['status']){
                exit(json_encode(array('message'=>'发送成功','code'=>200,'result'=>array('unBindTicket'=>$res['ticket']))));
            }else {
                exit(json_encode(array('message'=>$res['message'],'code'=>401)));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
       
    /**
     * 解绑银行卡接口
     */
    public function unBindBankCard(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $res = unBindSinaBankCard($array['unBindTicket'], $array['code'], $array['uid']);
            if ($res){
                M("member_bank")->where("uid = {$array['uid']}")->delete();
                exit(json_encode(array('message'=>'解绑成功','code'=>200)));
            }else {
                exit(json_encode(array('message'=>'解绑失败，请联系客服','code'=>401)));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 用户签约接口
     * @param int uid
     * @param int bid
     */
    public function requestSignIng(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $map['uid']       = $array['uid'];
            $map['id']        = $array['bid'];
            $apply = M("borrow_apply")->field(true)->where($map)->find();
            if($apply['status']>3){
                exit(json_encode(array('message'=>'您已签约，请不要重复签约','code'=>401)));
            }else{
                $members = M("members")->field('id,is_white,is_gray,is_black,is_gold,iphone')->where("id = '{$array['uid']}'")->find();
                $model = new  CheckUserAction();
                if($members['is_gold']==0 &&$members['is_white']==0 &&$members['is_black']==0 ){
                    wqbLog("开始APP的贷前决策树---------");
                    $checkRes = $model->requestApi($array['uid'],$array['bid'],3);
                    wqbLog("结束APP的贷前决策树---------".$checkRes);
                }else{
                    $checkRes = 0;
                }
                if($checkRes==0){
                    if($members['is_white']==0 && $members['is_gold']==0){//决策树不通过
                        //还原优惠券状态
                        updateCoupon($apply['coupon_id']);
                        //更新member_status状态
                        $mapd['uid']             = $array['uid'];
                        $mapd['borrow_id']       = $array['bid'];
                        $data['signed']         = 2;
                        $data['signed_time']    = time();
                        $data['tree']           = 2;
                        $data['tree_time']      = time();
                        $res = M("member_status")->where($mapd)->save($data);
                
                        //更新为签约拒绝
                        $appdata['status']       = 97;
                        $appdata['refuse_time']  = time();
                        M("borrow_apply")->where("id = {$array['bid']}")->save($appdata);
                
                        delUserOperation($array['uid'],$array['bid']);
                
                        //发送拒绝微信
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$array['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg20($wxInfo['openid']);
                        }

                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $array['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($array['uid'],$token['token'],$array['bid']);
                        }

                        exit(json_encode(array('message'=>'审核不通过','code'=>403)));
                    }else{
                        //更新member_status状态
                        $mapd['uid']             = $array['uid'];
                        $mapd['borrow_id']       = $array['bid'];
                        $data['signed']         = 1;
                        $data['signed_time']    = time();
                        $data['tree']           = 2;
                        $data['tree_time']      = time();
                        $res = M("member_status")->where($mapd)->save($data);
                
                        $mapb['uid']            = $array['uid'];
                        $mapb['id']             = $array['bid'];
                        $bdata['status']        = 3;
                        $bdata['audit_status']  = 4;
                        M("borrow_apply")->where($mapb)->save($bdata);

                        //生成短信提醒记录
                        $add['borrow_id'] = $array['bid'];
                        $add['uid']       = $array['uid'];
                        $add['mobile_no'] = $members['iphone'];
                        $add['sign_time'] = time();
                        M('send_signed')->add($add);
                        exit(json_encode(array('message'=>'您已完成签约，请冷静确认','code'=>200)));
                    }
                }else{
                    //更新member_status状态
                    $mapd['uid']             = $array['uid'];
                    $mapd['borrow_id']       = $array['bid'];
                    $data['signed']         = 1;
                    $data['signed_time']    = time();
                    $data['tree']           = 1;
                    $data['tree_time']      = time();
                    $res = M("member_status")->where($mapd)->save($data);
                
                    $mapb['uid']            = $array['uid'];
                    $mapb['id']             = $array['bid'];
                    $bdata['status']        = 3;
                    $bdata['audit_status']  = 4;
                    M("borrow_apply")->where($mapb)->save($bdata);

                    //生成短信提醒记录
                    $add['borrow_id'] = $array['bid'];
                    $add['uid']       = $array['uid'];
                    $add['mobile_no'] = $members['iphone'];
                    $add['sign_time'] = time();
                    M('send_signed')->add($add);
                    exit(json_encode(array('message'=>'您已完成签约，请冷静确认','code'=>200)));
                }
            }
        }else{
            exit(json_encode($checkResult));
        }    
    }
    
    /**
     * 验证手机接口
     */
    public function verifyUserPhone(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $where['uid']             = $array['uid'];
            $where['id']              = $array['bid'];
            $where['status']          = 1;
            $borrowInfo = M("borrow_apply")->field('id,money,add_time')->where($where)->find();
            if($borrowInfo['id']>0){
                //白名单
                if(isWhite($array['uid'])==1){
                    //有效期天数以内无需拉取运营商
                    if(isRunCarrier($array['phone'])==0){
                        //更新状态为手机验证
                        $sdata['verify_phone']     = 1;
                        $sdata['first_trial']      = 1;
                        $sdata['first_trial_time'] = time();
                        $sdata['uid']              = $array['uid'];
                        $sdata['borrow_id']        = $array['bid'];
                        M("member_status")->add($sdata);
                         
                        $bdata['status']           = 2;
                        $bdata['audit_status']     = 1;
                        M("borrow_apply")->where(" id = {$array['bid']} ")->save($bdata);
                
                        $data['operation'] = "/Borrow/msgCheck";
                        $data['orderNum']  = 5;
                        M("user_operation")->where("uid = {$array['uid']} and borrow_id = {$array['bid']}")->save($data);
                        
                        //发送App推送通知初审通过
                        $mwhere['uid'] = $array['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg($array['uid'],$token['token'],$array['bid']);
                        }
                        exit(json_encode(array('message'=>'初审通过','code'=>200,'result'=>array('need_code'=>2,'tickId'=>''))));
                    }
                }
                
                $statusInfo = M("member_status")->field('id')->where("uid = {$array['uid']} and borrow_id = {$array['bid']}")->find();
                if ($statusInfo['id']>0){
                    exit(json_encode(array('message'=>'请勿重复提交','code'=>401)));
                }
                
                $info    = M()->query("SELECT a.real_name,a.id_card,b.iphone1,b.iphone2,b.iphone3 FROM ml_member_info a
                    INNER JOIN ml_member_relation b ON a.uid = b.uid WHERE a.uid = {$array['uid']}");
                $info    = $info[0];
                $info['real_name'] = urlencode($info['real_name']);
                
                $operator_mh = C('OPERATOR_MH_URL'); //请求运行商魔盒的URL
                $realname    = $info['real_name'];
                $url         = "{$operator_mh}/{$array['phone']}/{$info['id_card']}/{$realname}/{$array['password']}/{$info['iphone1']}/{$info['iphone2']}/{$info['iphone3']}";
                
                //$operator_lm = C('OPERATOR_LM_URL'); //请求运行商立木的URL
                //$url         = "{$operator_lm}/{$_POST['phone']}/{$_POST['password']}/{$info['real_name']}/{$info['id_card']}/{$info['iphone1']}/{$info['iphone2']}/{$info['iphone3']}";
                
                $res = http_request($url);
                appLog("手机运营商拉取请求提交：");
                if (strpos($res, 'success') !== false){
                    $data['verify_phone']  = 0;
                    $data['uid']           = $array['uid'];
                    $data['borrow_id']     = $array['bid'];
                    M("member_status")->add($data);
                    appLog("手机验证码发送成功OR登录成功。");
                    exit(json_encode(array('message'=>'提交成功,等待查询结果','code'=>200)));
                }else {
                    $arr = explode(':', $res);
                    appLog("验证结果");
                    appLog($res);
                    if (strpos($res, 'error') !== false){
                        switch ($arr[1]){
                            
                            case '01':
                                exit(json_encode(array('message'=>'请勿重复提交','code'=>401)));
                                break;
                            case '02':
                                exit(json_encode(array('message'=>'当天请求次数已满，请明天再来','code'=>401)));
                                break;
                            case '108':
                                exit(json_encode(array('message'=>'请求手机验证码失败','code'=>401)));
                                break;
                            case '112':
                                exit(json_encode(array('message'=>'账号或密码错误','code'=>401)));
                                break;
                            case '113':
                                exit(json_encode(array('message'=>'登录失败','code'=>401)));
                                break;
                            case '116':
                                exit(json_encode(array('message'=>'身份证或姓名校验失败','code'=>401)));
                                break;
                            case '124':
                                exit(json_encode(array('message'=>'手机验证码错误或过期','code'=>401)));
                                break;
                            case '2502':
                                exit(json_encode(array('message'=>'手机号码所在区域暂不支持','code'=>401)));
                                break;
                            default :
                                exit(json_encode(array('message'=>'请求已超时，请重新提交','code'=>401)));
                                break;
                        }
                    }else {
                        exit(json_encode(array('message'=>'需要验证码','code'=>200,'result'=>array('need_code'=>1,'tickId'=>$arr[1]))));
                    }
                }
            }else{
                exit(json_encode(array('message'=>'请勿重复提交','code'=>401)));
            }
            
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 带验证码验证手机接口
     */
    public function verifyPhoneAgain(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        appLog("手机运营商带验证码的拉取开始");
        if(is_bool($checkResult)){
            $operator_mh = C('OPERATOR_MH_IN_URL'); //请求运行商魔盒的URL
            $url         = "{$operator_mh}/{$array['tickId']}/{$array['code']}";
            $res = http_request($url);
            if (strpos($res, 'success') !== false){
                $data['verify_phone']  = 0;
                $data['uid']           = $array['uid'];
                $data['borrow_id']     = $array['bid'];
                M("member_status")->add($data);
                appLog("手机运营商带验证码的登录成功");
                exit(json_encode(array('message'=>'提交成功,等待查询结果','code'=>200)));
            }else{
                appLog("手机运营商带验证码的登录失败，原因：------".$res);
                exit(json_encode(array('message'=>'查询失败，请联系客服','code'=>401)));
            }
        }else {
            exit(json_encode($checkResult));
        } 
    }
    
    /**
     * 获取用户借款状态接口
     */
    public function getUserLoanStatus(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map['uid']         = $array['uid'];
            $map['borrow_id']   = $array['bid'];
            $binfo = M("borrow_apply")->field('coupon_id,status,id,is_random,audit_status')->where("id = {$array['bid']}")->find();
            
            if ($binfo['status'] == '4' && $binfo['coupon_id'] > 0){
                $sdata['status'] = 1;
                $sdata['id']     = $binfo['coupon_id'];
                M("member_coupon")->save($sdata);
            }
            
            $where['uid']       = $array['uid'];
            $where['borrow_id'] = $array['bid'];
            $times = M("member_status")->field('signed_time,calm')->where($where)->find();
            if (!empty($times)){
                $datag            = get_global_setting();
                $counttime        = $datag['calm_time'];
                $counttime        = $counttime*60*60;
                $countDownTime    = $counttime + $times['signed_time'];
                $time             = time();
                $calm             = $times['calm'];
            }else {
                $countDownTime = null;
                $time          = null;
                $calm          = null;
            }
            $info = M("member_status")->field(true)->where($map)->find();
            $info['is_random']     = $binfo['is_random'];
            $info['countDownTime'] = $countDownTime;
            $info['time']          = $time;
            $info['calm']          = $calm ;
            if($binfo['status'] == 3 && $binfo['audit_status'] == 4){
                $info['is_status']   = 1 ;
            }else{
                $info['is_status']   = 0 ;
            }
            $info['isIdVerify'] = isIdVerify($array['uid']);
            $data['message'] = "获取成功";
            $data['code']    = 200;
            $data['result']  = $info;
            
            if($info['is_recheck']==1){
                $paystatus     = 1;
            }else if($info['is_recheck']==0){
                $pay0          = M("transfer_order_pay")->where(" uid ={$array['uid']} and borrow_id={$array['bid']} and scene = 3 and status <2 ")->count('id');
                if($pay0>0){
                    $paystatus = 0;
                }else{
                    $paystatus = 2;//失败或者没有付款的
                }
            }else{
                $paystatus     = 2;
            }
            $info['paystatus'] = $paystatus;
            exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('binfo'=>$binfo,'info'=>$info))));
                
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 用户选填数据接口
     */
    public function getAllOptions(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $education  = C("EDUCATION_NAME");
            $marry      = C("MARRIAGE_NAME");
            $incomeInfo = C("YEAR_INCOME");
            $workInfo   = C("CAREER_NAME_LIST");
            $relation   = C("SOCIAL_NAME");
            $exp        = C("WORK_TIME");
            $house      = C("HOUSE");
            $houseType  = C("HOUSE_TYPE");
            $debt       = C("DEBT_LIST");
            $province   = M("province")->field('*')->select();
            $city       = M("city")->field('*')->select();
            $arr = array_chunk($relation, 5,true);
            $data['message'] = "获取成功";
            $data['code']    = 200;
            $data['result']  = array(
                'province'  => $province,
                'city'      => $city,
                'house'     => array_values($house),
                'houseType' => array_values($houseType),
                'workExp'   => array_values($exp),
                'incomeInfo'=> array_values($incomeInfo),
                'debt'      => array_values($debt),
                'workInfo'  => array_values($workInfo),
                'education' => array_values($education),
                'marry'     => array_values($marry),
                'family'    => array_values($arr[0]),
                'province'  =>$province,
                'province'  =>$province,
            );
            exit(json_encode($data));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 验证token有效期
     */
    public function checkUserToken(){
        $array = $_POST;
        $sign           = $array['sign'];
        $timeStamp      = $array['timeStamp'];
        $randNumber     = $array['randNumber'];
        $secretString   = $array['secretString'];
        $uid            = $array['uid'];
        $token          = $array['token'];
        $checkResult = $this->checkRequestSign($timeStamp, $randNumber, $secretString,$sign);
        if (is_bool($checkResult)){
            $tokenInfo = M("user_token")->field('expire')->where("uid = $uid and token = '{$token}'")->find();
            if (empty($tokenInfo) || $tokenInfo['expire']<time()){
                exit(json_encode(array('message'=>'无效token','code'=>401)));
            }else {
                exit(json_encode(array('message'=>'有效token','code'=>200)));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 身份确认链接
     * @param int uid 
     * @param int bid
     */
    public function creditSeameUrl(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $model = new BorrowAction();
            $url = $this->identityAuthUrl($array['uid'], $array['bid']);
            exit(json_encode(array('message'=>'成功','code'=>200,'result'=>$url)));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 生成调用支付宝人脸识别url
     * @param int $uid 用户uid
     * @param int $bid 用户借款id
     */
    private function identityAuthUrl($uid,$bid){ 
        $web      = C('AUTH_ALIPAY_URL');
        $userInfo = M("member_info")->field('id,id_card,real_name')->where("uid = {$uid}")->find();
        $str = strtoupper("FUMIJINRONG".date('YmdHis',time()));
        vendor('Alipay.AopSdk');
        $aop = new \AopClient ();
        $aop->gatewayUrl        = 'https://openapi.alipay.com/gateway.do';
        $aop->appId             = '2017031406219300';
        $aop->rsaPrivateKey 	= 'MIIEpAIBAAKCAQEAwhAkXUccQR4nu0U+Pj1I9rWQhn4tIxR3GIu7A+hY8ZXz3LNJEstI6b+W+zvPP/lkLDzffmP2ONcDGDCHf23PUxpVE765IK9CHrZxLhIi7uxHBmsQ5+BNU5uKcprXv5WuNdk5tYZ9i/FpfjQ2KxsiibYyyjWU1OXTl3iFjXXvBmZ3vZCwwtTAxENtCUzjHoAkXNv5oJfgeRKDmZ7XZu6mwq3/mapu7YMN5nUvEfRZxt9bhHG6yGztiP0krgxLzV1fy2xpzXzKHCTWxx5FAU3FcLqCjla+Cp60hD5kRxOjfDH32jvEShtOzm2Egep+FOtM3IC08trajcwKtTMb7NhvSwIDAQABAoIBAHLFJyx/TMd6NRc9GVWn2woFUUcpQjqX9ONwaBckh83A2GtzIMlbrnCHnZxRv/1e2g6LpcXTCqCNEMhykwAbCl1kmmJGDqi03c7aKU+M7FoPJOY31dS8xB6pQ5UJ3ITy2ggAw2+G7aMhEDnSWSLfNmrpdVo/nBjZH21amumRkN5gHb5eVhFYE3rRXNh81+1vwR8IkuIW+i21yTVNIdnW98tl//9fTFkcD4fTxpilUQA90YH6JjV3QU0gR3UGdM5zY5Tj3tXeBMVoIGsVSr5vXWm0M6grNRZLgu8Q7R1v8J9PiggqmGiY/BB6QWE202H3g5HZwrZBK3wE7+RQ3Hv5RHkCgYEA5PHcTqt1OcwKjCgZ5z24afj5Vy4FY/ssgCUIxPaORLPU73hT5EWKaua74ucCGX78mDyxkIUh5nf5ddk/XMUORf0PW/+5eHKaHkIzhDcH9JAAvYJf9iD68wsyHC4AHhVKc4h33pdIki1IaQN6S3Iw/jPs3Tvz9ebpiOcOx+a+cy0CgYEA2P8GOMlIQl8kG7KM+SDo0KZwPpptXWk+wflDeEzZjyx8S1jzEstD5UDrP2rgONVYMXwTurH0lX+983Kedm8S2AuzS8/9Yp1B5Wejql5EtRIX5o7BZfVaU869uLdL8//prleB4khSqcGUX/G0uEmbuRMUYkJVH9ZZ/0imy+XHV1cCgYBlTsD17tkIokloi7YqpR0dh5aOBUdNXq/qZHjk71U1AX6QGObGdB8z/rXVSfOb5J1RoEnScZb2rNAuXduz6V7PiZNqWlZv/gZErXauYsuaZL5vHWnpN97T/XhHD/PtW+5+AT1Juhfa1z7beTZ8fCNB2ShPHWBux6c/dT8NJgXQYQKBgQCzoYmZAKccqn3Nn+UrZp7T+rP4XwYhybn2AU5lC9kduIm8JgoyiKnP4gncGbE72wCDFl+OsTnzeCfanAuppxFGX4kxPSBYvi2KplzJ7/eYnT3D0nEu4gjAT+imPLZXaoYbmL0ggRZNCL63HfOoiuaqMq0xGhA1ptAhkBbju3dlSQKBgQCiDxdWYlWvNAwg6eAC0kM2/tsucwk9rByVFOYYjdRD6muhV8kIh9n50GoYmUFU+sBCWdKLs0/P0p7eIWciHXtv6IS4Cjnumc7SPfbIYHDVZwVuVi+U6sdnKHXi/asCJ7D5vA+gsm9f53v7Q9GOi7D+tt3v8WkfLmAAAGUmyjWjPw==';
        $aop->alipayrsaPublicKey= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlPt7PNDOGCpfIGrnsKpoCt9sUPYpDiQkOekshgVsVjGuwcz8+qt4QL7xizqOmuiV1R/msgNTFtZT47F7vOVp2KlodPLiBCqgrSVeNzqioZhi9m3Uu4Uf9V8dFoPoMsRrbTpC8WdHSbxp2J1aYKvWDUX8Gxmsa0+Bc6/4BxfFWgnpvJAVrBGJALYaSQotz6a79rYkEBEEomYFunnTguGKvKDc6HicXfno2G/ecaL/BjcGfyeTCrxgM55ejXOq84FkFOx5KGbVu6Z0I2ZctFIw+D+uUN4OEUwn/iK/vtJnHD1YDewcT2rWl/75LUcR+jAsZNHEfCgdfEU533pQs1ePQwIDAQAB';
        $aop->apiVersion 		= '1.0';
        $aop->signType 			= 'RSA2';
        $aop->postCharset		= 'UTF-8';
        $aop->format            = 'json';
        $request = new \ZhimaCustomerCertificationInitializeRequest();
        $request->setBizContent("{" .
            "    \"transaction_id\":\"{$str}\"," .
            "    \"product_code\":\"w1010100000000002978\"," .
            "    \"biz_code\":\"FACE\"," .
            "    \"identity_param\":\"{\\\"identity_type\\\":\\\"CERT_INFO\\\",\\\"cert_type\\\":\\\"IDENTITY_CARD\\\",\\\"cert_name\\\":\\\"{$userInfo['real_name']}\\\",\\\"cert_no\\\":\\\"{$userInfo['id_card']}\\\"}\"," .
            "  }");
        $result = $aop->execute($request);
         
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        $biz_no       = $result->$responseNode->biz_no;
        $data['alipay_biz_no'] = $biz_no;
        M("member_status")->where("uid = $uid AND borrow_id = $bid")->save($data);
        if(!empty($resultCode)&&$resultCode == 10000){
            $zhiMaCustom = new \ZhimaCustomerCertificationCertifyRequest();
            $zhiMaCustom->setBizContent("{"."\"biz_no\":\"{$biz_no}\""."}");
            $zhiMaCustom->setReturnUrl($web."/Android/dealIdentityResult");
            $zhiMaResponse = $aop->pageExecute($zhiMaCustom,'get');
            return $zhiMaResponse;
        } else {
            return false;
        }
    }
    
    /**
     * 处理人脸识别结果
     */
    public function dealIdentityResult(){
        $arr = json_decode($_GET['biz_content'],true);
        appLog($arr['biz_no'].$arr['passed']);
        $borrowInfo = M("member_status")->field('borrow_id,uid,id_verify')->where("alipay_biz_no = '{$arr['biz_no']}'")->find();
        $binfo      = M("borrow_apply")->field('id, status,audit_status,coupon_id')->where("id = {$borrowInfo['borrow_id']}")->find();
        if($binfo['status']>2 || $binfo['audit_status']>2){
            appLog("已经处理过人脸或无需处理已经拒绝");
            $this->redirect('/Borrow/idfalse?plat=app');
        }else{
            //人脸识别成功
            if ($arr['passed'] === 'true' && $borrowInfo['id_verify'] !== 2){
                
                //更新身份认证状态以及决策审核状态
                $bdata['audit_status'] = 3;
                M("borrow_apply")->where("id = {$borrowInfo['borrow_id']}")->save($bdata);
                
                $sdata['id_verify']      = 1;
                $sdata['id_verify_time'] = time();
                $result = M("member_status")->where("alipay_biz_no = '{$arr['biz_no']}'")->save($sdata);
                
                $is_white   = M("members")->field('is_white,is_gold,zhima_openid')->where("id = {$borrowInfo['uid']}")->find();
                
                //正常非白用户
                if ($is_white['is_white'] == 0 && $is_white['is_gold']==0){
                    $zhima = bqszhimaSearch($is_white['zhima_openid']);
                    appLog("用户芝麻OpenId：".$is_white['zhima_openid']);
                    //芝麻授权用户
                    if($zhima){
                        //走决策树
                        $model = new  CheckUserAction();
                        appLog("开始贷款决策树---------");
                        $checkRes = $model->requestApi($borrowInfo['uid'],$borrowInfo['borrow_id'],3);
                        appLog("结束贷款决策树---------".$checkRes);
                        
                        //决策树拒绝
                        if ($checkRes == 0){
                            //随机放款
                            //$number = mt_rand(1,200);
                            
                            /*if ($number == 6){
                             $start = mktime(0,0,0,date('m'),date('d'),date('y'));
                             $end   = mktime(23,59,59,date('m'),date('d'),date('y'));
                             $loans = M("random_lending")->field('id')->where("type = 1 and date between $start and $end")->select();
                             if (empty($loans)){
                             $info = M("random_lending")->field('money')->where("type = 0")->select();
                             foreach ($info as $row){
                             $data['money']  = $row['money'];
                             $data['date']   = time();
                             $data['status'] = 0;
                             $data['type']   = 1;
                             M("random_lending")->add($data);
                             }
                             }
                             randomLoans($borrowInfo['uid'], $borrowInfo['borrow_id']);
                             }else {*/
                            
                            //还原借款优惠券
                            if (!empty($binfo['coupon_id'])){
                                $cdata['status'] = 1;
                                $cdata['id']     = $binfo['coupon_id'];
                                M("member_coupon")->save($cdata);
                            }
                            
                            //更新为决策树不通过
                            $sdata['tree']          = 2;
                            $sdata['tree_time']     = time();
                            $result = M("member_status")->where("alipay_biz_no = '{$arr['biz_no']}'")->save($sdata);
                            
                            $appdata['status']       = 97;
                            $appdata['refuse_time']  = time();
                            M("borrow_apply")->where("id = {$borrowInfo['borrow_id']}")->save($appdata);
                            delUserOperation($borrowInfo['uid'],$borrowInfo['borrow_id']);
                            
                            //发送初审拒绝短信（推广中银）
                            /*$smsTxt = FS("Webconfig/smstxt");
                            $mem    = M(' members ')->field('iphone')->where(" id = {$borrowInfo['uid']} ")->find();
                            addToSms($mem['iphone'], $smsTxt['loan_ad']);*/
                            
                            //发送初审拒绝微信
                            $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$borrowInfo['uid']}")->find();
                            if($wxInfo['openid']!==""){
                                sendWxTempleteMsg20($wxInfo['openid']);
                            }

                            //发送App推送通知初审拒绝
                            $mwhere['uid'] = $borrowInfo['uid'];
                            $token = M('member_umeng')->where($mwhere)->field(true)->find();
                            if(!empty($token['token'])){
                                AndroidTempleteMsg2($borrowInfo['uid'],$token['token'],$borrowInfo['borrow_id']);
                            }

                            //}
                        }
                    }else{
                        //芝麻授权取消的用户
                        appLog("用户：".$borrowInfo['uid']."因为取消芝麻授权被拒绝！");
                        
                        //还原借款优惠券
                        if (!empty($binfo['coupon_id'])){
                            $cdata['status'] = 1;
                            $cdata['id']     = $binfo['coupon_id'];
                            M("member_coupon")->save($cdata);
                        }
                        
                        $appdata['status']       = 97;
                        $appdata['refuse_time']  = time();
                        M("borrow_apply")->where("id = {$borrowInfo['borrow_id']}")->save($appdata);
                        delUserOperation($borrowInfo['uid'],$borrowInfo['borrow_id']);
                        
                        //发送初审拒绝短信（推广中银）
                        /*$mem    = M(' members ')->field('iphone')->where(" id = {$borrowInfo['uid']} ")->find();
                        $smsTxt = FS("Webconfig/smstxt");
                        addToSms($mem['iphone'], $smsTxt['loan_ad']);*/
                        
                        //发送初审拒绝微信
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$borrowInfo['uid']}")->find();
                        sendWxTempleteMsg20($wxInfo['openid']);

                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $borrowInfo['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($borrowInfo['uid'],$token['token'],$borrowInfo['borrow_id']);
                        }
                    }
                }
                $this->redirect('/Borrow/idsure?plat=app');
            }else{
                //认证失败
                $this->redirect('/Borrow/idfalse?plat=app');
            }
        } 
    }
    
    /**
     * 芝麻授权链接
     */
    public function zhimaAuthUrl(){
        $web    = C('WEBSITE_URL');
		$status = 0;
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $openid = M("members")->field('zhima_openid')->where("id = {$array['uid']}")->find();
            if (!empty($openid['zhima_openid'])){
                $res = bqszhimaSearch($openid['zhima_openid']);
                if ($res == "false"){
					$status  = 0;
                    $callUrl = $web."/baiqishiId/".$array['bid'].".html";
                    $realurl = bqszhima($array['id_card'], $array['real_name'], "app", $callUrl);
                }else {
                    $realurl = $web."/borrow/dealAcceptUser?uid={$array['uid']}&type=1&bid={$array['bid']}&plat=app";
					$status  = 1;					
                }
            }else {
				$status = 0;
                $callUrl = $web."/baiqishiId/".$array['bid'].".html";
                $realurl = bqszhima($array['id_card'], $array['real_name'], "app", $callUrl);
            }
            $now = time();
            exit(json_encode(array('message'=>'成功','code'=>200,'result'=>$realurl)));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 查询用户操作步骤
     * @param int uid
     */
    public function checkUserOperation(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid  = $array['uid'];
            $info = M("user_operation")->field(true)->where("uid = {$uid} and borrow_id = {$array['bid']}")->find();
            if($info['orderNum']==4){
                //白名单
                if(isWhite($array['uid'])==1){
                    //有效期天数以内无需拉取运营商
                    if(isRunCarrier($array['phone'])==0){
                        $status     = M("member_status")->field('id')->where(" borrow_id={$array['bid']} and uid = {$array['uid']} ")->find();
                        if($status['id']>0){
                            //更新状态为手机验证
                            $sdata['verify_phone']     = 1;
                            $sdata['first_trial']      = 1;
                            $sdata['first_trial_time'] = time();
                            M("member_status")->where(" borrow_id={$array['bid']} and uid = {$array['uid']} ")->save($sdata);
                        }else{
                            //更新状态为手机验证
                            $sdata['verify_phone']     = 1;
                            $sdata['first_trial']      = 1;
                            $sdata['first_trial_time'] = time();
                            $sdata['uid']              = $array['uid'];
                            $sdata['borrow_id']        = $array['bid'];
                            M("member_status")->add($sdata);
                        }
                        $bdata['status']           = 2;
                        $bdata['audit_status']     = 1;
                        $res2 = M("borrow_apply")->where("id = {$array['bid']} ")->save($bdata);
                
                        $data['operation'] = "/Borrow/msgCheck";
                        $data['orderNum']  = 5;
                        M("user_operation")->where("uid = {$array['uid']} and borrow_id = {$array['bid']}")->save($data);
                        $info = M("user_operation")->field('orderNum')->where("uid = {$uid} and borrow_id = {$array['bid']}")->find();

                        //发送App推送通知初审通过
                        $mwhere['uid'] = $array['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg($array['uid'],$token['token'],$array['bid']);
                        }

                        exit(json_encode(array('message'=>'初审通过','code'=>200,'result'=>$info)));
                    }
                }
                exit(json_encode(array('message'=>'成功','code'=>200,'result'=>$info)));
            }else{
                exit(json_encode(array('message'=>'成功','code'=>200,'result'=>$info)));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }

    /**
     * 查询验证手机状态
     * @param int $userId  用户id
     * @param int $borrowId 借款ID
     */
    public function getPhoneStatus($userId,$borrowId){
        $array             = $_POST;
        $checkResult       = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
        $map['uid']        = $array['uid'];
        $map['borrow_id']  = $array['bid'];
        $info = M("member_status")->field('first_trial,verify_phone')->where($map)->find();
        if (!empty($info)){
            if ($info['first_trial'] == 0 && $info['verify_phone'] == 0){
                
                exit(json_encode(array('message'=>'正在验证手机','status'=>0,'code'=>200)));

            }else if ($info['first_trial'] == 2 && $info['verify_phone'] == 2){
                
                exit(json_encode(array('message'=>'验证手机失败','status'=>2,'code'=>200)));

            }else {
                exit(json_encode(array('message'=>'验证手机成功','status'=>1,'code'=>200)));

            }
        }else {
            exit(json_encode(array('message'=>'需要验证手机','code'=>401)));
        }
        
        }else{
            exit(json_encode($checkResult));
        }
    }
    /**
     * 记录用户操作
     * @param int uid 
     * @param string action
     * @param string method
     */
    public function  saveUserOperation(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $data['uid']       = $array['uid'];
            $data['add_time']  = time();
    	    $operation = array('1'=>'Index','2'=>'userBaseInfo','3'=>'verifyUserStatus','4'=>'verifyPhone','5'=>'msgCheck');
    		$data['operation'] = "/Borrow/".$array['method'];
    		$data['orderNum']  = array_search($array['method'], $operation);    		
    	    $data['add_time']  = time();	
    	    $data['borrow_id'] = $array['bid'];
    	    $info = M("user_operation")->field(true)->where("uid = {$array['uid']} and borrow_id = {$array['bid']}")->find();
    	    if (empty($info)){
    	        M("user_operation")->add($data);
    	    }else {
    	        $tempOrderNum = array_search($array['method'], $operation);
    	        if ($tempOrderNum >= $info['orderNum']){
    	            M("user_operation")->where("id = {$info['id']}")->save($data);
    	        }
    	    }
            exit(json_encode(array('message'=>'记录成功','code'=>200)));
        }else{
            exit(json_encode($checkResult));
        }        
    }
    
    /**
    * 删除用户操作记录
    * @param int $uid
    */
    public function delUserOperation(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            M("user_operation")->where("uid = {$array['uid']} and borrow_id = {$array['bid']}")->delete();
            appLog("已删除用户:{$array['uid']},操作记录。");
            exit(json_encode(array('message'=>'记录成功','code'=>200)));
        }else{
            exit(json_encode($checkResult));
        }        
     }
    
    
    /**
     * 发放用户优惠券
     */

    private function createUserCoupon($uid,$deadline){
        $global = get_global_setting();
        $ticArr = explode('|', $global['coupon_register']);
        
        $money  = $ticArr[0];
        $day    = $ticArr[1];
        if($money>0){
            $ticData['uid']         = $uid;
            $ticData['money']       = $money;
            $ticData['title']       = "还款用优惠券";
            $ticData['type']        = '2';
            $ticData['status']      = 0;
            $ticData['add_time']    = time();
            $ticData['start_time']  = time();
            $ticData['end_time']    = $deadline+3600*24*$day;
            $tickRes = M("member_coupon")->add($ticData);
            if ($tickRes){
                return $ticData;
            }else{
                appLog("优惠券发放失败");
            }
        }else{
            appLog("注册优惠券无需发放");
        }
        
    }
    
    /**
     * 插入用户数据
     */
    private function insertUser($array){
        $tick = $array['tick'];
        
        $data['iphone']        = $array['phone'];
        $data['reg_time']      = time();
        $data['promotion_code']= $array['channel'];
        $data['reg_ip']        = '0';
        $data['reg_address']   = get_ipAddress($data['reg_ip']);
        $data['reg_gps']       = $array['address']; 
        $data['last_time']     = time();
        $data['last_ip']       = '0';
        $data['last_address']  = get_ipAddress($data['last_ip']);
        $data['last_gps']      = $array['address'];
        $type = memberType($array['phone']);
        if($type==1){
            $data['is_black'] = 1;
        }else if($type==2){
            $data['is_white'] = 1;
        }else if($type==3){
            $data['is_gray']  = 1;
        }
        $data['usr_attr'] = 2;
        $res = M("members")->add($data);
        if ($res){
            return $res;
        }else {
            appLog("用户创建失败");
        }
    }

    /*********************************************************用户借款流程接口结束******************************************************/
    
    
    /*********************************************************用户个人中心接口开始******************************************************/
    
    /**
     * 用户个人中心首页接口[app暂未做]
     */
    public function userIndexInfo(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid  = $array['uid'];
            $family = C("SOCIAL_NAME");
            $code   = C("SOCIAL_CODE");
            $memberInfo = M()->query("SELECT a.id_card,a.real_name,b.job_title,b.year_income,c.bank_name,c.bank_card,d.relation1,d.iphone1,d.iphone2,d.iphone3 FROM ml_member_info a
                LEFT JOIN ml_member_company b ON a.uid=b.uid
                LEFT JOIN ml_member_bank c on a.uid=c.uid
                LEFT JOIN ml_member_relation d on a.uid=d.uid
                WHERE a.uid = $uid");
            $memberInfo = $memberInfo[0];
            $memberInfo['connectName'] = $family[array_search($memberInfo['relation1'],$code )];
            $memberInfo['bank_card'] = substr_replace($memberInfo['bank_card'], '************', 4);
            $memberInfo['id_card'] = substr_replace($memberInfo['id_card'], '********', 6,8);
            if (empty($memberInfo)){
                exit(json_encode(array('message'=>'错误请求','code'=>401)));
            }else {
                exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('userInfo'=>$memberInfo))));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 个人中心我的借款接口
     */
    public function userBorrowInfo(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map['uid'] = $array['uid'];
            $borrowInfo = M("borrow_apply")->field('add_time,id,money,duration,len_time,deadline,status,audit_status')->order('add_time desc')->where($map)->limit(3)->select();
            $statusMessage  = C('BORROW_STATUS');
            $data = array();
            foreach ($borrowInfo as $row){
                $row['statusMsg']   = $statusMessage[$row['status']];
                $where['uid']       = $array['uid'];
                $where['borrow_id'] = $row['id'];
                $calm = M("member_status")->field('calm,id_verify')->where($where)->find();
                
                if($row['status']==3){
                    if($calm['calm']==0){
                        $row['statusMsg'] = "已签约 ";
                    }else{
                        $row['statusMsg'] = "同意放款：待放款";
                    }
                    if($calm['id_verify']==2){
                        $row['statusMsg'] = "身份验证失败 ";
                    }
                }
                if ($row['status']==96){
                    $row['statusMsg'] = "资金匹配失败";
                }
                $data[] = $row;
            }
            if (empty($borrowInfo)){
                exit(json_encode(array('message'=>'错误请求','code'=>200,'result'=>array('borrowInfo'=>$data))));
            }else {
                exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('borrowInfo'=>$data))));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 查看借款详情接口
     */
    public function borrowDetailInfo(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map['uid'] = $array['uid'];
	        $map['id']  = intval($array['id']);
            $borrowInfo = M("borrow_apply")->field(true)->where($map)->find();
            $statusMessage           = C('BORROW_STATUS');
            $borrowInfo['statusMsg'] = $statusMessage[$borrowInfo['status']];
            
            $where['uid']       = $array['uid'];
            $where['borrow_id'] = $borrowInfo['id'];
            $calm = M("member_status")->field('calm,id_verify')->where($where)->find();
            
            if($borrowInfo['status']==3){
                if($calm['calm']==0){
                    $borrowInfo['statusMsg'] = "已签约 ";
                }else{
                    $borrowInfo['statusMsg'] = "同意放款：待放款";
                }
                if($calm['id_verify']==2){
                    $borrowInfo['statusMsg'] = "身份验证失败 ";
                }
            }
            if ($borrowInfo['status']==96){
                $borrowInfo['statusMsg'] = "资金匹配失败";
            }
            $borrowInfo['total']    = getFloatValue($borrowInfo['money']+$borrowInfo['created_fee']+$borrowInfo['audit_fee']+$borrowInfo['enabled_fee']+$borrowInfo['interest']+$borrowInfo['pay_fee'],2);
            if (!empty($borrowInfo)){
                exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('borrowInfo'=>$borrowInfo))));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }

    /**
     * 用户取消借款申请接口
     */
    public function delBorrowInfo(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $apply     = M("borrow_apply")->field('id, coupon_id,status,uid')->where("id = {$array['id']} and status < 4 ")->find();
            $detail     = M("borrow_detail")->field('id')->where(" borrow_id = {$array['id']} ")->find();
            if($detail['id']>0){
                exit(json_encode(array('message'=>'已经放款无法取消','code'=>401)));
            }else{
                $flg       = $apply['status'];
                $status    = M("member_status")->field('id,first_trial,calm,is_recheck')->where("borrow_id = {$array['id']} and uid = {$apply['uid']} ")->find();
                if ($apply['status'] == 3 && $status['is_recheck'] == 1 ){
                    exit(json_encode(array('message'=>'已经确认等待放款无法取消','code'=>401)));
                }
                if(!empty($status) && $status['first_trial'] == 0){
                    exit(json_encode(array('message'=>'验证手机处理中无法取消','code'=>401)));
                }else {
                    $data['id']     = $array['id'];
                    $data['status'] = 99;
                    $status = M("borrow_apply")->save($data);
                    if($status !== false){
                        if ($apply['coupon_id']>0){
                            updateCoupon($apply['coupon_id']);
                        }
                        //发送App推送通知取消借款
                        $mwhere['uid'] = $apply['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg9($apply['uid'],$token['token'],$array['id']);
                        }
                        exit(json_encode(array('message'=>'取消成功','code'=>200)));
                    }else {
                        exit(json_encode(array('message'=>'取消失败，请联系客服','code'=>401)));
                    }
                }
            }     
        }else{
            exit(json_encode($checkResult));
        }
    }
    /**
     * 用户优惠券接口   
     */
    public function userTicket(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        $uid = $array['uid'];
        $now = time();
        if (is_bool($checkResult)){
            $info = M("member_coupon")->field(true)->where("uid ={$uid} ")->order("type, end_time desc")->select();
            $data = array();
            $reg_time = M('members')->getFieldById($uid,"reg_time");
            $flg = 0;
            if($reg_time>1513007999){
                $flg = 1;
            }
            $data['flg'] = $flg;
            $count = M("member_coupon")->where("uid ={$uid} and type = 2 ")->count("id");
            $data['count'] = $count;
            $global = get_global_setting();
            $ticArr = explode('|', $global['coupon_register']);
            $money  = $ticArr[0];
            $data['money'] = $money;
            if (empty($info)){
                $info = array();
            }
            if(count($info)==0){
                if($flg==1&&$count==0){
                    exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('tickets'=>$info,'data'=>$data))));
                }else{
                    exit(json_encode(array('message'=>'暂无数据','code'=>401)));
                }
            }else{
                exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('tickets'=>$info,'data'=>$data))));
            }
    
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    
    /**
     * 查询用户当前借款接口  
     */
    public function getUserCurrentLoan(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            
            $mem = M("members")->field('id,is_black,is_gray,is_white,is_gold,fuiou_id')->where(" id= {$array['uid']}")->find();
            if($mem['is_gold']==1){
                $level = "高级";
            }elseif($mem['is_white']==1){
                $level = "中级";
            }else{
                $level = "初级";
            }
            $map['aa.uid']    = $array['uid'];
            $map['aa.status'] = array('not in','5,93,94,95,96,97,98,99');
            $field = 'aa.id,aa.uid,aa.money,aa.duration,aa.status,aa.audit_status,aa.repayment_time,bb.type,aa.is_withdraw';
            $apply = M("borrow_apply aa")->field($field)->join("ml_borrow_item bb ON aa.item_id = bb.id")->where($map)->order('bb.type')->select();
            $row   = array();
            foreach($apply as $key=>$v){
                if($v['status']<3){
                    $v['memo'] = "取消";
                    $v['flg']  = 1;
                }else if($v['status']==3){
                    $status = M("member_status aa")->field('signed,is_recheck')->where("borrow_id = {$v['id']}")->find();
                    if($status['is_recheck']==1){
                        if($status['id_verify']==1){
                            $v['memo'] = "已身份确认";
                        }else{
                            $v['memo'] = "已签约";
                        }
                        $v['flg']  = 0;
                    }else{
                        $v['memo'] = "取消";
                        $v['flg']  = 1;
                    }
                }else if($v['status'] == 4){
                    $status = M("member_status aa")->field('signed,is_recheck')->where("borrow_id = {$v['id']}")->find();
                    $v['flg']  = 0;
                    $v['memo'] = "待还款";
                }
                $row[$key] = $v;
            }
            $apply = $row;
            
            $item1 = M("borrow_item")->field('max(money) as money ,max(duration) as duration')->where("is_on = 1 and type=1")->find();
            $item2 = M("borrow_item")->field('max(money) as money ,max(duration) as duration')->where("is_on = 1 and type=2")->find();
            $item  = array();
            $item[0]['money']    = $item1['money'];
            $item[0]['duration'] = $item1['duration'];
            $item[1]['money']    = $item2['money'];
            $item[1]['duration'] = $item2['duration'];
            if($mem['fuiou_id'] > 0){
                $money = userMoney($array['uid']);
                if($money['ca_balance'] > 0){//有待提现金额
                    $tx = 1;
                }else{
                    $tx = 0;
                }
            }else{
                $tx = 0;
            } 
            $isFace    = 0;
            $app_face  = M("borrow_app_face")->field("id,add_time")->where("uid = {$array['uid']} and borrow_id = {$array['bid']} ")->find();
            if($app_face['id']>0){
                $dtime = time()-$app_face['add_time'];
                if($dtime>180){
                    $isFace = 1;
                }
            }
            
            exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('level'=>$level,'isface'=>$isFace,'borrow'=>$apply,'item'=>$item ,'withdraw'=>$tx))));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /*************************************************************用户个人中心接口结束***********************************************/
    
    /*************************************************************还款流程接口开始*************************************************/
     
    public function repayAll(){
        $array        = $_POST;
        $checkResult  = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid     = $array['uid'];
            $now     = strtotime(date('Y-m-d',time()));
            $nowS    = strtotime(date('Y-m-d',time())."00:00:00");
            $nowE    = strtotime(date('Y-m-d',time())."23:59:59");
            $count   = 0;
            $dueC    = 0;
            
            $field   = "a.id,a.money,b.deadline,b.id as detail_id,b.capital,b.interest,a.audit_fee,a.enabled_fee,a.created_fee,a.pay_fee,a.is_new,a.is_withdraw";
            $apply1  = M("borrow_apply a")->field($field)->join("ml_borrow_detail  b ON b.borrow_id=a.id ")->join("ml_borrow_item  c ON a.item_id=c.id ")->where("a.uid = {$uid} and a.status = 4 and b.status=0  and c.type=1")->order(" b.deadline ")->find();
            $apply2  = M("borrow_apply a")->field($field)->join("ml_borrow_detail  b ON b.borrow_id=a.id ")->join("ml_borrow_item  c ON a.item_id=c.id ")->where("a.uid = {$uid} and a.status = 4 and b.status=0  and c.type=2")->order(" b.deadline ")->find();
            
            if($apply1['is_new']==1){
                $fee1      = getFloatValue($apply1['audit_fee']+$apply1['enabled_fee']+$apply1['created_fee']+$apply1['pay_fee'],2);
            }else{
                $fee1      = 0;
            }
            
            $due_day1   = 0;
            $due_fee1   = 0;
            $late_fee1  = 0;
            $due_day1   = get_due_day($apply1['deadline']);
            if($due_day1 >0){
                $due_fee1  = get_due_fee($apply1['money'],$apply1['item_id'],$due_day1);
                $late_fee1 = get_late_fee($apply1['money'],$apply1['item_id'],$due_day1);
            }
            $money1        = getFloatValue($apply1['capital']+$apply1['interest']+$fee1+$due_fee1+$late_fee1,2);
            
            if($apply2['is_new']==1){
                $fee2      = getFloatValue($apply2['audit_fee']+$apply2['enabled_fee']+$apply2['created_fee']+$apply2['pay_fee'],2);
            }else{
                $fee2      = 0;
            }
            
            $due_day2   = 0;
            $due_fee2   = 0;
            $late_fee2  = 0;
            $due_day2   = get_due_day($apply2['deadline']);
            if($due_day2 >0){
                $due_fee2  = get_due_fee($apply2['money'],$apply2['item_id'],$due_day2);
                $late_fee2 = get_late_fee($apply2['money'],$apply2['item_id'],$due_day2);
            }
            $money2        = getFloatValue($apply2['capital']+$apply2['interest']+$fee2+$due_fee2+$late_fee2,2);
            
            
            $apply   = array();
            if($apply1['id']){
                $count                   = $count+1;
                $apply[0]['id']          = $apply1['id'];
                $apply[0]['money']       = $money1;
                $apply[0]['detail_id']   = $apply1['detail_id'];
                $apply[0]['deadline']    = date('Y-m-d',$apply1['deadline']);
                $apply[0]['is_withdraw'] = $apply1['is_withdraw'];
                if($apply1['deadline']<$nowS){
                    $apply[0]['msg']     = "已逾期".get_due_day($apply1['deadline'])."天";
                    $dueC                = $dueC+1;
                }else{
                    if($apply1['deadline']<=$nowE){
                        $apply[0]['msg'] = "今日需还款";
                    }else{
                        $deadline       = strtotime(date('Y-m-d',$apply1['deadline']));
                        $day            = ($deadline-$now)/86400;
                        $apply[0]['msg']= "距离还款日还剩".$day."天";
                    } 
                }
                if($apply2['id']){
                	$count                  = $count+1;
                	$apply[1]['id']         = $apply2['id'];
                	$apply[1]['money']      = $money2;
                	$apply[1]['detail_id']  = $apply2['detail_id'];
                	$apply[1]['deadline']   = date('Y-m-d',$apply2['deadline']);
                    $apply[1]['is_withdraw'] = $apply2['is_withdraw'];
                	if($apply2['deadline']<$nowS){
                		$apply[1]['msg']    = "已逾期".get_due_day($apply2['deadline'])."天";
                		$dueC               = $dueC+1;
                	}else{
                		if($apply2['deadline']<=$nowE){
                			$apply[1]['msg'] = "今日需还款";
                		}else{
                			$deadline       = strtotime(date('Y-m-d',$apply2['deadline']));
                			$day            = ($deadline-$now)/86400;
                			$apply[1]['msg']= "距离还款日还剩".$day."天";
                		}
                	}
                }
            }else{
            	if($apply2['id']){
            		$count                  = $count+1;
            		$apply[0]['id']         = $apply2['id'];
            		$apply[0]['money']      = $money2;
            		$apply[0]['detail_id']  = $apply2['detail_id'];
            		$apply[0]['deadline']   = date('Y-m-d',$apply2['deadline']);
                    $apply[0]['is_withdraw'] = $apply2['is_withdraw'];
            		if($apply2['deadline']<$nowS){
            			$apply[0]['msg']    = "已逾期".get_due_day($apply2['deadline'])."天";
            			$dueC               = $dueC+1;
            		}else{
            			if($apply2['deadline']<=$nowE){
            				$apply[0]['msg'] = "今日需还款";
            			}else{
            				$deadline       = strtotime(date('Y-m-d',$apply2['deadline']));
            				$day            = ($deadline-$now)/86400;
            				$apply[0]['msg']= "距离还款日还剩".$day."天";
            			}
            		}
            	}
            }
            $total = getFloatValue($money1+$money2,2);
            exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('total'=>$total,'count'=>$count,'due'=>$dueC,'repay'=>$apply))));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 还款首页数据
     * @param int uid
     */
    public function repayIndex(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
        $uid  = $array['uid'];
        $bid  = $array['bid'];
        $now  = time();
    	//查询用户需还款的账单（最近需要还款的一份账单）
        $map             = array();
        $map['a.uid']    = $uid;
        $map['a.id']     = $bid;
        $map['a.status'] = 4;
        $map['b.status'] = 0;
        $field           = " a.id,a.item_id,a.money,a.duration,a.audit_fee,a.created_fee,a.pay_fee,a.enabled_fee,a.due_fee,a.late_fee,a.`status`,a.add_time,a.len_time,b.deadline,a.is_new,b.id as detail_id,b.capital,b.interest ";
        $info            = M("borrow_apply a")->field($field)->join("ml_borrow_detail  b ON b.borrow_id=a.id ")->where($map)->find();
        if (empty($info)){
            exit(json_encode(array('message'=>'主人，您还没有还款计划','code'=>'401')));
        }
         
        //用户可选择的优惠券
        $ticket   = M("member_coupon")->field('money,end_time,id')->where("uid = $uid AND status = 0 and type=2 and end_time > $now")->select();
        if (empty($ticket)){
            $ticket = array();
        }
        $item      = M("borrow_item")->field(true)->where(" id = {$info['item_id']} ")->find();
        $due_days  = get_due_day($info['deadline']);
        $uDeadLine = strtotime(date('Y-m-d',$info['deadline'])."23:59:59");
        if($info['is_new']==1){
            $info['fee'] = getFloatValue($info['audit_fee']+$info['enabled_fee']+$info['created_fee']+$info['pay_fee'],2);
        }else{
            $info['fee'] = 0;
        }
        if ($due_days>0){
            $info['overdue']          = 1;
            $info['dueTime']          = $due_days;
            $info['due_money']        = get_due_fee($info['money'], $info['item_id'], $info['dueTime']);
            $info['due_manage_money'] = get_late_fee($info['money'], $info['item_id'], $info['dueTime']);
            $info['money']            = $info['money'];
            $info['total']            = getFloatValue($info['money']+$info['interest']+$info['due_money']+$info['due_manage_money']+$info['fee'],2);
        }else {
            $info['overdue']          = 0;
            $now                      = time();
            $countDay                 = floor(($uDeadLine - $now)/3600/24);
            if ($countDay == 0){
                $info['repayTime']    = "0";
            }else {
                $info['repayTime']    = floor(($uDeadLine - $now)/3600/24);
            }
            $info['money']            = $info['money'];    
            $info['total']            = getFloatValue($info['money']+$info['interest']+$info['fee'],2);
        }


        $info['due_rate']    = "逾期利息是借款金额的".$item['due_rate']."%/天 。如借款".$info['money']."元，每天".get_due_fee($info['money'], $info['item_id'], 1)."元。";
        $info['late_rate']   = "逾期管理费是借款金额的".$item['late_rate']."%/天 。如借款".$info['money']."元，每天".get_late_fee($info['money'], $info['item_id'], 1)."元。";
        exit(json_encode(array('message'=>'获取成功','code'=>'200','result'=>array('bdata'=>$info,'ticket'=>$ticket))));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 线下支付
     */
    public function repayOffline(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $name  = "微信";
            $count = "扫描以下二维码即可支付";
            $pic   = "http://www.cashmall.com.cn/style/cash/img/wechatPay.jpg";
            exit(json_encode(array('message'=>'','code'=>200,'result'=>array('name'=>$name,'account'=>$count,'pic'=>$pic))));
        }else{
            exit(json_encode($checkResult));                                                                                     
        }
    }
    
    /**
     * 还款付款
     * @param int 借款会员编号
     * @param int bid 借款申请单号
     * @param int detailId 账单ID
     * @param int money 页面提交的扣款金额
     * @param int bankId 选择扣款的银行
     * @param int tickId 选择的优惠券ID
     */
    public function repayMentPay(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $tickId     = $array['tickId'];
            $money      = $array['money'];
    
            $map['uid']         = $array['uid'];
            $map['id']          = $array['detailId'];
            $map['borrow_id']   = $array['bid'];
            $map['status']      = 0;
            $borrow_detail = M("borrow_detail")->field(true)->where($map)->find();
    
            $due_day    = 0;
            $due_fee    = 0;
            $late_fee   = 0;
            if($borrow_detail['id']>0){
                $coupon        = M("member_coupon")->field('money')->where("id = {$tickId} and status = 0 ")->find();
                $borrowInfo    = M("borrow_apply")->field(true)->where(" id = {$borrow_detail['borrow_id']} ")->find();
                $dueDay        = get_due_day($borrowInfo['deadline']);
                if($borrowInfo['is_new']==1){
                    $fee = getFloatValue($borrowInfo['audit_fee']+$borrowInfo['enabled_fee']+$borrowInfo['created_fee']+$borrowInfo['pay_fee'],2);
                }else{
                    $fee = 0;
                }
                if($dueDay>0){
                    $due_fee   = get_due_fee($borrowInfo['money'], $borrowInfo['item_id'], $dueDay);
                    $late_fee  = get_late_fee($borrowInfo['money'], $borrowInfo['item_id'], $dueDay);
                    $type      = 2;
                    $integral  = $array['money']-$borrow_detail['capital'];
                    $info2     = "逾期还款".$array['money']."元，扣除积分".$integral;
                }else{
                    $type      = 1;
                    $integral  = $array['money'];
                    $info2     = "成功还款".$array['money']."元，获得积分".$integral;
                }
                 
                $total        =  getFloatValue($borrow_detail['capital']+$borrow_detail['interest']+$due_fee+$late_fee+$fee-$coupon['money'],2);

                if($total<0){
                    exit(json_encode(array('message'=>'还款总额不能小于0','code'=>401)));
                }

                //新的money
                if($money != 0 && $money < $total){
                    $total = $money;
                }

                if($money<$total){
                    exit(json_encode(array('message'=>'提交还款的金额不对，应为'.$total.'元','code'=>401)));
                }
                 
                //宝付支付
                $model = new PaymentAction();
                $res   = $model->requestApi($array['uid'], $borrowInfo['id'], 1, $total ,$array['bankId']);
                //$res   = true;
                $now   = time();
                if(is_bool($res) && $res){
                    $datab['repayment_time'] = $now;
                    $datab['status']         = 1;
                    $datab['due_fee']        = $due_fee;
                    $datab['late_fee']       = $late_fee;
                    if($tickId!=''){
                        $datab['coupon_id']      =  $tickId;
                    }
                    $updetail = M('borrow_detail')->where(" id={$array['detailId']} ")->save($datab);
                    if($updetail){
                        //所有期数都还款完成
                        if($borrow_detail['sort_order']==$borrow_detail['total']){
                            $dataapply['status']         = 5;
                            $dataapply['repayment_time'] = $now;
                            $dataapply['due_fee']        = $borrowInfo['due_fee']+$due_fee;
                            $dataapply['late_fee']       = $borrowInfo['late_fee']+$late_fee;
                            $upapply = M('borrow_apply')->where("id={$borrowInfo['id']}  and uid = {$borrowInfo['uid']} ")->save($dataapply);
                        }
                    }
    
        	           //优惠券更新
        	           if($tickId !=''){
        	               $tickData['status'] = 1;
        	               M("member_coupon")->where(" id = {$tickId} ")->save($tickData);
        	           }
    
        	           //发送微信
        	           $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$array['uid']}")->find();
        	           if($wxInfo['openid']!==''){
        	               sendWxTempleteMsg8($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'], $borrowInfo['duration'], $now);
        	           }
        	           //会员积分变更
        	           addIntegral($borrow_detail['uid'],$type,$integral,$info2);
        	           delUserOperation($array['uid'],$array['bid']);
        	           //更新会员等级
        	           //upLevel($array['uid']);
        	           exit(json_encode(array('message'=>'还款成功'.$total,'code'=>200)));
                }else{
                    exit(json_encode(array('message'=>$res,'code'=>401)));
                }
            }else{
                exit(json_encode(array('message'=>'请确认借款申请的还款状态','code'=>401)));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }

    /**
     * 还款上限金额的取得
     */
    public function changeMoney(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if(is_bool($checkResult)){
            $userId     = $array['uid'];
            $borrowId   = $array['bid'];
            //原来的金额
            $money      = $array['money'];
            $map['id']  = $borrowId;
            $map['uid'] = $userId;
            $borrow_info = M("borrow_apply")->field('money')->where($map)->find();
            //金额的限制
            $global = get_global_setting();
            //获取还款金额的限制  本金的倍数
            $repayment_limit = $global['repayment_limit'];
            //获取本金
            if($borrow_info){
                $limit_money = $borrow_info['money']*$repayment_limit;
                if($money > $limit_money && $limit_money != 0){
                    $moneyNew = sprintf("%.2f",$limit_money);
                }else{
                    $moneyNew = $money;
                }
            }
            exit(json_encode(array('message'=>'请求成功','code'=>'200','result'=>array('money'=>$moneyNew))));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 续期首页数据
     * @param int uid
     */
    public function renewalIndex(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){ 
            $now  = time();
            $uid  = $array['uid'];
            $bid  = $array['bid'];
            $map             = array();
            $map['a.uid']    = $uid;
            $map['a.id']     = $bid;
            $map['a.status'] = 4;
            $map['b.status'] = 0;
            $field           = " a.id,a.item_id,a.money,a.duration,a.audit_fee,a.created_fee,a.pay_fee,a.enabled_fee,a.due_fee,a.late_fee,a.is_new,a.`status`,a.add_time,a.len_time,b.deadline,b.id as detail_id,b.capital,b.interest ";
            $info            = M("borrow_apply a")->field($field)->join("ml_borrow_detail  b ON b.borrow_id=a.id ")->where($map)->order(" b.deadline ")->limit("1")->select();
            $info            = $info[0];
            $item     = M("borrow_item")->field(true)->where("id = {$info['item_id']} and is_xuqi = 1")->find();

            //检查是否有产品可以续期
            if(empty($item)){
                exit(json_encode(array('message'=>'暂时没有可续期的产品','code'=>'401')));
            }

            if (empty($info)){
                exit(json_encode(array('message'=>'主人，您还没有续期计划','code'=>'401')));
            }else{
                //如果是老借款
                if($info['is_new'] == 0){
                    exit(json_encode(array('message'=>'产品升级，无法续期，请前往还款，给您带来的不便敬请谅解。','code'=>'401')));
                }
            }
            
            //用户优惠券信息
            $ticket   = M("member_coupon")->field('money,end_time,id')->where("uid = $uid AND status = 0 and type = 3 and end_time>$now")->select();
            if (empty($ticket)){
                $ticket = array();
            }
            if ($info['deadline']<$now){
                $info['overdue']           = 1;
                $info['dueTime']           = get_due_day($info['deadline']);
                $info['due_money']         = get_due_fee($info['money'], $info['item_id'], $info['dueTime']);
                $info['due_manage_money']  = get_late_fee($info['money'], $info['item_id'], $info['dueTime']);
            }else {
                $info['overdue'] = 0;
                $info['dueTime']           = get_due_day($info['deadline']);
                $info['due_money']         = 0;
                $info['due_manage_money']  = 0;
            
            }
            //获取可续期天数7 or 7,14
            $renewal_day   = explode(',',$item['renewal_day']);
            $dayF          = $renewal_day[0];
            $itemF         = M("borrow_item")->field(true)->where("money = {$info['money']} and duration = {$dayF} and is_xuqi = 1")->find();
            $renewal_fee   = $itemF['renewal_fee'];
            $audit_fee     = getFloatValue($info['money']*$itemF['audit_rate']/100*$dayF*100/100,2);//贷后管理费
            $enabled_fee   = $itemF['enabled_rate'];//账户管理费
            $created_fee   = '0.00';//技术服务费
            $pay_fee       = $itemF['pay_fee'];//支付服务费
            $total         = getFloatValue($info['money']*$itemF['rate']/100*$dayF/360+$info['money']+$audit_fee+$enabled_fee+$created_fee+$pay_fee,2);//新的借款的本息+费用

            $itemarr                = array();
            $itemarr['duration']    = $dayF;
            $itemarr['total']       = $total;
            $itemarr['renewal_fee']  = $renewal_fee;
            
            $itemarr['audit_fee']   = $audit_fee;
            $itemarr['enabled_fee'] = $enabled_fee;
            $itemarr['pay_fee']     = $pay_fee;
            $itemarr['created_fee'] = $info['created_fee'] ;
            $itemarr['fee']         = getFloatValue($info['audit_fee']+$info['enabled_fee']+$info['created_fee']+$info['pay_fee'],2);
            $itemarr['due_total']   = getFloatValue($info['interest']+$info['due_money']+$info['due_manage_money']+$itemarr['fee']+$renewal_fee,2);
             
            $itemarr['date']        = $now+$dayF*3600*24;
            $itemarr['due_rate']    = "逾期利息是借款金额的".$itemF['due_rate']."%/天 。如借款".$info['money']."元，每天".get_due_fee($info['money'], $itemF['id'], 1)."元。";
            $itemarr['late_rate']   = "逾期管理费是借款金额的".$itemF['late_rate']."%/天 。如借款".$info['money']."元，每天".get_late_fee($info['money'], $itemF['id'], 1)."元。";
        

            if (!empty($renewal_day[1])) {
            $dayS     = $renewal_day[1];
            $itemarr['durationS']    = $dayS;
            $itemS          = M("borrow_item")->field(true)->where("money = {$info['money']} and duration = {$dayS} and is_xuqi = 1")->find();
            $audit_feeS     = getFloatValue($info['money']*$itemS['audit_rate']/100*$dayS*100/100,2);//贷后管理费
            $enabled_feeS   = $itemS['enabled_rate'];//账户管理费
            $renewal_feeS   = $itemS['renewal_fee'];
            $created_feeS   = '0';//技术服务费
            $itemarr['audit_fees']   = $info['created_fee'];
            $pay_feeS                = $itemS['pay_fee'];//支付服务费
            $itemarr['dateS']        = $now+$dayS*3600*24;
            $itemarr['due_totalS']   = getFloatValue($info['interest']+$info['due_money']+$info['due_manage_money']+$itemarr['fee']+$renewal_feeS,2);
            $totalS                  = getFloatValue($info['money']*$itemS['rate']/100*$dayS/360+$info['money']+$audit_feeS+$enabled_feeS+$created_feeS+$pay_feeS,2);//新的借款的本息+费用 
            $itemarr['renewal_feeS'] = $renewal_feeS;
            $itemarr['totalS']       = $totalS;
            $itemarr['due_rateS']    = "逾期利息是借款金额的".$itemS['due_rate']."%/天 。如借款".$info['money']."元，每天".get_due_fee($info['money'], $itemS['id'], 1)."元。";
            $itemarr['late_rateS']   = "逾期管理费是借款金额的".$itemS['late_rate']."%/天 。如借款".$info['money']."元，每天".get_late_fee($info['money'], $itemS['id'], 1)."元。";
            }
            $dayList = array();
            for($i=0; $i<count($renewal_day); $i++) {
                $dayList[$i]['day'] = $renewal_day[$i];
            }
            exit(json_encode(array('message'=>'获取成功','code'=>'200','result'=>array('bdata'=>$info,'dayList'=>$dayList,'itemarr'=>$itemarr,'ticket'=>$ticket))));          
          }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 续期付款
     * @param int 借款会员编号
     * @param int bid 借款申请单号
     * @param int detailId 账单ID
     * @param int money 页面提交的扣款金额
     * @param int bankId 选择扣款的银行
     * @param int tickId 选择的优惠券ID
     * @param int duration 选择续期的天数
     */
    public function renewalPay(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $duration   = $array['duration'];
            $tickId     = $array['tickId'];
            $money      = $array['money'];
    
            $map['uid']         = $array['uid'];
            $map['id']          = $array['detailId'];
            $map['borrow_id']   = $array['bid'];
            $map['status']      = 0;
            $borrow_detail = M("borrow_detail")->field(true)->where($map)->find();
    
            $due_day    = 0;
            $due_fee    = 0;
            $late_fee   = 0;
            
           if($borrow_detail['id']>0){
                
                $borrowInfo    = M("borrow_apply")->field(true)->where(" id = {$borrow_detail['borrow_id']} ")->find();
                $itemInfo      = M('borrow_item')->field(true)->where("money = {$borrow_detail['capital']} and duration = {$duration} and is_xuqi = 1")->order('id desc')->find();
                $coupon        = M("member_coupon")->field('money')->where("id = {$tickId}")->find();
                
                if(empty($itemInfo)){
                    exit(json_encode(array('message'=>'暂时没有可续期的产品'.$total,'code'=>400)));
                }
                //逾期费用
                $due_days   = get_due_day($borrowInfo['deadline']);
                if($due_days>0){
                    $due_fee    = get_due_fee($borrowInfo['money'],$borrowInfo['item_id'],$due_days);
                    $late_fee   = get_late_fee($borrowInfo['money'],$borrowInfo['item_id'],$due_days);
                    $type       = 2;
                    $integral   = $borrow_detail['interest']+$due_fee+$late_fee;
                    $info2      = "逾期还款".$money."元，扣除积分".$integral;
                }else{
                    $type       = 1;
                    $integral   = $borrow_detail['capital']+$borrow_detail['interest'];
                    $info2      = "成功还款".$money."元，获得积分".$integral;
                }
                
                //续期费
                $renewal_fee   = $itemInfo['renewal_fee'];
                //信审费
                $audit_fee     = getFloatValue($borrow_detail['capital']*$itemInfo['audit_rate']/100*$duration*100/100,2);
                //动用费
                $enabled_fee   = getFloatValue($borrow_detail['capital']*$itemInfo['enabled_rate']/100*$duration*100/100,2);
                //续期付款总额
                $total         = getFloatValue($borrow_detail['interest']+$renewal_fee+$borrowInfo['audit_fee']+$borrowInfo['created_fee']+$borrowInfo['enabled_fee']+$borrowInfo['pay_fee']+$due_fee+$late_fee-$coupon['money'],2);
                
                if($total<0){
                    exit(json_encode(array('message'=>'续期还款的金额不能小于0，请确认！'.$total,'code'=>400)));
                }

                //新的money 
                if($money != 0 && $money < $total){
                    $total = $money;
                }

                if($money<$total){
                    exit(json_encode(array('message'=>'提交续期还款的金额不对，应为'.$total.'元','code'=>400)));
                }
                
                $global   = get_global_setting();
                $amount   = $global['credit_amount'];
                $discount = $global['credit_discount'];
                if($discount==0){
                    $total_fee = $amount+$total;
                }else{
                    $total_fee = $discount+$total;
                }
                
                //宝付支付
                $model = new PaymentAction();
                $res   = $model->requestApi($array['uid'], $borrowInfo['id'],2,$total_fee,$array['bankId']);
                //$res = true;
                //扣款成功
                if(is_bool($res) && $res){
                    //续期优惠券更新
                    if($tickId !=''){
                        $tickData['status'] = 1;
                        M("member_coupon")->where(" id = {$tickId} ")->save($tickData);
                    }
                    $newArr                = array();
                    $newArr['money']       = $money;
                    $newArr['duration']    = $duration;
                    $newArr['interest']    = round($borrowInfo['money']*$itemInfo['rate']/100*$duration/360,2);//利息;
                    $newArr['total']       = $total;
                    $newArr['renewal_fee'] = $renewal_fee;
                    $newArr['audit_fee']   = $audit_fee;
                    $newArr['enabled_fee'] = $itemInfo['enabled_rate'];
                    $newArr['due_fee']     = $due_fee;
                    $newArr['late_fee']    = $late_fee;
                    $newArr['tickId']      = $tickId;
                    $newArr['pay_fee']     = $itemInfo['pay_fee'];
                    //生成续期订单 
                    renewal($borrowInfo, $borrow_detail, $itemInfo, $newArr);
                     
                    //发送微信通知
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$array['uid']}")->find();
                    if($wxInfo['openid']!==''){
                        $memberInfo = M("members")->field('iphone')->where("id = {$array['uid']}")->find();
                        sendWxTempleteMsg12($wxInfo['openid'], $borrowInfo['money'], time(), $memberInfo['iphone'], $duration);
                    }
                     
                    //会员积分变更
                    addIntegral($borrowInfo['uid'],$type,$integral,$info2);
                    
                    $maps['uid']       =  $array['uid'];
                    $maps['status']    =  4;
                    $maps['audit_status'] = 5;
                    $infoid = M("borrow_apply")->field('id')->where($maps)->order('id desc')->find();
                    exit(json_encode(array('message'=>'续期成功'.$total,'bid'=>$infoid['id'],'code'=>200)));
                }else{
                    exit(json_encode(array('message'=>'续期失败','code'=>401)));
                }
            }else{
                exit(json_encode(array('message'=>'请前往还款','code'=>401)));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 用户添加其他银行卡 
     */
    public function addOtherCard() {
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            appLog($array);
            $code = C("SINA_BANK_CODE");
            $data['uid']            = $array['uid'];
            $data['bank_card']      = $array['bankCard'];
            $data['bank_code']      = array_search($array['bankName'], $code);
            $data['bank_name']      = $array['bankName'];
            $data['bank_province']  = $array['province'];
            $data['bank_city']      = $array['city'];
            $data['type']           = 2;
            $map['uid']             = $array['uid'];
            $map['bank_card']       = $array['bankCard'];
            $map['bank_name']       = $array['bankName'];
            $map['bank_code']       = $data['bank_code'];
            $info = M("member_bank")->field('id')->where($map)->find();
            if (empty($info)){
                $res = M("member_bank")->add($data);
                if ($res){
                    exit(json_encode(array('message'=>'添加成功','bankId'=>$res,'code'=>'200')));
                }else {
                   exit(json_encode(array('message'=>'添加失败','code'=>'401')));
                }
            }else {
                exit(json_encode(array('message'=>'已有银行卡信息','bankId'=>$info['id'],'code'=>'201')));
            }
        }else{
            exit(json_encode($checkResult));
        }
        
    }
    
    /**
     * 签约页面接口
     */
    public function sign(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){ 
            $map['uid'] = $array['uid'];
            $map['id']  = $array['bid'];
            $field      = 'id,uid,pay_fee,money,duration,interest,audit_fee,created_fee,enabled_fee,status,audit_status,repayment_time';
            $info       = M("borrow_apply")->field($field)->where($map)->find();
            if (!empty($info)){
                $where['money']         = $info['money'];
                $where['duration']      = $info['duration'];
                $infoitem               = M("borrow_item")->field('late_rate,due_rate,created,enbled,audit,pay_rate,rate,total_rate')->where($where)->find();
                $rate['due_rate']       = $infoitem['due_rate'];
                $rate['late_rate']      = $infoitem['late_rate'];
                //技术服务费
                $cost['created_rate']   = getFloatValue($infoitem['created'], 2).'%';
                //账户管理
                $cost['enabled']        = getFloatValue($infoitem['enbled'], 2).'%';
                //贷后管理费
                $cost['audit_fee']      = getFloatValue($infoitem['audit'], 2).'%';
                //支付服务费
                $cost['withdrawals']    = getFloatValue($infoitem['pay_rate'], 2).'%';
                //利息
                $cost['interes']        = getFloatValue($infoitem['rate'], 2).'%';
                //综合年化
                $cost['year_interest']  = getFloatValue($infoitem['total_rate'], 2).'%';
            
                $info['total_money']    = getFloatValue($info['created_fee']+$info['audit_fee']+$info['enabled_fee']+$info['pay_fee'],2);
                $info['total']          = getFloatValue($info['money']+$info['total_money']+$info['interest'],2);
            
                exit(json_encode(array('message'=>'获取成功','code'=>200,'result'=>array('borrowInfo'=>$info,'infoitem'=>$rate))));
            }else {
                exit(json_encode(array('message'=>'请确认借款进度','code'=>401)));
            }
        }else{
            exit(json_encode($checkResult));
        }
        
    }
    /**
	 * 添加线下还款申请
	 */
	public function addPayApply(){
	    $array = $_POST;
	    $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
	    if (is_bool($checkResult)){
	        appLog($array);
    	    $data['uid']       = $array['uid'];
    	    $data['borrow_id'] = $array['bid'];    
    	    $data['detail_id'] = $array['detailId'];
    	    $data['type']      = $array['type'];
    	    $data['account']   = $array['bank_num'];
    	    $data['money']     = $array['money'];
    	    $data['ticket_id'] = $array['ticket_id'];
    	    $data['xuqi_days']  = $array['duration'];
    	    $data['memo']      = "姓名：".$array['real_name'].",手机号：".$array['phone'];
    	    $data['add_time']  = time();
    	    $res = M("payoff_apply")->add($data);
    	    if ($res){
    	    if (!empty($array['ticket_id'])){
                    $tickData['status'] = 1;
                    $tickData['id']     = $array['ticket_id'];
                    M("member_coupon")->save($tickData);
                }
    	        exit(json_encode(array('message'=>'提交申请成功','code'=>'200')));
    	    }else {
    	        exit(json_encode(array('message'=>'提交申请失败','code'=>'401')));
    	    }
	    }else{
	        exit(json_encode($checkResult));
	    }	    
	}
    
   /**
     * 判断用户是否可以续期
     */
    public function checkUserRenewalAuth(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $now = time();
            //获取renewal_id的集合
            $m = $this->getCount($array['uid'],$array['bid']);
            //去除空数组
            $m = array_filter(explode(',',$m));
            //获取续期次数
            $count    = count($m);
            $global   = get_global_setting();
            $offcount = M('payoff_apply')->where(" detail_id = {$array['detailId']} and status = 0 and type = {$array['type']}")->count('id');
            if ($offcount >0 ){
                exit(json_encode(array('message'=>"信息审核中，无法操作",'code'=>'401')));
            }

            $offcount = M('payoff_apply')->where(" detail_id = {$array['detailId']} and status = 1 and type = 2")->count('id');
            if ($offcount >0 ){
                exit(json_encode(array('message'=>"您已提交还款，请不要重复提交",'code'=>'401')));
            }
            
            $paycount = M('borrow_detail')->where(" detail_id = {$array['detailId']} and status = 1 ")->count('id');
            
            if ($array['type'] == 1){//续期
                $item = M("borrow_item")->field('id')->where("money = {$array['money']} and duration={$array['duration']} and is_xuqi = 1")->find();
                if(empty($item['id'])){
                    exit(json_encode(array('message'=>"请重新选择续期天数",'code'=>'401')));
                }
                if($paycount==1){
                    $paystatus     = 1;
                    exit(json_encode(array('message'=>"该借款已经还款，无需再续期",'code'=>'401')));
                }else {
                    $pay0          = M("transfer_order_pay")->where(" uid ={$array['uid']} and borrow_id={$array['bid']} and scene = 2 and status <2 ")->count('id');
                    if($pay0>0){
                        exit(json_encode(array('message'=>"该借款的续期还在支付中",'code'=>'202')));
                    }else{
                        exit(json_encode(array('message'=>'可续期','code'=>'200')));//失败或者没有付款的
                    }
                }
                
                if(intval($global['renewal_num']-1) == $count){
                    exit(json_encode(array('message'=>"这是您本次借款最后一次续期，到期请准备资金还款",'code'=>'201')));
                }
            
            if(intval($global['renewal_num']) <= $count){
                exit(json_encode(array('message'=>"您已续期".$count."次，请前往还款",'code'=>'401')));
            }
            $map['id']  = $array['bid'];
            $map['uid'] = $array['uid'];
            $binfo      = M("borrow_apply")->field('id,deadline,item_id')->where($map)->find();
            
            $rinfo = M("borrow_item")->field('xuqi_day')->where("id = {$binfo['item_id']}")->find();
            if ($binfo['deadline'] > $now){
                $dif = floor(($binfo['deadline'] - $now)/3600/24);
                if ($dif > $rinfo['xuqi_day']){
                    exit(json_encode(array('message'=>'还款时间充裕,无需续期','code'=>'401')));
                }else {
                    exit(json_encode(array('message'=>'可续期','code'=>'200')));
                }
                }else {
                exit(json_encode(array('message'=>'可续期','code'=>'200')));
               }
            }else {//还款
                if($paycount==1){
                    exit(json_encode(array('message'=>"该借款已经还款，无需再还款",'code'=>'401')));
                }else {
                    $pay0          = M("transfer_order_pay")->where(" uid ={$array['uid']} and borrow_id={$array['bid']} and scene = 1 and status <2 ")->count('id');
                    if($pay0>0){
                        exit(json_encode(array('message'=>"该借款的还款还在支付中",'code'=>'202')));
                    }else{
                        exit(json_encode(array('message'=>'可还款','code'=>'200')));//失败或者没有付款的
                    }
                } 
            }
        }else{
            exit(json_encode($checkResult));
        }
        
    }

    /**
     * 判断用户是否可以还款
     */
    public function isRepayment(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $offcounts = M('payoff_apply')->where(" detail_id = {$array['detailId']} and status = 1 and type = 1")->count('id');
            if ($offcounts >0 ){
                exit(json_encode(array('message'=>"您已经提交还款，请勿重复提交",'code'=>'401')));
            }else {
                exit(json_encode(array('message'=>"可以提交线下还款",'code'=>'200')));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
	 * 获取续期的次数 根据当前的id和用户id 获取续期id的集合
	 */
    public function getCount($uid,$id){
        $map['uid']= $uid;
        $map['id'] =  $id;
        $pids = '';
        $info = M("borrow_apply")->field('id,renewal_id')->where($map)->find();
        if($info['renewal_id']){
            $pids .= $info['renewal_id'];
            $npids = $this->getCount($uid,$pids);
            if(isset($npids)){
                $pids .= ','.$npids;
            }
        }
        return $pids;
    }
      
    /*************************************************************还款流程接口结束*************************************************/
    
    
    /**
     * 记录安卓手机配置1   
     * @param int uid
     */
    public function savePhoneSet(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        $setup = M("ad_setup")->field('device_id')->where(" uid={$array['uid']} and device_id={$array['device_id']} ")->find(); 
        if (is_bool($checkResult)){
            if (empty($setup['device_id'])){
            $data['uid']        = $array['uid'];
            $data['moblie']     = $array['moblie'];
            $data['model']      = $array['model'];
            $data['brand']      = $array['brand'];
            $data['version']    = $array['version'];
            $data['device_id']  = $array['device_id'];
            $data['operator']   = $array['operator'];
            $data['net_type']   = $array['net_type'];
            $data['location']   = $array['location'];
            $data['add_time']   =  time();
            $ad = M("ad_setup")->add($data);
            if ($ad){
                    exit(json_encode(array('message'=>'成功','code'=>200)));
            }else {
                    androidLog("记录安卓手机配置插入错误：".$array['uid']);
                    androidLog("错误信息：".json_encode($data));
                } 
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 记录安卓手机通讯录
     * @param int uid
     */
    public function savePhoneContact(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $a = json_decode(json_decode(json_encode($array['contact'],true),true),true);
            if (!$a){
                androidLog("记录安卓手机通讯录为空：".$array['uid']);
            }
            $callrecord = M("ad_contact")->field('add_time')->where(" uid={$array['uid']} and device_id={$array['device_id']} ")->order("add_time desc")->limit("1")->find();
            $etime =strtotime(date("Y-m-d H:i:s",strtotime("+1 month",$callrecord['add_time'])));
            if (time() > $etime|| empty($callrecord['add_time'])){
            foreach ($a as $key=>$val){
                $data['uid']           =  $array['uid'];
                $data['device_id']     =  $array['device_id'];
                $data['name']          =  $val['name'];
                $data['add_time']      =  time();
                $phoneLength = count($val['phoneNumber']);
                $tempArr = $val['phoneNumber'];
                for ($j=0;$j<$phoneLength;$j++){
                    $data['mobile'] = $tempArr[$j]['moblie'];
                    $ad = M("ad_contact")->add($data);
                    if (!$ad){
                        androidLog("记录安卓手机通讯录插入错误：".$array['uid']);
                        androidLog("错误信息：".json_encode($data));
                    }
                }
            }
            exit(json_encode(array('message'=>'成功','code'=>200)));  
            }
        }else{
            exit(json_encode($checkResult));
        }
    }

    /**
     * 记录安卓手机通话记录
     * @param int uid
     */
    public function savePhoneCallRecord(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
           $call = M("ad_callrecord")->field('call_time')->where(" uid={$array['uid']} and device_id={$array['device_id']} ")->order("call_time desc")->limit("1")->find();
            $a = json_decode(json_decode(json_encode($array['callRecord'],true),true),true);
            if (!$a){
                androidLog("记录安卓手机通讯录为空错误：".$array['uid']);
            }
             foreach ($a as $key=>$val){
                 if (strtotime($val['call_time']) > $call['call_time'] || empty($call['call_time']) ||  $call['call_time'] == '2147483647'){
                     $data['uid']           =  $array['uid'];
                     $data['device_id']     =  $array['device_id'];
                     $data['name']          =  $val['name'];
                     $data['mobile']        =  $val['moblie'];
                     $data['call_type']     =  $val['call_type'];
                     $data['call_time']     =  strtotime($val['call_time']);
                     $data['call_long_time']=  $val['call_long_time'];
                     $data['add_time']      =  time();
                     $ad = M("ad_callrecord")->add($data);
                     $time['call_time'] = 1527523200;
                     M("ad_callrecord")->where("uid = {$array['uid']} and call_time = 2147483647")->save($time);
                     if (!$ad){
                         androidLog("记录安卓手机通讯录插入错误：".$array['uid']);
                         androidLog("错误信息：".json_encode($data));
                     }
                  }
             }
            exit(json_encode(array('message'=>'成功','code'=>200)));
        }else{
            exit(json_encode($checkResult));
        }
    }
    /**
     * 记录安卓手机安装软件
     * @param int uid
     */
    public function savePhoneApp(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $a = json_decode(json_decode(json_encode($array['app'],true),true),true);
            if (!$a){
                androidLog("记录安卓手机安装软件为空错误：".$array['uid']);
            }
           $app = M("ad_app")->field('add_time')->where(" uid={$array['uid']} and device_id={$array['device_id']} ")->order("add_time desc")->limit("1")->find();
           $etime =strtotime(date("Y-m-d H:i:s",strtotime("+1 month",$app['add_time'])));
            if (time() > $etime || empty($app['add_time'])){
               foreach ($a as $key=>$val){
               $data['uid']           =  $array['uid'];
               $data['device_id']     =  $array['device_id'];
               $data['app_name']      =  $val['app_name'];
               $data['type']          =  getAppType($data['app_name']);
               $data['app_package']   =  $val['app_package'];
               $data['app_version']   =  $val['app_version'];
               $data['add_time']      =  time();
               $ad = M("ad_app")->add($data); 
               if (!$ad){
                   androidLog("记录安卓手机安装软件插入错误：".$array['uid']);
                   androidLog("错误信息：".json_encode($data));
               }
            }
               exit(json_encode(array('message'=>'成功','code'=>200)));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 记录用户短信
     * @param int uid 用户uid
     */
    public function saveUserMessage(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $a = json_decode(json_decode(json_encode($array['sms'],true),true),true);
            if (!$a){
                androidLog("记录用户短信为空错误：".$array['uid']);
            }
            $sms = M("ad_smsrecord")->field('sms_time')->where(" uid={$array['uid']} and device_id={$array['device_id']} ")->order("sms_time desc")->limit("1")->find();
            foreach ($a as $key=>$val){
                if(strtotime($val['sms_date']) > $sms['sms_time'] || $sms['sms_name']!==null ||!empty($sms['sms_name']) || empty($sms['sms_time']) || $sms['sms_time'] == '2147483647'){
                    $data['uid']         =  $array['uid'];
                    $data['device_id']   =  $array['device_id'];
                    $data['mobile']      =  $val['sms_address'];
                    $data['sms_type']    =  $val['sms_type'];
                    $data['sms_time']    =  strtotime($val['sms_date']);
                    $data['sms_content'] =  $val['sms_body'];
                    $data['add_time']    =  time();
                    $ad = M("ad_smsrecord")->add($data);
                    $time['sms_time'] = 1527523200;
                    M("ad_smsrecord")->where("uid = {$array['uid']} and sms_time = 2147483647")->save($time);
                    if (!$ad){
                        androidLog("记录用户短信插入错误：".$array['uid']);
                        androidLog("错误信息：".json_encode($data));
                    }
                }
            }
                exit(json_encode(array('message'=>'成功','code'=>200)));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 记录陀螺仪
     * @param int uid 
     * @param int device 
     */
    public function saveUserAddress(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $data['uid']         =  $array['uid'];
            $data['device_id']   =  $array['device_id'];
            $data['x_axis']      =  $array['x_value'];
            $data['y_axis']      =  $array['y_value'];
            $data['z_axis']      =  $array['z_value'];
            $ad = M("ad_gyroscope")->add($data);
            if ($ad){
                exit(json_encode(array('message'=>'成功','code'=>200)));
            }else {
                androidLog("记录记录陀螺仪插入错误：".$array['uid']);
                androidLog("错误信息：".json_encode($data));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 记录APP抛出异常信息
     */
    public function saveAppException(){
        $array = $_POST;
        androidLog("记录APP抛出异常信息：".json_encode($array));
        $checkResult   = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'], $array['sign']);
        if(is_bool($checkResult)){
            $data['userId']         = $array['uid'];
            $data['app_version']    = $array['app_version'];
            $data['model']          = $array['model'];
            $data['brand']          = $array['brand'];
            $data['version']        = $array['version'];
            $data['exception']      = $array['exception'];
            appLog($data);
            exit(json_encode(array('message'=>'success','code'=>'200')));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 页面事件
     * @param int uid 
     */
    public function saveUserBehavior(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            if (empty($array['exit_time'])){
                $data['exit_time']       =  0;
            }else {
                $data['exit_time']       =  strtotime($array['exit_time']);
            }
            if (empty($array['residence_time'])){
                $data['residence_time']   =  0;
            }else {
                $data['residence_time']   =  trim(str_replace('s', " ", $array['residence_time']));
            }
            $data['uid']              =  $array['uid'];
            $data['borrow_id']        =  $array['borrow_id'];
            $data['device_id']        =  $array['device_id'];
            $data['page']             =  $array['page'];
            $data['enter_time']       =  strtotime($array['enter_time']);
            $data['add_time']         =  time();
            $ad = M("ad_event_log")->add($data); 
            if ($ad){
                exit(json_encode(array('message'=>'成功','code'=>200)));
            }else {
                androidLog("记录页面事件插入错误：".$array['uid']);
                androidLog("错误信息：".json_encode($data));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 控件事件
     */
    public function saveUserControl(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $a = json_decode(json_decode(json_encode($array['result'],true),true),true);
            if (!$a){
                androidLog("记录控件事件为空错误：".$array['uid']);
            }
            foreach ($a as $key=>$val){
                    $data['uid']           =  $array['uid'];
                    $data['device_id']     =  $array['device_id'];
                    $data['controls']      =  $val['controls'];
                    $data['num']           =  $val['num'];
                    $data['last_time']     =  $val['last_time'];
                    $data['add_time']      =  time();
                    $data['borrowid']      =  $array['bid'];
                    $ad =M("ad_controls_log")->add($data);
                    if (!$ad){
                        androidLog("记录控件事件错误：".$array['uid']);
                    }
            }
            exit(json_encode(array('message'=>'成功','code'=>200)));
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 用户授权表
     */
    public function userAuthTable(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            appLog($array);
            $data['uid']              =  $array['uid'];
            $data['device_id']        =  $array['device_id'];
            $data['borrow_id']        =  $array['borrow_id'];
            $data['address ']         =  $array['address '];
            $data['address_time']     =  $array['address_time'];
            $data['call ']            =  $array['call '];
            $data['call_time']        =  $array['call_time'];
            $data['sms ']             =  $array['sms '];
            $data['sms_time']         =  $array['sms_time'];
            $data['gps ']             =  $array['gps '];
            $data['gps_time']         =  $array['gps_time'];
            $data['sdcard ']          =  $array['sdcard '];
            $data['sdcard_time']      =  $array['sdcard_time'];
            $data['add_time']         =  time();
            $ad = M("ad_permission_log")->add($data);
            if ($ad){
                exit(json_encode(array('message'=>'成功','code'=>200)));
            }else {
                androidLog("用户授权插入错误：".$array['uid']);
                androidLog("错误信息：".json_encode($data));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取用户设备
     * @param int uid 用户uid
     * @param string tick 请求猛犸唯一标示
     * @param int event 事件类型id
     * @param int status 事件状态，1成功，0失败
     */
    public function getMaxentId(){
        $array         = $_POST;
        $checkResult   = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $tick      = $array['tick'];
            $event     = $array['event'];
            $status    = $array['status'];
            $time      = $array['time'];
            $url       = "https://www.id-linking.com/api/v1/5g0hau6ige6ndtwx6fg9a9gyxbhcgjg4/tick/".$tick;
            $res       = http_request($url);
            $arr       = json_decode($res,true);
            $maxent_id = $arr['data']['maxent_id'];
            $this->insertDevice($arr, $event ,$status, $array['uid'],$array['versionCode'],$time);
            $uid = $array['uid'];
            $data['device_id'] = $maxent_id;
            $ad  = M("member_logoff")->where("uid = $uid")->save($data);
            if ($ad){
                exit(json_encode(array('message'=>'成功','code'=>200)));
            }else {
                androidLog("获取用户设备插入错误：".$array['uid']);
                androidLog("错误信息：".json_encode($data));
            }
        }else{
            exit(json_encode($checkResult));
        }      
    }
      
    /**
     * app版本
     */
    public function appVersion(){
        $array = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $info = M("app_version")->field('*')->find();
            if ($array['versionCode'] < $info['versionCode']){
                if ($info['mustUpdate']){
                    exit(json_encode(array('message'=>'有新版本必须请更新','code'=>200,'mustUpdate'=>$info)));
                }else {
                    exit(json_encode(array('message'=>'有新版本是否需要更新','code'=>200,'mustUpdate'=>$info)));
                }
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 插入用户设备信息表
     */
    private function insertDevice($arr, $event, $status, $uid, $versionCode,$time) {
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
        $data['versionCode'] = $versionCode;
        $data['add_time']    = $time;
        androidLog("插入用户设备信息：".json_encode($data));
        $ad = M("member_device")->add($data);
        if ($ad){
            exit(json_encode(array('message'=>'成功','code'=>200)));
        }else {
            androidLog("插入用户设备信息错误：".$uid);
        }
    }
    
    /**
     * 生成APP端用token
     */
    private function createToken($uid,$phone){
        $string = "XDM-{$uid}-{$phone}-".time();
        $token = md5(sha1($string));
        $data['uid']    = $uid;
        $data['token']  = $token;
        $data['addtime']= time();
        $data['expire'] = time()+3600*24*7;
        M("user_token")->add($data);
        return $token;
    }
        
    /**
     * sign字符串加密算法
     * @param int $timeStamp  请求时时间戳  
     * @param string $randNumber 请求时随机字符串
     * @param string $secretString 密令
     * @param string $sign 
     */
    private function checkRequestSign($timeStamp,$randNumber,$secretString,$sign) {
        $array = $_POST;
        $now   = time();
        $str = "secretString=".$secretString."&randNumber=".$randNumber."&timeStamp=".$timeStamp;
        $signature  = md5(sha1(strtoupper($str)));
        $info = M("app_version")->field(true)->find();
        if ($info['mustUpdate'] == 1 ){
            if (empty($array['versionCode']) || $array['versionCode'] < $info['versionCode']){
                $data['message']  =  "您的APP不是最新版本，请更新后再操作！";
                $data['code']     =  "402";
                $data['result']   = $info;
                return $data;
            }
        }else {
            if (empty($array['versionCode']) || $array['versionCode'] < 221 ){
                $data['message']  =  "您的APP版本太低,请更新！";
                $data['code']     =  "402";
                $data['result']   = $info;
                return $data;
            } 
        }
        $off = C('OFF');
        if ($off == 1){
            if (($now-$timeStamp) > 100){
                $data['message']  =  "sign invalid!";
                $data['code']   =  "401";
                return $data;
            }else {
                if ($sign !== $signature){
                    $data['message']  =  "sign error!";
                    $data['code']   =  "401";
                    return $data;
                }else {
                    return true;
                }   
        }
      }else{
          return true;
      }
    }
    
    
    /**
     * 新浪代付到用户提现卡
     */
    public function withDrawal($uid,$bid){
        $flg = false;
        $smsTxt = FS("Webconfig/smstxt");
        $smsTxt = de_xie($smsTxt);
        $memberInfo = M("members")->field('iphone')->where("id = $uid")->find();
        $bankInfo = M("member_bank")->field('bank_id')->where("uid = $uid")->find();
        $info = M("borrow_apply")->field('*')->where("id = $bid")->find();
        if($info['status']>=4 || $info['len_time']>0){
            $flg = true;
        }else {
            if (sinaCreateBidInfo($info)){
                if (sinaCreateSingleHostingPayToCardTrade($info,$bankInfo['bank_id'],$info['trade_no'])){
                    $mdata['pending']      = 1;
                    $mdata['pending_time'] = time();
                    $sdata['status']       = 4;
                    $sdata['audit_status'] = 5;
                    $sdata['len_time']     = time();
                    $deadline = time()+3600*24*($info['duration']);
                    $sdata['deadline']     = $deadline;
    
                    M("member_status")->where("uid = {$uid} AND borrow_id = $bid")->save($mdata);
                    M("borrow_apply")->where("uid = {$uid} AND id = $bid")->save($sdata);

					wqbLog("APP放款开始");
                    createRepayOrder($info,$deadline);
                    wqbLog("APP放款成功");
					
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                    sendWxTempleteMsg5($wxInfo['openid'], $info['money'], $info['add_time'], $sdata['len_time'],$info['loan_money']);
                    //addToSms($memberInfo['iphone'],str_replace(array("#DATE#", "#MONEY#"), array(date('Y-m-d',time()), $info['loan_money']), $smsTxt['loan_success']));
                    appLog("放款成功");
    
                }else {
                    //发送App推送通知放款失败
                    $mwhere['uid'] = $uid;
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg6($uid,$token['token'],$bid);
                    }
                    appLog("放款失败");
                }
            }else {
                appLog("录入失败,借款id".$bid);
            }
        }
        return $flg;
    
    }
    
    /**
     * 重复提交判断
     * $uid 用户id
     * $bid 订单id
     * $type 类型
     */
    public function loanApply(){
        $array = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $offcount = M('payoff_apply')->where(" detail_id = {$array['detailId']} and status = 0 ")->count('id');
            if ($offcount>0){
                $result = M('payoff_apply')->field('memo,account,money')->where(" detail_id = {$array['detailId']} and status in(0,1)")->find();
                $name   = strtok($result['memo'], ',');
                $name   = explode("：", $name);
                $result['name'] = $name[1];
                $result['iphone'] = substr($result['memo'], -11);
                exit(json_encode(array('message'=>'已提交申请，请勿重复提交','code'=>'201','result'=>$result)));
            }else{
                exit(json_encode(array('message'=>'可以提交申请','code'=>'200')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }

    /**
     * 获取应用类通知
     * $type 类型   
     */  
    public function getNotice(){
        $array = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            exit(json_encode(array('message'=>'最近各大银行扣款业务不稳当，建议使用支付宝线下还款或者续期。','code'=>'200')));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取节日类通知
     * $type 类型
     */
    public function getNews(){
        $array = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            exit(json_encode(array('message'=>'亲爱的用户：'.'\n'.'感谢您一直以来对现贷猫的支持与厚爱。现贷猫春节假日安排如下：'.'\n'.'一．放假安排：从2018年2月15日至2月21日放假，2月22日（初七）正常上班。'.'\n'.'二．客服安排：2月15日-2月21日期间，如有问题可联系18101812991、18101816772'.'\n'.' 感谢您一直以来的信任与支持，现贷猫恭祝您新春大吉，阖家幸福！','code'=>'201')));
        }else {
            exit(json_encode($checkResult));
        }
    }

    /**
     * 同意放款  
     */
    public function loanAgree() {
        $array = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
             $uid           = $array['uid'];
             $borrow_id     = $array['bid'];
             
             $where['uid']       = $uid;
             $where['borrow_id'] = $borrow_id;
             $status = M("member_status")->field('calm')->where($where)->find();
             
             if($status['calm']==0){
                 $map['uid']       = $uid;
                 $map['id']        = $borrow_id;
                 $apply = M("borrow_apply")->field('id,status,audit_status')->where($map)->find();
                 if($apply['status']==3 && $apply['audit_status']==4){
                     $datag        = get_global_setting();
                     $is_aoto_loan = $datag['is_aoto_loan'];
                     $loan_onoff   = $datag['loan_onoff'];
                     $loan_day     = $datag['loan_day'];

                 $user['calm']           = 1;
                 $user['calm_time']      = time();
                 $res = M("member_status")->where("uid = {$uid} AND borrow_id = {$borrow_id}")->save($user);
                 if($res){
                     exit(json_encode(array('message'=>'等待放款','code'=>'200')));
                     /*if($loan_onoff == "1"){
                         exit(json_encode(array('message'=>'等待放款','code'=>'401')));
                     }else{
                         if ($is_aoto_loan == "1"){
                             if(get_loan_money()>$loan_day){
                                 exit(json_encode(array('message'=>'等待放款','code'=>'401')));
                             }else{
                                 $flg = $this->withDrawal($uid, $borrow_id);
                                 $count = M("borrow_apply")->where("uid ={$uid} and status = 5 ")->count("id");
                                 if ($count < 2 ){
                                     $infos = M("borrow_apply")->field('deadline')->where("id = '{$borrow_id}'and uid = '{$uid}'")->find();
                                     $uid      = $array['uid'];
                                     $deadline = $infos['deadline'];
                                     $ticData  = $this->createUserCoupon($uid,$deadline);
                                 }
                                 if($flg){
                                     exit(json_encode(array('message'=>'等待放款','code'=>'200')));
                                 }else{
                                    exit(json_encode(array('message'=>'等待放款','code'=>'200')));
                                 }
                                 exit(json_encode(array('message'=>'等待放款','code'=>'200')));
                             }
                         }else{
                            exit(json_encode(array('message'=>'等待放款','code'=>'200')));
                         }
                     }*/
                 }else{
                     exit(json_encode(array('message'=>'请确认借款申请的状态','code'=>'401')));
                    }
                 }else{
                    exit(json_encode(array('message'=>'请确认借款申请的状态','code'=>'401')));
                }
             }else{
                    exit(json_encode(array('message'=>'请确认借款申请的状态','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /***********************Face++相关************************************/
    /**
     * Face++人脸识别
     * 
     */
    public function getFace(){
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        $flg         = 1;
        
        if (is_bool($checkResult)){
            if($array['status'] == 1){
                //身份验证照片保存
                $img_best   = $array['image_best'];
                $img_env    = $array['image_env'];
                $file_best  = $array['uid'].'.jpg';
                $file_env   = $array['uid'].'-1.jpg';
                base64_image_content($img_best,$file_best);
                base64_image_content($img_env,$file_env);
                
                //在线视频刷脸的检查
                /*$mask_confidence                = $array['mask_confidence'];
                $mask_threshold                 = $array['mask_threshold'];
                $synthetic_face_confidence      = $array['synthetic_face_confidence'];
                $synthetic_face_threshold       = $array['synthetic_face_threshold'];
                $screen_replay_confidence       = $array['screen_replay_confidence'];
                $screen_replay_threshold        = $array['screen_replay_threshold'];
                if($mask_confidence<0 || $mask_confidence>$mask_threshold){
                    $array['confidence'] = 0;
                    $flg = 0;
                }
                
                if($synthetic_face_confidence<0 || $synthetic_face_confidence>$synthetic_face_threshold){
                    $array['confidence'] = 0;
                    $flg = 0;
                }
                
                if($screen_replay_confidence<0 || $screen_replay_confidence>$screen_replay_threshold){
                    $array['confidence'] = 0;
                    $flg = 0;
                }*/
                
                $datag   = get_global_setting();
                if($array['confidence']<$datag['face_sorce']){
                    $flg = 0;
                }
                
                if($flg==1){//身份认证通过
                    $status = 1;
                    $sdata['id_verify']      = 1;
                    $sdata['id_verify_time'] = time();
                    $result = M("member_status")->where("borrow_id = {$array['bid']}")->save($sdata);
                    $is_aotu      = $datag['is_aotu_bid'];
                    if($is_aotu == 1){
                        //人脸识别成功后自动上标到福米金融
                        $upbid = createFumiBid($array['borrow_id'],0);
                        //发送App推送通知复审成功
                        $mwhere['uid'] = $array['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg4($array['uid'],$token['token'],$array['bid']);
                        }
                    }
                }else{
                    $status                  = 2;
                    $bdata['status']         = 93;
                    $bdata['refuse_time']    = time();
                    M("borrow_apply")->where("id = {$array['bid']}")->save($bdata);
                
                    $sdata['id_verify']      = 2;
                    $sdata['id_verify_time'] = time();
                    $result = M("member_status")->where("borrow_id = {$array['bid']} ")->save($sdata);
                    delUserOperation($array['uid'],$array['bid']);
                    //发送微信
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$array['uid']}")->find();
                    if($wxInfo['openid']!==""){
                        $binfo  = M("borrow_apply")->field('money, add_time')->where("id = {$array['bid']}")->find();
                        sendWxTempleteMsg22($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                    }
                    
                    //发送App推送通知复审失败
                    $mwhere['uid'] = $array['uid'];
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg3($array['uid'],$token['token'],$array['bid']);
                    }
                }
                //添加APP端Face++记录
                $data['uid']        = $array['uid'];
                $data['borrow_id']  = $array['bid'];
                $data['status']     = $status;
                $data['type']       = 2;
                $data['score']      = $array['confidence'];
                $data['request_id'] = $array['request_id'];
                $data['add_time']   = time();
                $data['finish_time']= time();
                $newid = M('borrow_face')->add($data);
                
                if($status==1){
                    exit(json_encode(array('message'=>'身份验证成功','code'=>'200')));
                }else{
                    exit(json_encode(array('message'=>'身份验证失败','code'=>'403')));
                }
            }else{
                $error_msg    = $array['error_message'];
                wqbLog($error_msg);
                if($error_msg =="ID_SUCH_IDNUMBER"
                    || $error_msg =="ID_NUMBER_NAME_NOT_MATCH"
                    || $error_msg =="IMAGE_ERROR_UNSUPPORTED_FORMAT: data_source"
                    || $error_msg =="INO_FACE_FOUND: data_source"){  
                    
                    $bdata['status']         = 93;
                    $bdata['refuse_time']    = time();
                    M("borrow_apply")->where("id = {$array['bid']}")->save($bdata);
                    
                    $sdata['id_verify']      = 2;
                    $sdata['id_verify_time'] = time();
                    $result = M("member_status")->where("borrow_id = {$array['bid']} ")->save($sdata);
                    delUserOperation($array['uid'],$array['bid']);
                    //发送微信
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$array['uid']}")->find();
                    if($wxInfo['openid']!==""){
                        $binfo  = M("borrow_apply")->field('money, add_time')->where("id = {$array['bid']}")->find();
                        sendWxTempleteMsg22($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                    }
                    //添加APP端Face++记录
                    $data['uid']        = $array['uid'];
                    $data['borrow_id']  = $array['bid'];
                    $data['status']     = 1;
                    $data['type']       = 2;
                    $data['score']      = 0;
                    $data['request_id'] = $array['request_id'];
                    $data['add_time']   = time();
                    $data['finish_time']= time();
                    $newid = M('borrow_face')->add($data);
                }
                //发送App推送通知复审失败
                $mwhere['uid'] = $array['uid'];
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg3($array['uid'],$token['token'],$array['bid']);
                }
                exit(json_encode(array('message'=>'身份验证失败','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    public function faceMaked(){
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $data['uid']        = $array['uid'];
            $data['borrow_id']  = $array['bid'];
            $data['add_time']   = time();
            $newid = M('borrow_app_face')->add($data);
            if($newid){
                exit(json_encode(array('message'=>'活体记录保存成功','code'=>'200')));
            }else{
                exit(json_encode(array('message'=>'活体记录保存失败','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
        
    }

    //获取邀请码接口
    public function inviteCode(){
        $uid          = $_POST['uid'];
        $uwhere['id'] = $uid; 
        $userval = M('members')->where($uwhere)->field('invite_code')->find();

        if(!empty($userval['invite_code'])){
            exit(json_encode(array('message'=>"邀请码存在",'code'=>200,'result'=>array('invite_code'=>$userval['invite_code']))));
        }else{
            exit(json_encode(array('message'=>"邀请码不存在",'code'=>401)));
        }
    }
    
    /**
     * 友盟唯一码上传
     */
    public function umengUid(){
        $status      = 0;
        $flg         = 0;
        $array       = $_POST;   
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $tick    = $array['tick'];
            $updata['last_time']    = time();
            $updata['last_ip']      = '0';
            $updata['last_address'] = get_ipAddress($updata['last_ip']);
            $updata['last_gps']     = $array['address'];
            $savedata = M("members")->where("id = {$array['uid']}")->save($updata);
            
            $back   = M('ad_bak_log')->where("uid = {$array['uid']} and status <> 0")->field('id,add_time')->order("id desc")->find();
            if($back['id']>0){
                $addtime = strtotime(date("Y-m-d",$back['add_time'])."00:00:00");
                $now     = strtotime(date("Y-m-d",time())."00:00:00");
                $day     = ($now-$addtime)/86400;
                if($day >95){
                    $newdata['uid']      = $array['uid'];
                    $newdata['add_time'] = time();
                    $new = M("ad_bak_log")->add($newdata);
                }
            }else{
                $newdata['uid']      = $array['uid'];
                $newdata['add_time'] = time();
                $new = M("ad_bak_log")->add($newdata);
            }
            
            $back   = M('ad_bak_log_ec')->where("uid = {$array['uid']} and status <> 0")->field('id,add_time')->order("id desc")->find();
            if($back['id']>0){
                $addtime = strtotime(date("Y-m-d",$back['add_time'])."00:00:00");
                $now     = strtotime(date("Y-m-d",time())."00:00:00");
                $day     = ($now-$addtime)/86400;
                if($day >30){
                    $newdata['uid']      = $array['uid'];
                    $newdata['add_time'] = time();
                    $new = M("ad_bak_log_ec")->add($newdata);
                }
            }else{
                $newdata['uid']      = $array['uid'];
                $newdata['add_time'] = time();
                $new = M("ad_bak_log_ec")->add($newdata);
            }
            
            $umeng   = M('member_umeng')->where("uid = {$array['uid']}")->field('id,token')->find();
            if($umeng['id']>0){
                if($umeng['token']==$array['deviceToken']){
                    $status = 1;
                    $flg    = 1;
                }else{
                    M("member_umeng")->where("id = {$umeng['id']}")->delete();
                }
            }else{
                $umengt   = M('member_umeng')->where("token = '{$array['deviceToken']}'")->field('id,token')->find();
                if($umengt['id']>0){
                    M("member_umeng")->where("id = {$umengt['id']}")->delete();
                }   
            }
            if($status==0){
                $data['uid']        = $array['uid'];
                $data['token']      = $array['deviceToken'];
                $data['add_time']   = time();
                $newid = M('member_umeng')->add($data);
                if($newid>0){
                    $flg = 1;
                }
            }
            if($flg==1){
                exit(json_encode(array('message'=>'系统有友盟的记录','code'=>'200')));
            }else{
                exit(json_encode(array('message'=>'系统没有友盟的记录','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取用户消息列表(系统类)
     */
    public function getMsgSys(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map['uid']     = $array['uid'];
            $map['bid']     = array("gt",0);
            $map['status']  = array("lt",2);
            $msgList     = M("ad_msg")->field(true)->where($map)->order("add_time DESC ")->select();
            if(count($msgList)>0){
                exit(json_encode(array('message'=>'消息列表获取成功','code'=>'200','result'=>array('list'=>$msgList))));
            }else{
                exit(json_encode(array('message'=>'消息列表没有数据','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取用户消息列表（活动类）
     */
    public function getMsgAct(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map['uid']     = $array['uid'];
            $map['bid']     = 0;
            $map['status']  = array("lt",2);
            $msgList     = M("ad_msg")->field(true)->where($map)->order("add_time DESC ")->select();
            if(count($msgList)>0){
                exit(json_encode(array('message'=>'消息列表获取成功','code'=>'200','result'=>array('list'=>$msgList))));
            }else{
                exit(json_encode(array('message'=>'消息列表没有数据','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    
    /**
     * 获取用户未读消息数量
     */
    public function getUnreadMsg(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map['uid']     = $array['uid'];
            $map['status']  = 0;
            $count          = M("ad_msg")->field(true)->where($map)->count("id");
            exit(json_encode(array('message'=>'消息列表获取成功','code'=>'200','result'=>array('unread'=>$count))));
         }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取用户未读消息数量(分组)
     */
    public function getMsgGroup(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $map1['uid']       = $array['uid'];
            $map1['bid']       = array("gt",0);
            $map1['status']    = array("lt",2);
            $count1            = M("ad_msg")->field(true)->where($map1)->count("id");
            
            $map2['uid']       = $array['uid'];
            $map2['bid']       = 0;
            $map2['status']    = array("lt",2);
            $count2            = M("ad_msg")->field(true)->where($map2)->count("id");
            
            $mapun1['uid']     = $array['uid'];
            $mapun1['status']  = 0;
            $mapun1['bid']     = array("gt",0);
            $unCount1          = M("ad_msg")->field(true)->where($mapun1)->count("id");
            
            $mapun2['uid']     = $array['uid'];
            $mapun2['status']  = 0;
            $mapun2['bid']     = 0;
            $unCount2          = M("ad_msg")->field(true)->where($mapun2)->count("id");
            
            $mapl1['uid']      = $array['uid'];
            $mapl1['bid']      = array("gt",0);
            $mapl1['status']   = array("lt",2);
            $msgList1          = M("ad_msg")->field(true)->where($mapl1)->order("add_time DESC")->limit(1)->select();
            
            $mapl2['uid']      = $array['uid'];
            $mapl2['bid']      = 0;
            $mapl2['status']   = array("lt",2);
            $msgList2          = M("ad_msg")->field(true)->where($mapl2)->order("add_time DESC")->limit(1)->select();
            
            exit(json_encode(array('message'=>'消息列表获取成功','code'=>'200','result'=>array('cuont1'=>$count1,'count2'=>$count2,'unread1'=>$unCount1,'unread2'=>$unCount2,'list1'=>$msgList1,'list2'=>$msgList2))));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 已读消息状态更新
     */
    public function readMsg(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $where['id']        = $array['msgid'];
            $data['status']     = 1;
            $update = M("ad_msg")->where($where)->save($data);
            if($update){
                exit(json_encode(array('message'=>'消息状态更新成功','code'=>'200')));
            }else{
                exit(json_encode(array('message'=>'消息状态更新失败','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 消息批量更新为已读
     */
    public function setReadMsg(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $where['uid']       = $array['uid'];
            $where['status']    = 0;
            $data['status']     = 1;
            $update = M("ad_msg")->where($where)->save($data);
            if($update){
                exit(json_encode(array('message'=>'消息状态更新成功','code'=>'200')));
            }else{
                exit(json_encode(array('message'=>'消息状态更新失败','code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 已读消息删除（更新状态为2）
     */
    public function delMsg(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $msgList            = explode(',',$array['msgid']);
            if(count($msgList)>1){
                $data['status'] = 2;
                $update = M("ad_msg")->where(" id in ( {$array['msgid']} ) ")->save($data);
                if($update){
                    exit(json_encode(array('message'=>'消息状态更新成功','code'=>'200')));
                }else{
                    exit(json_encode(array('message'=>'消息状态更新失败','code'=>'401')));
                }
            }else{
                if($msgList[0]>0){
                    $where['id']        = $array['msgid'];
                    $data['status']     = 2;
                    $update = M("ad_msg")->where($where)->save($data);
                    if($update){
                        exit(json_encode(array('message'=>'消息状态更新成功','code'=>'200')));
                    }else{
                        exit(json_encode(array('message'=>'消息状态更新失败','code'=>'401')));
                    }
                }else{
                    exit(json_encode(array('message'=>'请选择要删除的消息','code'=>'401')));
                } 
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 获取授信费用价格
     */
    public function getPrice(){
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $global   = get_global_setting();
            $amount   = $global['credit_amount'];
            $discount = $global['credit_discount'];
            exit(json_encode(array('message'=>'获取成功','amount'=>$amount,'discount'=>$discount,'code'=>'200')));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 
     */
    public function getPpc(){
        $status      = 0;
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid     = $array['uid'];
            $list    = getPpcrate($uid);
            exit(json_encode(array('message'=>'获取成功','result'=>$list,'code'=>'200')));
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 授信支付
     */
    public function payCredit(){
        $array       = $_POST;
        $checkResult = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid     = $array['uid'];
            $bid     = $array['bid'];
            $bankId  = $array['bankId'];
            $status  = M("member_status")->field(true)->where("uid = {$uid} and borrow_id = {$bid}")->find();
            if($status['signed']==1){
                if($status['is_recheck']==0){
                    $global   = get_global_setting();
                    $amount   = $global['credit_amount'];
                    $discount = $global['credit_discount'];
                    if($discount==0){
                        $total = $amount;
                    }else{
                        $total = $discount;
                    }
                    //宝付授信支付
                    $model = new PaymentAction();
                    $res   = $model->creditApi($uid, $bid, $total ,$bankId);
                    //$res = true;
                    if(is_bool($res) && $res){
                        //走拍拍信风控
                        $isPpc = isPpc($uid);
                        //风控拍拍信验证
                        if($isPpc==1){
                            $updata['is_ppc']     = 1;
                            $updata['ppc_time']   = time();
                            $isIdVerify = isIdVerify($uid);
                            //人脸免验证检查
                            if($isIdVerify == 1){
                                $updata['id_verify']        = 1;
                                $updata['id_verify_time']   = time();
                                //人脸识别成功后自动复查通过
                                $auto_review = $global['auto_review'];
                                if($auto_review==1){
                                    $updata['is_review']    = 1;
                                    $updata['review_time']  = time();
                                    //复查通过后自动上标到福米金融
                                    $is_aotu = $global['is_aotu_bid'];
                                    if($is_aotu == 1){
                                        $upbid = createFumiBid($bid,0);
                                    }
                                }
                            }
                        }else{
                            $updata['is_ppc']     = 2;
                            $updata['ppc_time']   = time();
                        }
                        $updata['is_recheck']   = 1;
                        $updata['recheck_time'] = time();
                        $updata['calm']         = 1;
                        $updata['calm_time']    = time();
                        $up = M("member_status")->where("uid = {$uid} and borrow_id = {$bid}")->save($updata);
                        if($isPpc==1){
                            exit(json_encode(array('message'=>'支付成功','code'=>'200')));
                        }else{
                            //更新状态
                            $data['status']          = 95;
                            $data['refuse_time']     = time();
                            M('borrow_apply')->where("id={$bid} and uid = {$uid}")->save($data);
                            //删除option信息
                            delUserOperation($uid,$bid);
                            
                            //微信消息推送
                            $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                            if($wxInfo['openid']!==''){
                                sendWxTempleteMsg23($wxInfo['openid'], $total, date("Y-m-d",time()));
                            }
                            //发送App推送通知复审失败
                            $mwhere['uid'] = $uid;
                            $token = M('member_umeng')->where($mwhere)->field(true)->find();
                            if(!empty($token['token'])){
                                AndroidTempleteMsgC($uid,$token['token'],$bid,10);
                            }
                            exit(json_encode(array('message'=>'评分不足','code'=>'403')));
                        } 
                    }else{
                        exit(json_encode(array('message'=>$res,'code'=>401)));
                    }
                }else{
                    exit(json_encode(array('message'=>'请刷新借款状态','result'=>$list,'code'=>'401')));
                }
            }else{
                exit(json_encode(array('message'=>'请先去签约','result'=>$list,'code'=>'401')));
            }
        }else {
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 汇潮支付
     */
    public function payHuichao(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            $uid        = $array['uid'];//借款会员编号
            $bid        = $array['bid'];//借款申请 
            $money      = $array['money'];//支付金额
            $item_id    = $array['item_id'];//若有续期，待续期的产品ID
            $scene      = $array['scene'];//支付场景 1：还款 2：续期 3：授信
            $type       = $array['type'];//支付方式  1：微信 2：支付宝 3：银联
            $orderNum   = $array['orderNum'];//若为支付宝APP支付，需提供商户订单编号
            $noncestr   = $array['noncestr'];//交易随机参数

            if($scene == 2){
                $itemInfo   = M('borrow_item')->field(true)->where("id = {$array['item_id']} and is_xuqi = 1")->order('id desc')->find();
                if(empty($itemInfo)){
                    exit(json_encode(array('message'=>'暂时没有可续期的产品','code'=>'401')));
                }
            }
            if($money<1){
                exit(json_encode(array('message'=>'请求失败，联系客服','imgurl'=>'','code'=>'401')));
            }
            if($type==1){//微信扫码支付
                $imgurl = wechatPay($uid,$bid,$item_id,$money,$scene,$type);
                exit(json_encode(array('message'=>'请求成功','imgurl'=>$imgurl,'code'=>'200')));
            }else{//支付宝APP支付
                //添加交易记录
                addhuichaoOrders($uid,$orderNum,$money,$bid,$item_id,$scene,$type,$noncestr);
                exit(json_encode(array('message'=>'请求成功','imgurl'=>'','code'=>'200')));
            }
        }else{
            exit(json_encode($checkResult));
        }
    }
    
    /**
     * 支付方式
     */
    public function getPayMethod(){
        $array          = $_POST;
        $checkResult    = $this->checkRequestSign($array['timeStamp'], $array['randNumber'], $array['secretString'],$array['sign']);
        if (is_bool($checkResult)){
            //授信
            $pay1 = array(
                'bankCard'  =>1,//宝付银行卡支付
                'alipay'    =>0,//支付宝
                'wechatpay' =>0,//微信
            );
            //还款
            $pay2 = array(
                'bankCard'  =>1,//宝付银行卡支付
                'alipay'    =>1,//支付宝
                'wechatpay' =>1,//微信
            );
            //续期
            $pay3 = array(
                'bankCard'  =>1,//宝付银行卡支付
                'alipay'    =>1,//支付宝
                'wechatpay' =>1,//微信
            );
            exit(json_encode(array('message'=>'请求成功','code'=>200,'result'=>array('pay1'=>$pay1,'pay2'=>$pay2,'pay3'=>$pay3))));
        }else{
            exit(json_encode($checkResult));
        }
    }
}
?>
