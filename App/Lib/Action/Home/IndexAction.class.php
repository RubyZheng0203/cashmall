<?php
class IndexAction extends HCommonAction{
    
	public function index(){
	    //$this->redirect('/online');
	    $this->display();
	}
	
	public function borrow(){
	    $this->display(); 
	}

	public function message(){
	    $this->display();
	}
	
	public function verifyPhone(){
	    $this->display();
	}
	
	public function browser() {
        $this->display();
	}
	/**
	 * 借款协议
	 */
	public function borrowAgreement(){
	    //借款申请ID
	    $id       = $_GET['id'];
	    $field    = "aa.id,aa.item_id,aa.repayment_type,aa.purpose,aa.money,aa.duration,aa.rate,dd.due_rate,aa.interest,bb.real_name,bb.id_card,bb.address,bb.province,bb.city,cc.bank_card,cc.bank_name";
	    $apply    = M('borrow_apply aa')
	                   ->field($field)
	                   ->join("ml_member_info bb on aa.uid = bb.uid ")
	                   ->join("ml_member_bank cc on aa.uid =cc.uid ")
	                   ->join("ml_borrow_item dd on aa.item_id = dd.id ")
	                   ->where(" aa.id = {$id} and cc.type = 1 ")
	                   ->find();
	    $address  = get_province($apply['province']).get_city($apply['city']).$apply['address'];
		//转大写金额
	    $cny              = get_cny($apply['money']);
	    $apply['cny']     = $cny;
	    $apply['address'] = $address;
	    $apply['time']    = time();
	    $apply['borrow_id']    = str_repeat("0",8-strlen($id)).$id;
	    $this->assign('vo',$apply);
	    $this->display();
	}
	/**
	 * 服务协议
	 */
	public function serviceAgreement(){
	    //借款申请ID
	    $id        = $_GET['id'];
	    $field     = "aa.id,aa.uid,aa.item_id,aa.purpose,dd.due_rate,dd.late_rate,aa.money,aa.duration,aa.rate,aa.late_fee,aa.audit_fee,aa.created_fee,aa.enabled_fee,aa.renewal_fee,aa.pay_fee,aa.interest,bb.real_name,bb.id_card,bb.address,bb.province,bb.city,cc.bank_card,cc.bank_name";
	    $apply     = M('borrow_apply aa')
	                   ->field($field)
	                   ->join("ml_member_info bb on aa.uid = bb.uid ")
	                   ->join("ml_member_bank cc on aa.uid =cc.uid ")
	                   ->join("ml_borrow_item dd on aa.item_id = dd.id ")
	                   ->where(" aa.id = {$id} and cc.type = 1 ")
	                   ->find();
	    $apply['borrow']    = str_repeat("0",8-strlen($id)).$id;
	    $apply['loan_borrow']   = XDM.str_repeat("0",8-strlen($id)).$id;
	    $apply['time']    = time();
	  	$apply['sumoney'] = round($apply['money'] + $apply['interest'],2);
	    $this->assign('vo',$apply);
	    $this->display();
	}
	/**
	 * 关于借款人综合资金成本的警示书
	 */
	public function borrowWarning(){
		$id    = $_GET['id'];
		$apply = M('borrow_apply')->field(true)->where("id = {$id}")->find();
		//续期天数
		$day  = $_GET['day'];
		
		//如果是续期
		if($day==""){
		    $item   = M('borrow_item')->field(true)->where("id = {$apply['item_id']}")->find();
		    //贷后管理费
		    $item['feedai']      = $apply['audit_fee'];
		    $item['created_fee'] = $apply['created_fee'];
		    $item['enabled_rate'] = $apply['enabled_fee'];
		}else{
		    $item   = M('borrow_item')->field(true)->where("id = {$apply['item_id']}")->find();
		    //贷后管理费
		    $audit_fee            = getFloatValue($apply['money']*$item['audit_rate']/100*$day*100/100,2);
		    $item['feedai']       = $audit_fee;
		    $item['created_fee']  = 0;
		}
	
		//综合成本 认证费+账户管理费+贷后管理费+支付服务费+利息）/借款本金/借款期限*360*100%
		$total =  getFloatValue(($item['created_fee']+$item['enabled_rate']+$item['feedai']+$item['pay_fee']+$item['interest'])/$item['money']/$item['duration']*360*100/100*100,2);
		
		//年化  （利息+账户管理费+支付服务费）/借款金额/借款期限*360*100%
		$year_rate  = getFloatValue(($item['interest']+$item['enabled_rate']+$item['pay_fee'])/$item['money']/$item['duration']*360*100/100,2);
		$this->assign('year_rate',$year_rate*100);
		$this->assign('item',$item);
		$this->assign('total',$total);
		$this->display();
	}

	/**
	 * 授权诊断咨询授权书
	 */
	public function diagnosisAuthorization(){
		$uid = $_SESSION['uid'];
        if(empty($uid)){
            $uid = $_GET['uid'];
        }
        if(empty($uid)){
            $this->redirect('/Member/regist');
        }

		$map['uid'] = $uid;
		$member = M('member_info')->where($map)->field('real_name,id_card')->find();
		
		$this->assign('real_name',$member['real_name']);//姓名
		$this->assign('id_card',$member['id_card']);//身份证号

		$this->display();
	}


}