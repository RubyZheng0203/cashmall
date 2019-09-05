<?php
class MemberAction extends HCommonAction{
    
    /**
     * 首页的banner
     */
    public function  banner(){
      $ad = M("ad")->field(true)->where("id = 2")->find();
      $ad['content'] = unserialize($ad['content']);
      foreach ($ad['content'] as $key=>$val){
         $ad['content'][$key]['img'] = "/".$val['img'];
      }
     if ($ad){
        exit(json_encode(array('message'=>"成功",'code'=>200,'result'=>$ad)));
     }else {
         exit(json_encode(array('message'=>"失败",'code'=>401)));
     }
    } 
    
	public function index(){
	    if (!isset($_SESSION['uid'])){
	        $this->redirect('Member/regist');
	    }
	    $uid  = $_SESSION['uid'];
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
        $this->assign('memberInfo',$memberInfo);
        $this->display(); 
	}
	
	/**
	 * 注册页面
	 */
	public function regist(){
	    $this->display();
	}
	
	/**
	 * insert新用户数据
	 */
    public function addUser(){
	    $verifyCode  = $_POST['verifyCode'];
	    $phone       = $_POST['phone'];
	    $recoCode    = $_POST['recoCode'];
	    $codeInfo    = M("member_code")->where("phone = '{$phone}' and reg_code = '{$verifyCode}'")->count('phone');
	    if ($codeInfo <= 0){
	       ajaxmsg('手机验证码错误',0);
	    }else{
	        $memberInfo = M("members")->field('id,usr_attr')->where("iphone = '{$phone}'")->find();
	        if (!empty($memberInfo)){
	            $data['last_time']    = time();
	            $data['last_ip']      = get_client_ip();
	            $data['last_address'] = get_ipAddress($data['last_ip']);
	            if($memberInfo['usr_attr'] == 0){
                    $data['usr_attr'] = 2; //默认借款人
                }
	            $savedata = M("members")->where("iphone = '{$phone}'")->save($data);
	            
	            $operationInfo  = M("user_operation")->field('operation')->where("uid = {$memberInfo['id']}")->find();
	            if (empty($operationInfo)){
	                $operation = "/Member/currborrow";
	            }else{
					$operation = $operationInfo['operation'];
				}
	            addUserWxBindInfo($memberInfo['id'], $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
	            $tick = "USERLOGIN".time().$memberInfo['id']; 

	            $where['status'] = 4;
	            $where['uid'] = $memberInfo['id'];
	            $appct = M("borrow_apply")->where($where)->count();
	            if($appct > 0){
	            	$operation = "/Repayment/index";
	            }
	            session('uid',$memberInfo['id']);
	            session('user_phone',$phone);
	            ajaxmsg($tick.",".$memberInfo['id'].",login,".$operation,1);
	        }else {
	            $mem = M('members')->field('id')->where("invite_code='{$recoCode}'")->find();
	            if(session("Promotion_temp")!='') {
	                $data['promotion_code'] = session("Promotion_temp");
	                session('Promotion_temp','');
	            }
	            
	            $data['iphone']       = $phone;
	            $data['reg_time']     = time();
	            $data['reg_ip']       = get_client_ip();
	            $data['reg_address']  = get_ipAddress($data['reg_ip']);
	            $data['last_time']    = time();
	            $data['last_ip']      = get_client_ip();
	            $data['last_address'] = get_ipAddress($data['last_ip']);
	            $data['type']         = 1;
	            $data['usr_attr'] = 2; //默认借款人
	            //$data['recommend_id'] = $mem['id'];
	            $type = memberType($phone);
	            if($type==1){
	                $data['is_black'] = 1;
	            }else if($type==2){
	                $data['is_white'] = 1;
	            }else if($type==3){
	                $data['is_gray']  = 1;
	            }
	            $res = M("members")->add($data);
	            if ($res){
	                session('uid',$res);
	                session('user_phone',$phone);
	                $uid = checkSinaUid($phone);
	                if (empty($uid)){
	                    
	                }else {
	                    $user['id']      = $res;
	                    $user['sina_id'] = "fumi".$uid;
	                    M("members")->save($user);
	                }
	                addUserWxBindInfo($res, $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
	                $tick = "USERREGIST".time().$res;
	                ajaxmsg($tick.",".$res.",regist",1);
	            }else {
	                ajaxmsg('注册失败',0);
	            }
	        }
	    }
	}
	
	/**
	 * 发送手机验证码
	 */
	public function sendPhoneCode() {
	  $smsTxt = FS("Webconfig/smstxt");
	  $smsTxt = de_xie($smsTxt);
	  $code       = rand_string_reg(6,1);
	  $phone      = $_POST['phone'];
	  $verifyCode = $_POST['verifyCode'];
	  $time       = time();
	  $add_time   = M()->query("SELECT id,add_time FROM ml_send_sms WHERE phone = {$phone}  order by add_time desc limit 1 ");
	  $add_time   = $add_time[0];
	  if($add_time['id']>0){
	      if(($time-$add_time['add_time'])<60){
	          ajaxmsg('请60秒之后再请求！',0);
	      }
	  }
	  if(md5($verifyCode) == $_SESSION['verifyCode']){
	      ajaxmsg('false222',0);
	  }else {
	          $phoneExsit = M('member_code')->where("phone = '{$phone}'")->count('phone');
	          $res = addToSms($phone,str_replace(array("#UserName#", "#CODE#"), array($phone, $code), $smsTxt['verify_phone']));	          
	          if ($res){
	              if($phoneExsit > 0){
	                  M()->execute("update ml_member_code set reg_code = '{$code}',mod_time= {$time} where phone={$phone}");
	              } else {
	                  $data['phone']            = $phone;
	                  $data['reg_code']         = $code;
	                  $data['passWord_code']    = "";
	                  $data['payWord_code']     = "";
	                  $data['add_time']         = time();
	                  $data['mod_time']         = time();
	                  M('member_code')->add($data);
	              }
	              $memberInfo = M("members")->field('id')->where("iphone = '{$phone}'")->find();
	              if (!empty($memberInfo['id'])){
	                  $userStatus = 1;
	              }else {
	                  $userStatus = 0;
	              }
	              session('temp_phone_code',$code);
	              ajaxmsg($phone.",".$userStatus,1);
	          }else {
	              ajaxmsg('验证码发送失败',0);
	          }
	  }
	  
	}
	
	/**
	 * 生成随机验证码
	 */
	public function verifyCode($param) {
	    import("ORG.Util.Image");
        Image::buildImageVerify();  
        session("verifyCode",$_SESSION['verify']);
	    session_start();	   
	}
	
	//判断是否可解绑银行卡
	public function checkexBank(){
        $where['uid']             = $_SESSION['uid'];
        $where['status']          = array('not in','5,93,94,95,96,97,98,99,0');
        $borrowInfo = M("borrow_apply")->field('id')->where($where)->find();
        if(empty($borrowInfo)){
            ajaxmsg("可以解绑",0);
        }else{
            ajaxmsg("不可解绑",1);
        }
    }


	/**
	 * 借款列表页
	 */
	public function borrow() {
	    if (!isset($_SESSION['uid'])){
	        $this->redirect('/Member/regist');
	    }
	    $map['uid'] = $_SESSION['uid'];
	    $borrowInfo = M("borrow_apply")->field('add_time,id,money,duration,len_time,deadline,status')->order('add_time desc')->where($map)->limit(3)->select();
	    $statusMessage  = C('BORROW_STATUS');
	    $data = array();
	    foreach ($borrowInfo as $row){
	        $row['statusMsg'] = $statusMessage[$row['status']];
	        $status    = M("member_status")->field('calm,id_verify,is_recheck')->where("borrow_id = {$row['id']}")->order("id Desc")->find();
	        $row['flg']= 0;
	        if($row['status']<3||($row['status']==3 && $status['calm']==0)||$status['is_recheck'] == 0){
	            $row['flg'] = 1;
	        }
	        if($row['status']==3){
	            if($status['calm']==0){
	                $row['statusMsg'] = "已签约";
	            }else{
	                $row['statusMsg'] = "同意放款：待放款";
	            }
	            if($status['id_verify']==2){
	                $row['statusMsg'] = "身份验证失败 ";
	            }
	            if($status['id_verify']==1){
	                $row['statusMsg'] = "身份已验证";
	            }
	        }
	        if ($row['status']==96){
	            $row['statusMsg'] = "资金匹配失败";
	        }
	        $data[] = $row;
	    }
	    $this->assign('borrowInfo',$data);
	    $this->display();
	}

	/**
	 * 借款信息详情页
	 */
	public function details(){
	    if (!isset($_SESSION['uid'])){
	        $this->redirect('/Member/regist');
	    }
	    
	    $bid        = intval($_GET['id']);
	    $map['uid'] = $_SESSION['uid'];
	    $map['id']  = $bid;
	    $borrowInfo       = M("borrow_apply")->field(true)->where($map)->find();
	    $status           = M("member_status")->field("calm,id_verify")->where("borrow_id = {$bid} ")->find();
	    
	    $statusMessage    = C('BORROW_STATUS');
	    $borrowInfo['statusMsg'] = $statusMessage[$borrowInfo['status']];
	    if($borrowInfo['status']==3){
	        if($status['calm']==0){
	            $borrowInfo['statusMsg'] = "冷静期倒计时：借款冷静期 ";
	        }else{
	            $borrowInfo['statusMsg'] = "同意放款：待放款";
	        }
	        if($status['id_verify']==2){
	            $borrowInfo['statusMsg'] = "身份验证失败 ";
	        }
	        if($status['id_verify']==1){
	            $borrowInfo['statusMsg'] = "身份已验证 ";
	        }
	    }
	    if ($borrowInfo['status']==96){
	        $borrowInfo['statusMsg'] = "资金匹配失败";
	    }
	    $other = 0;
	    if($borrowInfo['status']==5){
	        $detail = M("borrow_detail")->field("coupon_id")->where("borrow_id = {$bid} and uid = {$_SESSION['uid']} and status = 1  ")->find();
	        $coupon = M("member_coupon")->field("money")->where("id = {$detail['coupon_id']} and status=1 ")->find();
	        $other = $coupon['money'];
	    }
	    
	    $borrowInfo['total']  = getFloatValue($borrowInfo['interest']+$borrowInfo['money']+$borrowInfo['pay_fee']+$borrowInfo['audit_fee']+$borrowInfo['created_fee']+$borrowInfo['enabled_fee']-$other,2);
	    $borrowInfo['other']  = $other;
	    $this->assign('borrowInfo',$borrowInfo);
	    $this->display();
	}
	
	/**
	 * 用户优惠券页
	 */
	public function ticket(){
	    if (!isset($_SESSION['uid'])){
	        $this->redirect('/Member/regist');
	    }
	    $uid   = $_SESSION['uid'];
	    $now   = time();
	    
	    $info1 = M("member_coupon")->field(true)->where("uid ={$uid} and status = 0 and type in (1,3) and end_time >= {$now} ")->order(" type, end_time desc")->select();
	    $info2 = M("member_coupon")->field(true)->where("uid ={$uid} and status = 0 and type = 2 and end_time >= {$now} ")->order(" end_time desc")->select();
	    $info3 = M("member_coupon")->field(true)->where("uid ={$uid} and (status = 1 or (status=0 and end_time <{$now} )) ")->order(" end_time desc")->select();
	    
	    $reg_time = M('members')->getFieldById($uid,"reg_time");
	    $flg = 0;
	    if($reg_time>1513007999){
	        $flg = 1;
	    }
	   
	    $count = M("member_coupon")->where("uid ={$uid} and type = 2 ")->count("id");
	    $global = get_global_setting();
	    $ticArr = explode('|', $global['coupon_register']);
	    $money = $ticArr[0];
	    
	    $this->assign('flg',$flg);//判断新政策用户的
	    $this->assign('money',$money);
	    $this->assign('count',$count);//判断是否有还款优惠券
	    $this->assign('info1',$info1);//可使用的借款，续期优惠券
	    $this->assign('info2',$info2);//可使用的还款优惠券
	    $this->assign('info3',$info3);//所有过期和已经使用的
	    $this->display();
	}
	
	/**
	 * 用户注销页面
	 */
	public function logoff() {
	    $this->assign('phone',substr_replace($_SESSION['user_phone'], '****', 3,4));
	    $this->assign('telnum',$_SESSION['user_phone']);
	    $tick = "LOGOUT_".$_SESSION['uid']."_".time();
	    $this->assign('tick',$tick);
	    $this->display();
	}
	
	/**
	 * 用户注销操作
	 */
	public function delUserInfo(){
	    $uid = $_SESSION['uid'];
	    $verifyCode = $_POST['verifyCode'];
	    if($verifyCode !== $_SESSION['temp_phone_code']){
	        ajaxmsg('验证码错误',0);
	    }else {
	        $borrowInfo = M("borrow_apply")->field('id')->where("uid = $uid and status = 4")->find();
	        if (!empty($borrowInfo)){
	            ajaxmsg('您还有借款未还',0);
	        }else {
	            $data['id']        = $uid;
	            $data['is_logoff'] = 1;
	            $status = M("members")->save($data);
	            $status2 = M("member_wechat_bind")->where("uid = $uid")->delete();
	            $logData['uid']  = $uid;
	            $logData['add_time'] = time();
	            $logData['reason']   = $_POST['reason'];
	            $logData['off_ip']   = get_client_ip();
	            $logData['device_id']= 1;
	            $res = M("member_logoff")->add($logData);
	            if($status !== false && $status2 !== false && $res){
	                ajaxmsg('注销成功',1);
	            }else {
	                ajaxmsg('注销失败,请联系客服人员',0);
	            }
	        }
	    }
	}
	
	/**
	 * 获取用户设备id
	 */
	public function getMaxentId(){
	    $tick      = $_POST['tick'];
	    $event     = $_POST['event'];
	    $status    = $_POST['status'];
	    $url       = "https://www.id-linking.com/api/v1/5g0hau6ige6ndtwx6fg9a9gyxbhcgjg4/tick/".$tick;
	    $res       = http_request($url);
	    $arr = json_decode($res,true);
	    $maxent_id = $arr['data']['maxent_id'];
	    $this->insertDevice($arr, $event ,$status);
	    $uid = $_SESSION['uid'];
	    $data['device_id'] = $maxent_id;
	    M("member_logoff")->where("uid = $uid")->save($data);
	}
	
	/**
	 * 插入用户设备信息表 
	 */
	private function insertDevice($arr , $event, $status) {
	    $data['uid']         = $_SESSION['uid'];
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
	    M("member_device")->add($data);
	}
	
	/**
	 * 借款页
	 */
	public function currborrow() {
	    if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $uid = $_SESSION['uid'];
        if (empty($uid)){
            $this->redirect("/Index/browser");
        }
        $tick          = "LOGIN".time().$_SESSION['uid'];

        //用户借款情况(银卡)
	    $bwhere1['a.uid']    = $uid; 
	    $bwhere1['b.type']   = 1; 
	    $bwhere1['a.status'] = array('not in','5,93,94,95,96,97,98,99');
	    $field1 = "a.money,a.duration,a.audit_status,a.id,a.uid,a.status,a.repayment_time,b.type,a.is_withdraw";
	    $apply1 = M("borrow_apply a")->where($bwhere1)->join("ml_borrow_item b on a.item_id=b.id")->field($field1)->select();

	    //用户借款情况(金卡)
	    $bwhere2['a.uid']    = $uid; 
	    $bwhere2['b.type']   = 2; 
	    $bwhere2['a.status'] = array('not in','5,93,94,95,96,97,98,99');
	    $field2 = "a.money,a.duration,a.audit_status,a.id,a.uid,a.status,a.repayment_time,b.type,a.is_withdraw";
	    $apply2 = M("borrow_apply a")->where($bwhere2)->join("ml_borrow_item b on a.item_id=b.id")->field($field2)->select();

	    //银卡
	    $item1 = M("borrow_item")->field('max(money) as money ,max(duration) as duration,id')->where("is_on = 1 and type=1")->find();
	    //金卡
	    $item2 = M("borrow_item")->field('max(money) as money ,max(duration) as duration,id')->where("is_on = 1 and type=2")->find();
	    
	    $this->assign('money1',$item1['money']);
	    $this->assign('duration1',$item1['duration']);
	    $this->assign('money2',$item2['money']);
	    $this->assign('duration2',$item2['duration']);
        
        //用户等级
        $user = M("members")->where("id = {$uid}")->field("is_black,is_white,is_gold,is_gray")->find();
        if($user['is_white'] == 1){//白名单-中级
            $lv = 2;
        }elseif($user['is_gold'] == 1){//金名单-高级
            $lv = 3;
        }else{//无名单or黑名单-灰名单
            $lv = 1;
        }    	

        $statusMessage  = C('BORROW_STATUS');

	    foreach ($apply1 as $k => $v) {
	    	$apply1s[] = $v;
	    	$apply1s[$k]['url'] = getUserLastOperation($_SESSION['uid'],$v['id'])."?bid=".$v['id'];
	    	$apply1s[$k]['statusMsg'] = $statusMessage[$v['status']];
	        $status    = M("member_status")->field('calm,id_verify,is_recheck')->where("borrow_id = {$v['id']}")->order("id Desc")->find();
	        $detail    = M("borrow_detail")->field(' id ')->where(" borrow_id = {$v['id']} ")->find();
	        $apply1s[$k]['flg'] = 0;

	        if($detail['id']>0){
                $apply1s[$k]['flg'] = 0;
        	}else{
        		if($v['status']<3||($v['status']==3 && $status['calm']==0)||$status['is_recheck'] == 0){
	            	$apply1s[$k]['flg'] = 1;
	         	}
        	}

        	if($v['status']==3){
	            if($status['calm']==0){
	                $apply1s[$k]['statusMsg'] = "已签约";
	            }else{
	                $apply1s[$k]['statusMsg'] = "同意放款：待放款";
	            }
	            if($status['id_verify']==2){
	                $apply1s[$k]['statusMsg'] = "身份验证失败 ";
	            }
	            if($status['id_verify']==1){
	                $apply1s[$k]['statusMsg'] = "身份已验证";
	            }
	        }
	        if($v['status']==4){
	        	$apply2s[$k]['is_withdraw'] = $v['is_withdraw'];
	        }
	        if ($v['status']==96){
	            $apply1s[$k]['statusMsg'] = "资金匹配失败";
	        }
	    }

	    foreach ($apply2 as $k => $v) {
	    	$apply2s[] = $v;
	    	$apply2s[$k]['url']   = getUserLastOperation($_SESSION['uid'],$v['id'])."?bid=".$v['id'];
	    	$apply2s[$k]['statusMsg'] = $statusMessage[$v['status']];
	        $status    = M("member_status")->field('calm,id_verify,is_recheck')->where("borrow_id = {$v['id']}")->order("id Desc")->find();
	        $detail    = M("borrow_detail")->field(' id ')->where(" borrow_id = {$v['id']} ")->find();
	        $apply2s[$k]['flg'] = 0;

	        if($detail['id']>0){
                $apply2s[$k]['flg'] = 0;
        	}else{
        		if($v['status']<3||($v['status']==3 && $status['calm']==0)||$status['is_recheck'] == 0){
	            	$apply2s[$k]['flg'] = 1;
	         	}
        	}

	        if($v['status']==3){
	            if($status['calm']==0){
	                $apply2s[$k]['statusMsg'] = "已签约";
	            }else{
	                $apply2s[$k]['statusMsg'] = "同意放款：待放款";
	            }
	            if($status['id_verify']==2){
	                $apply2s[$k]['statusMsg'] = "身份验证失败 ";
	            }
	            if($status['id_verify']==1){
	                $apply2s[$k]['statusMsg'] = "身份已验证";
	            }
	        }
	        if($v['status']==4){
	        	$apply2s[$k]['is_withdraw'] = $v['is_withdraw'];
	        }
	        if ($v['status']==96){
	            $apply2s[$k]['statusMsg'] = "资金匹配失败";
	        }
	    }
		
		$money = userMoney($array['uid']);
		if($money['ca_balance'] > 0){//有未提现金额
			$tx = 1;
		}else{
			$tx = 0;
		}

	    $this->assign('apply1',$apply1s);
	    $this->assign('apply2',$apply2s);
        $this->assign('lv',$lv);
        $this->assign('tx',$tx);
	    $this->assign('tick',$tick);
	    $this->assign('uid',$_SESSION['uid']);
	    $this->assign('url',$url);
	    $this->display();
	}
	
	/**
	 * 记录用户操作
	 */
	public function  saveUserOperation(){
	    $arr = explode('/', $_POST['url']);
		//接收bid
		$bid = $_SESSION['bid'];
	    $operation = array('1'=>'Index','2'=>'userBaseInfo','3'=>'verifyUserStatus','4'=>'verifyPhone','5'=>'msgCheck');
	    $data['uid']       = $_SESSION['uid'];
	    if(strpos($arr[4],'html')!==false){
			$data['operation'] = "/".$arr[3]."/".substr($arr[4],0,5);
			$data['orderNum']  = array_search(ucfirst(substr($arr[4],0,5)), $operation);
		}else{
			$data['operation'] = "/".$arr[3]."/".$arr[4];
			if ($arr[4] == 'index'){
			    $data['orderNum']  = array_search(ucfirst($arr[4]), $operation);
			}else {
			    $data['orderNum']  = array_search($arr[4], $operation);
			}
		}
	    $data['add_time']  = time();
	    $data['borrow_id'] = $bid;	    
	    $info = M("user_operation")->field('*')->where("uid = {$_SESSION['uid']} and borrow_id = {$bid}")->find();
	    if (empty($info)){
	        M("user_operation")->add($data);
	    }else {
	        if(strpos($arr[4],'html')!==false){
	            $tempOrderNum = array_search(substr($arr[4],0,5), $operation);
	        }else{
	            $tempOrderNum = array_search($arr[4], $operation);
	        }	        
	        if ($tempOrderNum >= $info['orderNum']){
	            M("user_operation")->where("id = {$info['id']}")->save($data);
	        }
	    }
		//查找状态
		if($bid){
			$borrow = M('borrow_apply')->field('status,audit_status')->where("uid = {$data['uid']} and id = {$bid}")->find();
			$member_status = M('member_status')->field('calm')->where("uid = {$data['uid']} and borrow_id = {$bid}")->find();
			if($borrow['status'] == 3 && $borrow['audit_status'] == 4){
				if($member_status['calm'] == 1){
					ajaxmsg('返回成功',1);
				}else{
					ajaxmsg('返回成功',0);
				}

			}
		}

 	}

 	//邀请活动注册页面
 	public function invite_regist(){
		$part = "";
		if ($_SERVER['HTTPS'] != "on") {
			$part = "http://" . $_SERVER["SERVER_NAME"];
			$this->assign('flg',0);
		}else{
			$part = "https://" . $_SERVER["SERVER_NAME"];
			$this->assign('flg',1);
		}
		$uid = session('uid');
 	    if(!empty($uid)){
     	    $this->assign('user',1);//已登录
 	    }else{
 	     	$this->assign('user',0);//未登录
 	    }

 		if(isset($_GET['invite_code'])){
 			$this->assign('invite_code',$_GET['invite_code']);
 			$url       = $part."/member/invite_regist?invite_code=".$_GET['invite_code'];
 			$title     = '恭喜您获得好友邀请资格！';
 			$des       = "您的好友帮您申请了一笔1500元贷款额度，点击领取！";
 		}else{
 		    $url       = $part."/member/invite_regist";
 		    $title     = '恭喜您成为现贷猫的体验用户！';
 		    $des       = "您有一笔1500元贷款额度，点击领取！";
 		}
 		$pic_url   = $part."/style/cash/img/hidden.jpg";
 		$post_url  = $_SERVER["REQUEST_URI"];
 		$check     = strpos($post_url, "from=singlemessage&isappinstalled=0");
 		if($check >0) {
 		    header("Location: ".$url);
 		}
 		$signPackage = wxShare($url,$title,$des,$pic_url);
 		$this->assign('signPackage',$signPackage);
 		$this->display(); 
 	}


 	//活动注册接口
 	public function add_yquser(){
 		$verifyCode  = $_POST['verifyCode'];
	    $phone       = $_POST['phone'];
	    $recoCode    = $_POST['recoCode'];

	    $codeInfo    = M("member_code")->where("phone = '{$phone}' and reg_code = '{$verifyCode}'")->count('phone');
	    if ($codeInfo <= 0){
	       ajaxmsg('手机验证码错误',0);
	    }else{
	    	//验证区
		    $mwhere['iphone'] = $phone;
		    $ucount = M("members")->where($mwhere)->field('id')->count();
		    if($ucount > 0){
		    	ajaxmsg('该手机号码已被注册!',0);
		    }

		    if(!empty($recoCode)){
		    	$codewhere['invite_code'] = $recoCode;
			    $ucode = M("members")->where($codewhere)->field('id')->find();
			    if(empty($ucode)){
			    	ajaxmsg('该邀请码不存在!',0);
			    }else{
			    	$data['recommend_id'] = $ucode['id'];
			    }
		    }

	        $memberInfo = M("members")->field('id')->where("iphone = '{$phone}'")->find();
	        if (!empty($memberInfo)){
	            $operationInfo  = M("user_operation")->field('operation')->where("uid = {$memberInfo['id']}")->find();
	            if (empty($operationInfo)){
	                $operation = "/Member/currborrow";
	            }else{
					$operation = $operationInfo['operation'];
				}
	            addUserWxBindInfo($memberInfo['id'], $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
	            $tick = "USERLOGIN".time().$memberInfo['id'];
	            
	            session('uid',$memberInfo['id']);
	            session('user_phone',$phone);
	            ajaxmsg($tick.",".$memberInfo['id'].",login,".$operation,1);
	        }else {
	            $mem = M('members')->field('id')->where("invite_code='{$recoCode}'")->find();
	            if(session("Promotion_temp")!='') {
	                $data['promotion_code'] = session("Promotion_temp");
	                session('Promotion_temp','');
	            }
	            
	            $data['iphone']       = $phone;
	            $data['reg_time']     = time();
	            $data['reg_ip']       = get_client_ip();
	            $data['reg_address']  = get_ipAddress($data['reg_ip']);
	            $data['last_time']    = time();
	            $data['last_ip']      = get_client_ip();
	            $data['last_address'] = get_ipAddress($data['last_ip']);
	            $data['type']         = 1;
	            //$data['recommend_id'] = $mem['id'];
	            $type = memberType($phone);
	            if($type==1){
	                $data['is_black'] = 1;
	            }else if($type==2){
	                $data['is_white'] = 1;
	            }else if($type==3){
	                $data['is_gray']  = 1;
	            }
	            $res = M("members")->add($data);
	            if ($res){
	                session('uid',$res);
	                session('user_phone',$phone);
	                $uid = checkSinaUid($phone);
	                if (empty($uid)){
	                    if (sinaCreatMember($res)){
	                        if (!sinaBindingVerify($res, $phone)){
	                            ajaxmsg('注册失败',0);
	                        }
	                    }else {
	                        ajaxmsg('注册失败',0);
	                    }
	                }else { 
	                    $user['id']      = $res;
	                    $user['sina_id'] = "fumi".$uid;
	                    M("members")->save($user);
	                }
	                addUserWxBindInfo($res, $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
	                $tick = "USERREGIST".time().$res;
	                ajaxmsg($tick.",".$res.",regist",1);
	            }else {
	                ajaxmsg('注册失败',0);
	            }
	        }
	    }
 	} 

 	//邀请活动详情页
 	public function invite(){
 		$uid = session('uid');
 		$openid = session('gopenid');
 		if(!empty($uid)){
 			$uwhere['id'] = $uid; 
 			$userval = M('members')->where($uwhere)->field('invite_code')->find();

 			if(!empty($userval['invite_code'])){
 				$this->assign('invite_code',$userval['invite_code']);//邀请码
 			}else{
 				$opwhere['uid'] = $uid;
 				$user_operation = M('user_operation')->where($opwhere)->field('id,operation')->find();
 				if(!empty($user_operation)){
 					$this->assign('user_operation',$user_operation['operation']);//跳转
 				}else{
 					$this->assign('user_operation',null);
 				}
 			}
 			$this->assign('user',1);//已登录		
 		}else{
 			$this->assign('user',0);//未登录
 		}
 		$this->display();
 	}
 	
 	
 	//登录接口
 	public function yq_login(){
 		$verifyCode    = $_POST['verifyCode'];
	    $phone         = $_POST['phone'];
	    $openid        = $_POST['openid'];

	    $codeInfo    = M("member_code")->where("phone = '{$phone}' and reg_code = '{$verifyCode}'")->count('phone');
	    if ($codeInfo <= 0){
	       ajaxmsg('手机验证码错误',0);
	    }else{
	        $memberInfo = M("members")->field('id')->where("iphone = '{$phone}'")->find();
	        if (!empty($memberInfo)){
	            $operationInfo  = M("user_operation")->field('operation')->where("uid = {$memberInfo['id']}")->find();
	            if (empty($operationInfo)){
	                $operation = "/Member/currborrow";
	            }else{
					$operation = $operationInfo['operation'];
				}
	            addUserWxBindInfo($memberInfo['id'], $openid, $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
	            $tick = "USERLOGIN".time().$memberInfo['id'];
	            
	            session('uid',$memberInfo['id']);
	            session('user_phone',$phone);
	            ajaxmsg($tick.",".$memberInfo['id'].",login,".$operation,1);
	        }else {
	            $mem = M('members')->field('id')->where("invite_code='{$recoCode}'")->find();
	            if(session("Promotion_temp")!='') {
	                $data['promotion_code'] = session("Promotion_temp");
	                session('Promotion_temp','');
	            }
	            
	            $data['iphone']       = $phone;
	            $data['reg_time']     = time();
	            $data['reg_ip']       = get_client_ip();
	            $data['reg_address']  = get_ipAddress($data['reg_ip']);
	            $data['last_time']    = time();
	            $data['last_ip']      = get_client_ip();
	            $data['last_address'] = get_ipAddress($data['last_ip']);
	            $data['type']         = 1;
	            //$data['recommend_id'] = $mem['id'];
	            $type = memberType($phone);
	            if($type==1){
	                $data['is_black'] = 1;
	            }else if($type==2){
	                $data['is_white'] = 1;
	            }else if($type==3){
	                $data['is_gray']  = 1;
	            }
	            $res = M("members")->add($data);
	            if ($res){
	                session('uid',$res);
	                session('user_phone',$phone);
	                $uid = checkSinaUid($phone);
	                if (empty($uid)){
	                    if (sinaCreatMember($res)){
	                        if (!sinaBindingVerify($res, $phone)){
	                            ajaxmsg('注册失败',0);
	                        }
	                    }else {
	                        ajaxmsg('注册失败',0);
	                    }
	                }else { 
	                    $user['id']      = $res;
	                    $user['sina_id'] = "fumi".$uid;
	                    M("members")->save($user);
	                }
	                addUserWxBindInfo($res, $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
	                $tick = "USERREGIST".time().$res;
	                ajaxmsg($tick.",".$res.",regist",1);
	            }else {
	                ajaxmsg('注册失败',0);
	            }
	        }
	    }
 	}
	 	
 	public function editinv(){
 		session('uid',null);
 	}
}