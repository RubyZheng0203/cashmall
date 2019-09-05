<?php
/**
 * 借款申请
 * @author Rubyzheng
 *
 */
class HuichaoPayAction extends ACommonAction
{
    /**
     * 交易中的记录
     */
    public function index()
    {
        $map = array();
        $map['m.status']     = 0;
    
        if($_REQUEST['type']){
            $map['m.type']  = $_REQUEST['type'];
            $search['type'] = $map['m.type'];
        }
        
        if($_REQUEST['scene']){
            $map['m.scene']  = $_REQUEST['scene'];
            $search['scene'] = $map['m.scene'];
        }
        
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
        $count = M('transfer_order_pay m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.type,m.scene,m.addtime,m.notify_time,m.status,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('transfer_order_pay m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();
      
        $this->assign("list",$list);
        $this->assign("pagebar",$page);
        $this->assign("search",$search);
        $this->assign("query",http_build_query($search));
        
        $typeList = array(1 => '微信支付',2 => '支付宝支付',3 => '银联支付',);
        $this->assign("typeList", $typeList);
        $sceneList = array(1 => '还款',2 => '续期',3 => '授信',);
        $this->assign("sceneList",$sceneList);
    
        $this->display();
    }
    
    /**
     * 支付成功的交易记录
     */
    public function trades()
    {
        $map = array();
        $map['m.status']    = 1;
    
        if($_REQUEST['type']){
            $map['m.type']  = $_REQUEST['type'];
            $search['type'] = $map['m.type'];
        }
        
        if($_REQUEST['scene']){
            $map['m.scene']  = $_REQUEST['scene'];
            $search['scene'] = $map['m.scene'];
        }
        
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
    
        if($_REQUEST['uid']){
            $map['m.uid']   = urldecode($_REQUEST['uid']);
            $search['uid'] = $map['m.uid'];
        }
    
        if($_REQUEST['orderId']){
            $map['m.notify_id']   = urldecode($_REQUEST['orderId']);
            $search['orderId'] = $map['m.notify_id'];
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
        $count = M('transfer_order_pay m')->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.type,m.scene,m.addtime,m.notify_id,m.notify_time,m.status,m.is_refund,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('transfer_order_pay m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();
        
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $typeList = array(1 => '微信支付',2 => '支付宝支付',3 => '银联支付',);
        $this->assign("typeList", $typeList);
        $sceneList = array(1 => '还款',2 => '续期',3 => '授信',);
        $this->assign("sceneList",$sceneList);
        
        $this->display();
    }
    
    /**
     * 支付失败的交易记录
     */
    public function tradesNo()
    {
        $map = array();
        $map['m.status']     = 2;
    
        if($_REQUEST['type']){
            $map['m.type']  = $_REQUEST['type'];
            $search['type'] = $map['m.type'];
        }
        
        if($_REQUEST['scene']){
            $map['m.scene']  = $_REQUEST['scene'];
            $search['scene'] = $map['m.scene'];
        }
        
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
        $count = M('transfer_order_pay m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.outer_orderId,m.type,m.scene,m.addtime,m.notify_time,m.status,m.pay_amount,m.borrow_id,m.uid,mi.iphone,mi.real_name,mb.money,mb.interest";
        $list  = M('transfer_order_pay m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}borrow_apply mb ON mb.id=m.borrow_id")->where($map)->limit($Lsql)->order('m.notify_time DESC')->select();

        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $typeList = array(1 => '微信支付',2 => '支付宝支付',3 => '银联支付',);
        $this->assign("typeList", $typeList);
        $sceneList = array(1 => '还款',2 => '续期',3 => '授信',);
        $this->assign("sceneList",$sceneList);
        
        $this->display();
    }
}
?>