<?php 
class ActivityAction extends HCommonAction{
	
	/**
	 * 获取调用微信API的code值
	 */
	public function appDown(){
		$this -> display();
	}
	public function index(){
		$url = M("app_version") -> field("url") ->find();
		$this -> assign($url,$url);
		$this -> display();
	}
	public function tuiguangwechat()
	{
		$promoteCode = $_GET["promoteCode"];
		$this->assign('promoteCode',$promoteCode);
		$this->display();
	}
	public function tuiguangApp()
	{	
		$promoteCode = $_GET["promoteCode"];
		$url = M("app_version")->field(true)->find();
		$this->assign('promoteCode',$promoteCode);
	 	$this->assign('url',$url);
		$this->display();
	}
	/**
	 * insert新用户数据
	 */
	public function addUser(){
		$verifyCode  = $_POST['verifyCode'];
		$phone       = $_POST['phone'];
		$members = M("members");
		$codeInfo = M("member_code")->where("phone = '{$phone}' and reg_code = '{$verifyCode}'")->count('phone');
		if ($codeInfo <= 0){
			ajaxmsg('手机验证码错误',0);
		}else{
			$memberInfo = M("members")->field('id')->where("iphone = '{$phone}'")->find();
			if (!empty($memberInfo)){
				addUserWxBindInfo($memberInfo['id'], $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
				$tick = "USERLOGIN".time().$memberInfo['id'];
				session('uid',$memberInfo['id']);
				session('user_phone',$phone);
				ajaxmsg($tick.",".$memberInfo['id'].",login,",1);
			}else {
				//接收活动码
				$data['promotion_code'] = $_POST['promoteCode'];
				$data['iphone']       = $phone;
				$data['reg_time']     = time();
				$data['reg_ip']       = get_client_ip();
				$data['reg_address']  = get_ipAddress($data['reg_ip']);
				$data['last_time']    = time();
				$data['last_ip']      = get_client_ip();
				$data['last_address'] = get_ipAddress($data['last_ip']);
				//$data['recommend_id'] = $mem['id'];
				$type = memberType($phone);
				if($type==1){
					$data['is_black'] = 1;
				}else if($type==2){
					$data['is_white'] = 1;
				}else if($type==3){
					$data['is_gray']  = 1;
				}
				$res = $members->add($data);
				if ($res){
					session('uid',$res);
					session('user_phone',$phone);
					$uid = checkSinaUid($phone);
					if (empty($uid)){
						if (sinaCreatMember($res)){
							if (!sinaBindingVerify($res, $phone)){
								ajaxmsg('注册失败',0);
							}
						}else {
							ajaxmsg('注册失败',0);
						}
					}else {
						$user['id']      = $res;
						$user['sina_id'] = "fumi".$uid;
						M("members")->save($user);
					}
					addUserWxBindInfo($res, $_SESSION['openid'], $_SESSION['access_token'], $_SESSION['refresh_token'], $_SESSION['wx_nickname'], $_SESSION['wx_image']);
					$tick = "USERREGIST".time().$res;
					ajaxmsg($tick.",".$res.",regist",1);
				}else {
					ajaxmsg('注册失败',0);
				}
			}
		}
	}
	
    function invite_j(){
        $url       = "http://www.cashmall.com.cn/Activity/invite_j";
	    $title     = '恭喜您获得好友邀请资格！';
	    $des       = "您的好友帮您申请了一笔1500元贷款额度，点击领取！";
	    $pic_url   = "http://www.cashmall.com.cn/style/cash/img/hidden.jpg";
		$post_url  = $_SERVER["REQUEST_URI"];
        $check     = strpos($post_url, "from=singlemessage&isappinstalled=0");
        if($check >0) {
			header("Location: ".$url);
        }
	    $signPackage = wxShare($url,$title,$des,$pic_url);
	    $this->assign('signPackage',$signPackage);
	    $this->display();
	}
}
?>