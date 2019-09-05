<?php

/**
 * 线下还款审核
 * @author Rubyzheng
 *
 */
class PayOffAction extends ACommonAction
{

    /**
     * 线下还款申请一览
     */
    public function index()
    {
		$map = array();
		if($_REQUEST['uid']){
		    $map['m.uid']   = urldecode($_REQUEST['uid']);
		    $search['uid'] = $map['m.uid'];	
		}
		
		if($_REQUEST['mobile']){
		    $map['mi.iphone']   = urldecode($_REQUEST['mobile']);
		    $search['mobile'] = $map['mi.iphone'];
		}
		
		if($_REQUEST['methodid'] ){
		    $map['m.type'] = $_REQUEST['methodid'];
		    $search['methodid'] = $map['m.type'];
		}
		
		if($_REQUEST['status']){
		    if($_REQUEST['status'] == 1){
		        $statuss = 0;
		    }else if($_REQUEST['status'] == 2){
		        $statuss = 1;
		    }else {
		        $statuss = 2;
		    }
		    $map['m.status'] = $status;
		    $search['status'] = $_REQUEST['status'];
		}
		
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.add_time']     = array("between",$timespan);
			$search['start_time']    = strtotime(urldecode($_REQUEST['start_time']));	
			$search['end_time']      = strtotime(urldecode($_REQUEST['end_time']));	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.add_time']     = array("gt",$xtime);
			$search['start_time']    = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.add_time']     = array("lt",$xtime);
			$search['end_time']      = $xtime;	
		}
		
		if(!empty($_REQUEST['start_time1']) && !empty($_REQUEST['end_time1'])){
		    $timespan = strtotime(urldecode($_REQUEST['start_time1'])).",".strtotime(urldecode($_REQUEST['end_time1']));
		    $map['m.audit_time']     = array("between",$timespan);
		    $search['start_time1']    = strtotime(urldecode($_REQUEST['start_time1']));
		    $search['end_time1']      = strtotime(urldecode($_REQUEST['end_time1']));
		}elseif(!empty($_REQUEST['start_time1'])){
		    $xtime = strtotime(urldecode($_REQUEST['start_time1']));
		    $map['m.audit_time']     = array("gt",$xtime);
		    $search['start_time1']    = $xtime;
		}elseif(!empty($_REQUEST['end_time1'])){
		    $xtime = strtotime(urldecode($_REQUEST['end_time1']));
		    $map['m.audit_time']     = array("lt",$xtime);
		    $search['end_time1']      = $xtime;
		}
		
		
		//分页处理
		import("ORG.Util.Page");
		$count = M('payoff_apply m')->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";

		$field = "m.id,m.uid,m.borrow_id,m.detail_id,m.type,m.account,m.money,m.xuqi_days,m.memo,m.add_time,m.audit_user,m.audit_time,m.reason,m.status,md.capital,md.interest,md.deadline,md.status as paystatus,md.repayment_time,md.due_fee,md.late_fee,ma.item_id,m.ticket_id,ma.money as borrow_money,ma.duration,ma.renewal_fee,ma.audit_fee,ma.enabled_fee,ma.created_fee,ma.pay_fee,ma.is_new,mi.real_name";
		//sql查询
		$list  = M('payoff_apply m')
		//->field(true)
		->field($field)
		->join("{$this->pre}borrow_apply ma ON ma.id=m.borrow_id")
		->join("{$this->pre}borrow_detail md ON md.id=m.detail_id")
		->join("{$this->pre}member_info mi ON mi.uid=ma.uid")
		->where($map)->limit($Lsql)->order('m.id DESC')->select();
		$list  = $this->_listFilter($list);

