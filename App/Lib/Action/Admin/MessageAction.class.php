<?php

/**
 * SMS
 * @author Rubyzheng
 *
 */
class MessageAction extends ACommonAction
{
    
    public function index()
    {
        $msgconfig = FS("Webconfig/msgconfig");
        $onoff = $msgconfig['sms']['onoff']; //短信开关
        $type  = $msgconfig['sms']['type']; //1大汉三通接口 2漫道短信接口 
        $uid1  = $msgconfig['sms']['user1']; //大汉三通帐号
        $pwd1  = $msgconfig['sms']['pass1']; //大汉三密码
        $uid2  = $msgconfig['sms']['user2']; //绿麻雀漫道帐号
        $pwd2  = $msgconfig['sms']['pass2']; //绿麻雀漫道密码
        
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
        
        $this->assign('sms_config',$msgconfig['sms']);
        $this->assign('sms_config_type',$msgconfig['sms']['type']);
        $this->assign('baidu_config',$msgconfig['baidu']);
        $this->assign("onoff_list", array("0"=>'开通短信服务',"1"=>'关闭短信服务'));
        $this->assign("type_list", array("1"=>'大汉三通',"2"=>'绿麻雀'));
        $this->display();
    }
    

    /**
     * SMS模板一览页面
     */
    public function templet()
    {
		$smsTxt = FS("Webconfig/smstxt");

		$this->assign('smsTxt',de_xie($smsTxt));
        $this->display();

    }
    
    public function templetsave()
    {
        FS("smstxt",$_POST['sms'],"Webconfig/");
        alogs("Msgonline",0,1,'成功执行了通知信息模板的编辑操作！');//管理员操作日志
        $this->success("操作成功",__URL__."/templet/");
    }
    
	
	/**
	 * SMS发送页面
	 */
	public function send ()
	{
	    
	    $this->display();
	}
	
	/**
	 * 发送SMS
	 */
	public function doSend()
	{
	    $uids      = trim($_POST['phones']);
	    $content   = $_POST['content'];
        $uids      = explode(';', $uids);
        if ($_POST['sendall']) { //发送给所有人
            $uids = array();
            $res = M('members')->field("iphone")->where('is_black = 0 and is_logoff = 0')->select();
            foreach ($res as $row) {
                $uids[] = $row['iphone'];
            }
        }
        if (!$uids) {
            $this->error(L('发送用户格式错误，请用分号分割'));
        } else {
            foreach ($uids as $uid) {
                $data['phone']       = $uid;
                $data['content']     = $content;
                $data['status']      = 0;
                $data['add_time']    = time();
                if ($data) {
                    M('send_sms')->add($data);
                }
            }
        }
	    
	    $this->success(L('成功发送给'.count($uids).'个用户'));
	}

	/**
	 * 
	 */
	public function save(){
	    $pwd1 = $_POST['msg']['sms']['pwd1'];
	    $_POST['msg']['sms']['pass1'] = md5($pwd1);
	    $_POST['msg']['sms']['pwd1']  = $_POST['msg']['sms']['pwd1'];
	    
        $pwd2 = $_POST['msg']['sms']['user2'].$_POST['msg']['sms']['pwd2'];
        $_POST['msg']['sms']['pass2'] = strtoupper(md5($pwd2));
        $_POST['msg']['sms']['pwd2']  = $_POST['msg']['sms']['pwd2'];
       

        FS("msgconfig",$_POST['msg'],"Webconfig/");
        alogs("Msgonline",0,1,'成功执行了通知信息接口的编辑操作！');//管理员操作日志
        $this->success("操作成功",__URL__."/index/");
    }
	
	
	
}
?>