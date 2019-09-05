<?php

/**
 * 借款产品
 * @author Rubyzheng
 *
 */
class ItemAction extends ACommonAction
{

    /**
     * 借款产品一类
     */
    public function index()
    {
		$map           = array();
		$map['m.type'] = 1;
		if($_REQUEST['borrowid']){
		    $map['m.id'] = urldecode($_REQUEST['borrowid']);
		    $search['borrowid']  = $map['m.id'];
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
		$count = M('borrow_item m')->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";
		//sql查询
		$list  = M('borrow_item m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
		$list  = $this->_listFilter($list);
		$this->assign("list", $list);
        $this->assign("pagebar", $page);
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));

        $this->display();
    }
    
  
    /**
     * 借款产品二类
     */
    public function itemSec()
    {
        $map           = array();
        $map['m.type'] = 2;
        if($_REQUEST['borrowid']){
            $map['m.id'] = urldecode($_REQUEST['borrowid']);
            $search['borrowid']  = $map['m.id'];
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
        $count = M('borrow_item m')->where($map)->count('m.id');
        $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //sql查询
        $list  = M('borrow_item m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
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
     * 结款产品上下架
     */
    public function changeOn(){
        $id     = $_POST['id'];
        $status = $_POST['status'];
        
        $data['is_on'] = $status;
        $res = M("borrow_item")->where("id= {$id}")->save($data);
        if ($res!==false) {
            ajaxmsg('',1);
        }else {
            ajaxmsg('',0);
        }
    }
    
    /**
     *
     */
    public function add(){
        $repayment  = C('REPAYMENTTYPE');
        $this->assign("repayment", $repayment);
        
        $type = $_GET['type'];
        if($type==1){
            $name = "Ⅰ类";
        }else{
            $name = "Ⅱ类";
        }
        $this->assign("type", $type);
        $this->assign("name", $name);
    
        $this->display();
    }
    
    public function doAdd(){
    
            $model     = M("borrow_item");
             
            if (false === $model->create()) {
                $this->error($model->getError());
            }
            $model->startTrans();
            
            $model->money           = $_POST['money'];
            $model->duration        = $_POST['duration'];
            $model->repayment_type  = $_POST['repayment_type'];
            $model->rate            = $_POST['rate'];
            $model->interest        = $_POST['interest'];
            $model->audit_rate      = $_POST['audit_rate'];
            $model->created_rate    = $_POST['created_rate'];
            $model->enabled_rate    = $_POST['enabled_rate'];
            $model->renewal_fee     = $_POST['renewal_fee'];
            $model->due_rate        = $_POST['due_rate'];
            $model->late_rate       = $_POST['late_rate'];
            $model->is_on           = $_POST['is_on'];
            $model->type            = $_POST['type'];
            $model->is_xuqi         = $_POST['is_xuqi'];
            $model->add_time        = time();
            $result = $model->add();
             
        
            if ($result) { //保存成功
                $model->commit();
                alogs("Tborrow",$result,1,'成功执行了借款产品的添加操作！');//管理员操作日志
                //成功提示
                if($_POST['type']==1){
                    $this->assign('jumpUrl', __URL__/index);
                }else{
                    $this->assign('jumpUrl', __URL__/itemSec);
                }
                $this->success(L('新增成功'));
            }else{
                alogs("Tborrow",$result,0,'执行借款产品的添加操作失败！');//管理员操作日志
                $model->rollback();
                //失败提示
                $this->error(L('新增失败'));
            }
        
    
    }
    
    
    /**
     * 
     */
    public function edit(){
        $id = $_GET['id'];
        $repayment  = C('REPAYMENTTYPE');
        
        $vo = M('borrow_item')->where(" id = {$id}")->find();
        $this->assign("vo", $vo);
        $this->assign("repayment", $repayment);
        
        $this->display();
    }
    
    public function doEdit(){
    
        $model = M("borrow_item");
        
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        
        $model->startTrans();
        $model->money           = $_POST['money'];
        $model->duration        = $_POST['duration'];
        $model->repayment_type  = $_POST['repayment_type'];
        $model->rate            = $_POST['rate'];
        $model->interest        = $_POST['interest'];
        $model->audit_rate      = $_POST['audit_rate'];
        $model->created_rate    = $_POST['created_rate'];
        $model->enabled_rate    = $_POST['enabled_rate'];
        $model->renewal_fee     = $_POST['renewal_fee'];
        $model->due_rate        = $_POST['due_rate'];
        $model->late_rate       = $_POST['late_rate'];
        $model->is_on           = $_POST['is_on'];
        $model->is_xuqi         = $_POST['is_xuqi'];
        
        $result = $model->save();
        
        if ($result) { //保存成功
            $model->commit();
            alogs("Tborrow",0,1,'成功执行了借款产品的修改操作！');//管理员操作日志
            //成功提示
            $this->assign('jumpUrl', __URL__/index);
            $this->success(L('修改成功'));
        } else {
            alogs("Tborrow",0,0,'执行借款产品的修改操作失败！');//管理员操作日志
            $model->rollback();
            //失败提示
            $this->error(L('修改失败'));
        }
    }
}
?>