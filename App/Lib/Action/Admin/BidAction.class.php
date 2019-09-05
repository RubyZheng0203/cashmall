<?php
// 本类由系统自动生成，仅供测试用途
class BidAction extends ACommonAction {
    
    /**
     * 带授信的列表
     */
    public function recheck(){
        $map = array();
        $map['m.status']         = 3;
        $map['m.audit_status']   = 4;
        $map['ms.signed']        = 1;
        $map['m.up_bid']         = 0;
        $map['m.renewal_id']     = 0;
        $map['ms.is_recheck']    = 0;
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
        }
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.signed_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.signed_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime; 
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.signed_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-2 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.signed_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time,mbs.promotion_code";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
        ->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.signed_time DESC')->select();
        
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
     /**
     * 待复审的列表
     */
    public function review(){
        $map = array();
        $map['m.status']         = 3;
        $map['m.audit_status']   = 4;
        $map['ms.signed']        = 1;
        $map['m.up_bid']         = 0;
        $map['m.renewal_id']     = 0;
        $map['ms.id_verify']     = 1;
        $map['ms.is_recheck']    = 1;
        $map['ms.is_review']     = 0;
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
        }
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.recheck_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.recheck_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.recheck_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-4 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.recheck_time']       = array("egt",$time);
        }
        //分页处理 
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time,mbs.promotion_code,ms.recheck_time";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
        ->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.recheck_time ASC')->select();
        
        $list  = $this->_listFilter($list);

        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }

    /*
     * 复审审核页面
     */
    public function showreview(){
        //查找用户信息
        $uid = $_GET['uid'];
        $bid = $_GET['id'];
        $info = M('member_info')->field(true)->where("uid = {$uid}")->find();
        //图片信息
        $path = C("MEM_PATH");
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $filename1 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-1.jpg';
        $filename2 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-2.jpg';
        $filename3 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-3.jpg';
        
        //身份验证最佳图片获取
        get_face_pic($uid,$bid);
        $path_face = C("Face_PIC_PATH");
        $filename4 = $http_type.$_SERVER['HTTP_HOST'].$path_face.$uid.'.jpeg';
        $filename5 = $http_type.$_SERVER['HTTP_HOST'].$path_face.$uid.'-1.jpg';
        
        $this->assign("filename1",$filename1);
        $this->assign("filename2",$filename2);
        $this->assign("filename3",$filename3);
        $this->assign("filename4",$filename4);
        $this->assign("filename5",$filename5);
        $this->assign("info",$info);
        $this->assign("bid",$bid);
        $this->display();
    }

    /*
     * 待复审(通过||不通过)
     */
    public function revsave(){
        $bid = $_POST['bid'];
        $uid = $_POST['uid'];
    
        if($_POST['status'] == 1){//通过
            $where['borrow_id']  = $bid;
            $data['is_review']   = 1;
            $data['review_time'] = time();
            /*            $data['reason'] = $_POST['reason'];*/
            $demo = M('member_status')->where($where)->save($data);
    
            if($demo){
                //复审成功发送微信
                $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                if($wxInfo['openid']!==""){
                    $binfo = M('borrow_apply')->field('money')->where("id={$bid} and uid = {$uid}")->find();
                    sendWxTempleteMsg21($wxInfo['openid'], $binfo['money'], date("Y-m-d",time()));
                }
    
                //发送App推送通知复审成功
                $mwhere['uid'] = $uid;
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg4($uid,$token['token'],$bid);
                }
                $this->success("审核通过,操作成功");
            }else{
                $this->error("审核通过,操作失败");
            }
        }else if($_POST['status'] == 2){//不通过
            $where['borrow_id'] = $bid;
            $data['is_review'] = 2;
            $data['review_time'] = time();
            /*            $data['reason'] = $_POST['reason'];*/
            $demo = M('member_status')->where($where)->save($data);
            $app['status'] = 94;
            $app['refuse_time'] = time();
            if($demo){
                $demo2 = M('borrow_apply')->where("id = {$bid}")->save($app);
                //复审失败发送微信
                $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                if($wxInfo['openid']!==""){
                    sendWxTempleteMsg22($wxInfo['openid']);
                }
    
                //发送App推送通知复审失败
                $mwhere['uid'] = $uid;
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg3($uid,$token['token'],$bid);
                }
                delUserOperation($uid,$bid);
                $this->success("审核不通过,操作成功");
            }else{
                $this->error("审核不通过,操作失败");
            }
        }
    }
    
    /*
     * 授信(通过||不通过)
     */
    public function checksave(){
        $bid = $_POST['bid'];
        $uid = $_POST['uid'];

        if($_POST['status'] == 1){//通过
            $res = updaterecheck($uid,$bid);
            if($res==1){
                $this->success("审核通过,操作成功");
            }else{
                $this->error("审核通过,拍拍信分数未通过");
            }
        }
    }

    /**
     * 复审失败列表
     */
    public function reviews(){
        $map = array();
        $map['ms.is_review']     = 2;
        /*$map['mp.status']        = 1;*/
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
        }
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.review_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.review_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.review_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-4 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.review_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time,mbs.promotion_code,ms.recheck_time,ms.review_time";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
        ->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.recheck_time desc')->select();
        
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 资金筹集失败列表
     */
    public function reviews2(){
        $map = array();
        $map['ms.pending']       = 2;
/*        $map['mp.status']        = 1;*/
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
        }
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.pending_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.pending_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.pending_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-4 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.pending_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time,mbs.promotion_code,ms.recheck_time,ms.review_time";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
        ->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.recheck_time desc')->select();
      
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }

    /**
     * 人脸失败列表
     */
    public function reviews3(){
        $map = array();
        $map['ms.id_verify']     = 2;
/*        $map['mp.status']        = 1;*/
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
        }
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.id_verify_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.id_verify_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.id_verify_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-4 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.id_verify_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time,mbs.promotion_code,ms.recheck_time,ms.review_time";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
        ->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.recheck_time desc')->select();
        
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }

    /**
     * 拍拍信失败列表
     */
    public function reviews4(){
        $map = array();
        $map['ms.is_ppc']        = 2;
/*        $map['mp.status']        = 1;*/
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
        }
        if($_REQUEST['iphone']){
            $map['mi.iphone'] = urldecode($_REQUEST['iphone']);
            $search['iphone']  = $map['mi.iphone'];
        }
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.ppc_time']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.ppc_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.ppc_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-4 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.ppc_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.signed_time,mbs.promotion_code,ms.recheck_time,ms.review_time";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
        ->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.recheck_time desc')->select();
        
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }

    /*
     * 确认退款(*暂时废弃)
     */
    public function mback(){
        $where['borrow_id'] = $_POST['id'];
        $where['status']    = 1;
        $data['is_refund']  = 1;
        $demo = M('transfer_order_pay')->where($where)->save($data);

        if($demo){
            ajaxmsg('退款确认成功',1);
        }else{
            ajaxmsg('退款确认失败',0);
        }
    } 
    
    

    /*
     * 可上标审核
     */
    public function showrecheck(){
        //查找用户信息
        $uid = $_GET['uid'];
        $bid = $_GET['id'];
        
        $this->assign("uid",$uid);
        $this->assign("bid",$bid);
        $this->display();
    }
    
    /**
     * 可上标审核执行
     */
    public function dorecheck(){
        //接收
        $uid    = $_POST['uid'];
        $bid    = $_POST['bid'];
        $status = $_POST['status'];
        $reason = $_POST['reason'];
        if($status == 2){//复审拒绝
            //更新状态
            $data['status']          = 94;
            $data['refuse_time']     = time();
            $updata = M('borrow_apply')->where("id={$bid} and uid = {$uid}")->save($data);
            
            $data1['is_recheck']     = 2;
            $data1['recheck_time']   = time();
            $data1['recheck_uid']    = session('adminname');
            $data1['reason']         = $reason;
            $updata1 = M('member_status')->where("borrow_id={$bid} and uid = {$uid}")->save($data1);
            
            //删除option信息
            delUserOperation($uid,$bid);
            
            //复审失败发送微信
            $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
            if($wxInfo['openid']!==""){
                sendWxTempleteMsg22($wxInfo['openid']);
            }

            //发送App推送通知复审失败
            $mwhere['uid'] = $uid;
            $token = M('member_umeng')->where($mwhere)->field(true)->find();
            if(!empty($token['token'])){
                AndroidTempleteMsg3($uid,$token['token'],$bid);
            }
            
            if($updata1){
                $this->success("状态更新成功");
            }else{
                $this->error("状态更新失败");
            }
        }else{
        
            $isPpc = isPpc($uid);
            //风控拍拍信验证
            if($isPpc==1){
                $data1['is_ppc']     = 1;
                $data1['ppc_time']   = time();
                $isIdVerify = isIdVerify($uid);
                //人脸免验证检查
                if($isIdVerify == 1){
                    $data1['id_verify']        = 1;
                    $data1['id_verify_time']   = time();
                }
            }else{
                $data1['is_ppc']     = 2;
                $data1['ppc_time']   = time();
            }
            
            
            $data1['is_recheck']     = 1;
            $data1['recheck_time']   = time();
            $data1['recheck_uid']    = session('adminname');
            $data1['reason']         = $reason;
            
            //复审通过冷静期没有确认的直接确认掉
            $memstatus = M('member_status')->field("id,calm")->where("borrow_id = {$bid} and uid = {$uid}")->find();
            if($memstatus['calm']==0){
                $data1['calm']           = 1;
                $data1['calm_time']      = time();
            }
            $updata1 = M('member_status')->where("borrow_id = {$bid} and uid = {$uid}")->save($data1);
            
            //拍拍信通过后
            if($isPpc==1){
                //复审通过后免人脸的自动上标
                if($isIdVerify == 1){
                    $datag        = get_global_setting();
                    $is_aotu      = $datag['is_aotu_bid'];
                    if($is_aotu == 1){
                        //人脸识别成功后自动上标到福米金融
                        $upbid = createFumiBid($bid,0);
                    }
                }
                
                //复审成功发送微信
                $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                if($wxInfo['openid']!==""){
                    $binfo = M('borrow_apply')->field('money')->where("id={$bid} and uid = {$uid}")->find();
                    sendWxTempleteMsg21($wxInfo['openid'], $binfo['money'], date("Y-m-d",time()));
                }

                //发送App推送通知复审成功
                $mwhere['uid'] = $uid;
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg4($uid,$token['token'],$bid);
                }
            }else{
                //更新状态
                $data['status']          = 95;
                $data['refuse_time']     = time();
                $updata = M('borrow_apply')->where("id={$bid} and uid = {$uid}")->save($data);
                
                //删除option信息
                delUserOperation($uid,$bid);
                
                //复审失败发送微信
                $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                if($wxInfo['openid']!==""){
                    sendWxTempleteMsg22($wxInfo['openid']);
                }

                //发送App推送通知复审失败
                $mwhere['uid'] = $uid;
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg3($uid,$token['token'],$bid);
                } 
            }
            
            if($updata1){
                $this->success("状态更新成功");
            }else{
                $this->error("状态更新失败");
            }
        }
    }
    
    public function doAppRisk(){
        $model = new CheckUserAction();
        $model->appRiskAudit($uid,$moblie,$borrowId);
        
    }
    
    /**
     * 可上标(已复审的)
     */
    public function index(){
		$map = array();
		$map['m.status']         = 3;
		$map['m.audit_status']   = 4;
		$map['m.up_bid']         = 0;
		$map['m.renewal_id']     = 0;
		$map['ms.is_recheck']    = 1;
		$map['ms.is_review']     = 1;
		$map['ms.id_verify']     = 1;
		
		if($_REQUEST['borrowid']){
		    $map['m.id'] = urldecode($_REQUEST['borrowid']);
		    $search['borrowid']  = $map['m.id'];
		}
		if($_REQUEST['uid']){
		    $map['m.uid'] = urldecode($_REQUEST['uid']);
		    $search['uid']  = $map['m.uid'];
		}
		
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.recheck_time']       = array("between",$timespan);
            $search['start_time']      = urldecode($_REQUEST['start_time']);
            $search['end_time']        = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['ms.recheck_time']       = array("gt",$xtime);
            $search['start_time']      = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['ms.recheck_time']       = array("lt",$xtime);
            $search['end_time']        = $xtime;
        }else{
            $time  = strtotime("-2 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['ms.recheck_time']       = array("egt",$time);
        }
        
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->join("{$this->pre}members mbs ON mi.uid=mbs.id")->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";
		//sql查询
		$field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,ms.recheck_time,mbs.promotion_code";
        $list  = M('borrow_apply m')->field($field)
        ->join("{$this->pre}member_info mi ON mi.uid=m.uid")
        ->join("{$this->pre}members mbs ON mi.uid=mbs.id")
		->join("{$this->pre}member_status ms ON ms.borrow_id=m.id")->where($map)->limit($Lsql)->order('ms.recheck_time DESC')->select();
		$list  = $this->_listFilter($list);
		$this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }
    
    /*
     * 可上标审核
     */
    public function check(){
        //查找用户信息
        $uid = $_GET['uid'];
        $bid = $_GET['id'];
        $info = M('member_info')->field(true)->where("uid = {$uid}")->find();
        //图片信息
        $path = C("MEM_PATH");
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $filename1 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-1.jpg';
        $filename2 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-2.jpg';
        $filename3 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-3.jpg';
        
        //身份验证最佳图片获取
        get_face_pic($uid,$bid);
        $path_face = C("Face_PIC_PATH");
        $filename4 = $http_type.$_SERVER['HTTP_HOST'].$path_face.$uid.'.jpeg';
        $filename5 = $http_type.$_SERVER['HTTP_HOST'].$path_face.$uid.'-1.jpg';
        
        $this->assign("filename1",$filename1);
        $this->assign("filename2",$filename2);
        $this->assign("filename3",$filename3);
        $this->assign("filename4",$filename4);
        $this->assign("filename5",$filename5);
        $this->assign("info",$info);
        $this->assign("bid",$bid);
        $this->display();
    }
    
    /**
     * 可上标审核执行
     */
    public function doCheck(){
        //接收
        $uid = $_POST['uid'];
        //borrow_id
        $bid = $_POST['bid'];
        $type = $_POST['type'];
        if($type == 1){
            //更新状态
            $data['status']      = 96;
            $data['refuse_time'] = time();
            $updata = M('borrow_apply')->where("id={$bid} and uid = {$uid}")->save($data);
            $data1['pending']    = 2;
            $updata1 = M('member_status')->where("borrow_id={$bid} and uid = {$uid}")->save($data1);
            //删除option信息
            delUserOperation($uid,$bid);
            //发送App推送通知放款失败
            $mwhere['uid'] = $uid;
            $token = M('member_umeng')->where($mwhere)->field(true)->find();
            if(!empty($token['token'])){
                AndroidTempleteMsg6($uid,$token['token'],$bid);
            }
            $this->success("审核失败,状态更新成功");
        }else{
            $this->success("审核成功");
        }
    }
    
    /**
     * (续期的借款）
     */
    public function renewal(){
        $map = array();
        $map['m.status']         = 4;
        $map['m.audit_status']   = 5;
        $map['m.up_bid']         = 0;
        $map['m.renewal_id']     = array("gt","0");
        //$map['m.is_new']         = 1;
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
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
            $time  = strtotime("-2 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['m.add_time']       = array("egt",$time);
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time";
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->limit($Lsql)->order('m.id DESC')->select();
        $list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     *需调整的续期
     */
    public function transferList(){
        $map = array();
        $map['m.up_bid']         = array("gt","0");
        $map['m.renewal_id']     = array("gt","0");
        $map['m.is_full']        = 1;
        $map['m.is_balance']     = 0;
    
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.uid'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.uid'];
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
            $time  = strtotime("-2 day",strtotime(date("Y-m-d",time())." 00:00:00"));
            $map['m.len_time']       = array("egt",$time);
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,m.len_time,m.up_bid,m.is_full,m.renewal_id";
        $list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->limit($Lsql)->order('m.up_bid DESC,m.id DESC')->select();
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
            $row[$key]=$v;
        }
        return $row;
    }
	
    /**
     * 已上标
     */
    public function upList(){
        $map = array();
        /*$map['m.status']         = array("in","4,5");
        $map['m.audit_status']   = 5;*/
        $map['m.up_bid']         = array("gt","0");
		if($_REQUEST['borrowid']){
		    $map['m.id'] = urldecode($_REQUEST['borrowid']);
		    $search['borrowid']  = $map['m.id'];
		}
		if($_REQUEST['uid']){
		    $map['m.uid'] = urldecode($_REQUEST['uid']);
		    $search['uid']  = $map['m.uid'];
		}
		
		if($_REQUEST['iphone']){
		    $map['mi.iphone']  = urldecode($_REQUEST['iphone']);
		    $search['iphone']  = $map['mi.iphone'];
		}
		if($_REQUEST['is_loan']=='yes'){
		    $map['m.status']       = 3;
		    $map['m.audit_status'] = 4;
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
		}
		
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";
		//sql查询
		$field = "m.id,m.uid,mi.iphone,mi.real_name,m.money,m.rate,m.duration,m.repayment_type,m.interest,m.add_time,m.len_time,m.up_bid,m.is_full,m.renewal_id";
		$list  = M('borrow_apply m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.uid")->where($map)->limit($Lsql)->order('m.up_bid DESC,m.id DESC')->select();
		$list  = $this->_listFilter($list);
		$this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }
    
    public function upbid(){
        $id      = intval($_GET['id']);
        //是否发送福米推送
        $send_wechat = $_GET['send_wechat']?$_GET['send_wechat']:0;
        $borrow  = M('borrow_apply m')->field("id,up_bid")->where("id = {$id} ")->find();
        if($borrow['up_bid']>0){
            $this->error("已经上过标了");
        }else{
            $res = createFumiBid($id,$send_wechat);
            if($res){
                $this->success("处理成功");
            }else{
                $this->error("处理失败");
            }
        } 
        
    }
    
    public function upBatchBid(){
        $id    = $_POST['id'];
        $rate  = $_POST['rate'];
        //是否发送福米推送
        $send_wechat = $_POST['send_wechat']?$_POST['send_wechat']:0;
        $list  = M('borrow_apply')->field(true)->where("id in ({$id}) ")->select();
        $flg   = 1;
        $msg   = "";
        $ids   = "";
        foreach($list as $key=>$v){
            if($v['up_bid'] >0){
                if($msg==""){
                    $msg = "借款申请".$v['id'];
                    
                }else{
                    $msg = ",".$v['id'];
                }
                $flg = 0;
            }
        }
        if($flg==0){
            ajaxmsg($msg."已经上过标了！",0);
        }else{
            $money  = M('borrow_apply')->where(" id in ({$id}) ")->sum("money");
            $res    = batchFumiBid($id,$list,$rate,$money,$send_wechat);
            $res    = false;
            if($res){
                 ajaxmsg("",1);
            }else{
                 ajaxmsg("",0);
            }
        }
    
    }
    
    /**
     * 续期转账
     */
    public function transfer(){
        $id      = $_POST['id'];
        $list    = M('borrow_apply')->field(true)->where("id in ({$id}) ")->select();
        
        $msg     = "";
        $idc     = "";
        $ids     = "";
        $idf     = "";
        $idgf    = "";
        $datag   = get_global_setting();
        $account = $datag['loan_account'];
        $uid     = "fumi".$account;
        foreach($list as $key=>$v){
            if($v['is_balance'] >0){
                if($idc==""){
                    $idc = "借款申请".$v['id'];
                }else{
                    $idc = ",".$v['id'];
                }
            }else{
                $apply   = M('borrow_apply')->field(true)->where(" id = {$v['id']} ")->find();
				$res     = xqHostingPayTrade($apply['trade_no'], $v['id'], $uid, $apply['money']);
                if($res->success()){
                    $data['is_balance'] = 1;
                    $update = M('borrow_apply')->where(" id = {$v['id']} ")->save($data);
                    if($update>0){
                        if($ids==""){
                            $ids = "借款申请".$v['id'];
                        }else{
                            $ids = ",".$v['id'];
                        }
                    }else{
                        if($idgf==""){
                            $idgf = "借款申请".$v['id'];
                        }else{
                            $idgf = ",".$v['id'];
                        }
                    }
                }else{
                    if($idf==""){
                        $idf = "借款申请".$v['id'];
                    }else{
                        $idf = ",".$v['id'];
                    }
                }
            }
        }
        if($idc !=""){
            $msg = $msg.$idc."已经转过账了不做处理了！";
        }
        if($ids !=""){
            $msg = $msg.$ids."转账成功！";
        }
        if($idgf !=""){
            $msg = $msg.$idgf."转账成功，更新失败！";
        }
        if($idf !=""){
            $msg = $msg.$idf."转账失败！";
        }
        ajaxmsg($msg,1);
    }
    
    /**
     * 已结算的费用
     */
    public function balance(){
        $map = array();
        $map['m.status']         = array("in","4,5");
        $map['m.audit_status']   = 5;
        $map['m.is_balance']     = 1;
        $map['m.up_bid']         = array("gt","0");
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.loan_account'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.loan_account'];
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
        }
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_item m')->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('borrow_apply m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
        //$list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     *未结算的费用
     */
    public function unbalance(){
        $map = array();
        $map['m.status']         = array("in","4,5");
        $map['m.audit_status']   = 5;
        $map['m.is_balance']     = 0;
        $map['m.up_bid']         = array("gt","0");
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
        }
        if($_REQUEST['uid']){
            $map['m.loan_account'] = urldecode($_REQUEST['uid']);
            $search['uid']  = $map['m.loan_account'];
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
        }
    
        //分页处理
        import("ORG.Util.Page");
        $count = M('borrow_item m')->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('borrow_apply m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
        //$list  = $this->_listFilter($list);
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
    
        $this->display();
    }
    
    /**
     * 汇总结算
     */
    public function sumbill() {
        $res       = true;
        $borrow_id = $_GET['id'];
        wqbLog($borrow_id);
        $apply     = M('borrow_apply m')->field(true)->where(" id = ".$borrow_id." and m.`status` in (4,5) and m.audit_status = 5 and m.is_balance = 0 ")->find();
        if(empty($apply)){
            $this->error("该记录已经结算过或还未放款，请确认后再操作！");
        }else{
            $other_fee = $apply['enabled_fee']+$apply['created_fee'];
            $audit_fee = $apply['audit_fee'];
            $total_fee = $other_fee+$audit_fee;
            
            //更新平账状态
            $data['is_balance'] = 1;
            $update    = M('borrow_apply m')->field(true)->where(" m.id = ".$borrow_id ." and m.`status` in (4,5) and m.audit_status = 5 and m.is_balance = 0 ")->save($data);
        
            //$this->success("处理成功");
            
            //新浪过账
            $borrow_name = "现贷猫费用";
    
            $datag        = get_global_setting();
            $api_account  = $datag['api_account'];
            $loan_account = $datag['loan_account'];
    
            $res    = sinaHostingCollectionTradeDirect($apply['loan_account'], $borrow_name, $total_fee);
            if($res == 1){
                //代付请求给代付第三方
                $ress = wqbHostingPayTradeToUID($api_account, $audit_fee,$borrow_name.":".$borrow_id);
                if($res == 1){
                    $msg = "信审费转出失败";
                    $this->error("信审费转出失败");
                }else{
                    $this->success("处理成功");
                }
            }else{
                $data['is_balance'] = 0;
                $update    = M('borrow_apply m')->field(true)->where(" id = ".$borrow_id)->save();
                $this->error("结算失败，新浪支付接口有错");  
            } 
        }
    }
    
    
    /**
     * 汇总结算
     */
    public function sumamount() {
        $res        = true;
        $borrow_id  = $_POST['id'];
        if($borrow_id ==""){
            $msg    = "请选择需要汇总的记录！";
            $res    = false;
        }else{
            $sqlcount = "SELECT distinct loan_account  from ml_borrow_apply aa  where aa.`status` in (4,5) and aa.audit_status = 5 and aa.is_balance = 0 and up_bid > 0 and id in (".$borrow_id.")  ";
            $count   = M()->query($sqlcount);
            if(count($count)>1){
                $msg    = "请选择放款帐户一致的结算！";
                $res    = false;
            }else{
                $sql = "SELECT sum(aa.audit_fee) as audit_fee ,sum(aa.created_fee) as created_fee ,sum(aa.enabled_fee) as enabled_fee from ml_borrow_apply aa  where aa.`status` in (4,5) and aa.audit_status = 5 and aa.is_balance = 0 and up_bid > 0 and id in (".$borrow_id.")  ";
                $apply   = M()->query($sql);
                
                $other_fee = $apply[0]['enabled_fee']+$apply[0]['created_fee'];
                $audit_fee = $apply[0]['audit_fee'];
                $total_fee = $other_fee+$audit_fee;
                //更新平账状态
                M()->query("update ml_borrow_apply set is_balance = 1 where id in ($borrow_id)");
                
                //新浪过账
                $borrow_name = "现贷猫费用";
                
                $datag        = get_global_setting();
                $api_account  = $datag['api_account'];
                $loan_account = $datag['loan_account'];
                
                $res    = sinaHostingCollectionTradeDirect($count[0]['loan_account'], $borrow_name, $total_fee);
                if($res == 1){
                    //代付请求给代付第三方
                    $ress = wqbHostingPayTradeToUID($api_account, $audit_fee,$borrow_name.":".$borrow_id);
                    if($res == 1){
                        $msg = "信审费转出失败";
                    }
                }else{
                    M()->query("update ml_borrow_apply set is_balance = 0 where id in ($borrow_id)");
                    $msg = "结算失败，新浪支付接口有错";
                }
            }
            
        }
        if ($msg !="") {
            ajaxmsg($msg,1);
        }else{
            ajaxmsg('',0);
        }
    }
    
    public function checkrisk(){
        $uid        = intval($_GET['uid']);
        $iphone     = $_GET['iphone'];
        $borrowid   = $_GET['id'];
        $this->assign("uid",$uid);
        $this->assign("id",$borrowid);
        $this->assign("iphone",$iphone);
        $this->display();
    }
    
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
}