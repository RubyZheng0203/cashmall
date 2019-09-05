<?php
require_once APP_PATH.'/Lib/Action/Home/PaymentAction.class.php';
set_time_limit(0);

class AutoDueAction extends HCommonAction {
	private $updir = null;
	public function _MyInit() {
		$this->updir = dirname(C("WEB_ROOT")) . "/cashmall/AutoDo/";
		$this->rlock = $this->updir . "due.lock";
	} 

	public function autorepayment() {
		//判断是否存在文件锁
		if (file_exists($this->rlock)) {
			$lock_time = file_get_contents($this->rlock);
			if ((time() - $lock_time) > 12 * 3600) {
				// 大于12小时则解除锁定
				unlink($this->rlock);
			} else {
				return false;
			}
		}
		//确认密钥
		$key = $_GET['key'];
		$arg = file_get_contents($this -> updir . "config.txt");
		$arga = explode("|", $arg);
		$rate = intval($arga[1]);
		//if ($key != $arga[2]) exit("fail|密钥错误");
		//开启锁定
		file_put_contents($this->rlock, time());
		//开始扣款
		$pre = C("DB_PREFIX");
		$strOut = "-----------正在执行代扣还款程序：服务器当前时间" . date("Y-m-d H:i:s", time()) . "---------------";
		
		$datag        = get_global_setting();
		$charge_times = $datag['charge_times'];
		$zhima        = $datag['zhima_data'];
		$map = array();
		$map['hope_charge_time'] = array("lt", time());
		$map['charge_times'] 	 = array("egt", $charge_times);
		$map['status'] 	         = 0;
		$list = M("borrow_detail") -> field("id,uid,borrow_id,capital,interest,sort_order,total,renewal_fee,renewal_id,due_fee,late_fee,status,add_time,deadline,repayment_time,hope_charge_time,charge_times") -> where($map)->order("hope_charge_time")->limit('3')-> select();
		$i = 0;
		if (is_array($list) && !empty($list)) {
		    foreach ($list as $v) {
		        if($v['uid']>0){
		            $apply = M("borrow_apply") -> field(true) -> where("id = ".$v['borrow_id']) -> find();
		            if($apply['repayment_type'] == 1){
		                $day = $apply['duration']."天";
		            }else if($apply['repayment_type'] == 2){
		                $day = $apply['duration']."周";
		            }else if($apply['repayment_type'] == 3){
		                $day = $apply['duration']."个月";
		            }else if($apply['repayment_type'] == 4){
		                $day = $apply['duration']."个季度";
		            }else{
		                $day = $apply['duration']."年";
		            }
		            $mem   =  M("members") -> field(true) -> where("id = ".$v['uid']) -> find();
		            if($mem['id']>0){

		                $due_day   = 0;
		                $due_fee   = 0;
		                $late_fee  = 0;
		                $due_day   = get_due_day($v['deadline']);
		                if($due_day >0){
		                    $due_fee  = get_due_fee($apply['money'],$apply['item_id'],$due_day);
		                    $late_fee = get_late_fee($apply['money'],$apply['item_id'],$due_day);
		                }
		                
		                if($apply['is_new']==1){
		                    $fee      = getFloatValue($apply['audit_fee']+$apply['enabled_fee']+$apply['created_fee']+$apply['pay_fee'],2);
		                }else{
		                    $fee      = 0;
		                }
		                $money   = getFloatValue($v['capital']+$v['interest']+$fee+$due_fee+$late_fee,2);
		                
		                $bank    =  M("member_bank") -> field(true) -> where("type = 1 and uid = ".$v['uid']) -> find();
		                
		                $payment = new PaymentAction();
		                //宝付扣款
		                $res = $payment->requestApi($v['uid'], $v['borrow_id'], 1, $money,$bank['id']);
						$memwechat =  M("member_wechat_bind") -> field(true) -> where("uid = ".$v['uid']) -> find();
		                if(is_bool($res) && $res){
		                    $reswechat = getWechatmsg8($memwechat['openid'],date("Y-m-d",$v['add_time']),$apply['money'],$day,$money);
		                    wechatLog("微信自动扣款成功：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                    //账单表更新
							$now    = time();
							$update = M()->query("update ml_borrow_detail set status = 1,due_fee = {$due_fee}, late_fee = {$late_fee},repayment_time = {$now} where uid = {$v['uid']} and id = {$v['id']}");

							if($v['total']==$v['sort_order']){
								$upapply = M()->query("update ml_borrow_apply set status = 5, repayment_time = {$now} where  id = {$v['borrow_id']} ");
								$strOut .= "扣款成功,已经成功还款;";
							}else{
								$strOut .="扣款成功,已经成功还款;";
							}
		                    //会员积分变更
		                    if($due_day >0){
		                        $type = 2;
		                        $integral = $money-$v['capital'];
		                        $info = "逾期还款".$money."元，扣除积分".$integral;
		                    }else{
		                        $type = 1;
		                        $integral = $money;
		                        $info = "成功还款".$money."元，获得积分".$integral;
		                    }
		                    addIntegral($v['uid'],$type,$integral,$info);
		                    
		                    //删除用户操作记录
		                    delUserOperation($v['uid'],$v['borrow_id']);
		                   
		                }else {
		                    $smsTxt = FS("Webconfig/smstxt");
		                    switch ($due_day){
		                        case 0:
		                            $reswechat = sendWxTempleteMsg13($memwechat['openid'],date("Y-m-d",$v['add_time']),$apply['money'],$day,date("Y-m-d",$v['deadline']),$money,substr($bank['bank_card'],-4),$bank['bank_name']);
		                            //$ressms    = addToSms($mem['iphone'],str_replace(array("#DATE#", "#MONEY#", "#BANK#","#BANKNAME#", "#AMOUNT#"), array(date("Y-m-d",$v['add_time']),$apply['money'],substr($bank['bank_card'],-4),$bank['bank_name'],$money), $smsTxt['repayment_before']));
		                            wechatLog("微信当天自动扣款失败后催款：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                            break;
		                        case 1:
		                            //$ressms    = addToSms($mem['iphone'],str_replace(array("#NAME#", "#MONEY#"), array($meminfo['real_name'], $apply['money']), $smsTxt['due_one']));
		                            $reswechat = sendWxTempleteMsg10($memwechat['openid'],$meminfo['real_name'],date("Y-m-d",$v['add_time']),$apply['money'],$day,date("Y-m-d",$v['deadline']),$money,$due_day,$due_fee,$late_fee);
		                            wechatLog("微信逾期一天内自动扣款失败后催款：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                            break;
		                        case 2:
		                            $reswechat = sendWxTempleteMsg10($memwechat['openid'],$meminfo['real_name'],date("Y-m-d",$v['add_time']),$apply['money'],$day,date("Y-m-d",$v['deadline']),$money,$due_day,$due_fee,$late_fee);
		                            wechatLog("微信逾期两天内自动扣款失败后催款：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                            break;
		                        case 3:
		                            $reswechat = sendWxTempleteMsg10($memwechat['openid'],$meminfo['real_name'],date("Y-m-d",$v['add_time']),$apply['money'],$day,date("Y-m-d",$v['deadline']),$money,$due_day,$due_fee,$late_fee);
		                            wechatLog("微信逾期三天内自动扣款失败后催款：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                            break;
		                        case 4:
		                            $reswechat = sendWxTempleteMsg10($memwechat['openid'],$meminfo['real_name'],date("Y-m-d",$v['add_time']),$apply['money'],$day,date("Y-m-d",$v['deadline']),$money,$due_day,$due_fee,$late_fee);
		                            wechatLog("微信逾期四天内自动扣款失败后催款：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                            break;
		                        case 7:
		                            $memrel    =  M("member_relation") -> field(true) -> where("uid = ".$v['uid']) -> find();
		                            //$ressms    = addToSms($mem['iphone'],str_replace(array("#NAME#", "#DATE#", "#MONEY#", "#DUEFEE#", "#LATEFEE#", "#AMOUNT#"), array($meminfo['real_name'], date("Y-m-d",$v['add_time']),$apply['money'],$due_fee,$late_fee,$money), $smsTxt['due_seven']));
		                            $reswechat = sendWxTempleteMsg11($memwechat['openid'],$meminfo['real_name'],date("Y-m-d",$v['add_time']),$apply['money'],$day,date("Y-m-d",$v['deadline']),$money,$due_day,$due_fee,$late_fee,$memrel['iphone1'],$memrel['iphone2']);
									//白骑士芝麻数据反馈接口
									if($zhima == 1){
										bqszhimaOrder($v['borrow_id'],$v['uid'],1,"");
									}
		                            wechatLog("微信逾期七天自动扣款失败后催款：还款人uid-".$v['uid']."账单编号ID-".$v['id']."发送结果-".$reswechat['errmsg']);
		                            break;
		                        default:
		                            break;
		                    }
		                    $mod = ($v['charge_times']+1)%3;
		                    if($mod==1){
		                        $data['hope_charge_time'] = $v['hope_charge_time'] + 6*3600;
		                    }elseif($mod==2){
		                        $data['hope_charge_time'] = $v['hope_charge_time'] + 6*2*3600;
		                    }else{
		                        $data['hope_charge_time'] = $v['hope_charge_time'] + 6*3*3600;
		                    }
		                    $data['charge_times']     = $v['charge_times']+1;
		                    $update = M('borrow_detail')->where("id = ".$v['id'])->save($data);
		                    
		                    $strOut .= "扣款失败，还款失败;";
		                }
		            }else{
		                $strOut .= "借款人信息不存在！";
		            }
		        }
		        $i++;
		    }
		}else{
		    $strOut .="\r\n". "-----------没有要还款的借款-----------";
		}
		//解除锁定
		unlink($this->rlock);
		$data = $strOut . "\r\n" . "-----------代扣还款完成：服务器当前时间".date("Y-m-d H:i:s", time()); //服务器时间
		echo $data;
	} 
} 
