<?php
use App\Library\Fuiou\Fuiou;
/**
 * 富友支付后台回调通知
 * @author Rubyzheng
 *
 */
class FuiouAction extends HCommonAction{
    
    /**
     * 插入富友会员开户授权状态记录
     * @param 接口异步返回的数组 $arr
     * @param 会员编号 $uid
     */
    private function creatUser($arr,$uid){ 
        $data['uid']                = $uid;
        $data['fuiou_id']           = $arr['login_id'];
        $data['contract_st']        = 1;
        $data['auth_st']            = $arr['auth_st'];
        $data['usr_attr']           = $arr['usr_attr'];
        $data['auto_repay_term']    = $arr['auto_repay_term'];
        $data['auto_repay_amt']     = $arr['auto_repay_amt'];
        $data['auto_fee_term']      = $arr['auto_fee_term'];
        $data['auto_fee_amt']       = $arr['auto_fee_amt'];
        $data['add_time']           = time();
        M("fuiou_user_status")->add($data);
    }
    
    /**
     * 修改富友会员开户授权状态记录
     * @param 接口异步返回的数组 $arr
     * @param 会员编号 $uid
     * @param 用户类型 $type 1:出借人 2:借款人
     */
    private function updataUser($arr,$uid,$type){ 
        $status  = M("fuiou_user_status")->where("uid = '{$uid}'")->field('id')->find();
        $user    = M("members")->where("id = '{$uid}'")->field('fuiou_id,id')->find();

        $data['uid']                = $user['id'];
        $data['fuiou_id']           = $user['fuiou_id'];
        $data['contract_st']        = 1;
        $data['auth_st']            = $arr['auth_st'];
        $data['usr_attr']           = $type;
        $data['auto_repay_term']    = $arr['auto_repay_term'];
        $data['auto_repay_amt']     = $arr['auto_repay_amt'];
        $data['auto_fee_term']      = $arr['auto_fee_term'];
        $data['auto_fee_amt']       = $arr['auto_fee_amt'];
        $data['add_time']           = time();
        if(empty($status)){
            M("fuiou_user_status")->add($data);
        }else{
            M("fuiou_user_status")->where("uid = '{$uid}'")->save($data);
        }
    }
 
    /**
     * 银行卡插入
     * @param unknown $arr
     * @param unknown $uid
     */
     private function creatBank($arr,$uid){
        $bank = M("fuiou_user_bank")->where("uid = {$uid}")->field('id')->find();
        if(empty($bank)){
            $data['uid']                = $uid;
            $data['city_code']          = $arr['city_id'];
            $data['parent_bank_code']   = $arr['parent_bank_id'];
            $data['bank_nm']            = $this->unicodeDecode($arr['bank_nm']);
            $data['card_no']            = $arr['card_no'];
            $data['add_time']           = time();
            M("fuiou_user_bank")->add($data);
        }
    }

