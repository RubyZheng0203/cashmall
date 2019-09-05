<?php

/**
 * 借款申请
 * @author Rubyzheng
 *
 */
class BlackAction extends ACommonAction
{
    /**
     * 随机放款金额的维护
     */
    public function random(){
        $map = array();
        $map['type'] = 1;
        if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
            $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
            $map['m.date']       = array("between",$timespan);
            $search['start_time']    = urldecode($_REQUEST['start_time']);
            $search['end_time']      = urldecode($_REQUEST['end_time']);
        }elseif(!empty($_REQUEST['start_time'])){
            $xtime = strtotime(urldecode($_REQUEST['start_time']));
            $map['m.date']       = array("gt",$xtime);
            $search['start_time']    = $xtime;
        }elseif(!empty($_REQUEST['end_time'])){
            $xtime = strtotime(urldecode($_REQUEST['end_time']));
            $map['m.date']       = array("lt",$xtime);
            $search['end_time']      = $xtime;
        }else{
            $time = strtotime(date("Y-m-d",time())." 00:00:00");
            $map['m.date'] = array("egt",$time);
        }
        
        //分页处理
        import("ORG.Util.Page");
        $count = M('random_lending m')->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('random_lending m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
        
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
        $this->display();
    }
    
    public function deleteRandom($param) {
        $id  = $_POST['id'];
        $res = M("random_lending")->where("id= {$id}")->delete();
        if ($res!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
    public function setup(){
        //分页处理
        import("ORG.Util.Page");
        $count = M('random_setup m')->where(" 1 = 1 ")->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('random_setup m')->field(true)->where(" 1 =1 ")->limit($Lsql)->order('m.id DESC')->select();
        $this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        
        $this->display();
    }
    
    public function changeStatus()
    {
        $id     = $_POST['id'];
        $status = $_POST['status'];
         
        $data['status'] = $status;
         
        $res = M("random_setup")->where("id= {$id}")->save($data);
        if ($res!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
    public function changeInterval()
    {
        $id         = $_POST['id'];
        $interval   = $_POST['interval'];
         
        $data['interval'] = $interval;
         
        $res = M("random_setup")->where("id= {$id}")->save($data);
        if ($res!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
    public function changeNumDay()
    {
        $id      = $_POST['id'];
        $num_day = $_POST['num_day'];
        $data['num_day'] = $num_day;
         
        $res = M("random_setup")->where("id= {$id}")->save($data);
        if ($res!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
    public function changeNum()
    {
        $id     = $_POST['id'];
        $num    = $_POST['num'];
         
        $data['num'] =$num;
         
        $res = M("random_setup")->where("id= {$id}")->save($data);
        if ($res!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
}
?>