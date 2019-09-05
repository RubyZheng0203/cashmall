<?php
class RepaymentAction extends HCommonAction{

	public function index(){
		if (!isset($_SESSION['uid'])){
			$this->redirect('/Member/regist');
		}
		$uid     = $_SESSION['uid'];
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
                $apply[0]['msg']     = "<span class='borrow_red'>已逾期".get_due_day($apply1['deadline'])."天<span>";
                $dueC                = $dueC+1;
            }else{
                if($apply1['deadline']<=$nowE){
                    $apply[0]['msg'] = "<span class='borrow_red'>今日需还款<span>";
                }else{
                    $deadline       = strtotime(date('Y-m-d',$apply1['deadline']));
                    $day            = ($deadline-$now)/86400;
                    $apply[0]['msg']= "<span class='borrow_orange'>距离还款日还剩".$day."天</span>";
                } 
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
                $apply[1]['msg']    = "<span class='borrow_red'>已逾期".get_due_day($apply2['deadline'])."天<span>";
                $dueC               = $dueC+1;
            }else{
                if($apply2['deadline']<=$nowE){
                    $apply[1]['msg'] = "<span class='borrow_red'>今日需还款<span>";
                }else{
                    $deadline       = strtotime(date('Y-m-d',$apply2['deadline']));
                    $day            = ($deadline-$now)/86400;
                    $apply[1]['msg']= "<span class='borrow_orange'>距离还款日还剩".$day."天</span>";
                } 
            }
        }
        
        $total = getFloatValue($money1+$money2,2);
        $this->assign('total',$total);//待还总额
        $this->assign('count',$count);//待还笔数
        $this->assign('dueC',$dueC);//逾期笔数
        $this->assign('apply',$apply);//还款数据
		$this->display();
	}

	public function editBank(){
		$this->display();  
	}

	/**
	 * 还款首页
	 */
	public function repaymentInfo() {
		if (!isset($_SESSION['uid'])){
			$this->redirect('/Member/regist');
		}
		if(empty($_POST['bid'])){
			$this->redirect('/Member/currborrow');	
		}
		$uid  = $_SESSION['uid'];
		$bid  = $_POST['bid'];
		$now  = time();

		//查询用户需还款的账单（最近需要还款的一份账单）
		$map             = array();
		$map['a.uid']    = $uid;
		$map['a.id']     = $bid;
		$field           = " a.id,a.item_id,a.money,a.duration,a.audit_fee,a.created_fee,a.pay_fee,a.enabled_fee,a.due_fee,a.late_fee,a.`status`,a.add_time,a.len_time,a.is_new,b.deadline,b.id as detail_id,b.capital,b.interest ";
		$info            = M("borrow_apply a")->field($field)->join("ml_borrow_detail  b ON b.borrow_id=a.id ")->where($map)->order(" b.deadline ")->limit("1")->select();
		$info            = $info[0];
		if (empty($info)){
			$loans = M('borrow_apply')->where(" uid = {$uid} and  status <5 ")->count('id');
			if ($loans>0){
				$url = getUserLastOperation($uid);
				header("Location: ".$url);
			}else {
				header("Location: /Borrow/index");
			}
		}

		//用户可选择的优惠券
		$ticket   = M("member_coupon")->field('money,end_time,id')->where("uid = $uid AND status = 0 and type=2 and end_time > $now")->select();
		$this->assign('ticket',$ticket);

		$item      = M("borrow_item")->field(true)->where(" id = {$info['item_id']} ")->find();
		$due_days  = get_due_day($info['deadline']);;
		$uDeadLine = strtotime(date('Y-m-d',$info['deadline'])."23:59:59");

		if($info['is_new']==1){
			$info['fee']   = getFloatValue($info['audit_fee']+$info['enabled_fee']+$info['created_fee']+$info['pay_fee'],2);
		}else{
			$info['fee']   = 0;
		}

		if ($due_days>0){
			$info['overdue']          = 1;
			$info['dueTime']          = $due_days;
			$info['due_money']        = get_due_fee($info['money'], $info['item_id'], $info['dueTime']);
			$info['due_manage_money'] = get_late_fee($info['money'], $info['item_id'], $info['dueTime']);
			$info['money']            = $info['money'];
			$info['total']            = $info['money']+$info['interest']+$info['due_money']+$info['due_manage_money']+$info['fee'];
		}else {
			$info['overdue']          = 0;
			$now                      = time();
			$countDay                 = floor(($uDeadLine - $now)/3600/24);
			if ($countDay == 0){
				$info['repayTime']    = "今天是您的还款日";
			}else {
				$info['repayTime']    = floor(($uDeadLine - $now)/3600/24);
			}
			$info['money']            = $info['money'];

			$info['total']            = $info['money']+$info['interest']+$info['fee'];
		}


		$info['due_rate']    = "逾期利息是借款金额的".$item['due_rate']."%/天 。如借款".$info['money']."元，每天".get_due_fee($info['money'], $info['item_id'], 1)."元。";
		$info['late_rate']   = "逾期管理费是借款金额的".$item['late_rate']."%/天 。如借款".$info['money']."元，每天".get_late_fee($info['money'], $info['item_id'], 1)."元。";
		//查询是否申请了续期
		$is_renewal = M('payoff_apply')->field('id')->where(" detail_id = {$info['detail_id']} and type = 2 and status = 0")->find();
		if($is_renewal){
			$status = 1;
		}else{
			$status = 0;
		}
		$info['bid'] = $info['id'];
		$this->assign('status',$status);
		$this->assign('info',$info);
		$this->display();
	}

	/**
	 * 还款付款页面
	 */
	public function repaymentType() {
		if (!isset($_SESSION['uid'])){
			$this->redirect('/Member/regist');
		}
		$uid       = $_SESSION['uid'];
		$postmoney = $_POST['postmoney'];
		$detail_id = $_POST['detail_id'];
		$info      = M("borrow_detail")->field(true)->where(" id = {$detail_id} and status = 0 ")->select();
		$info      = $info[0];
		if (empty($info)){
			$loans = M('borrow_apply')->where(" uid = {$uid} and  status <5 ")->count('id');
			if ($loans>0){
				header("Location: /Borrow/msgCheck");
			}else {
				header("Location: /Borrow/index");
			}
		}else{
			$datag = get_global_setting();
			//获取还款金额的限制  本金的倍数
			$repayment_limit = $datag['repayment_limit'];
			$limit_money = $info['capital']*$repayment_limit;
			if($postmoney > $limit_money && $limit_money != 0){
				$postmoney = $limit_money;
			}
		}

		$bankCard = M("member_bank")->field('bank_card,id')->where("uid = $uid and type=2")->select();

		$now      = time();
		if (empty($_POST['selectTick'])){
			$ticketMoney = 0;
		}else {
			//用户优惠券信息
			$ticket      = M("member_coupon")->field('money')->where("uid = $uid AND status = 0 AND id = {$_POST['selectTick']}")->find();
			$ticketMoney = $ticket['money'];
		}

		$data = array();
		foreach ($bankCard as $row){
			$row['bank_card'] = "尾号(".substr($row['bank_card'], -4).")";
			$data[] = $row;
		}
		$off = haveOff($detail_id);
		$this->assign('off',$off);
		$this->assign('money',$postmoney);
		$this->assign('detail_id',$detail_id);
		$this->assign('bankcard',$data);
		$this->assign('bid',$info['borrow_id']);
		//原来一共多少
		$this->assign('old_money',$_POST['postmoney']);
		$this->assign('ticket',$_POST['selectTick']);
		$this->assign('bankcardjson',json_encode($bankCard));
		$this->display();
	}

	/**
	 * 宝付还款
	 */
	public function repayLoansOrder(){
		$ticket        = $_POST['ticket'];
		$money         = $_POST['money'];
		$detailId      = $_POST['detailId'];

		$map['status'] = 0;
		$map['id']     = $detailId;
		$borrow_detail = M("borrow_detail")->field(true)->where($map)->find();

		$due_day    = 0;
		$due_fee    = 0;
		$late_fee   = 0;
		if($borrow_detail['id']>0){
			$borrowInfo    = M("borrow_apply")->field(true)->where("id = {$borrow_detail['borrow_id']}")->find();
			$coupon        = M("member_coupon")->field('money')->where("id = {$ticket} and status=0 ")->find();

			$dueDay        = get_due_day($borrowInfo['deadline']);
			if($dueDay>0){
				$due_fee   = get_due_fee($borrowInfo['money'], $borrowInfo['item_id'], $dueDay);
				$late_fee  = get_late_fee($borrowInfo['money'], $borrowInfo['item_id'], $dueDay);
				$type      = 2;
				$integral  = $borrow_detail['interest']+$due_fee+$late_fee;
				$info2     = "逾期还款".$money."元，扣除积分".$integral;
			}else{
				$type      = 1;
				$integral  = $detail['capital']+$detail['interest'];
				$info2     = "成功还款".$money."元，获得积分".$integral;
			}
			if($borrowInfo['is_new']==1){
				$fee      = getFloatValue($borrowInfo['audit_fee']+$borrowInfo['enabled_fee']+$borrowInfo['created_fee']+$borrowInfo['pay_fee'],2);
			}else{
				$fee      = 0;
			}
			$total    = getFloatValue($borrow_detail['capital']+$borrow_detail['interest']+$due_fee+$late_fee+$fee-$coupon['money'],2);
			if($total<0){
				ajaxmsg('还款的金额不能为空或者负数，请确认！',0);
			}

			//新的money
			if($money != 0 && $money < $total){
				$total = $money;
			}

			if($money<$total){
				ajaxmsg('提交还款的金额不对，应为'.$total.'元',0);
			}

			//宝付支付
			$model    = new PaymentAction();
			$res      = $model->requestApi($borrowInfo['uid'], $borrowInfo['id'], 1,$total ,$_POST['bankId']);
			wqblog("还款支付金额:".$total);
			wqbLog("还款支付结果:".$res);
			$now      = time();
			if(is_bool($res) && $res){
				$datab['repayment_time'] = $now;
				$datab['status']         = 1;
				$datab['due_fee']        = $due_fee;
				$datab['late_fee']       = $late_fee;
				if($ticket>0){
					$datab['coupon_id']  = $ticket;
				}

				$updetail = M('borrow_detail')->where(" id= {$detailId} ")->save($datab);
				if($updetail){
					//所有期数都还款完成
					if($borrow_detail['sort_order']==$borrow_detail['total']){
						$dataapply['status']         = 5;
						$dataapply['repayment_time'] = $now;
						$dataapply['due_fee']        = $borrowInfo['due_fee']+$due_fee;
						$dataapply['late_fee']       = $borrowInfo['late_fee']+$late_fee;
						$upapply = M('borrow_apply')->where(" id={$borrowInfo['id']}  and uid = {$borrowInfo['uid']} ")->save($dataapply);
					}
				}

				$wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$borrowInfo['uid']}")->find();
				if($wxInfo['openid']!==''){
					sendWxTempleteMsg8($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'], $borrowInfo['duration'], $bdata['repayment_time']);
				}

				//还款优惠券更新
				if($ticket!=''){
					$tickData['status'] = 1;
					$res = M('member_coupon')->where(" id = {$ticket} ")->save($tickData);
				}
				//白骑士芝麻数据反馈接口
				$datag        = get_global_setting();
				$zhima        = $datag['zhima_data'];
				if($zhima == 1){
					bqszhimaOrder($borrowInfo['id'],$borrowInfo['uid'],2,"");
				}

				//会员积分变更
				addIntegral($borrow_detail['uid'],$type,$integral,$info2);
				session('bid',"");
				delUserOperation($_SESSION['uid'],$borrowInfo['id']);
				ajaxmsg('还款成功'.$total,1);
			}else{
				ajaxmsg('还款失败',0);
			}
		}else{
			ajaxmsg('请确认借款申请的还款状态',0);
		}
	}

	/**
	 * 判断用户是否可以还款
	 */
	public function checkUserRepayWech(){
		$uid   = $_SESSION['uid'];
		$demo  = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['bid']} and scene = 1 and status = 0")->count('id');
		$apply = M("borrow_apply")->where("id = {$_POST['bid']}")->field('status')->find();
		if(!empty($demo) && $apply['status'] != 5){
			ajaxmsg('请等待支付完成',1);
		}else{
			if($apply['status'] == 5){
				ajaxmsg('已经还款结束,无需再还款',2);
			}
			ajaxmsg('',0);
		}
	}

	/** 
	 * 微信还款
	 */
	public function repayLoansWechat(){
		$ticket        = $_POST['ticket'];
		$money         = $_POST['money'];
		$detailId      = $_POST['detailId'];

		$map['status'] = 0;
		$map['id']     = $detailId;
		$borrow_detail = M("borrow_detail")->field(true)->where($map)->find();

		$due_day    = 0;
		$due_fee    = 0;
		$late_fee   = 0;
		if($borrow_detail['id']>0){
			$borrowInfo    = M("borrow_apply")->field(true)->where("id = {$borrow_detail['borrow_id']}")->find();
			$coupon        = M("member_coupon")->field('money')->where("id = {$ticket} and status=0 ")->find();

			$dueDay        = get_due_day($borrowInfo['deadline']);
			$due_fee   = get_due_fee($borrowInfo['money'], $borrowInfo['item_id'], $dueDay);
			$late_fee  = get_late_fee($borrowInfo['money'], $borrowInfo['item_id'], $dueDay);
			
			if($borrowInfo['is_new']==1){
				$fee      = getFloatValue($borrowInfo['audit_fee']+$borrowInfo['enabled_fee']+$borrowInfo['created_fee']+$borrowInfo['pay_fee'],2);
			}else{
				$fee      = 0;
			}
			$total    = getFloatValue($borrow_detail['capital']+$borrow_detail['interest']+$due_fee+$late_fee+$fee-$coupon['money'],2);
			if($total<0){
				ajaxmsg('还款的金额不能为空或者负数，请确认！',0);
			}

			//新的money
			if($money != 0 && $money < $total){
				$total = $money;
			}

			if($money<$total){
				ajaxmsg('提交还款的金额不对，应为'.$total.'元',0);
			}

			$res = wechatPay($borrow_detail['uid'],$borrow_detail['borrow_id'],0,$total,1,1);
			wqblog("还款支付金额:".$total);
			wqbLog("还款支付结果:".$res);
			if($res){
            	ajaxmsg($res,1);//返回支付二维码url
            }
		}else{
			ajaxmsg('请确认借款申请的还款状态',0);
		}
	}

	/**
	 * 还款其他银行卡还款
	 */
	public function repaymentOthercard(){
		if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $bankList = C("SINA_BANK_NAME");
        $length = count($bankList);
        for ($i=1 ; $i<$length;$i++){
            $str .= "<option value='{$bankList[$i]}'>".$bankList[$i]."</option>";
        }

		$detail_id = $_POST['detail_id'];  
		$money     = $_POST['money'];
		$ticket    = $_POST['ticket'];

		$this->assign('str',$str);
		$this->assign('detail_id',$detail_id);
		$this->assign('money',$money);
		$this->assign('ticket',$ticket);
		$this->display();
	}
	/**
	 * 续期首页
	 */
	public function renewalInfo() {
	    /*$this->redirect('/Repayment/repaymentType');*/
		if (!isset($_SESSION['uid'])){
			$this->redirect('/Member/regist');
		}
		if(empty($_POST['bid'])){
			$this->redirect('/Member/currborrow');	
		}
		$now      = time();
		$uid      = $_SESSION['uid'];
		$ticket   = M("member_coupon")->field('money,end_time,id')->where("uid = $uid AND status = 0 and type = 3 and end_time>$now")->select();//用户优惠券信息
		$this->assign('ticket',$ticket);

		$map             = array();
		$map['a.uid']    = $uid;
		$map['a.id']	 = $_POST['bid'];
		$field           = " a.id,a.item_id,a.money,a.duration,a.audit_fee,a.created_fee,a.pay_fee,a.enabled_fee,a.due_fee,a.late_fee,a.is_new,a.`status`,a.add_time,a.len_time,b.deadline,b.id as detail_id,b.capital,b.interest ";
		$info            = M("borrow_apply a")->field($field)->join("ml_borrow_detail  b ON b.borrow_id=a.id ")->where($map)->order(" b.deadline ")->limit("1")->select();
		$info            = $info[0];
		if($info['is_new']==0){
			$this->redirect('/Repayment/repaymentInfo');
		}
		$member   = M("member_info")->field('real_name')->where("uid = $uid")->find();
		$item     = M("borrow_item")->field(true)->where("id = {$info['item_id']}")->find();

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
		$created_fee   = 0;//技术服务费
		$pay_fee       = $itemF['pay_fee'];//支付服务费
		$total         = getFloatValue($info['money']*$itemF['rate']/100*$dayF/360+$info['money']+$audit_fee+$enabled_fee+$created_fee+$pay_fee,2);//新的借款的本息+费用
		$itemarr                = array();
		$itemarr['duration']    = $dayF;
		$itemarr['total']       = $total;
		$itemarr['renewal_fee'] = $renewal_fee;
		$itemarr['audit_fee']   = $audit_fee;
		$itemarr['enabled_fee'] = $enabled_fee;
		$itemarr['pay_fee']     = $pay_fee;
		//$itemarr['due_total']   = getFloatValue($info['interest']+$info['due_money']+$info['due_manage_money']+$audit_fee+$enabled_fee+$renewal_fee,2);
		$itemarr['fee']         = getFloatValue($info['audit_fee']+$info['enabled_fee']+$info['created_fee']+$info['pay_fee'],2);
		$itemarr['due_total']   = getFloatValue($info['interest']+$info['due_money']+$info['due_manage_money']+$itemarr['fee']+$renewal_fee,2);

		$itemarr['date']        = $now+$dayF*3600*24;
		$itemarr['due_rate']    = "逾期利息是借款金额的".$itemF['due_rate']."%/天 。如借款".$info['money']."元，每天".get_due_fee($info['money'], $itemF['id'], 1)."元。";
		$itemarr['late_rate']   = "逾期管理费是借款金额的".$itemF['late_rate']."%/天 。如借款".$info['money']."元，每天".get_late_fee($info['money'], $itemF['id'], 1)."元。";
		$dayList = array();
		for($i=0; $i<count($renewal_day); $i++) {
			$dayList[$i]['day'] = $renewal_day[$i];
		}

		//获取renewal_id的集合
		$m = $this->getCount($info['id']);
		//去除空数组
		$m = array_filter(explode(',',$m));
		//获取续期次数
		$count = count($m)?count($m):0;
		$global = get_global_setting();
		if(intval($global['renewal_num']) - $count == 1){
			$status = 3;
		}
		//查询是否申请了续期
		$is_renewal = M('payoff_apply')->field('id')->where(" detail_id = {$info['detail_id']} and type = 1 and status = 0")->find();
		if($is_renewal){
			$is_repayment = 1;
		}else{
			$is_repayment = 0;
		}
		$this->assign('status',$status);
		$this->assign('is_repayment',$is_repayment);
		$this->assign('itemarr',$itemarr);
		$this->assign('dayList',$dayList);
		$this->assign('member',$member);
		$this->assign('info',$info);
		$this->display();
	}

	/**
	 * 授信费收取页面(续期)
	 */
	public function shouxin(){
		$detailid   = $_POST['detail_id'];
		$money      = $_POST['money'];
		$day        = $_POST['day'];
		$selectTick = $_POST['selectTick'];
		$bid 		= $_POST['bid'];
		if(empty($bid)){
			$this->redirect('/Member/currborrow');	
		}
		S('global_setting',NULL);//测试
		$global   = get_global_setting();
        $amount   = $global['credit_amount'];//原价
        $discount = $global['credit_discount'];//折扣价

		$this->assign('detailid',$detailid);
		$this->assign('money',$money);
		$this->assign('day',$day);
		$this->assign('selectTick',$selectTick);
		$this->assign('bid',$bid);
		$this->assign('credit_amount',$amount);
		$this->assign('credit_discount',$discount);

		$this->display();
	}

	/**
     * 检查是否可以续期(授信页)
     */
    public function checkUserRenewalAuth2(){
        $uid = $_SESSION['uid'];
        $demo = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['bid']} and scene = 2 and status = 0")->count('id');
        $apply = M("borrow_apply")->where("id = {$_POST['bid']}")->field('status')->find();
        if(!empty($demo) && $apply['status'] != 5){
            ajaxmsg('请等待',1);//支付处理中
        }else{
        	if($apply['status'] == 5){
        		ajaxmsg('此单已经支付完成,清勿重复提交',2); //已经支付
        	}
            ajaxmsg('',0); //往下走
        }
    }

	/**
	 * 续期付款页面
	 */
	public function renewalPay() {
		if (!isset($_SESSION['uid'])){
			$this->redirect('/Member/regist');
		}
		$uid        = $_SESSION['uid'];
		$detailid   = $_POST['detail_id'];
		$money      = $_POST['money'];
		$day        = $_POST['day'];
		$selectTick = $_POST['selectTick'];
		$amount     = $_POST['amount'];
		$sumoney    = round($amount + $money,2);

		$datag = get_global_setting();
		//获取还款金额的限制  本金的倍数
		$repayment_limit = $datag['repayment_limit'];
		//获取本金
		$borrow_info = M('borrow_detail')->field('capital,borrow_id')->where("id = {$detailid} and uid = {$_SESSION['uid']}")->find();
		if($borrow_info){
			$limit_money = $borrow_info['capital']*$repayment_limit;
			if($money > $limit_money && $limit_money != 0){
				$money = $limit_money;
			}
		}
		$bankCard = M("member_bank")->field('bank_card,id')->where("uid = $uid")->select();
		foreach ($bankCard as $row){
			$row['bank_card'] = "尾号(".substr($row['bank_card'], -4).")";
			$data[] = $row;
		}
		$off = haveOff($detailid);
		$this->assign('off',$off);

		$this->assign('bankcard',$data);
		$this->assign('tickId',$selectTick);
		$this->assign('day',$day);
		$this->assign('money',$money);
		$this->assign('bid',$borrow_info['borrow_id']);
		$this->assign('sumoney',$sumoney);//授信费+续期费用

		//原来的金额
		$this->assign('old_money',$_POST['money']);
		$this->assign('bankcard',$data);
		$this->assign('detailid',$detailid);
		$this->display();
	}

	/**
	 * 跳转续期页面检查
	 */
	public function checkRenewalRedir(){

		$apply    	      = M("borrow_apply")->field("item_id")->where("id={$_POST['bid']}")->find();
		$where['id']      = $apply['item_id'];
		$where['is_xuqi'] = 1;
		$itemInfo = M('borrow_item')->field('id')->where($where)->count();
		//检查产品是否存在
		if($itemInfo == 0){
			ajaxmsg('暂时没有可续期的产品',0);
		}else{
			ajaxmsg('',1);
		}
	}

	/**
	 * 宝付续期
	 */
	public function renewalLoansOrder(){
		$money      = $_POST['money'];
		$duration   = $_POST['duration'];
		$tickId     = $_POST['tickId'];
		$detailid   = $_POST['detailid'];

		$map['uid']    = $_SESSION['uid'];
		$map['status'] = 0; 
		$map['id']     = $detailid;
		$borrow_detail = M("borrow_detail")->field(true)->where($map)->find();

		$due_day    = 0;
		$due_fee    = 0;
		$late_fee   = 0;
		if($borrow_detail['id']>0){
			$borrowInfo    = M("borrow_apply")->field(true)->where("uid = {$borrow_detail['uid']} and id = {$borrow_detail['borrow_id']}")->find();
			$itemInfo      = M('borrow_item')->field(true)->where("money = {$borrow_detail['capital']} and duration = {$duration} and is_xuqi = 1")->order('id desc')->find();
			$info          = M("member_coupon")->field('money')->where("id = {$tickId} and status=0 ")->find();
			if(empty($itemInfo)){
				ajaxmsg("暂时没有可续期的产品",0);
			}
			//逾期费用
			$due_days   = get_due_day($borrow_detail['deadline']);
			if($due_days>0){
				$due_fee    = get_due_fee($borrow_detail['capital'],$borrowInfo['item_id'],$due_days);
				$late_fee   = get_late_fee($borrow_detail['capital'],$borrowInfo['item_id'],$due_days);
				$type       = 2;
				$integral   = $borrow_detail['interest']+$due_fee+$late_fee;
				$info2      = "逾期还款".$_POST['money']."元，扣除积分".$integral;
			}else{
				$type       = 1;
				$integral   = $borrow_detail['capital']+$borrow_detail['interest'];
				$info2      = "成功还款".$_POST['money']."元，获得积分".$integral;
			}

			$datag = get_global_setting();
			$withdrawals_fee = $datag['withdrawals_fee'];

			//续期费
			$renewal_fee = $itemInfo['renewal_fee'];
			//贷后管理费
			$audit_fee   = getFloatValue($borrow_detail['capital']*$itemInfo['audit_rate']/100*$duration*100/100,2);
			//账户管理费
			$enabled_fee = $itemInfo['enabled_rate'];
			//支付服务费
			$pay_fee = $itemInfo['pay_fee'];
			//技术服务费
			$created_fee = 0;

			//续期付款总额
			//$total       = getFloatValue($borrow_detail['interest']+$renewal_fee+$audit_fee+$enabled_fee+$due_fee+$late_fee-$info['money'],2);
			$total       = getFloatValue($borrow_detail['interest']+$renewal_fee+$borrowInfo['audit_fee']+$borrowInfo['enabled_fee']+$borrowInfo['created_fee']+$borrowInfo['pay_fee']+$due_fee+$late_fee-$info['money'],2);
			if($total<0){
				ajaxmsg("续期还款的金额不能为空或者负数，请确认！",0);
			}

			//新的money
			if($money != 0 && $money < $total){
				$total = $money;
			}

			if($money<$total){
				ajaxmsg("提交续期还款的金额不对，应为".$total."元",0);
			}

			S('global_setting',NULL);//测试
			$global   = get_global_setting();
            $amount   = $global['credit_amount'];
            $discount = $global['credit_discount'];
            if($discount==0){
                $total = round($amount+$total,2);
            }else{
                $total = round($discount+$total,2);
            }
            wqbLog('续期支付金额:'.$total);
			//宝付支付扣款
			$model = new PaymentAction();
			$res   = $model->requestApi($borrow_detail['uid'], $borrow_detail['id'],2,$total,$_POST['bankId']);

			wqbLog('续期支付返回数据---'.$res);
			//扣款成功
			$res = true;
			if(is_bool($res) && $res){

				//续期优惠券更新
				if($tickId!=''){
					$tickData['status'] = 1;
					$res = M('member_coupon')->where(" id = {$tickId} ")->save($tickData);
				}

				$newArr                = array();
				$newArr['money']       = $money;
				$newArr['duration']    = $duration;
				$newArr['interest']    = round($borrow_detail['capital']*$itemInfo['rate']/100*$duration/360,2);//利息;
				$newArr['total']       = $total;
				$newArr['renewal_fee'] = $renewal_fee;
				$newArr['audit_fee']   = $audit_fee;
				$newArr['enabled_fee'] = $enabled_fee;
				$newArr['created_fee'] = $created_fee;
				$newArr['pay_fee']     = $pay_fee;
				$newArr['due_fee']     = $due_fee;
				$newArr['late_fee']    = $late_fee;
				$newArr['tickId']      = $tickId;

				//生成续期订单
				renewal($borrowInfo, $borrow_detail, $itemInfo, $newArr);

				//发送微信通知
				$wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$borrowInfo['uid']}")->find();
				if($wxInfo['openid']!==''){
					$memberInfo = M("members")->field('iphone')->where("id = {$borrowInfo['uid']}")->find();
					sendWxTempleteMsg12($wxInfo['openid'], $borrowInfo['money'], time(), $memberInfo['iphone'], $duration);
				}

				//会员积分变更
				addIntegral($borrowInfo['uid'],$type,$integral,$info2);

				//白骑士芝麻数据反馈接口
				$datag        = get_global_setting();
				$zhima        = $datag['zhima_data'];
				if($zhima == 1){
					bqszhimaOrder($borrowInfo['id'],$borrowInfo['uid'],2,"");
				}

				ajaxmsg('续期成功',1);
			}else{
				ajaxmsg('续期失败',0);
			}
		}else{
			ajaxmsg('请确认还款状态',0);
		}

	}

	/**
	 * 微信续期
	 */
	public function renewalLoansWechat(){
		$money      = $_POST['money'];
		$duration   = $_POST['duration'];
		$tickId     = $_POST['tickId'];
		$detailid   = $_POST['detailId'];

		$map['uid']    = $_SESSION['uid'];
		$map['status'] = 0;
		$map['id']     = $detailid;
		$borrow_detail = M("borrow_detail")->field(true)->where($map)->find();

		wqblog("uid---".$_SESSION['uid']);
		wqblog("bid---".$detailid);

		$due_day    = 0;
		$due_fee    = 0;
		$late_fee   = 0;
		if($borrow_detail['id']>0){
			$borrowInfo    = M("borrow_apply")->field(true)->where("uid = {$borrow_detail['uid']} and id = {$borrow_detail['borrow_id']}")->find();
			$itemInfo      = M('borrow_item')->field(true)->where("money = {$borrow_detail['capital']} and duration = {$duration} and is_xuqi = 1")->order('id desc')->find();
			$info          = M("member_coupon")->field('money')->where("id = {$tickId} and status=0 ")->find();

			if(empty($itemInfo)){
				ajaxmsg("暂时没有可续期的产品",0);
			}
			//逾期费用
			$due_days   = get_due_day($borrow_detail['deadline']);
			$due_fee    = get_due_fee($borrow_detail['capital'],$borrowInfo['item_id'],$due_days);
			$late_fee   = get_late_fee($borrow_detail['capital'],$borrowInfo['item_id'],$due_days);
			

			$datag = get_global_setting();
			$withdrawals_fee = $datag['withdrawals_fee'];

			//续期费
			$renewal_fee = $itemInfo['renewal_fee'];
			//贷后管理费
			$audit_fee   = getFloatValue($borrow_detail['capital']*$itemInfo['audit_rate']/100*$duration*100/100,2);
			//账户管理费
			$enabled_fee = $itemInfo['enabled_rate'];
			//支付服务费
			$pay_fee = $itemInfo['pay_fee'];
			//技术服务费
			$created_fee = 0;

			//续期付款总额
			//$total       = getFloatValue($borrow_detail['interest']+$renewal_fee+$audit_fee+$enabled_fee+$due_fee+$late_fee-$info['money'],2);
			$total       = getFloatValue($borrow_detail['interest']+$renewal_fee+$borrowInfo['audit_fee']+$borrowInfo['enabled_fee']+$borrowInfo['created_fee']+$borrowInfo['pay_fee']+$due_fee+$late_fee-$info['money'],2);
			if($total<0){
				ajaxmsg("续期还款的金额不能为空或者负数，请确认！",0);
			}

			//新的money
			if($money != 0 && $money < $total){
				$total = $money;
			}

			if($money<$total){
				ajaxmsg("提交续期还款的金额不对，应为".$total."元",0);
			}

			S('global_setting',NULL);//测试
			$global   = get_global_setting();
            $amount   = $global['credit_amount'];
            $discount = $global['credit_discount'];
            if($discount==0){
                $total = round($amount+$total,2);
            }else{
                $total = round($discount+$total,2);
            }
            
            wqbLog('续期支付金额:'.$total);
			//微信支付扣款
            $res = wechatPay($borrow_detail['uid'],$borrow_detail['borrow_id'],$itemInfo['id'],$total,2,1);
            if($res){
            	ajaxmsg($res,1);//返回支付二维码url
            }

			wqbLog('续期支付返回数据---'.$res);
			
		}else{
			ajaxmsg('请确认还款状态',0);
		}
	}

	//其他银行卡付款页(续期)
	public function renewalOthercard(){
		if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }

		$money    = $_POST['money'];
		$duration = $_POST['duration'];
		$tickId   = $_POST['tickId'];
		$detailid = $_POST['detailid'];

		if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $bankList = C("SINA_BANK_NAME");
        $length = count($bankList);
        for ($i=1 ; $i<$length;$i++){
            $str .= "<option value='{$bankList[$i]}'>".$bankList[$i]."</option>";
        }
        $this->assign('str',$str);
		$this->assign('money',$money);
		$this->assign('duration',$duration);
		$this->assign('tickId',$tickId);
		$this->assign('detailid',$detailid);
		$this->display();
	}

	/**
	 * 判断用户是否可以续期
	 */
	public function checkUserRenewalAuth(){
		$now = time();
		$uid = $_SESSION['uid'];
		$map['id'] = $_POST['b_id'];
		$map['uid']= $uid;
		$duration  = $_POST['duration']; 

		$where['uid']    = $uid;
		$where['status'] = 0;
		$where['id']     = $_POST['detail_id'];
		$borrow_detail   = M("borrow_detail")->field(true)->where($where)->find();

		$itemInfo        = M('borrow_item')->field('id')->where("money = {$borrow_detail['capital']} and duration = {$duration} and is_xuqi = 1")->count();

		//检查产品是否存在
		if($itemInfo == 0){
			ajaxmsg('请重新选择续期天数',0);
		}

		$demo = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['b_id']} and scene = 2 and status = 0")->count('id');
		$apply = M("borrow_apply")->where("id = {$_POST['b_id']}")->field('status')->find();
		if(!empty($demo) && $apply['status'] != 5){
			ajaxmsg('请等待支付完成',2);
		}
		if($apply['status'] == 5){
			ajaxmsg('您已经续期成功,无需再续期',3);
		}

		$binfo = M("borrow_apply")->field('id,deadline,item_id')->where($map)->find();
		//获取renewal_id的集合
		$m = $this->getCount($binfo['id']);
		//去除空数组
		$m = array_filter(explode(',',$m));
		//获取续期次数
		$count = count($m);
		$global = get_global_setting();
		if(intval($global['renewal_num']) <= $count){
			ajaxmsg("您已续期".$count."次，请前往还款",0);
		}
		$rinfo = M("borrow_item")->field('xuqi_day')->where("id = {$binfo['item_id']}")->find();
		if ($binfo['deadline'] > $now){
			$dif = floor(($binfo['deadline'] - $now)/3600/24);
			if ($dif > $rinfo['xuqi_day']){
				ajaxmsg('还款时间充裕,无需续期',0);
			}else {
				ajaxmsg('',1);
			}
		}else {
			ajaxmsg('',1);
		}
	}

	/*
	 * 获取续期的次数 根据当前的id获取续期id的集合
	 */
	public function getCount($id){
		$map['uid']= $_SESSION['uid'];
		$map['id'] =  $id;
		$pids = '';
		$info = M("borrow_apply")->field('id,renewal_id')->where($map)->find();
		if($info['renewal_id']){
			$pids .= $info['renewal_id'];
			$npids = $this->getCount($pids);
			if(isset($npids)){
				$pids .= ','.$npids;
			}
		}
		return $pids;
	}

	/**
	 * 计算续期还款时间
	 */
	public function countRepayTime() {
		$selectDay   = $_POST['day'];
		$time        = $_POST['time'];
		$money       = $_POST['money'];
		$interest    = $_POST['interest'];
		$due_money   = $_POST['due_money'];
		$due_manage  = $_POST['due_manage'];
		$tickMoney   = $_POST['tickMoney'];
		$fee         = $_POST['fee'];

		$itemInfo    = M("borrow_item")->field(true)->where("money = {$money} and duration = {$selectDay} ")->find();

		$renewal_fee = $itemInfo['renewal_fee'];
		$audit_fee   = getFloatValue($money*$itemInfo['audit_rate']/100*$selectDay*100/100,2);
		$enabled_fee = $itemInfo['enabled_rate'];
		$pay_fee     = $itemInfo['pay_fee'];
		$created_fee = 0;
		$total       = getFloatValue($money*$itemInfo['rate']/100*$selectDay/360+$money+$audit_fee+$enabled_fee+$created_fee+$pay_fee,2);//本息+费用

		$due_total   = getFloatValue($interest+$fee+$due_money+$due_manage+$renewal_fee-$tickMoney,2);
		$totalTime   = time()+$selectDay*3600*24;
		$realTime    = date('Y-m-d',$totalTime);
		ajaxmsg($realTime.",".$total.",".$due_total,1);
	}


	/**
	 * 线下还款
	 */
	public function repaymentOffline(){

		//账单ID
		$detail_id = intval($_REQUEST['detail_id']);

		//接收金额
		$money = $_REQUEST['money'];

		//接收优惠券id
		$ticket_id = intval($_REQUEST['ticket_id']);
		$coupon    = M("member_coupon")->field('money')->where(" id = {$ticket_id} and status=0 ")->find();

		//接收续期天数
		$xuqi_days = intval($_REQUEST['xuqi_days']);

		//接收转账类型
		$type = $_REQUEST['type'];
		//获取原来的数据
		$old_info = M('payoff_apply')->field('uid,type,account,money,memo')->where("detail_id = {$detail_id}  and status in (0,1) and type = {$type}")->find();
		if($old_info){
			$arr = explode('：',$old_info['memo']);
			$name = explode(',',$arr[1]);
			$old_info['name'] = $name[0];
			$old_info['mobile'] =  $arr[2];
			$status = 1;
		}else{
			$status = 0;
		}
		$this->assign('status',$status);
		$this->assign('old_info',$old_info);
		$this->assign('type', $type);
		$this->assign('ticket_id', $ticket_id);
		$this->assign('coupon', $coupon['money']);
		$this->assign('xuqi_days', $xuqi_days);
		$this->assign('detail_id',$detail_id);
		$this->assign('money',$money);
		$this->display();
	}

	/**
	 * 添加线下还款申请
	 */
	public function addPayApply(){
		$detail = M("borrow_detail")->field(true)->where("uid = {$_SESSION['uid']} and id = {$_POST['detail_id']}")->find();
		$data['uid']       = $_SESSION['uid'];
		$data['borrow_id'] = $detail['borrow_id'];
		$data['detail_id'] = $_POST['detail_id'];
		$data['type']      = $_POST['aim'];
		$data['account']   = $_POST['bank_num'];
		$data['money']     = $_POST['money'];
		$data['memo']      = "姓名：".$_POST['real_name'].",手机号：".$_POST['phone'];
		$data['add_time']  = time();
		//接收优惠券的id
		$data['ticket_id'] = $_POST['ticket_id'];
		$data['xuqi_days'] = $_POST['xuqi_days'];
		wqbLog($data);
		wqbLog($detail);
		$res = M("payoff_apply")->add($data);
		if ($res){
			if (!empty($data['ticket_id'])) {
				//修改状态为已使用
				$datapon['status'] = 1;
				$res = M('member_coupon')->where("id = {$data['ticket_id']}")->save($datapon);
			}
			ajaxmsg('申请已提交，请等待审核',1);
		}else {
			ajaxmsg('提交申请失败',0);
		}
	}


}