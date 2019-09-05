<?php
class BaiqishiIdAction extends HCommonAction{
    
    /**
     * 白骑士异步回调
     */
	public function index(){
	    
	    $str = str_replace('/baiqishiId/', '', $_SERVER['REQUEST_URI']);
	    $postion       = strpos($str,".");
	    $borrow_id     = substr($str,0,$postion);
	    $success       = $_GET['success'];
	    $error_code    = $_GET['error_code'];
	    $open_id       = $_GET['open_id'];
	    $error_message = $_GET['error_message'];
	    $where['id']   = $borrow_id;
	    $apply = M("borrow_apply")->field(true)->where($where)->find();
	    if($success =="true"){
	        $data['zhima_openid']   = $open_id;
	        M("members")->where("id =". $apply['uid'] )->save($data);
	        findZhima($apply['uid'], $open_id);
	        if (isset($_GET['plat'])){
	            $realurl = "/borrow/dealAcceptUser?uid={$apply['uid']}&type=1&bid={$borrow_id}&plat=app";
	        }else {
	            $realurl = "/borrow/dealAcceptUser?uid={$apply['uid']}&type=1&bid={$borrow_id}";
	        }  
	    }else{
	    if (isset($_GET['plat'])){
	            $realurl = "/borrow/dealAcceptUser?uid={$apply['uid']}&type=0&bid={$borrow_id}&plat=app";
	        }else {
	            $realurl = "/borrow/dealAcceptUser?uid={$apply['uid']}&type=0&bid={$borrow_id}";
	        }  
	    }
	    $this->redirect($realurl);
	}
	
}
