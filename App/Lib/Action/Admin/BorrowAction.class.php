<?php
require_once APP_PATH.'/Lib/Action/Home/PaymentAction.class.php';


/**
 * 借款申请
 * @author Rubyzheng
 *
 */
class BorrowAction extends ACommonAction
{
    /**
     * 待初审的页面
     */
    public function index()
    {
		$map = array();
		$map['m.audit_status'] = 0;
		$map['m.status']       = 1;
		
		if($_REQUEST['borrowid']){
		    $map['m.id'] = urldecode($_REQUEST['borrowid']);
		    $search['borrowid']  = $map['m.id'];
		}
		
		if($_REQUEST['uid']){
		    $map['m.uid']   = urldecode($_REQUEST['uid']);
		    $search['uid'] = $map['m.uid'];	
		}
        
		if($_REQUEST['real_name']){
		    $map['mi.real_name'] = urldecode($_REQUEST['realname']);
		    $search['realname']  = $map['mi.real_name'];
		}

		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.add_time']       = array("between",$timespan);
			$search['start_time']    = urldecode($_REQUEST['start_time']);	
			$search['end_time']      = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.add_time']       = array("gt",$xtime);
			$search['start_time']    = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.add_time']       = array("lt",$xtime);
			$search['end_time']      = $xtime;	
		}else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.add_time']       = array("egt",$time);
        }
		
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";
		//sql查询
		$field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline";
		$list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->limit($Lsql)->order('m.id DESC')->select();
		$list  = $this->_listFilter($list);
		$this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }
    
    /**
     * 初审审批页面
     */
    public function approval(){
        setBackUrl();
        $id  = intval($_GET['id']);
        $uid = intval($_GET['uid']);
        
        $this->assign("id",$id);
        $this->assign("uid",$uid);
        $this->display();
    }
    
    /**
     * 处理初审
     */
    public function doapproval(){
        $id     = intval($_POST['id']);
        $uid    = intval($_POST['uid']);
        $status = intval($_POST['status']);
        $reason = $_POST['reason'];
        $statusx = M('member_status')->field("first_trial")->where("borrow_id={$id}")->find();
        if ($statusx['first_trial'] == 1){
            $this->error("请不要重复提交表单");
        }
        if($status==1){
            //添加状态表
            $save['uid']              = $uid;
            $save['borrow_id']        = $id;
            $save['first_trial']      = 1;
            $save['first_trial_uid']  = session('adminname');
            $save['first_trial_time'] = time();
            $save['reason']           = $reason;
            $updata = M('member_status')->where("borrow_id={$id}")->save($save);
            if($updata){
                //更新借款申请表
                $saveb['status']        = 2;
                $saveb['audit_status']  = 1;
                $updatab = M('borrow_apply')->where("id={$id}")->save($saveb);
                if($updatab){
                    $this->success("处理成功");
                }else{
                    $this->error("借款申请同步处理失败");
                }
            }else{
                $this->error("处理失败");
            }
            
        }else{
            //添加状态表
            $save['uid']              = $uid;
            $save['borrow_id']        = $id;
            $save['first_trial']      = 2;
            $save['first_trial_uid']  = session('adminname');
            $save['first_trial_time'] = time();
            $save['reason']           = $reason;
            $updata = M('member_status')->where("borrow_id={$id}")->save($save);
            if($updata){
                //更新借款申请表
                $saveb['status']        = 98;
                $saveb['audit_status']  = 1;
                $updatab = M('borrow_apply')->where("id={$id}")->save($saveb);
                if($updatab){
                    delUserOperation($uid,$id);
                    $this->success("处理成功");
                }else{
                    $this->error("借款申请同步处理失败");
                }
            }else{
                $this->error("处理失败");
            }
            
        }
    }
    
    /**
     * @param  数组集合 $list
     * @return 数组
     */
    public function _listFilter($list){
        $row = array();
        foreach($list as $key=>$v){
            if($v['repayment_type'] == 1){
                $v['type'] = "天";
            }else if($v['repayment_type'] == 2){
                $v['type'] = "周";
            }else if($v['repayment_type'] == 3){
                $v['type'] = "个月";
            }else if($v['repayment_type'] == 4){
                $v['type'] = "个季度";
            }else{
                $v['type'] = "年";
            }
            if($v['sinabid_flg'] == 0){
                $v['sina'] = "新浪标未录入";
            }else if($v['sinabid_flg'] == 1){
                $v['sina'] = "新浪标录入成功";
            }else{
                $v['sina'] = "新浪标录入失败";
            }
            if($v['status']==1){
                $count = M('payoff_apply m')->where("detail_id = {$v['id']} and status = 1")->count('id');
                $v['offpay'] = $count; 
            }else{
                $v['offpay'] = 0;
            }
            //是否有已经处理和未处理的
            $payoff = haveOff($v['id']);
            if($payoff){
                $v['can_status'] = 0;
            }else{
                $v['can_status'] = 1;
            }

			if($v['is_new']==1){
                $v['totalmoney']        = getFloatValue($v['capital']+$v['interest']+$v['audit_fee']+$v['enabled_fee']+$v['created_fee']+$v['pay_fee'],2);
            }else{
                $v['totalmoney']        = getFloatValue($v['capital']+$v['interest'],2);
            }
            $row[$key]=$v;
        }
        return $row;
    }
    
    /**
     * 待签约页面 
     */
    public function signing()
    {
        $map = array();
        $map['m.status']        = 2;
        $map['m.audit_status']  = 3;
        $map['ms.tree']         = 1;
        $map['ms.signed']       = 0;
        if($_REQUEST['uid']){
            $map['m.id']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.id'];
        }
    
        if($_REQUEST['real_name']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if($_REQUEST['promotion_code']){
            $map['m.promotion_code'] = urldecode($_REQUEST['promotion_code']);
            $search['promotion_code'] = $map['m.promotion_code'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.add_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline";
        
        //sql查询
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id= m.id")
        ->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
        
    }
    
    /**
     * 签约审批页面
     */
    public function appsigning(){
        setBackUrl();
        $id  = intval($_GET['id']);
        $uid = intval($_GET['uid']);
    
        $this->assign("id",$id);
        $this->assign("uid",$uid);
        $this->display();
    }
    
    /**
     * 处理签约
     */
    public function dosigning(){
        $id     = intval($_POST['id']);
        $uid    = intval($_POST['uid']);
        $status = intval($_POST['status']);
        $reason = $_POST['reason'];
        $statusx = M('member_status')->field("first_trial")->where("borrow_id={$id}")->find();
        if ($statusx['signed'] == 1){
            $this->error("请不要重复提交表单");
        }
        wqbLog($status);
        if($status==1){
            //添加状态表
            $save['uid']              = $uid;
            $save['borrow_id']        = $id;
            $save['signed']           = 1;
            $save['signed_uid']       = session('adminname');
            $save['signed_time']      = time();
            $save['reason']           = $reason;
            $updata = M('member_status')->where("borrow_id={$id}")->save($save);
            if($updata){
                //更新借款申请表
                $saveb['status']        = 3;
                $saveb['audit_status']  = 4;
                $updatab = M('borrow_apply')->where("id={$id}")->save($saveb);
                if($updatab){
                    $this->success("处理成功");
                }else{
                    $this->error("借款申请同步处理失败");
                }
            }else{
                $this->error("处理失败");
            }
    
        }else{
            //添加状态表
            $save['uid']              = $uid;
            $save['borrow_id']        = $id;
            $save['signed']           = 2;
            $save['signed_uid']       = session('adminname');
            $save['signed_time']      = time();
            $save['reason']           = $reason;
            $updata = M('member_status')->where("borrow_id={$id}")->save($save);
            if($updata){
                //更新借款申请表
                $saveb['status']        = 97;
                $saveb['audit_status']  = 4;
                $updatab = M('borrow_apply')->where("id={$id}")->save($saveb);
                if($updatab){
                    $this->success("处理成功");
                }else{
                    $this->error("借款申请同步处理失败");
                }
            }else{
                $this->error("处理失败");
            }
    
        }
    }
    
    /**
     * 待放款页面
     */
    public function pending()
    {
        $map = array();
        $map['m.status']        = 3;
        $map['m.audit_status']  = 4;
        $map['m.is_full']       = 1;
		$map['ms.calm']         = 1;
		
        if($_REQUEST['borrowid']){
            $map['m.id']   = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.add_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,m.sinabid_flg,m.loan_error";
        
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }

    /**
     * 已取消的借款申请
     */
    public function cancled()
    {
        $map = array();
        $map['m.status'] = 99;
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.bank_bing_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.bank_bing_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.bank_bing_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,ms.bank_bing_time";
        
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 已拒绝的放款
     */
    public function pendNg()
    {
        $map = array();
        $map['m.status'] = 96;
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if($_REQUEST['borrow_id']){
            $map['m.id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.pending_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.pending_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.pending_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.pending_time']       = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,ms.pending_time";
    
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    
    /**
     * 初审处理
     */
    public function dofirst()
    {
        $id     = intval($_POST['id']);
        $status = intval($_POST['status']);
        
        $statusx =  M('member_status')->field(true)->where("borrow_id = {$id} ")->find();
        if ($statusx['first_trial']!=0){
            $this->error("请不要重复提交表单");
        }
        
        //更改借款申请表的状态
        if($status == 1){
            $save['status']       = 2;//通过
        }else{
            $save['status']       = 98;//拒绝
        }
        
        $save['audit_status'] = 1;
        $update = M('borrow_apply')->where("id={$id}")->save($save);
        
        //更新借款会员申请状态表的状态
        $savem['first_trial_uid']   = session('adminname');
        if($status == 1){
            $savem['first_trial']   = 1;
        }else{
            $savem['first_trial']   = 2;
        }
        $savem['first_trial_time']  = time();
        $updates =  M('member_status')->where("borrow_id = {$id} ")->save($savem);
        
        if($update>0 && $updates>0){
            alogs("Borrowlog",0,1,'管理员'.session('adminname').'执行了借款申请初审操作！');//管理员操作日志
            $this->success("处理成功");
        }else{
            alogs("Borrowlog",0,1,'管理员'.session('adminname').'执行了借款申请初审操作失败！');//管理员操作日志
            $this->error("处理失败");
        }
    }

    public function pendingEdit(){
        $id    = $_GET['id'];
        $vo    = M('borrow_apply m')->field(true)->where(" id = {$id} ")->find();
        if($vo['repayment_type'] == 1){
            $vo['type'] = "天";
        }else if($vo['repayment_type'] == 2){
            $vo['type'] = "周";
        }else if($vo['repayment_type'] == 3){
            $vo['type'] = "个月";
        }else if($vo['repayment_type'] == 4){
            $vo['type'] = "个季度";
        }else{
            $vo['type'] = "年";
        }
        $this->assign("vo", $vo);
        
        $type  = C('REPAYMENTTYPE');
        $this->assign("type", $type);
        
        $this->display();
    }
    
    public function doPendingEdit(){
        $id             = intval($_POST['id']);
        $money          = floatval($_POST['money']);
        $duration       = intval($_POST['duration']);
        $repayment_type = intval($_POST['repayment_type']);
        $interest       = floatval($_POST['interest']);
        $borrow_fee     = floatval($_POST['borrow_fee']);
        $deposit        = floatval($_POST['deposit']);
        $security_fee   = floatval($_POST['security_fee']);
        $info           = $_POST['info'];
        
        $copy = M('borrow_apply')->field(true)->where("id={$id}")->find();
        if($copy['id']>0){
            
            //copy目前的借款申请表的数据
            $data['borrow_id']      = $copy['id'];
            $data['uid']            = $copy['uid'];
            $data['money']          = $copy['money'];
            $data['duration']       = $copy['duration'];
            $data['repayment_type'] = $copy['repayment_type'];
            $data['rate']           = $copy['rate'];
            $data['interest']       = $copy['interest'];
            $data['audit_fee']      = $copy['audit_fee'];
            $data['deposit']        = $copy['deposit'];
            $data['security_fee']   = $copy['security_fee'];
            $data['borrow_fee']     = $copy['borrow_fee'];
            $data['renewal_fee']    = $copy['renewal_fee'];
            if($copy['renewal_id'] != ''){
                $data['renewal_id'] = $copy['renewal_id'];
            }else{
                $data['renewal_id'] = 0;
            }
            $data['due_fee']        = $copy['due_fee'];
            $data['late_fee']       = $copy['late_fee'];
            $data['status']         = $copy['status'];
            $data['audit_status']   = $copy['audit_status'];
            $data['add_time']       = time();
            $data['add_uid']        = session('adminname');
            $data['reason']         = $info;
            
            print_r($data);
            $newId = M('borrow_apply_pendingedit')->add($data);
            
            if($newId > 0){
                //更改借款申请表
                if($_POST['money']!=""){
                    $save['money']          = $money;
                }
                if($_POST['duration']!=""){
                    $save['duration']       = $duration;
                }
                if($_POST['repayment_type']!=""){
                    $save['repayment_type'] = $repayment_type;
                }
                if($_POST['interest']!=""){
                    $save['interest']       = $interest;
                }
                if($_POST['borrow_fee']!=""){
                    $save['borrow_fee']     = $borrow_fee;
                }
                if($_POST['deposit']!=""){
                    $save['deposit']        = $deposit;
                }
                if($_POST['security_fee']!=""){
                    $save['security_fee']   = $security_fee;
                }
                
                $save['status']         = 2;//通过
                $save['audit_status']   = 1;
                $update = M('borrow_apply')->where("id={$id}")->save($save);
                
                //更新借款会员申请状态表的状态
                $savem['pending_uid']   = session('adminname');
                $savem['pending']       = 1;
                $savem['pending_time']  = time();
                $updates =  M('member_status')->where("borrow_id = {$id} ")->save($savem);
                if($update>0 && $updates>0){
                    $this->success("处理成功");
                }else{
                    $this->error("处理失败");
                }
                
            }else{
                $this->error("处理失败");
            }
            
        }else{
            $this->error("处理失败");
        }
        
    }
    
    /**
     *放款审批页面
     */
    public function appPending(){
        setBackUrl();
        $id = intval($_GET['id']);
        $send_wechat = intval($_GET['status']);
        $this->assign("id",$id);
        $this->assign("send_wechat",$send_wechat);
        $this->display();
    }
    
    /**
     * 放款审核
     */
public function doPending(){
        $now            = time();
        $id             = intval($_POST['id']);
        $status         = intval($_POST['status']);
        $send_wechat    = intval($_POST['send_wechat']);

        $statusx =  M('member_status')->field(true)->where("borrow_id = {$id} ")->find();
        if ($statusx['pending'] ==1){
            $this->error("请不要重复提交表单");
        }
        $datag        = get_global_setting();
        $loan_onoff   = $datag['loan_onoff'];
        $loan_day     = $datag['loan_day'];
        $zhima        = $datag['zhima_data'];
        $is_aotu      = $datag['is_aotu_bid'];
        
        if($loan_onoff == '1'){
            $this->error("已设置停止放款");
        }else{
            if(get_loan_money()>$loan_day){
                $this->error("今日放款额度已经满了");
            }else{
                if($status==1){
                    //更新状态表
                    $save['pending']          = 1;
                    $save['pending_uid']      = session('adminname');
                    $save['pending_time']     = time();
                    $save['reason']           = $reason;
                    $updata = M('member_status')->where("borrow_id={$id}")->save($save);
                
                    if($updata){
                        $detail     = M("borrow_detail")->field("id")->where("borrow_id = {$id}")->find();
                        if (empty($detail)){
                            $apply = M("borrow_apply")->field(true)->where(" id = ".$id )->find();
                            //新浪放款处理
                            //标的录入
                            $res = sinaCreateBidInfo($apply);
                            if($res){
                                $datag = get_global_setting();
                                //先出借人放款到中间帐户
                                //$borrow_uid = $datag['loan_account'];
                                //$resCt = wqbHostingCollectionTrade($borrow_uid,$apply['loan_money']);
                                /*if($resCt){
                                 //中间帐户放款费用到资管方
                                 $api_account = $datag['api_account'];
                                 $fee   = $apply['audit_fee']+$apply['late_fee'];
                                 $resPt = wqbHostingPayTrade($api_account, $fee);
                            
                                 //中间帐户放款费用到平台
                                 $fee_plat = $apply['created_fee']+$apply['enabled_fee']+$apply['renewal_fee']-$apply['coupon_amount'];
                                $resFPt   = wqbFumiPayTrade($money);*/
                            
                                //中间帐户单笔代付到借款人提现卡
                                $bank = M('member_bank')->where("uid={$apply['uid']} ")->find();
                                $restrade = sinaCreateSingleHostingPayToCardTrade($apply,$bank['bank_id'],$apply['trade_no']);
                                
                                if($restrade){
                                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$apply['uid']}")->find();
                                    sendWxTempleteMsg5($wxInfo['openid'], $apply['money'], $apply['add_time'], time() ,$apply['loan_money']);
                                    //发送App推送通知放款成功
                                    $mwhere['uid'] = $apply['uid'];
                                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                                    if(!empty($token['token'])){
                                        AndroidTempleteMsg5($apply['uid'],$token['token'],$id);
                                    }
                            
                                    //更新借款申请表
                                    $saveb['status']        = 4;
                                    $saveb['audit_status']  = 5;
                                    $saveb['len_time']      = $now;
                                    if($apply['repayment_type'] == 1){
                                        $expire = $apply['duration'];
                                    }else if($apply['repayment_type'] == 2){
                                        $expire = $apply['duration']*7;
                                    }else if($apply['repayment_type'] == 3){
                                        $expire = $apply['duration']*30 ;
                                    }else if($apply['repayment_type'] == 4){
                                        $expire = $apply['duration']*120 ;
                                    }else{
                                        $expire = $apply['duration']*365 ;
                                    }
                            
                                    $deadline = $now +  $expire *24* 3600;
                                    $saveb['deadline']     = $deadline;
                                    $updatab = M('borrow_apply')->where("id={$id}")->save($saveb);
                            
                                    createRepayOrder($apply,$deadline);
                            
                                    if($updatab){
                                        //白骑士芝麻数据反馈接口
                                        if($zhima == 1){
                                            bqszhimaOrder($id,$apply['uid'],5,"");
                                        }
                                        //自动福米金融上标
                                        if($is_aotu == 1){
                                            $resBid = createFumiBid($id,$send_wechat);
                                        }
                            
                                        $this->success("处理成功");
                                    }else{
                                        $this->error("借款申请表同步处理失败");
                                    }
                                }else{
                                    $this->error("新浪代付到提现卡接口处理失败");
                                }
                                //}
                            }else{
                                $this->error("新浪新标录入接口处理失败");
                            }
                        }else{
                            $this->error("该借款申请已经生成账单");
                        }
                    }else{
                        $this->error("借款状态表处理失败");
                    }
                }else{
                    //更新状态表
                    $save['pending']          = 2;
                    $save['pending_uid']      = session('adminname');
                    $save['pending_time']     = time();
                    $save['reason']           = $reason;
                    $updata = M('member_status')->where("borrow_id={$id}")->save($save);
                    if($updata){
                        //更新借款申请表
                        $saveb['status']      = 96;
                        $saveb['refuse_time'] = time();
                        $updatab = M('borrow_apply')->where("id={$id}")->save($saveb);
                        if($updatab){
                            delUserOperation($statusx['uid'],$id);
                            $this->success("处理成功");
                        }else{
                            $this->error("借款申请表同步处理失败");
                        }
                    }else{
                        $this->error("处理失败");
                    }
                }
            }
            
        }
    }
    
    //未提交申请
    public function notApply(){
        $map = array();
        $map['m.status'] = 0;
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.add_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.add_time']    = array("egt",$time);
        }
        
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline";
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
        
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
        $this->display();
    }
    
    /**
     * 已初审
     */
    public function firstTrial(){
        $map = array();
        $map['m.audit_status']  = 1;
        $map['ms.first_trial']  = 1; 
        $map['ms.bank_bing']    = 0;
        if($_REQUEST['borrowid']){
            $map['m.id']   = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.first_trial_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.first_trial_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.first_trial_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['ms.first_trial_time']    = array("egt",$time);
        }
        
        $field = "m.id,m.uid,m.money,m.duration,m.repayment_type,m.interest,m.add_time,ms.first_trial_time,ms.first_trial_uid,mi.iphone,mi.real_name";
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")
        ->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 已绑卡
     */
    public function bingBank(){
        $map = array();
        $map['ms.bank_bing']    = 1;
        $map['m.audit_status']  = 2;
        $map['ms.zhima_auth']   = 0;
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['borrow_id']){
            $map['m.id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.bank_bing_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.bank_bing_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.bank_bing_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,ms.bank_bing_time";
        
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 已决策
     */
    public function risk(){
        $map = array();
        $map['ms.tree']         = array("gt",'0');
        $map['m.status']        = array("lt",'90');
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if($_REQUEST['borrow_id']){
            $map['m.id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.tree_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.tree_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.tree_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->count('m.id');;
        
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,ms.tree_time,ms.tree";
    
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 已授权
     */
    public function zhima(){
        $map = array();
        $map['ms.zhima_auth']   = array("gt",'0');
        $map['m.status']        = array("lt",'90');
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if($_REQUEST['borrow_id']){
            $map['m.id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.zhima_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.zhima_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.zhima_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,ms.zhima_time,ms.zhima_auth";
    
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    
    //已身份确认
    public function idVerify(){
        $map = array();
        $map['ms.id_verify'] = 1;
        $map['m.status']     = array("lt",'90');
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['borrow_id']){
            $map['m.id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.id_verify_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.id_verify_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.id_verify_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,m.deadline,ms.id_verify_time";
        
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    
    //已签约
    public function signed(){
        $map = array();
        $map['ms.signed']       = 1;
        $map['m.audit_status']  = 4;
        $map['m.status']        = 5;
        if($_REQUEST['borrowid']){
            $map['m.id']   = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.signed_time']   = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.signed_time']   = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.signed_time']   = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.signed_time']    = array("egt",$time);
        }
        
        //分页处理
        import("ORG.Util.Page");
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time";
        $count = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
        
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    //签约失败
    public function signfailed(){
        $map = array();
        $map['ms.signed']       = 2;
        $map['m.audit_status']  = 4;
        $map['m.status']        = 97;
        if($_REQUEST['borrowid']){
            $map['m.id']   = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.signed_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.signed_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.signed_time']   = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
    
        //分页处理
        import("ORG.Util.Page");
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time";
        $count = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    //已放款
    public function pended(){
        $map = array();
        $map['m.status']       = 4;
        $map['m.audit_status'] = 5;
        if($_REQUEST['borrow_id']){
            $map['m.id']   = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.id'];
        }
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.len_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.len_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.len_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.len_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.repayment_type,m.money,m.duration,m.len_time,m.interest,m.add_time,m.renewal_id,ms.is_withdraw";
        
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.uid=m.uid and ms.borrow_id = m.id ")->where($map)->limit($Lsql)->order('m.len_time DESC')->select();
        $list  = $this->_listFilter($list);
    
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
    
        $this->display();
    }
    
    /**
     * 待还款
     */
    public function repayment(){
        $map = array();
        $map['m.repayment_time'] = 0;
        $map['m.status']         = 0;
        $map['a.status']         = 4;
        $map['a.audit_status']   = 5;
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['mobile']){
            $map['mi.iphone'] = urldecode($_REQUEST['mobile']);
            $search['mobile']  = $map['mi.iphone'];
        }
        
        if($_REQUEST['borrow_id']){
            $map['m.borrow_id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.borrow_id'];
        }
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.deadline']       = array("between",$timespan);
            $search['start_time']    = strtotime(urldecode($_REQUEST['start_time']));
            $search['end_time']      = strtotime(urldecode($_REQUEST['end_time']));
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.deadline']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.deadline']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.deadline']       = array("egt",$time);
            
        }
        
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply a ON a.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        
        $field = "m.id,m.uid,m.borrow_id,m.capital,m.interest,m.sort_order,m.total,m.renewal_fee,m.renewal_id,m.hope_charge_time,m.status,m.add_time,m.deadline,m.repayment_time,mi.real_name,mi.iphone,a.duration, a.repayment_type,a.audit_fee,a.created_fee,a.enabled_fee,a.pay_fee,a.is_new";
        
        //sql查询
        $list  = M('borrow_detail m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply a ON a.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.deadline ASC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
        $this->display();
    }
    
    /**
     * 待还款导出
     */
    public function repaymentExport(){
        import("ORG.Io.Excel");
        $map = array();
        $map['m.repayment_time'] = 0;
        $map['m.status']         = 0;
        $map['a.status']         = 4;
        $map['a.audit_status']   = 5;
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['mobile']){
            $map['mi.iphone'] = urldecode($_REQUEST['mobile']);
            $search['mobile']  = $map['mi.iphone'];
        }
        
        if($_REQUEST['borrow_id']){
            $map['m.borrow_id'] = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.borrow_id'];
        }
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = $_REQUEST['start_time'].",".$_REQUEST['end_time'];
            $map['m.deadline']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = $_REQUEST['start_time'];
            $map['m.deadline']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = $_REQUEST['end_time'];
            $map['m.deadline']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.deadline']       = array("egt",$time);
            
        }

        $field = "m.id,m.uid,m.borrow_id,m.capital,m.interest,m.sort_order,m.total,m.renewal_fee,m.renewal_id,m.hope_charge_time,m.status,m.add_time,m.deadline,m.repayment_time,mi.real_name,mi.iphone,a.duration, a.repayment_type,a.audit_fee,a.created_fee,a.enabled_fee,a.pay_fee,a.is_new";
        
        //sql查询
        $list  = M('borrow_detail m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply a ON a.id=m.borrow_id")->where($map)->order('m.deadline ASC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
        $row    = array();
        $row[0] = array('会员ID','借款人手机','真实姓名','借款金额（元）','借款利息（元）','借款期限','应还金额（元）','应还日期');
        $i = 1;
        foreach($list as $v){
            $row[$i]['uid']            = $v['uid'];
            $row[$i]['iphone']         = $v['iphone'];
            $row[$i]['real_name']      = $v['real_name'];
            $row[$i]['capital']        = $v['capital'];
            $row[$i]['interest']       = $v['interest'];
            $row[$i]['duration']       = $v['duration'].$v['type'];
            $row[$i]['totalmoney']     = $v['totalmoney'];
            $row[$i]['deadline']       = date("Y-m-d H:i:s", $v['deadline']);
            $i++;
        }
         
        $xls = new Excel_XML('UTF-8', false, 'datalist');
        $xls->addArray($row);
        $xls->generateXML("export");
    }
    
    
    /**
     * 已还款
     */
    public function repaymented(){
        $map = array();
        $map['m.status']         = 1;
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
    
        if($_REQUEST['borrow_id']){
            $map['m.borrow_id']  = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.borrow_id'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.repayment_time'] = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.repayment_time'] = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.repayment_time'] = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.repayment_time'] = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply a ON a.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
    
        $field = "m.id,m.uid,m.borrow_id,m.capital,m.interest,m.sort_order,m.total,m.renewal_fee,m.renewal_id,m.due_fee,m.late_fee,m.status,m.add_time,m.deadline,m.repayment_time,mi.real_name,mi.iphone,a.duration, a.repayment_type,a.renewal_id";
        
        //sql查询
        $list  = M('borrow_detail m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply a ON a.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.repayment_time Desc')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    //已逾期
    public function due(){
        $map = array();
        $map['m.repayment_time'] = 0;
        $map['m.status']         = 0;
        $map['m.deadline']       = array("lt", time());
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['borrow_id']){
            $map['m.borrow_id']  = urldecode($_REQUEST['borrow_id']);
            $search['borrow_id'] = $map['m.borrow_id'];
        }
        
        if($_REQUEST['iphone']){
            $map['mi.iphone']  = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.deadline']      = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.deadline']      = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.deadline']      = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
        
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_detail m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.borrow_id,m.uid,mi.iphone,mi.real_name,m.capital,m.interest,ma.money,ma.duration,ma.item_id,ma.repayment_type,ma.audit_fee,ma.created_fee,ma.enabled_fee,ma.pay_fee,ma.is_new,m.interest,ma.add_time,m.deadline,m.charge_times,m.hope_charge_time,ma.coupon_id";
        $list  = M('borrow_detail m')->field($field)->join("{$this->pre}borrow_apply ma ON ma.id=m.borrow_id")->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->limit($Lsql)->order('m.deadline DESC')->select();
        
        $row = array();
        foreach($list as $key=>$v){
            if($v['repayment_type'] == 1){
                $v['type'] = "天";
            }else if($v['repayment_type'] == 2){
                $v['type'] = "周";
            }else if($v['repayment_type'] == 3){
                $v['type'] = "个月";
            }else if($v['repayment_type'] == 4){
                $v['type'] = "个季度";
            }else{
                $v['type'] = "年";
            }
            if($v['is_new']==1){
                $v['total']        = getFloatValue($v['capital']+$v['interest']+$v['audit_fee']+$v['enabled_fee']+$v['created_fee']+$v['pay_fee'],2);
            }else{
                $v['total']        = getFloatValue($v['capital']+$v['interest'],2);
            }
            $dueDay = get_due_day($v['deadline']); 
            $v['due_fee']  = get_due_fee($v['money'],$v['item_id'],$dueDay);
            $v['late_fee'] = get_late_fee($v['money'],$v['item_id'],$dueDay);
            
            //是否有已经处理和未处理的
            $payoff = haveOff($v['id']);
            if($payoff){
                $v['can_status'] = 0;
            }else{
                $v['can_status'] = 1;
            }
            $row[$key]=$v;
        }
        $list = $row;
       
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    
    public function cancle(){
        $id     = intval($_GET['id']);
        $type   = intval($_GET['type']);;
        $this->assign("id", $id);
        $this->assign("type", $type);
        $this->display();
    }
    
    
    public function docancle(){
        $id     = intval($_POST['id']);
        $status = intval($_POST['status']);
        $type   = intval($_POST['type']);
        $statusx =  M('borrow_apply')->field(true)->where("id = {$id} ")->find();
        if ($statusx['status'] == 99){
            $this->error("请不要重复提交表单");
        }
        
        //更新借款会员申请借款的状态为取消]
        if($status == 1){
            if($type == 1){
                $save['status']      = 96;
                $save['refuse_time'] = time();
                $update =  M('borrow_apply')->where("id = {$id} ")->save($save);
                
                $datag        = get_global_setting();
                $zhima        = $datag['zhima_data'];
                //白骑士芝麻数据反馈接口
                //if($zhima == 1){
                //    bqszhimaOrder($id,$statusx['uid'],4,"");
                //}
            }else{
                $save['status']      = 99;
                $save['refuse_time'] = time();
                $update =  M('borrow_apply')->where("id = {$id} ")->save($save);
            }
            delUserOperation($statusx['uid'],$id);
            $memwechat = M("member_wechat_bind")->field(true)->where("uid = ".$statusx['uid'])->find();
            $reswechat = sendWxTempleteMsg14($memwechat['openid']);
            $smsTxt = FS("Webconfig/smstxt");
            $mem = M(' members ')->field('iphone')->where(" id = {$statusx['uid']} ")->find();
            addToSms($mem['iphone'], $smsTxt['loan_ad']);
            
            if($update>0){
                alogs("Borrowlog",0,1,'管理员'.session('adminname').'执行了借款申请取消的操作！');//管理员操作日志
                $this->success("处理成功");
            }else{
                alogs("Borrowlog",0,1,'管理员'.session('adminname').'执行了借款申请取消的操作失败！');//管理员操作日志
                $this->error("处理失败");
            }
        }
    }
    
    /**
     * 手动代扣还款
     */
    public function dee(){
        $id       = intval($_GET['id']);
        $pay      = intval($_GET['type']);//type为1，表示提前还款
        $detail   = M('borrow_detail')->field(true)->where("id = ".$id)->find();
        $apply    = M('borrow_apply')->field(true)->where("id = ".$detail['borrow_id'])->find();
        
        $due_fee  = 0;
        $late_fee = 0;
        $due_day  = get_due_day($detail['deadline']);
        if($due_day>0){
            $due_fee  = get_due_fee($apply['money'],$apply['item_id'],$due_day);
            $late_fee = get_late_fee($apply['money'],$apply['item_id'],$due_day);
        }
        if($apply['is_new']==1){
            $fee      = getFloatValue($apply['audit_fee']+$apply['enabled_fee']+$apply['created_fee']+$apply['pay_fee'],2);
        }else{
            $fee      = 0;
        }
        $money        = getFloatValue($detail['capital']+$detail['interest']+$fee+$due_fee+$late_fee,2);
        $bank         = M('member_bank')->field(true)->where("uid = ".$detail['uid']." and type = 1")->find();
        
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
        
        $payment = new PaymentAction();
        //宝付扣款
        $res  = $payment->requestApi($detail['uid'],$detail['borrow_id'],1,$money,$bank['id']);
        if(is_bool($res) && $res){
            $memwechat =  M("member_wechat_bind") -> field(true) -> where("uid = ".$detail['uid']) -> find();
            $reswechat = getWechatmsg8($memwechat['openid'],date("Y-m-d",$detail['add_time']),$apply['money'],$day,$money);
            wechatLog("微信手动扣款成功：还款人uid-".$detail['uid']."账单编号ID-".$detail['id']."发送结果-".$reswechat['errmsg']);
            
            //账单表更新
            $data['status']         = 1;
            $data['due_fee']        = $due_fee;
            $data['late_fee']       = $late_fee;
            $data['repayment_time'] = time();
            $data['charge_times']   = $detail['charge_times']+1;
            $update = M('borrow_detail')->where("id = ".$id)->save($data);
            
            if($update){
                if($detail['total']==$detail['sort_order']){
                    $dataa['status']         = 5;
                    $dataa['repayment_time'] = time();
                    $upapply = M('borrow_apply')->where("id = ".$detail['borrow_id'])->save($dataa);
                    
                    if($upapply){
                        $this->success("扣款成功,已经成功还款");
                    }else{
                        $this->success("银行扣款成功，申请表更新失败，请及时更新借款申请表状态！");
                    }
                }else{
                    $this->success("扣款成功,已经成功还款");
                }
                
                //会员积分变更
                if($due_day >0){
                    $type = 2;
                    $integral = $money-$detail['capital'];
                    $info = "逾期还款".$money."元，扣除积分".$integral;
                }else{
                    $type = 1;
                    $integral = $money;
                    $info = "成功还款".$money."元，获得积分".$integral;
                }
                
                addIntegral($detail['uid'],$type,$integral,$info);
            }else{
                $this->success("银行扣款成功，账单表更新失败，请及时更新账单表状态");
            }
        }else{
            if($pay == 1){
                $mod = ($detail['charge_times']+1)/3;
                if($mod==1){
                    $data['hope_charge_time'] = $detail['hope_charge_time'] + 6*3600;
                }elseif($mod==2){
                    $data['hope_charge_time'] = $detail['hope_charge_time'] + 6*2*3600;
                }else{
                    $data['hope_charge_time'] = $detail['hope_charge_time'] + 6*3*3600;
                }
                $data['charge_times']     = $detail['charge_times']+1;
                $update = M('borrow_detail')->where("id = ".$id)->save($data);
            }
            
            $this->error("扣款失败，还款失败");
        }
    }
    
    /**
     * 更改下次扣款时间
     */
    public function updatetime(){
		$id               = $_POST['id'];
		$hope_charge_time = strtotime($_POST['hope_charge_time']);
		
		$data['hope_charge_time'] = $hope_charge_time;
        $status = M('borrow_detail')->where(" id =".$id)->save($data);
        if ($status){
            $this->success("修改成功");
        }else {
            $this->error("修改失败");
        }
    }
    
    /**
     * 支付平台交易记录
     */
    public function trades()
    {
        $map = array();
        $map['m.type']       = array("in",'1,2,4');
        $map['m.status']     = 1;
    
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['orderId']){
            $map['m.outer_orderId']   = urldecode($_REQUEST['orderId']);
            $search['orderId'] = $map['m.outer_orderId'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.addtime']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.addtime']       = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('sina_order_pay m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.type as paytype,m.addtime,m.notify_time,m.status,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('sina_order_pay m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 新浪交易放款交易记录
     */
    public function tradesSina()
    {
        $map = array();
        $map['m.type']       = 3;
        $map['m.status']     = 1;
    
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['orderId']){
            $map['m.outer_orderId']   = urldecode($_REQUEST['orderId']);
            $search['orderId'] = $map['m.outer_orderId'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.addtime']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.addtime']       = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('sina_order_pay m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.addtime,m.notify_time,m.status,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('sina_order_pay m')->field($field)->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->join("{$this->pre}member_info mi ON mi.uid=mb.uid")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }

    /**
     * 支付平台交易记录
     */
    public function tradesno()
    {
        $map = array();
        $map['m.type']       = array("in",'1,2,4');
        $map['m.status']     = 2;
        
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['orderId']){
            $map['m.outer_orderId']   = urldecode($_REQUEST['orderId']);
            $search['orderId'] = $map['m.outer_orderId'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.addtime']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.addtime']       = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('sina_order_pay m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.type as paytype,m.addtime,m.notify_time,m.status,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('sina_order_pay m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 支付平台交易记录
     */
    public function tradesSinano()
    {
        $map = array();
        $map['m.type']       = 3;
        $map['m.status']     = 2;
    
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['orderId']){
            $map['m.outer_orderId']   = urldecode($_REQUEST['orderId']);
            $search['orderId'] = $map['m.outer_orderId'];
        }
    
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
    
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.addtime']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.addtime']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.addtime']       = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('sina_order_pay m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.addtime,m.notify_time,m.status,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('sina_order_pay m')->field($field)->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->join("{$this->pre}member_info mi ON mi.uid=mb.uid")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    //待还款线下申请
    public function linedownapply()
    {
        //账单id
        $id = intval($_GET['id']);
        $field = "mi.iphone,mi.real_name";
        $info  = M('borrow_detail m')->field($field)->join("{$this->pre}borrow_apply ma ON ma.id=m.borrow_id")->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where("m.id = {$id}")->find();
        $this->assign('info',$info);
        $this->assign('id',$id);
        $this->display();
    }
    //待还款线下申请执行
    public function doapply()
    {
        $id = intval($_POST['id']);
        $xuqi_days = intval($_POST['xuqi_days']);
        if($xuqi_days == 1){
            $data['xuqi_days'] = 7;
        }elseif($xuqi_days == 2){
            $data['xuqi_days'] = 14;
        }

        $detail = M("borrow_detail")->field('uid,borrow_id')->where("id = {$id}")->find();
        $data['uid']       = $detail['uid'];
        $data['borrow_id'] = $detail['borrow_id'];
        $data['detail_id'] = $id;
        $data['type']      = $_POST['type'];
        $data['account']   = $_POST['bank_card'];
        $data['money']     = $_POST['money'];
        $data['memo']      = "姓名：".$_POST['real_name'].",手机号：".$_POST['iphone'];
        $data['add_time']  = time();

        wqbLog($data);
        wqbLog($detail);
        $res = M("payoff_apply")->add($data);
        if ($res){
            $this->success('申请已提交，请等待审核');
        }else {
            $this->error('提交申请失败');
        }

    }

    //已逾期线下申请
    public function lineapply()
    {
        //账单id
        $id = intval($_GET['id']);
        $field = "mi.iphone,mi.real_name";
        $info  = M('borrow_detail m')->field($field)->join("{$this->pre}borrow_apply ma ON ma.id=m.borrow_id")->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where("m.id = {$id}")->find();
        $this->assign('info',$info);
        $this->assign('id',$id);
        $this->display();
    }
    //已逾期线下申请执行
    public function dolineapply()
    {
        $id = intval($_POST['id']);
        $xuqi_days = intval($_POST['xuqi_days']);
        if($xuqi_days == 1){
            $data['xuqi_days'] = 7;
        }elseif($xuqi_days == 2){
            $data['xuqi_days'] = 14;
        }

        $detail = M("borrow_detail")->field('uid,borrow_id')->where("id = {$id}")->find();
        $data['uid']       = $detail['uid'];
        $data['borrow_id'] = $detail['borrow_id'];
        $data['detail_id'] = $id;
        $data['type']      = $_POST['type'];
        $data['account']   = $_POST['bank_card'];
        $data['money']     = $_POST['money'];
        $data['memo']      = "姓名：".$_POST['real_name'].",手机号：".$_POST['iphone'];
        $data['add_time']  = time();
        wqbLog($data);
        wqbLog($detail);
        $res = M("payoff_apply")->add($data);
        if ($res){
            $this->success('申请已提交，请等待审核');
        }else {
            $this->error('提交申请失败');
        }

    }
    
    public function unIdVerify(){
        $map = array();
        $map['m.status']        = 3;
        $map['m.audit_status']  = 4;
        $map['ms.id_verify']    = 0;
        $map['ms.is_recheck']   = 1;
        
        if($_REQUEST['borrowid']){
            $map['m.id']   = urldecode($_REQUEST['borrowid']);
            $search['borrowid'] = $map['m.id'];
        }
        
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
        
        if($_REQUEST['realname']){
            $map['mi.real_name'] = urldecode($_REQUEST['realname']);
            $search['realname']  = $map['mi.real_name'];
        }
        
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.add_time']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.add_time']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }
        
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.duration,m.repayment_type,m.interest,m.add_time";
        
        //sql查询
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        
        $list  = $this->_listFilter($list);
        
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
        $this->display();
        
        
    }
    
    //借款取消
    public function cancel(){
        $id      = intval($_POST['id']);
        $statusx =  M('borrow_apply')->field(true)->where("id = {$id} ")->find();
        if ($statusx['status'] == 99){
            ajaxmsg("该借款已经取消",0);
        }
        //更新借款会员申请借款的状态为取消]
        $save['status']      = 99;
        $save['refuse_time'] = time();
        $update =  M('borrow_apply')->where("id = {$id} ")->save($save);
        if($update){
            //借款优惠券的还原
            if ($statusx['coupon_id']>0){
                updateCoupon($statusx['coupon_id']);
            }
            delUserOperation($statusx['uid'],$id);
            //取消推送
            //微信
            /*$memwechat = M("member_wechat_bind")->field(true)->where("uid = ".$statusx['uid'])->find();
            if(!empty($memwechat['openid'])){
                $reswechat = sendWxTempleteMsg14($memwechat['openid']);
            }*/
            //APP
            /*$mwhere['uid'] = $apply['uid'];
            $token = M('member_umeng')->where($mwhere)->field(true)->find();
            if(!empty($token['token'])){
                AndroidTempleteMsg9($apply['uid'],$token['token'],$array['id']);
            }*/
            alogs("Borrowlog",0,1,'管理员'.session('adminname').'执行了借款申请取消的操作！');//管理员操作日志
            ajaxmsg("该借款取消成功",1);
        }else{
            alogs("Borrowlog",0,1,'管理员'.session('adminname').'执行了借款申请取消的操作失败！');//管理员操作日志
            ajaxmsg("该借款取消失败",0);
        }
    }

    //发送优惠券页面
    public function send(){
        $bid = $_GET['bid'];
        $uid = $_GET['uid'];
        $this->assign('bid',$bid);
        $this->assign('uid',$uid);
        $this->display();
    }
    
    /**
     * 发送优惠券
     */
    public function doSend()
    {
        set_time_limit(0);
        $type      = 2;
        $money     = $_POST['money'];
        $expire    = $_POST['expire'];
        $memo      = $_POST['memo'];
        $uid       = $_POST['uid'];
        $bid       = $_POST['bid'];
    
        $dtime = time();
        $etime = $dtime + $expire * 24 * 3600;
    
        $data['uid']         = $uid;
        $data['money']       = $money;
        $data['status']      = 1;
        $data['title']       = "还款优惠券 ";
        $data['memo']        = $memo;
        $data['type']        = $type;
        $data['add_time']    = $dtime;
        $data['start_time']  = $dtime;
        $data['end_time']    = $etime;
        $demo = M('member_coupon')->add($data);
        if($demo){
            $save['coupon_id'] = $demo;
            M('borrow_apply')->where("id = $bid")->save($save);
        }
    
        $this->assign('jumpUrl', U('/admin/Borrow/due'));
        $this->success('成功发送');
    
    }
    
}
?>