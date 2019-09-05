<?php
class CreditAction extends ACommonAction
{

	public function index()
	{
		$field = "id,score_from,score_to,rate";
		$list  =  M("ppc_passing_rate")->field($field)->order("id DESC")->select();
		$this->assign('list',$list);
		$this->display();
	}
	
	public function add()
	{
	    $this->display();
	}
	
	public function doAdd()
	{
	    $model     = M("ppc_passing_rate");
	     
	    if (false === $model->create()) {
	        $this->error($model->getError());
	    }
	    $model->startTrans();
	    
	    $model->score_from      = $_POST['score_from'];
	    $model->score_to        = $_POST['score_to'];
	    $model->rate            = $_POST['rate'];
	    $result = $model->add();
	     
	    if ($result) { //保存成功
	        $model->commit();
	        $this->assign('jumpUrl', __URL__/index);
	        $this->success(L('新增成功'));
	    }else{
	        $model->rollback();
	        //失败提示
	        $this->error(L('新增失败'));
	    }
	    
	}
	
	public function doEdit()
	{   
	    $id = $_POST['id'];
	    
	    $data['score_from'] = $_POST['score_from'];
	    $data['score_to']   = $_POST['score_to'];
	    $data['rate']       = $_POST['rate'];
	    $res  =  M("ppc_passing_rate")->where("id = {$id}")->save($data);
	    if ($res){ 
	        $this->assign('jumpUrl', __URL__/index);
	        $this->success(L('新增成功'));
	    }else{
	        $this->error(L('新增失败'));
	    }
	     
	}
	
	public function edit()
	{
	    $id = intval( $_GET['id'] );
	    $vo =M("ppc_passing_rate" )->find( $id );
	    $this->assign("vo", $vo );
	    $this->display();
	}
	
	public function del()
	{
	    $id   = intval( $_POST['id'] );
	    $res  =  M("ppc_passing_rate")->where("id = {$id}")->delete();
	    if($res){
	        ajaxmsg($msg."删除成功",1);
	    }else{
	        ajaxmsg($msg."删除失败",0);
	    }
	}
}

?>