    /**
     * 个人开户回调
     * @throws Exception
     */
    public function regUserByFive(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\RegUserByFive\Request();
            $res    = new App\Library\Fuiou\Protocol\RegUserByFive\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("个人开户返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("个人开户接口返回签名错误!");
                }else{
					wqbLog("个人开户返回数据OK:".json_encode($res));
                    $mchnt_txn_ssn = $res['mchnt_txn_ssn'];
                    $user = M('fuiou_order_user')->field('uid,status,bid')->where("mchnt_txn_ssn='{$mchnt_txn_ssn}'")->find();
                    if($res['resp_code']==0000){
                        $fuiouId = $res['login_id'];
                        if($res['auth_st'] != '000000000000'){
                            $status = M("fuiou_user_status")->where("uid = {$user['uid']}")->field('id')->find();
                            //更新富友会员开户授权状态记录
                            if(empty($status)){
                                $this->creatUser($res,$user['uid']);
                            }
                        }
                        //插入开户银行卡信息
                        $this->creatBank($res,$user['uid']);
                        //更新会员表的富友会员ID
                        $member['fuiou_id'] = $fuiouId;
                        M('members')->where("id={$user['uid']}")->save($member);
                        if($user['status'] == 0){
                            //更新交易记录表的状态为成功
                            $save['status']      = 1;
                            $save['login_id']    = $fuiouId;
                            $save['resp_desc']   = $res['resp_desc'];
                            $save['notify_time'] = time();
                            $orderwhere['mchnt_txn_ssn'] = $mchnt_txn_ssn;
                            M('fuiou_order_user')->where($orderwhere)->save($save);
                        }

                        $mstatus = M("member_status")->where("uid = {$user['uid']} and borrow_id = {$user['bid']}")->field('first_trial')->find();
                        //未处理状态进行处理
                        if($mstatus['first_trial'] == 0){
                            //请求众安数据
                            $musr = M("members")->where("id = {$user['uid']}")->field('iphone,is_white,is_gold')->find();
                            $info  = M("member_info")->field('id,real_name,id_card')->where("uid = {$user['uid']}")->find();
                            $type  = getZhongan($info['id_card'],$musr['iphone'],$info['real_name']);
                            //非白名单众安风控不通过后初审拒绝
                            if($musr['is_white']==0 && $musr['is_gold']==0){
                                //请求众安数据
                                $iszan = zanType($type);
                                if($iszan==0){
                                    $data['first_trial']       = 2;
                                    $data['first_trial_time']  = time();
                                    $data['mid_tree']          = 2;
                                    $data['mid_tree_time']     = time();
                                    $data['uid']               = $user['uid'];
                                    $data['borrow_id']         = $user['bid'];
                                    M("member_status")->add($data);
                                    
                                    $ubdata['status']      = "98";
                                    $ubdata['refuse_time'] = time();
                                    delUserOperation($user['uid'],$user['bid']);
                                    M("borrow_apply")->where("uid = {$user['uid']} and id = {$user['bid']}")->save($ubdata);
                                
                                    //初审失败推送
                                    $binfo  = M("borrow_apply")->where("id = {$user['bid']}")->field("money,add_time")->find();
                                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$user['uid']}")->find();
                                    if($wxInfo['openid']!==""){
                                        sendWxTempleteMsg2($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                                    }

                                    //发送App推送通知初审拒绝
                                    $mwhere['uid'] = $user['uid'];
                                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                                    if(!empty($token['token'])){
                                        AndroidTempleteMsg2($user['uid'],$token['token'],$user['bid']);
                                    }
                                    wqbLog("众安数据请求失败,评分不足");
                                }else{
                                    //用户操作记录
                                    $data['operation'] = "/Borrow/verifyUserStatus";
                                    $data['orderNum']  = 3;
                                    $id = M("user_operation")->where("uid = {$user['uid']} AND borrow_id = {$user['bid']}")->save($data);
                                }
                            }else{
                                //用户操作记录
                                $data['operation'] = "/Borrow/verifyUserStatus";
                                $data['orderNum']  = 3;
                                $id = M("user_operation")->where("uid = {$user['uid']} AND borrow_id = {$user['bid']}")->save($data);
                            }
                        }
                    }else{
                        if($user['status'] == 0){
                            //更新交易记录表的状态为失败
                            $save['status']      = 2;
                            $save['notify_time'] = time();
                            $save['resp_desc']   = $res['resp_desc'];
                            $orderwhere['mchnt_txn_ssn'] = $mchnt_txn_ssn;
                            M('fuiou_order_user')->where($orderwhere)->save($save);
                        }
                    }
                    echo "success";
                }
            } 
        }
    }
    
    
    /**
     * 绑卡
     * @throws Exception
     */
    public function bindCard(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\BindCard\Request();
            $res    = new App\Library\Fuiou\Protocol\BindCard\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("绑卡返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("接口返回签名错误!");
                }else{
                    wqbLog("绑卡返回数据接口OK:".json_encode($res));
                    if($res['resp_code'] == 0000){//成功
                        $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                        $user = M("members")->where("fuiou_id = {$res['login_id']}")->field('id')->find();
                        creatBank($res,$user['id']);
                        if($order['status'] == 0){
                            $ordersave['login_id']       = $fuiouId['fuiou_id'];
                            $ordersave['status']         = 1;
                            $ordersave['reserved']       = $res['reserved'];
                            $ordersave['notify_time']    = time();
                            M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->save($ordersave);
                        }
                    }else{
                        //请求状态更新
                        $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                        if($order['status'] == 0){
                            $ordersave['status']         = 2;
                            $ordersave['resp_desc']      = $res['resp_desc'];
                            $ordersave['notify_time']    = time();
                            M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->save($ordersave); 
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 解绑
     * @throws Exception
     */
    public function unbindCard(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\UnBindCard\Request();
            $res    = new App\Library\Fuiou\Protocol\UnBindCard\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("解绑银行卡返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("接口返回签名错误!");
                }else{
                    wqbLog("解绑银行卡返回数据接口OK:".json_encode($res));
                    if($res['resp_code'] == 0000){//成功
                        $user = M("members")->where("fuiou_id = {$res['login_id']}")->field('id')->find();
                        $bank = M("fuiou_user_bank")->where("uid = {$user['id']}")->field('uid')->find();
                        if(!empty($bank)){
                            M("fuiou_user_bank")->where("uid = {$user['id']}")->delete();
                        }
                        //请求状态更新
                        $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                        if($order['status'] == 0){
                            $ordersave['login_id']       = $fuiouId['fuiou_id'];
                            $ordersave['status']         = 1;
                            $ordersave['reserved']       = $res['reserved'];
                            $ordersave['notify_time']    = time();
                            M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->save($ordersave);
                        }
                    }else{
                        //请求状态更新
                        $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                        if($order['status'] == 0){
                            $ordersave['status']         = 2;
                            $ordersave['resp_desc']      = $res['resp_desc'];
                            $ordersave['notify_time']    = time();
                            M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$res['mchnt_txn_ssn']}' ")->save($ordersave); 
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 提现
     * @throws Exception
     */
    public function withdraw(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\Withdraw\Request();
            $res    = new App\Library\Fuiou\Protocol\Withdraw\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("提现返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("提现接口返回签名错误!");
                }else{
                    wqbLog("提现返回数据接口OK:".json_encode($res));
                    $nid = $res['mchnt_txn_ssn'];
                    $withdraw = M('member_withdraw')->where(" nid = '{$nid}' ")->field('withdraw_status')->find();
                    if($res['resp_code']!="0000"){
                        //更新交易记录表的状态为失败
                        if($withdraw['withdraw_status'] == 0){
                            $save['withdraw_status']    = 2;
                            M('member_withdraw')->where(" nid = '{$nid}' ")->save($save);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * 借款人授权后台通知接口
     * @throws Exception
     */
    public function borrowerGrant(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\LenderGrant\Request();
            $res    = new App\Library\Fuiou\Protocol\LenderGrant\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("借款人授权返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("授权接口返回签名错误!");
                }else{
                    wqbLog("借款人授权返回数据接口OK:".json_encode($res));
                    $mchnt_txn_ssn = $res['mchnt_txn_ssn'];
                    if($res['resp_code']==0000){
                        $user = M('fuiou_order_user')->field('uid,status')->where("mchnt_txn_ssn='{$mchnt_txn_ssn}'")->find();
                        //更新富友会员开户授权状态记录
                        $fuiouId = M('members')->where("id = {$user['uid']}")->field('fuiou_id,usr_attr')->find();
                        $this->updataUser($res,$user['uid'],$fuiouId['usr_attr']);
                        if($user['status'] == 0){
                            //更新交易记录表的状态为成功
                            $save['status']      = 1;
                            $save['login_id']    = $fuiouId['fuiou_id'];
                            $save['resp_desc']   = $res['resp_desc'];
                            $save['notify_time'] = time();
                            M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$mchnt_txn_ssn}' ")->save($save);
                        }
                    }else{
                        $user = M('fuiou_order_user')->field('uid,status')->where("mchnt_txn_ssn='{$mchnt_txn_ssn}'")->find();
                        if($user['status'] == 0){
                            //更新交易记录表的状态为失败
                            $save['status']      = 2;
                            $save['notify_time'] = time();
                            $save['resp_desc']   = $res['resp_desc'];
                            M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$mchnt_txn_ssn}' ")->save($save);
                        }
                    }
                    echo "success";
                }
            }
        }
    }
    
    /**
     * 验密解冻
     * @throws Exception
     */
    public function passwordFreeze(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\PasswordFreeze\Request();
            $res    = new App\Library\Fuiou\Protocol\PasswordFreeze\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("验密冻结返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("授权接口返回签名错误!");
                }else{
                    $mchnt_txn_ssn       = $res['mchnt_txn_ssn'];
                    if($res['resp_code']=="0000"){
                        $data['status']      = 1;
                        $data['contract_no'] = $res['contract_no'];
                        M('fuiou_order_freeze')->where("mchnt_txn_ssn = '{$mchnt_txn_ssn}'")->save($data);
                    }else{
                        $data['status']      = 2;
                        M('fuiou_order_freeze')->where("mchnt_txn_ssn = '{$mchnt_txn_ssn}'")->save($data);
                    }
                    wqbLog("验密冻结返回数据接口OK:".json_encode($res));
                }
            }
        }
    }
    
    /**
     * 验密解冻
     * @throws Exception
     */
    public function passwordUnfreeze(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\PasswordUnfreeze\Request();
            $res    = new App\Library\Fuiou\Protocol\PasswordUnfreeze\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("验密解冻返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("验密解冻接口返回签名错误!");
                }else{
					if($res['resp_code']=="0000"){
                        $mchnt_txn_ssn       = $res['mchnt_txn_ssn'];
                        
                        $data['is_unfreeze'] = 1;
                        M('fuiou_order_freeze')->where("mchnt_txn_ssn_un = '{$mchnt_txn_ssn}'")->save($data);
                    }
                    wqbLog("验密解冻返回数据接口OK:".json_encode($res));
                }
            }
        }
    }
    
    /**
     * 直连冻结
     * @throws Exception
     */
    public function freeze(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\Freeze\Request();
            $res    = new App\Library\Fuiou\Protocol\Freeze\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("冻结返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("授权接口返回签名错误!");
                }else{
                    wqbLog("冻结返回数据接口OK:".json_encode($res));
                }
            }
        }
    }
    
    /**
     * 直连解冻
     * @throws Exception
     */
    public function Unfreeze(){
        if(IS_POST){
            $req    = new App\Library\Fuiou\Protocol\Unfreeze\Request();
            $res    = new App\Library\Fuiou\Protocol\Unfreeze\Response();
            $res    = $_POST;
            if(!empty($res)){
                $fuiou  = new Fuiou($req,$res);
                $check  = $fuiou->checkSign($res);
                if(!$check){
                    wqbLog("解冻返回数据接口失败:签名错误!".json_encode($res));
                    throw new Exception("验密解冻接口返回签名错误!");
                }else{
                    wqbLog("解冻返回数据接口OK:".json_encode($res));
                }
            }
        }
    }
    
    
    public function postparm(){
        wqbLog(json_encode($_POST));
        echo "mchnt_txn_ssn:".$_POST['mchnt_txn_ssn'];
		echo " <br/>";
        echo "resp_code:".$_POST['resp_code'];
        wqbLog(unicodeDecode($_POST['resp_desc']));
    }
    
    
    /**
     * 提现通知
     */
    public function withdrawNotice(){
        $amt           = $_POST['amt'];
        $mchnt_cd      = $_POST['mchnt_cd'];
        $mchnt_txn_dt  = $_POST['mchnt_txn_dt'];
        $mchnt_txn_ssn = $_POST['mchnt_txn_ssn'];
        $mobile_no     = $_POST['mobile_no'];
        $signature     = $_POST['signature'];
        $signStr       = $amt."|".$mchnt_cd."|".$mchnt_txn_dt."|".$mchnt_txn_ssn."|".$mobile_no."|";
        
        $check = checkfuiouSign($signStr,$signature);
        if(!$check){
            wqbLog("提现通知失败:签名错误!".json_encode($check));
            throw new Exception("充值通知返回签名错误!");
        }else{
            if($_POST['resp_code'] == 0000){//成功
                $withdraw = M('member_withdraw')->where(" nid = '{$mchnt_txn_ssn}' ")->field(true)->find();
                if($withdraw['withdraw_status']==0){
                    //更新提现状态
                    $save['withdraw_status']    = 1;
                    $save['deal_time']          = time();
                    M('member_withdraw')->where(" nid = '{$mchnt_txn_ssn}' ")->save($save);
                    $appsa['is_withdraw'] = 1;
                    M('member_status')->where("borrow_id = {$withdraw['bid']}")->save($appsa);

                    echo "SUCCESS";
                    return "SUCCESS";
                }
            }else{
                $withdraw = M('member_withdraw')->where(" nid = '{$mchnt_txn_ssn}' ")->field(true)->find();
                if($withdraw['withdraw_status'] == 0){
                    $save['withdraw_status']    = 2;
                    $save['deal_time']          = time();
                    M('member_withdraw')->where(" nid = '{$mchnt_txn_ssn}' ")->save($save);
                    echo "SUCCESS";
                    return "SUCCESS";
                }
            }
        }
    }
    
    //提现退票通知
    public function refundNotice(){
        $amt           = $_POST['amt'];
        $mchnt_cd      = $_POST['mchnt_cd'];
        $mchnt_txn_dt  = $_POST['mchnt_txn_dt'];
        $mchnt_txn_ssn = $_POST['mchnt_txn_ssn'];
        $mobile_no     = $_POST['mobile_no'];
        $remark        = $_POST['remark'];
        $signature     = $_POST['signature'];
        $signStr       = $amt."|".$mchnt_cd."|".$mchnt_txn_dt."|".$mchnt_txn_ssn."|".$mobile_no."|".$remark;
        
        $check = checkfuiouSign($signStr,$signature);
        if(!$check){
            wqbLog("提现退票通知失败:签名错误!".json_encode($res));
            throw new Exception("充值通知返回签名错误!");
        }else{
            wqbLog($mchnt_txn_ssn."提现申请有退票");
            
            $save['withdraw_status']    = 3;
            M('member_withdraw')->where(" nid = '{$mchnt_txn_ssn}' ")->save($save);
            echo "SUCCESS";
            return "SUCCESS";
        }
    }
}

