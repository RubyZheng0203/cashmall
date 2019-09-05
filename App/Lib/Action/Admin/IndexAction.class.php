<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends ACommonAction {

	var $justlogin = true;
	
    public function index(){
		require(C('APP_ROOT')."Common/acl.inc.php");
		require(C('APP_ROOT')."Common/menu.inc.php");
		
       	$this->assign('menu_left',$menu_left);
		$this->display();
    }
	
	
	 public function logincheck(){
		$code=$_GET["code"];
		$datag = get_global_setting();
		$codecheck=$datag['admin_url'];
			
	    if($code!=$codecheck){
			$this->assign('jumpUrl', C('WEB_URL').'/admin/index/login');
            $this->error("非法请求");
	    }else{
			$this->redirect('login');
		}

	 }

	
	public function verify(){
		Header("Content-type: image/GIF");
	    import("ORG.Util.Imagecode");
	    $imagecode=new Imagecode(54,20,10,10,0,1,4,"1235467890");//(96,30);//参数控制图片宽、高54,20;字体大小 10、10 ;干扰雪花数量 0,1;验证码数量3
	    $imagecode->imageout();
	}

    public function login()
    {
		require C("APP_ROOT")."Common/menu.inc.php";
		if( session("admin") > 0){
			$this->redirect('index');
			exit;
		}
		if($_POST){
			if($_SESSION['code'] != sha1($_POST['code'])){
				$this->error("验证码错误!");
			}
			$data['user_name'] = text($_POST['admin_name']);
			$data['user_pass'] = md5(strtolower($_POST['admin_pass']));
			$data['is_ban'] = array('neq','1');
			$data['user_word'] = text($_POST['user_word']);
			$admin = M('ausers')->field('id,user_name,u_group_id,real_name,is_kf,area_id,user_word,last_log_time,last_log_ip')->where($data)->find();
			
			if(is_array($admin) && count($admin)>0 ){
				foreach($admin as $key=>$v){
					session("admin_{$key}",$v);
				}
				if(session("admin_area_id")==0) session("admin_area_id","-1");
				session('admin',$admin['id']);
				session('adminname',$admin['user_name']);
				$info['last_log_time'] = time();
                $info['last_log_ip'] = get_client_ip();
				M("ausers")->where('id='.$admin['id'])->save($info);
				 
				alogs("login",'','1',"管理员登陆成功");//管理员操作日志之登陆日志
				$this->assign('jumpUrl', "__URL__/index");
				$this->success('登陆成功，现在转向管理主页');
			}else{
				alogs("login",'','0',"管理员登陆失败",$admin['real_name']);
				$this->error('用户名或密码或口令错误，登陆失败');
			}
		}else{
			//$this->error("非法请求");
			//require('App/Tpl/Admin/default/Index/login.html');
			//require(C('WEB_URL').'/lvmaque/admin');
			$this->display();
		}
		
    }
	public function index_menu2()
    {
		require(C('APP_ROOT')."Common/acl.inc.php");
		require(C('APP_ROOT')."Common/menu.inc.php");
		
       	$this->assign('menu_left',$menu_left);
		$this->display("Public:index_menu");
		
    }

    public function logout()
    {
		alogs("logout",'','1',"管理员退出");
		session(null);
		$this->assign('jumpUrl', '/');
		$this->success('注销成功，现在转向首页');
    }
	
}