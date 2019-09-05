<?php
// 全局设置
class MsgonlineAction extends ACommonAction
{
    /**
    +----------------------------------------------------------
    * 默认操作
    +----------------------------------------------------------
    */
    public function index()
    {
		$msgconfig = FS("Webconfig/msgconfig");
		$type = $msgconfig['sms']['type'];// type=0 漫道短信接口
		$uid2=$msgconfig['sms']['user2']; //分配给你的账号
		$pwd2=$msgconfig['sms']['pass2']; //密码 
		if($type==0){
			$d=@file_get_contents("http://sdk2.zucp.net:8060/webservice.asmx/balance?sn={$uid2}&pwd={$pwd2}",false);
			preg_match('/<string.*?>(.*?)<\/string>/', $d, $matches);
			
			if($matches[1]<0){ 
				switch($matches[1]){
					case -2:
						$d="帐号/密码不正确或者序列号未注册";
					break;
					case -4:
						$d="余额不足";
					break;
					case -6:
						$d="参数有误";
					break;
					case -7:
						$d="权限受限,该序列号是否已经开通了调用该方法的权限";
					break;
					case -12:
						$d="序列号状态错误，请确认序列号是否被禁用";
					break;
					default:
						$d="用户名或密码错误";
					break;
				}
			}else{
				$d = $d."条";
			}
			$this->assign('zucp',$d);
		}
		
		$this->assign('stmp_config',$msgconfig['stmp']);
		$this->assign('sms_config',$msgconfig['sms']);
		$this->assign('sms_config_type',$msgconfig['sms']['type']);
		$this->assign('baidu_config',$msgconfig['baidu']);
		$this->assign("type_list", array("0"=>'开通短信服务',"1"=>'关闭短信服务'));
        $this->display();
    }
    public function save(){	
		if($_GET['yx']){
		    import("ORG.Net.UploadFile");
			$upload=new UploadFile();
	        $upload->maxSize=3145728;
	        $upload->saveRule = 'time';
			$upload->thumb = true ;
			$upload->thumbMaxWidth ="80,80" ;
			$upload->thumbMaxHeight = "80,80";
			$upload->allowExts=array('jpg','gif','png','jpg');
	        $upload->savePath='./UF/Uploads/Article/';
		    $pathsave="/UF/Uploads/Article/";
		    $upload->upload();
	
		    $info=$upload->getUploadFileInfo();
		        
		  	if(empty($info)){
				$json['message']=$pathsave.$info[0]['savename'];
				$json['status']=0;
				exit(json_encode($json));
			}else{
			   $json['message']=$pathsave.$info[0]['savename'];
			   $json['status']=1;
			   exit(json_encode($json));
			}
		  }else{
	        $status = $_POST['msg']['sms']['type'];
			if($status=='0'){
				
				$pwd = $_POST['msg']['sms']['user2'].$_POST['msg']['sms']['pwd'];
				$_POST['msg']['sms']['pass2'] =strtoupper(md5($pwd));//$pwd
				$_POST['msg']['sms']['pwd'] = $_POST['msg']['sms']['pwd'];
			}
	   
			FS("msgconfig",$_POST['msg'],"Webconfig/");
			alogs("Msgonline",0,1,'成功执行了通知信息接口的编辑操作！');//管理员操作日志
			$this->success("操作成功",__URL__."/index/"); 
		 }
    }
	
	
    public function templet()
    {
		$emailTxt = FS("Webconfig/emailtxt");
		$smsTxt = FS("Webconfig/smstxt");
		$msgTxt = FS("Webconfig/msgtxt");

		$this->assign('emailTxt',de_xie($emailTxt));
		$this->assign('smsTxt',de_xie($smsTxt));
		$this->assign('msgTxt',de_xie($msgTxt));
        $this->display();
    }
	
    public function templetsave()
    {
		FS("emailtxt",$_POST['email'],"Webconfig/");
		FS("smstxt",$_POST['sms'],"Webconfig/");
		FS("msgtxt",$_POST['msg'],"Webconfig/");
		alogs("Msgonline",0,1,'成功执行了通知信息模板的编辑操作！');//管理员操作日志
		$this->success("操作成功",__URL__."/templet/");
    }
    
    public function traffic()
    {
        $traffic = FS("Webconfig/traffictxt");
        $this->assign("traffic",$traffic);
        $this->display();
    }
    
    public function trafficsave(){
        
        FS("traffictxt",$_POST['traffic'],"Webconfig/");
        
        alogs("Msgonline",0,1,'成功执行了流量包接口的编辑操作！');//管理员操作日志
        $this->success("操作成功",__URL__."/traffic/");
    }
}
?>
