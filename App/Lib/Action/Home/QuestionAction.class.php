<?php 
class QuestionAction extends HCommonAction{
    public function index(){
        $item   = M("borrow_item")->field(true)->order(" id desc ")->find();
	    $this->assign('due_rate',$item['due_rate']);
	    $this->assign('late_rate',$item['late_rate']);
	    $this->display();
	}
}

?>