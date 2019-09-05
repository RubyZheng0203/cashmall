<?php 
class WeChatAction extends HCommonAction{
	
	/**
	 * 获取调用微信API的code值
	 */
    public function getApiCode($type = TRUE) {
		session('Promotion_temp', $_GET['txtPromotion']);
		$wx        = C("WEIXIN");
		$web_url   = $wx['return_url'];
		
		if (isset($_GET['url']) && !empty($_GET['url'])){
		  session('wechatstate',$_GET['url']);
		}
		$state = session('wechatstate');
		
		if (isset($state) && !empty($state)){
		    if ($state == 'repay'){
		        $url = 'Repayment/index';
		    }elseif ($state== 'member'){
		        $url = 'member/index';
		    }elseif ($state == 'invite'){
		        $url = 'member/invite';
		    }else {
		        $url = 'member/currborrow';
		    }
		}else {
		    $url = 'member/regist';
		    
		}
		if(session('uid')>0){
		       header("Location: {$web_url}{$url}");
	    }else{
				$wx           = C("WEIXIN");
				$appId        = $wx['app_id'];//微信APPID
				$wxBindUrl    = $wx['return_url'];//微信公众平台设置回调域名地址	
				$scope        = $type ? "snsapi_base" : "snsapi_userinfo";//静默授权 or 用户授权
				$returnUrl    = $type ? "{$wxBindUrl}WeChat/getOpenId" : "{$wxBindUrl}WeChat/getUserInfo";//静默授权回调 or 用户授权回调
				$requestUrl   = "https://open.weixin.qq.com/connect/oauth2/authorize?";
				$data         = "appid={$appId}&redirect_uri={$returnUrl}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
				$realUrl      = $requestUrl.$data;
				header("Location: {$realUrl}");
		}
	    
	}
    
	
	/**
	 * 获取用户OPENID
	 */
	public  function getOpenId(){
	    $wx        = C("WEIXIN");
	    $web_url   = $wx['return_url'];
		$code      = $_GET['code'];
		$state     = $_GET['state'];
		$appId     = $wx['app_id'];
		$appSecret = $wx['app_secret'];
		$getTokenUrl  = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appId}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
		$tokenRes     = $this->http_request($getTokenUrl);
		$tokenData    = json_decode($tokenRes,true);
		if (isset($tokenData['errcode'])){
			return FALSE;
		}else {
			$openid      = $tokenData['openid'];
			$bind_info   = M("member_wechat_bind")->field('uid')->where("openid = '{$openid}'")->find();
			if (empty($bind_info)){
			    $this->getApiCode(false);
			}else {			    	    
			    session('uid',$bind_info['uid']);
			    $data['last_time']    = time();
			    $data['last_ip']      = get_client_ip();
			    $data['last_address'] = get_ipAddress($data['last_ip']);
			    $savedata = M("members")->where("id = {$bind_info['uid']}")->save($data);
			    
			    if ($state == 'repay'){
			        $url = 'Repayment/index';
			    }elseif ($state == 'member'){
			        $url = 'Member/index';
		        }elseif ($state == 'invite'){
		            $url = 'Member/invite';
			    }else {
			        $url = 'Member/currborrow';
			    }
			    header("Location: {$web_url}{$url}");
			}
		}
	}
	
	/**
	 * 获取用户详情
	 */
	public function getUserInfo(){
	    $wx          = C("WEIXIN");
		$code      = $_GET['code'];
		$state     = $_GET['state'];
		$web_url   = $wx['return_url'];
		$appid     = $wx['app_id'];
		$appsecret = $wx['app_secret'];
		$token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$appsecret}&code={$code}&grant_type=authorization_code";
		$res       = $this->http_request($token_url);
		$token_data= json_decode($res,true);
		//使用access_token获取用户信息
		$user_info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$token_data['access_token']}&openid={$token_data['openid']}&lang=zh_CN";
		$user_info_res = $this->http_request($user_info_url);
		$user_info     = json_decode($user_info_res,true);
		session('openid',$token_data['openid']);
		session('access_token',$token_data['access_token']);
		session('refresh_token',$token_data['refresh_token']);
		session('wx_nickname',$user_info['nickname']);
		session('wx_image',$user_info['headimgurl']);
		
		if ($state == 'invite'){
		    $url = 'member/invite';
		}else {
		    $url = 'member/regist';
		}
		header("Location: {$web_url}{$url}");
	}
	
	/**
	 * curl操作
	 * @param string $url 要访问URL
	 * @param array $data 待发送数据体
	 */
	protected function http_request($url,$data=NULL){
		$curl=curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return $output;
	}
}
?>