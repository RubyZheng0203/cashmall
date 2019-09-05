<?php

/**
 * 借款优惠券
 * @author Rubyzheng
 *
 */
class CouponAction extends ACommonAction
{
    /**
     * 优惠券一览页面
     */
    public function index()
    {
		$map = array();
		if($_REQUEST['uid']){
		    $map['m.uid']   = urldecode($_REQUEST['uid']);
		    $search['uid'] = $map['m.uid'];	
		}
        
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.start_time']       = array("between",$timespan);
			$search['start_time']    = urldecode($_REQUEST['start_time']);	
			$search['end_time']      = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.start_time']       = array("gt",$xtime);
			$search['start_time']    = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.start_time']       = array("lt",$xtime);
			$search['end_time']      = $xtime;	
		}
		
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_coupon m')->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";
		
		//sql查询
		$list  = M('member_coupon m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
		$list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }
    
    /**
     * @param  数组集合 $list
     * @return 数组
     */
    public function _listFilter($list){
         
        $row = array();
        foreach($list as $key=>$v){
            if($v['type']==1){
                $v['type_name'] = "借款用";
            }else if($v['type']==2){
                $v['type_name'] = "还款用";
            }else{
                $v['type_name'] = "续期用";
            }
            $row[$key]=$v;
        }
        return $row;
    }
	
	/**
	 * 优惠券发送页面
	 */
	public function send ()
	{
	    
	    $this->display();
	}
	
	/**
	 * 发送优惠券
	 */
	public function doSend()
	{
	    set_time_limit(0);
	    $type      = intval($_POST['type']);
	    $uids      = trim($_POST['uids']);
	    $money     = $_POST['money'];
	    $expire    = $_POST['expire'];
	    $title     = $_POST['title'];
	    $memo      = $_POST['memo'];
	   
        $uids = explode(';', $uids);
        if ($_POST['sendall']) { //发送给所有人
            $uids = array();
            $res = M('members')->field("id")->where('is_bank = 0 and is_logoff = 0')->select();
            foreach ($res as $row) {
                $uids[] = $row['id'];
            }
        }
        if (!$uids) {
            $this->error(L('发送用户格式错误，请用分号分割'));
        } else {
            $dtime = time();
            $etime = $dtime + $expire * 24 * 3600;
            foreach ($uids as $uid) {
                $data['uid']         = $uid;
                $data['money']       = $money;
                $data['status']      = 0;
                $data['title']       = $title;
                $data['memo']        = $memo;
                $data['type']        = $type;
                $data['add_time']    = $dtime;
                $data['start_time']  = $dtime;
                $data['end_time']    = $etime;
                if ($data) {
                    M('member_coupon')->add($data);
                }
            }
        }
	    
	    $this->assign('jumpUrl', U('/admin/coupon'));
	    $this->success('成功发送给'.count($uids).'个用户');
	    
	}
	
	/**
	 * 删除优惠券
	 */
	public function deleteCoupon()
	{
	    $id  = $_POST['id'];
	    $res = M("member_coupon")->where("id= {$id}")->delete();
	    if ($res!==false) {
	        ajaxmsg('',1);
	    }else {
	        ajaxmsg('',0);
	    }
	}
	
	public function disabled()
	{
	    $id = $_POST['id'];
	    
	    $data['status'] = 2;
	    
	    $res = M("member_coupon")->where("id= {$id}")->save($data);
	    if ($res!==false) {
	        ajaxmsg('',1);
	    }else {
	        ajaxmsg('',0);
	    }
	}
	
}
?>