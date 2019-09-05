<?php 
//富友回调处理
class FuiouBackAction extends HCommonAction {
	//富友个人开户回调页面地址
    public function regUserByFive(){
        if(!empty($_POST)){
            $user = M("members")->where("iphone = '{$_POST['mobile_no']}'")->field('id,sina_id,iphone,is_white,is_gold')->find();
            $uid = $user['id'];
            $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,bid,plat')->find();
            if($_POST['resp_code'] == 0000){//成功
                //请求状态更新
                if($order['status'] == 0){
                    $ordersave['login_id']       = $_POST['login_id'];
                    $ordersave['status']         = 1;
                    $ordersave['reserved']       = $_POST['reserved'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave);
                }
                //添加银行卡信息
                $this->addbank($uid,$_POST);

                if($_POST['auth_st'] != '000000000000'){//有授权操作
                    $status = M("fuiou_user_status")->where("uid = {$uid}")->field('id')->find();
                    if(empty($status)){
                        //插入授权信息
                        $this->addAuthst($uid,$_POST);
                    } 
                } 
     
                //更新用户表富友ID  
                $memsave['fuiou_id']         = $_POST['login_id'];
                $memwhere['id']              = $uid;
                M("members")->where($memwhere)->save($memsave); 

                //会员登录风控接口
                $model = new CheckUserAction();
                $model->requestRegistApi($uid);
                set_member_invite_code($uid);
                $info  = M("member_info")->field('id,real_name,id_card')->where("uid = {$uid}")->find();
                $type  = getZhongan($info['id_card'],$user['iphone'],$info['real_name']);
                //非白名单众安风控不通过后初审拒绝
                if($user['is_white']==0 && $user['is_gold']==0){
                    //请求众安数据
                    $iszan = zanType($type);
                    if($iszan==0){
                        $data['first_trial']       = 2;
                        $data['first_trial_time']  = time();
                        $data['mid_tree']          = 2;
                        $data['mid_tree_time']     = time();
                        $data['uid']               = $uid;
                        $data['borrow_id']         = $order['bid'];
                        M("member_status")->add($data);
                        
                        $ubdata['status']      = "98";
                        $ubdata['refuse_time'] = time();
                        delUserOperation($uid,$order['bid']);
                        M("borrow_apply")->where("uid = {$uid} and id = {$order['bid']}")->save($ubdata);
                    
                        //初审失败推送
                        $binfo  = M("borrow_apply")->where("id = {$order['bid']}")->field("money,add_time")->find();
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg2($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                        }

                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $uid;
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($uid,$token['token'],$order['bid']);
                        }

                        if($order['plat'] == 1){//app
                            //跳转待处理页面
                            $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>1));
                        }else{
                            //跳转评分失败页
                            $this->redirect('/Borrow/refuse');
                        }
                    }else{
                        //用户操作记录
                        $data['operation'] = "/Borrow/verifyUserStatus";
                        $data['orderNum']  = 3;
                        $id = M("user_operation")->where("uid = {$uid} AND borrow_id = {$order['bid']}")->save($data);

                        if($order['plat'] == 1){//app
                            //跳转待处理页面
                            $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>1));
                        }else{
                            //跳转验证身份页面
                            $this->redirect('/Borrow/verifyUserStatus');
                        }
                    }
                }else{
                    //用户操作记录
                    $data['operation'] = "/Borrow/verifyUserStatus";
                    $data['orderNum']  = 3;
                    $id = M("user_operation")->where("uid = {$uid} AND borrow_id = {$order['bid']}")->save($data);
                    if($order['plat'] == 1){//app
                        //跳转待处理页面
                        $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>1));
                    }else{
                        //跳转成功页面
                        $this->redirect('/Borrow/verifyUserStatus');
                    }
                }
            }else{
                //请求状态更新
                if($order['status'] == 0){
                    $ordersave['status']         = 2;
                    $ordersave['resp_desc']      = $_POST['resp_desc'];
                    $ordersave['notify_time']    = time();
                    $orderwhere['mchnt_txn_ssn'] = $_POST['mchnt_txn_ssn'];
                    M("fuiou_order_user")->where($orderwhere)->save($ordersave); 
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>1));
                }else{
                    //跳转失败页面
                    $this->redirect('fail',array('type'=>1,'msg'=>$this->unicodeDecode($_POST['resp_desc']),'url'=>'ByFive'));
                }
            }
        }
    }

    //富友授权页面回调地址(借款人)
    public function userGrant(){
        if(!empty($_POST)){
            if($_POST['resp_code'] == 0000){//成功
                //请求状态更新
                $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                //更新授权信息
                $fuiouId = M('members')->where("id = {$order['uid']}")->field('fuiou_id,usr_attr')->find();
                $this->saveAuthst($order['uid'],$_POST,$fuiouId['usr_attr']);
                if($order['status'] == 0){
                    $ordersave['login_id']       = $fuiouId['fuiou_id'];
                    $ordersave['status']         = 1;
                    $ordersave['reserved']       = $_POST['reserved'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave);
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>2));
                }else{
                    //跳转成功页面
                    $this->redirect('success',array('type'=>2));
                }
            }else{
                //请求状态更新
                $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                if($order['status'] == 0){
                    $ordersave['status']         = 2;
                    $ordersave['resp_desc']      = $_POST['resp_desc'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave); 
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>2));
                }else{
                    //跳转失败页面
                    $this->redirect('fail',array('type'=>2,'msg'=>$this->unicodeDecode($_POST['resp_desc']),'url'=>'Grant'));
                }
            }
        }
    }

    //富友绑卡页面回调地址
    public function bindCard(){
        if(!empty($_POST)){
            $uid = M("members")->where("fuiou_id = '{$_POST['login_id']}'")->field('id')->find();
            $uid = $uid['id'];
            if($_POST['resp_code'] == 0000){
                //请求状态更新
                $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                $this->addbank($uid,$_POST);
                if($order['status'] == 0){
                    $ordersave['login_id']       = $fuiouId['fuiou_id'];
                    $ordersave['status']         = 1;
                    $ordersave['reserved']       = $_POST['reserved'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave);
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>3));
                }else{
                    //跳转成功页面
                    $this->redirect('success',array('type'=>5));
                }
            }else{
                //请求状态更新
                $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                if($order['status'] == 0){
                    $ordersave['status']         = 2;
                    $ordersave['resp_desc']      = $_POST['resp_desc'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave); 
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>3));
                }else{
                    //跳转失败页面
                    $this->redirect('fail',array('type'=>5,'msg'=>$this->unicodeDecode($_POST['resp_desc']),'url'=>'Card'));
                }
            }
        }
    }

    //富友解绑页面回调地址
    public function unbindCard(){
        if(!empty($_POST)){
            $uid = M("members")->where("fuiou_id = '{$_POST['login_id']}'")->field('id')->find();
            $uid = $uid['id'];
            if($_POST['resp_code'] == 0000){
                $bank = M("fuiou_user_bank")->where("uid = {$uid}")->field('uid')->find();
                if(!empty($bank)){
                    M("fuiou_user_bank")->where("uid = {$uid}")->delete();
                }

                //请求状态更新
                $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                if($order['status'] == 0){
                    $ordersave['login_id']       = $fuiouId['fuiou_id'];
                    $ordersave['status']         = 1;
                    $ordersave['reserved']       = $_POST['reserved'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave);
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>4));
                }else{
                    //跳转成功页面
                    $this->redirect('success',array('type'=>4));
                }
            }else{
                //请求状态更新
                $order = M('fuiou_order_user')->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->field('status,uid,plat')->find();
                if($order['status'] == 0){
                    $ordersave['status']         = 2;
                    $ordersave['resp_desc']      = $_POST['resp_desc'];
                    $ordersave['notify_time']    = time();
                    M("fuiou_order_user")->where(" mchnt_txn_ssn = '{$_POST['mchnt_txn_ssn']}' ")->save($ordersave); 
                }
                if($order['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>4));
                }else{
                    //跳转失败页面
                    $this->redirect('fail',array('type'=>4,'msg'=>$this->unicodeDecode($_POST['resp_desc']),'url'=>'Decard'));
                }
            }
        }
    }

    //富友提现页面回调地址
    public function Withdraw(){
        if(!empty($_POST)){
            $withdraw = M("member_withdraw")->where("nid = '{$_POST['mchnt_txn_ssn']}'")->field(true)->find();
            if($_POST['resp_code'] == 0000){
                //同步余额
                userMoney($withdraw['uid']);

                if($withdraw['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>5));
                }else{
                    //跳转成功页面 
                    $this->redirect('success',array('type'=>3));
                }
            }else{
                if($withdraw['withdraw_status'] == 0){
                    $save['withdraw_status']    = 2;
                    M('member_withdraw')->where(" nid = '{$_POST['mchnt_txn_ssn']}' ")->save($save);
                }
                if($withdraw['plat'] == 1){//app
                    //跳转待处理页面
                    $this->redirect('wait',array('mchnt_txn_ssn'=>$_POST['mchnt_txn_ssn'],'type'=>5));
                }else{
                    //跳转失败页面
                    $this->redirect('fail',array('type'=>3,'msg'=>$this->unicodeDecode($_POST['resp_desc'])));
                }
            }
        }
    }
	//---------------------------------------------------------------------------调用方法--------------------------------------------------//
	//四码开户
	public function ByFive(){
        if(empty($_GET['uid'])){
            $uid = $_SESSION['uid'];
        }else{
            $uid = $_GET['uid'];
        }
        if(empty($_GET['bid'])){
            $bid = $_SESSION['bid'];
        }else{
            $bid = $_GET['bid'];
        }
        if(empty($_GET['ap'])){
            $ap = 0;
        }else{
            $ap = $_GET['ap'];
        }
        if(empty($bid)){
            $this->redirect('/Member/currborrow');
        }else if(empty($uid)){
            $this->redirect('/Member/regist');
        }else{
            $is_transfer = M("members")->where("id = '{$uid}'")->field('usr_attr')->find();
            $res = regUserByFive($uid,$is_transfer['usr_attr'],1,$bid,$ap);
            echo $res;
        }
	}

	//授权页面
	public function Grant(){
		if(empty($_GET['uid'])){
            $uid = $_SESSION['uid'];
        }else{
            $uid = $_GET['uid'];
        }
        if(empty($_GET['ap'])){
            $ap = 0;
        }else{
            $ap = $_GET['ap'];
        }
        if(empty($uid)){
            $this->redirect('/Member/regist');
        }else{
            $fuiou_id = M("members")->where("id = '{$uid}'")->field('fuiou_id,usr_attr')->find();
            $res = userGrant($fuiou_id['fuiou_id'],1,$fuiou_id['usr_attr'],$ap);
            echo $res;
        }
	}
    //--------------------------------------------------临时接口----------------------------------------------//
	//查询用户信息
	public function demo2(){
		$res=userQuery($_GET['uid'],$_GET['phone']);
		header("Content-Type: text/html;charset=utf-8");
        dump($res);die();
	}
	//查询用户余额
	public function semoney(){
		$m = userMoney($_GET['uid']);
		dump($m);die();
	}
    //充值
    public function chongzhi(){
        $fuiou = M('members')->where("id = {$_GET['uid']}")->field('fuiou_id')->find();
        $res = quickRecharge($_GET['uid'],$fuiou['fuiou_id'],0,$_GET['money']);
        echo $res;
    }

	//绑定银行卡
	public function Card(){
		if(empty($_GET['uid'])){
            $uid = $_SESSION['uid'];
        }else{
            $uid = $_GET['uid'];
        }
        if(empty($_GET['ap'])){
            $ap = 0;
        }else{
            $ap = $_GET['ap'];
        }
        if(empty($uid)){
            $this->redirect('/Member/regist');
        }else{
            $fuiou = M('members')->where("id = {$uid}")->field('fuiou_id')->find();
            $res   = bindCard($uid,$fuiou['fuiou_id'],1,$ap);
            echo $res;
        }
	}

	//解绑银行卡
	public function Decard(){
		if(empty($_GET['uid'])){
            $uid = $_SESSION['uid'];
        }else{
            $uid = $_GET['uid'];
        }
        if(empty($_GET['ap'])){
            $ap = 0;
        }else{
            $ap = $_GET['ap'];
        }
        if(empty($uid)){
            $this->redirect('/Member/regist');
        }else{
            $fuiou = M('members')->where("id = {$uid}")->field('fuiou_id')->find();
            $res   = unbindCard($uid,$fuiou['fuiou_id'],1,$ap);
            echo $res;
        }
	}

	//提现
	public function Withd(){
		if(empty($_GET['uid'])){
            $uid = $_SESSION['uid'];
        }else{
            $uid = $_GET['uid'];
        }
        if(empty($_GET['ap'])){
            $ap = 0;
        }else{
            $ap = $_GET['ap'];
        }
        if(empty($_GET['bid'])){
            $bid = $_POST['bid'];
        }else{
            $bid = $_GET['bid'];
        }
        if(empty($uid)){
            $this->redirect('/Member/regist');
        }else{
            $money = userMoney($uid);
            $where['a.id'] = $bid;
            $where['a.audit_status'] = 5;
            $where['a.status'] = 4;
            $where['b.status'] = 0;
            $where['a.uid'] = $uid;
            $apply = M("borrow_apply a")->join("ml_borrow_detail b ON a.id=b.borrow_id")->where($where)->field("b.capital,a.id")->find();
            if($money['ca_balance'] > $apply['capital']){
                $fuiou = M('members')->where("id = {$uid}")->field('fuiou_id')->find();
                withdraw($uid,$fuiou['fuiou_id'],1,$apply['capital'],$apply['id'],$ap);
            }else{
                $this->error('操作异常,请重新操作');
            }
        }
	}

	//-----------------------------------------------------------------------------------封装---------------------------------------------------------
	
	/**
     * unicode转中文
     * @param 需要转的字符串  $unicode_str
     */
    private function unicodeDecode($unicode_str){
        $json = '{"str":"'.$unicode_str.'"}';
        $arr  = json_decode($json,true);
        return $arr['str'];
    }


	//添加银行卡信息
	public function addBank($uid,$arr){
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

	//插入授权信息
	public function addAuthst($uid,$arr){
			$data['uid']                = $uid;
	        $data['fuiou_id']           = $arr['login_id'];
	        $data['contract_st']        = $arr['contract_st'];
	        $data['auth_st']            = $arr['auth_st'];
	        $data['usr_attr']           = $arr['usr_attr'];
	        $data['auto_repay_term']    = $arr['auto_repay_term'];
	        $data['auto_repay_amt']     = $arr['auto_repay_amt'];
	        $data['auto_fee_term']      = $arr['auto_fee_term'];
	        $data['auto_fee_amt']       = $arr['auto_fee_amt'];
	        $data['add_time']           = time();
	        M("fuiou_user_status")->add($data);
	}

	//更新授权信息or插入
	public function saveAuthst($uid,$arr,$type){
			$status  = M("fuiou_user_status")->where("uid = '{$uid}'")->field('id')->find();
			$user    = M("members")->where("id = '{$uid}'")->field('fuiou_id,id')->find();
	        $data['fuiou_id']           = $user['fuiou_id'];
	        $data['uid']                = $user['id'];
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

	//-----------------------------------------------------------------------------------页面-----------------------------------------------------------------//
	
	//成功页面(共通)
	public function Success(){
		$this->assign('type',$_GET['type']);
		$this->display();
	}

	//失败页面(共通)
	public function Fail(){
		$url = "/fuiouBack/".$_GET['url'];
		$this->assign('type',$_GET['type']); 
		$this->assign('msg',$_GET['msg']);
		$this->assign('url',$url);
		$this->display();
	}

    //待处理页面(app)
    public function wait(){
        $mchnt_txn_ssn = $_GET['mchnt_txn_ssn'];
        $this->assign('mchnt_txn_ssn',$mchnt_txn_ssn);
        $this->assign('type',$_GET['type']);
        $this->display();
    }
}   
?>