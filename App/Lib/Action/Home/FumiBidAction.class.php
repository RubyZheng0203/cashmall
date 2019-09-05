<?php
// 本类由系统自动生成，仅供测试用途
class FumiBidAction extends HCommonAction {
    /**
     * 接口放款
     */
    public function bid(){
        $array      = json_decode(file_get_contents("php://input"),true);
        $apply      = M("borrow_apply")->field(true)->where("up_bid = {$array['bid']}")->select();
        fumiLog("福米上标1：准备".$array['bid']);
        foreach ($apply as $row){
            $data['is_full']  = 1;
            $data['trade_no'] = $array['trade_no'];
            $update     = M("borrow_apply")->where("id = {$row['id']}")->save($data);
            if($row['renewal_id'] == 0 ){
                $detail     = M("borrow_detail")->field("id")->where("borrow_id = {$row['id']}")->find();
                if (empty($detail)){
                    $this->payCash($row,$array['trade_no']);
                }
            }else{
                //续期转账
                $datag   = get_global_setting();
                $account = $datag['loan_account'];
                $uid     = "fumi".$account;
                $this->toRenewal($row,$uid,$array['trade_no']);
            }
        }
	} 
	
	/**
	 * 手动申请放款
	 */
	public function bidLine(){
	    $bid        = $_POST['bid']; 
	    $apply      = M("borrow_apply")->field(true)->where("up_bid = {$bid}")->select();
	    fumiLog("福米上标2：准备".$bid);
	    foreach ($apply as $row){
	        $data['is_full'] = 1;
	        $update     = M("borrow_apply")->where("id = {$row['id']}")->save($data);
	        if($apply['renewal_id'] == 0 ){
	            $detail     = M("borrow_detail")->field("id")->where("borrow_id = {$row['id']}")->find();
	            if (empty($detail)){
	                $this->payCash($row,$row['trade_no']);
	            }
	        }else{
	            $datag   = get_global_setting();
	            $account = $datag['loan_account'];
	            $uid     = "fumi".$account;
	            $this->toRenewal($row,$uid,$row['trade_no']);
	        }
	    }
	    ajaxmsg("",0);
	}
	
	/**
	 * 新浪代付到用户提现卡
	 */
	
	private function payCash($info,$trade_no){
	    $flg = false;
	    $bankInfo   = M("member_bank")->field('bank_id')->where("uid = {$info['uid']} and type=1 ")->find();
	    if($info['status']>=4){
	        fumiLog("福米上标：借款申请编号---".$info['id']."已经放过款");
	    }else {
	        if (sinaCreateBidInfo($info)){
	            fumiLog("福米上标：借款申请编号---".$info['id']."新浪录入成功！");
	            if (sinaCreateSingleHostingPayToCardTrade($info,$bankInfo['bank_id'],$trade_no)){
	                fumiLog("福米上标：借款申请编号---".$info['id']."新浪放款成功！");
	                $now = time();
	                //更新借款状态
	                $mdata['pending']      = 1;
	                $mdata['pending_time'] = $now;
	                M("member_status")->where("uid = {$info['uid']}  AND borrow_id = {$info['id']} ")->save($mdata);
	    
	                $sdata['status']       = 4;
	                $sdata['audit_status'] = 5;
	                $sdata['len_time']     = $now;
	                $deadline              = $now+3600*24*($info['duration']);
	                $sdata['deadline']     = $deadline;
	                M("borrow_apply")->where("uid = {$info['uid']} AND id = {$info['id']} ")->save($sdata);
	    
	                //新建账单表
	                fumiLog("福米上标：借款申请编号---".$info['id']."账单表创建开始");
	                createRepayOrder($info,$deadline);
	                fumiLog("福米上标：借款申请编号---".$info['id']."账单表创建结束");
	                
	                //发放还款优惠券
	                $count = M("borrow_detail")->where("uid = {$info['uid']} ")->count('id');
	                if($count==1){
	                    $global = get_global_setting();
	                    $ticArr = explode('|', $global['coupon_register']);
	                    $ticData['uid']        = $uid;
	                    $ticData['money']      = $ticArr[0];
	                    $ticData['title']      = "还款优惠券";
	                    $ticData['type']       = '2';
	                    $ticData['status']     = 0;
	                    $ticData['add_time']   = time();
	                    $ticData['start_time'] = time();
	                    $ticData['end_time']   = $deadline+3600*24*$ticArr[1];
	                    $res = M("member_coupon")->add($ticData);
	                }
	                fumiLog("福米上标：借款申请编号---".$info['id']."标满自动放款成功");
	            }
	        }else {
	            fumiLog("福米上标：借款申请编号---".$info['id']."新浪录入失败");
	        }
	    }
	}
	
	/**
	 * 续期转账到指定帐户
	 * @param unknown $info
	 */
	private function toRenewal($info,$uid,$trade_no){
	    if($info['is_balance']==0){
	        $summary = $info['id'];
	        $amount  = $info['money'];
	        sleep(4);
	        $res     = xqHostingPayTrade($trade_no, $summary, $uid, $amount);
	        if($res->success()){
	            $data['is_balance'] = 1;
	            $update = M('borrow_apply')->where(" id = {$info['id']} ")->save($data);
	            if($update>0){
	                fumiLog("福米上标：借款申请编号---".$info['id']."的续期平帐成功");
	            }else{
	                fumiLog("福米上标：借款申请编号---".$info['id']."的续期代付交易成功，更新失败");
	            }
	        }else{
	            fumiLog("福米上标：借款申请编号---".$info['id']."的续期平帐失败，原因：".$res->error());
	        }
	    }else{
	        fumiLog("福米上标：借款申请编号---".$info['id']."的续期已经平好帐了");
	    } 
	}
} 
