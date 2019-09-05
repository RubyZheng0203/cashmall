<?php
use App\Library\Weiqianbao\Protocol\ShowMemberInfosSina\Request as ShowMemberInfosSinaRequest;
use App\Library\Weiqianbao\Protocol\ShowMemberInfosSina\Response as ShowMemberInfosSinaResponse;
use App\Library\Weiqianbao\Weiqianbao;

use App\Library\Weiqianbao\PayMethod\Balance;
use App\Library\Weiqianbao\PayMethod\Extend\BalanceExtend;
use App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade\Request as CreateHostingCollectTradeRequest;
use App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade\Response as CreateHostingCollectTradeResponse;
use App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade\Request as CreateSingleHostingPayTradeRequest;
use App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade\Response as CreateSingleHostingPayTradeResponse;

use App\Library\Http;

// 全局设置
class SinaAction extends ACommonAction
{
    var $justlogin = true;
    
	public function index()
	{

	}
	
	public function memberinfo()
	{
		$this->assign('_post',$_POST);
		$this->assign('_get',$_GET);
		$this->display();
	}

	public function memberphone()
	{
		$this->assign('_post',$_POST);
		$this->assign('_get',$_GET);
		$this->display();
	}

	public function transmoney()
	{
		$this->assign('_post',$_POST);
		$this->assign('_get',$_GET);
		$this->display();
	}
	
	public function fumimoney()
	{
		$this->assign('_post',$_POST);
		$this->assign('_get',$_GET);
		$this->display();
	}
	
	public function hostingtransfer()
	{
		$this->assign('_post',$_POST);
		$this->assign('_get',$_GET);
		$this->display();
	}
	
	public function callsina()
	{
		$act = intval($_GET['act']);
		switch ($act) {
			case 1: // 查询用户信息
				$uid = intval($_POST['uid']);
				if ($uid) {
					$wqbRequest = new ShowMemberInfosSinaRequest();
					$wqbResponse = new ShowMemberInfosSinaResponse();
					$wqbRequest->identity_id = "mall" . $uid;
					$wqbRequest->identity_type = "UID";
					$param = $wqbRequest->getRequestParam();
					
					$param["sign"] = Weiqianbao::sign($param, $param["sign_type"]);
					$weiqianbaoConfig = require(APP_PATH . "/Conf/weiqianbao.php");
					$serviceUrl = $weiqianbaoConfig["member_gateway"];
					$query = \http_build_query($param);
					$url = $serviceUrl . (strstr($serviceUrl, "?") ? "&" : "?").$query;
					$http = new Http();
					$res = $http->get($url);
					exit($res);
				} else {
					exit('请输入用户ID');
				}
				break;
			case 2: // 查询手机信息
				$uid = intval($_POST['uid']);
				if ($uid) {
			    	$wqbRequest = new App\Library\Weiqianbao\Protocol\QueryVerify\Request();
			    	$wqbResponse = new App\Library\Weiqianbao\Protocol\QueryVerify\Response();
			    	$wqbRequest->identity_id = "mall" . $uid;
			    	$wqbRequest->identity_type = "UID";
			    	$wqbRequest->verify_type = "MOBILE";
			    	$wqbRequest->is_mask = "N";
			    	$weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
			    	$weiqianbao->fire();
			    	if ( !$wqbResponse->success() ) {
			    		exit($wqbResponse->error());
			    	}
			    	echo "绑定手机号：" . $wqbResponse->verify_entity;
			    	exit;
				} else {
					exit('请输入用户ID');
				}
				break;
		}
	}
}
?>