		$this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        $method  = C('REPAYMENT_METHODS');
        $this->assign("method", $method);
        $statusList = array(
            1   => '未审核',
            2   => '已通过',
            3   => '不通过',
        );
        $this->assign("statusList", $statusList);
        $this->display();
    }
    
    /**
     * @param  数组集合 $list
     * @return 数组 
     */
	public function _listFilter($list){
	    $method  = C('REPAYMENT_METHODS');
		$row       = array();
		foreach($list as $key=>$v){
		    if($v['paystatus'] == 0){
		        $dueDay = get_due_day($v['deadline']);
				$v['dueDay']    = intval($dueDay);
		    }else{
				$v['dueDay'] = intval(($v['repayment_time']-$v['deadline'])/3600/24);
		    }
			//获取borrow_item表
			$borrow_item = M('borrow_item')->field("due_rate,late_rate")->where("duration = {$v['duration']} and money = {$v['borrow_money']}")->find();
			if($v['dueDay']>1){
				$v['due_fee']   = get_due_fee($v['borrow_money'],$v['item_id'],$v['dueDay']);
				$v['late_fee']  = get_late_fee($v['borrow_money'],$v['item_id'],$v['dueDay']);
			}else{
				$v['dueDay']    = 0;
				$v['due_fee']   = 0;
				$v['late_fee']  = 0;
			}
		    $v['type_name'] = $method[$v['type']];
		    
		    switch ($v['type']) {
		        case 1:
		            $v['is_type'] = '还款';
		            break;
		        case 2:
		            $v['is_type'] = '续期';
		            break;
		        default:
		            $v['is_type'] = '未知';
		            break;
		    }
			//查找优惠券的金额
			$coupon_money = M('member_coupon')->field('money')->where("id = {$v['ticket_id']}")->find();
			$v['coupon_money'] = $coupon_money['money'] ? $coupon_money['money'] : '0.00';
			if($v['is_new']==1){
				$fee      = getFloatValue($v['audit_fee']+$v['enabled_fee']+$v['created_fee']+$v['pay_fee'],2);
			}else{
				$fee      = 0;
			}
			if($v['type'] == 1){
				//应还总额
				$v['total_money']    = getFloatValue($v['capital']+$v['interest']+$v['due_fee']+$v['late_fee']+$fee-$v['coupon_money'],2);
			}else{
				$itemInfo      = M('borrow_item')->field(true)->where("money = {$v['capital']} and duration = {$v['xuqi_days']} ")->find();
				$v['total_money']    = getFloatValue($v['interest']+$itemInfo['renewal_fee']+$v['audit_fee']+$v['enabled_fee']+$v['created_fee']+$v['pay_fee']+$v['due_fee']+$v['late_fee']-$v['coupon_money'],2);
			}

			$datag = get_global_setting();
			//获取还款金额的限制  本金的倍数
			$repayment_limit = $datag['repayment_limit'];
			//获取本金
			if($v['capital']){
				$limit_money = $v['capital']*$repayment_limit;
				if($v['total_money'] > $limit_money && $limit_money != 0){
					$v['total_money'] = $limit_money;
				}
			}

			$row[$key]=$v;
		}
		return $row;
	}
	
	/**
	 * 审核页面
	 */
    public function edit(){
		setBackUrl();
        $id = intval($_GET['id']);
		//查找是否已经通过
		$payoff = M('payoff_apply')->field("detail_id")->where("id = {$id}")->find();
		$status = M('borrow_detail')->field('status')->where("id = {$payoff['detail_id']}")->find();
		$this->assign("status",$status['status']?$status['status']:0);
		$this->assign("id",$id);
		$this->display();

	}
	
	/**
	 * 
	 */
	public function doEdit(){
		$id     = intval($_POST['id']);
		//该笔银行卡续期成功，审核拒绝
		if(intval($_POST['is_status']) == 1){
			$status = 2;
		}else{
			$status = intval($_POST['status']);
		}
		$reason = $_POST['reason'];
		//获取还款时间
		$repayment = $_POST['repayment_time'];
		if (empty($repayment) && $status == 1) {
			$this->error("请选择放款时间");
		}
		$repayment_time = strtotime($repayment);
		$statusx = M('payoff_apply')->getFieldById($id,"status");
		if ($statusx!=0){
			$this->error("请不要重复提交表单");
		}

		if($status==1){
		    $apply     = M('payoff_apply')->field(true)->where("id={$id}")->find();
		    $borrow    = M('borrow_apply')->field(true)->where("id={$apply['borrow_id']}")->find();
		    $detail    = M('borrow_detail')->field(true)->where("id={$apply['detail_id']} and borrow_id={$apply['borrow_id']}  and uid = {$apply['uid']} ")->find();
		    $itemInfo  = M('borrow_item')->field(true)->where("money = {$borrow['money']} and duration = {$apply['xuqi_days']}")->find();

		    $due_day   = 0;
		    $due_fee   = 0;
		    $late_fee  = 0;
		    $ticket    = 0;
		    $total     = 0;
		    //获取逾期天数
		    $due_day      = $this->due_day($detail['deadline'],$repayment_time);
		    if($due_day >0){
		        $due_fee  = get_due_fee($borrow['money'],$borrow['item_id'],$due_day);
		        $late_fee = get_late_fee($borrow['money'],$borrow['item_id'],$due_day);
		    }
		    //优惠券
		    if($apply['ticket_id']>0){
		        $coupon   = M('member_coupon')->field(true)->where("id = {$apply['ticket_id']}")->find();
		        $ticket   = $coupon['money'];
		    }
			$datag = get_global_setting();
			//获取还款金额的限制  本金的倍数
			$repayment_limit = $datag['repayment_limit'];
			$limit_money = $borrow['money']*$repayment_limit;

		    if($apply['type']==2){//续期
		        wqbLog("xuqi");

				//续期费
				$renewal_fee = $itemInfo['renewal_fee'];
				//贷后管理费
				$audit_fee   = getFloatValue($detail['capital']*$itemInfo['audit_rate']/100*$apply['xuqi_days']*100/100,2);
				//账户管理费
				$enabled_fee = $itemInfo['enabled_rate'];
				//支付服务费
				$pay_fee = $itemInfo['pay_fee'];
				//技术服务费
				$created_fee = 0;

				//续期付款总额
				$total       = getFloatValue($detail['interest']+$renewal_fee+$borrow['audit_fee']+$borrow['enabled_fee']+$borrow['created_fee']+$borrow['pay_fee']+$due_fee+$late_fee-$ticket,2);

		        if($total<0){
		            $this->error("还款总金额不能为负数，请确认！");
		        }

				//新的money
				if($apply['money'] != 0 && $apply['money'] < $total && $limit_money == $apply['money']){
					$total = $apply['money'];
				}

		        if($apply['money']<$total){
		            $this->error("提交还款的金额不对，应为".$total."元");
		        }
		        //积分
		        $type        = 2;
		        $integral    = $detail['interest']+$due_fee+$late_fee;
		        $info2       = "逾期还款".$borrow['money']."元，扣除积分".$integral;
		    }else{
				if($borrow['is_new']==1){
					$fee      = getFloatValue($borrow['audit_fee']+$borrow['enabled_fee']+$borrow['created_fee']+$borrow['pay_fee'],2);
				}else{
					$fee      = 0;
				}
				$total    = getFloatValue($detail['capital']+$detail['interest']+$due_fee+$late_fee+$fee-$ticket,2);

		        if($total<0){
		            $this->error("还款总金额不能小于0，请确认！");
		        }
				//新的money
				if($apply['money'] != 0 && $apply['money'] < $total && $limit_money == $apply['money']){
					$total = $apply['money'];
				}

		        if($apply['money']<$total){
		            $this->error("提交还款的金额不对，应为".$total."元");
		        }
                //积分
		        $type        = 1;
                $integral    = $detail['capital']+$detail['interest'];
                $info2       = "成功还款".$borrow['money']."元，获得积分".$integral;
            }

			$save['audit_user'] = session('adminname');
			$save['audit_time'] = time();
			$save['status']     = 1;
			if($reason!=''){
			    $save['reason'] = $reason;
			}
			$updata = M('payoff_apply')->where("id={$id}")->save($save);
			if($updata){
				$datab['repayment_time'] = $repayment_time;
			    $datab['status']         = 1;
			    $datab['due_fee']        = $due_fee;
			    $datab['late_fee']       = $late_fee;
			    $datab['coupon_id']      = $apply['ticket_id'];
			    $updetail = M('borrow_detail')->where("id={$apply['detail_id']} and borrow_id={$apply['borrow_id']}  and uid = {$apply['uid']} ")->save($datab);
			    if($updetail){
			        //所有期数都还款完成
			        if($detail['sort_order']==$detail['total']){
			            $dataapply['status']         = 5;
						$dataapply['repayment_time'] = $repayment_time;
						$dataapply['due_fee']        = $apply['due_fee']+$due_fee;
						$dataapply['late_fee']       = $apply['late_fee']+$late_fee;
			            $upapply = M('borrow_apply')->where("id={$apply['borrow_id']}  and uid = {$apply['uid']} ")->save($dataapply);
			            if($upapply){
			                if($apply['type']==2){
								$this->renewal_new($borrow, $itemInfo,$total,$audit_fee,$enabled_fee,$pay_fee,$repayment_time);
			                }
			                if($borrow['repayment_type'] == 1){
			                    $day = $borrow['duration']."天";
			                }else if($borrow['repayment_type'] == 2){
			                    $day = $borrow['duration']."周";
			                }else if($borrow['repayment_type'] == 3){
			                    $day = $borrow['duration']."个月";
			                }else if($borrow['repayment_type'] == 4){
			                    $day = $borrow['duration']."个季度";
			                }else{
			                    $day = $borrow['duration']."年";
			                }
			                //发送微信
							$memwechat = M("member_wechat_bind") -> field(true) -> where("uid = ".$apply['uid'])->find();
							if($memwechat['openid'] !=""){
							    $res   = getWechatmsg8($memwechat['openid'],date("Y-m-d",$borrow['add_time']),$borrow['money'],$day,$apply['money']);
							}
							
		                    //会员积分变更
							addIntegral($borrow['uid'], $type, $integral, $info2);
							
		                    //白骑士芝麻数据反馈接口
		                    /*$datag        = get_global_setting();
		                    $zhima        = $datag['zhima_data'];
		                    if($zhima == 1){
		                        bqszhimaOrder($borrow['id'],$borrow['uid'],2,"");
		                    }*/
							//如果是还款将Operation删除
							if($apply['type']==1){
								delUserOperation($borrow['uid'],$borrow['id']);
							}
							$this->success("处理成功");
			            }else{
			                $this->error("借款申请状态同步处理失败");
			            }
			        }
			        
			    }else{
			        $this->success("借款明细状态同步处理失败");
			    }
			    
			}else{
			    $this->error("处理失败");
			}
		}else{
			//根据优惠券id 将状态改为未使用
			$ticket_id = M('payoff_apply')->field('ticket_id')->where("id={$id}")->find();
			
			$coupon1['status'] = 0;
			$res = M('member_coupon')->where("id = {$ticket_id['ticket_id']}")->save($coupon1);

			$save['audit_user'] = session('adminname');
			$save['audit_time'] = time();
			$save['status']     = 2;
			if($reason!=''){
			    $save['reason'] = $reason;
			}
			$updata = M('payoff_apply')->where("id={$id}")->save($save);

			if ($updata) {
			    $this->success("处理成功");
			}else{
			    $this->error("处理失败");
			}
		}
	}
	
	/**
	 * 更改实际支付金额
	 */
	public function updateMoney(){
	    $id            = $_POST['id'];
	    $money         = floatval($_POST['money']);
	     
	    $data['money'] = $money;
	    $status = M('payoff_apply')->where(" id = {$id} ")->save($data);
	    if ($status){
	        ajaxmsg("",1);
	    }else {
	        ajaxmsg("",0);
	    }
	}
	
	/**
	 * 更改实际支付金额
	 */
	public function updateDay(){
	    $id            = $_POST['id'];
	    $money         = floatval($_POST['day']);
	
	    $data['xuqi_days'] = $money;
	    $status = M('payoff_apply')->where(" id = {$id} ")->save($data);
	    if ($status){
	        ajaxmsg("",1);
	    }else {
	        ajaxmsg("",0);
	    }
	}

	/**
	 * 续期并重新生成订单和账单
	 * @param array $borrow_info 订单信息
	 * @param array $itemInfo 产品信息
	 * @param double $amount 续期费用
	 * @param double $audit_fee 信息审核费
	 * @param double $enabled_fee 账户动用费
	 * @param int $repayment_time 还款时间
	 * @return boolean
	 */
	private function renewal_new($borrow_info, $itemInfo, $amount, $audit_fee, $enabled_fee,$pay_fee,$repayment_time)
	{
		$model                  = M("borrow_apply");
	    $data['deadline']       = $repayment_time + $itemInfo['duration']*3600*24;
		$data['uid']            = $borrow_info['uid'];
		$data['money']          = $borrow_info['money'];//借款金额
		$data['purpose']        = $borrow_info['purpose'];
		$data['duration']       = $itemInfo['duration'];//借款期限
		$data['repayment_type'] = 1;//还款类型
		$data['rate']           = $itemInfo['rate'];//利率
		$data['item_id']        = $itemInfo['id'];//产品id
		$data['interest']       = round($borrow_info['money'] * $itemInfo['rate']/100*$itemInfo['duration']/360, 2);//利息
		$data['audit_fee']      = $audit_fee;//信息审核费
		$data['created_fee']    = 0;//账户建立费
		$data['enabled_fee']    = $enabled_fee;//账户动用费
		$data['pay_fee'] =  $pay_fee;//支付服务费
		$data['renewal_fee']    = $amount;
		$data['renewal_id']     = $borrow_info['id'];
		$data['status']         = 4;
		$data['audit_status']   = 5;
		$data['add_time']       = time();
		$data['len_time']       = $repayment_time;
		if($borrow_info['is_new'] == 1){
			$data['is_new'] = 1;
		}else{
			$data['is_new'] = 0;
		}
		$data['loan_money']     = $borrow_info['money'];//round($borrow_info['money']-$data['audit_fee']-$data['enabled_fee'], 2);
		wqbLog($data);
		$result    = $model->add($data);
		$orRenewal = true;
		$bool      = lending($result, $orRenewal);
		if ($result && $bool !== false) {
		    //更新operation的borrow_id
		    $updata['borrow_id'] = $result;
		    M("user_operation")->where("uid = {$borrow_info['uid']} and borrow_id = {$borrow_info['id']}")->save($updata);
		    return $bool;
		} else {
			return false;
		}
	}
	
	/**
	 * 计算实际的逾期日期
	 * @param 到期日 $deadline
	 * @param 实际还款日 $repayment
	 */
	private function due_day($deadline,$repayment)
	{
	    if($deadline<1000) return "数据有误";
	    $deadtime   =  strtotime("+1 day",strtotime(date("Y-m-d",$deadline)." 00:00:00"));
	    $starttime  = strtotime(date("Y-m-d",$repayment)." ".date("H:i:s",$deadline));
	    $endtime    = strtotime(date("Y-m-d",$repayment)." 23:59:59");
	    if($repayment>$deadtime){
	        if($repayment>$deadline){
	            if($repayment < $endtime && $repayment> $starttime){
	                return ceil( ($repayment-$deadline)/3600/24-1);
	            }else{
	                return ceil( ($repayment-$deadline)/3600/24);
	            }
	        }else{
	            return 1;
	        }
	    }else{
	        return  0;
	    }
	}
	
	
}
?>