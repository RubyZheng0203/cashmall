<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/android/AndroidBroadcast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/android/AndroidListcast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/android/AndroidFilecast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/android/AndroidGroupcast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/android/AndroidUnicast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/android/AndroidCustomizedcast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/ios/IOSBroadcast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/ios/IOSFilecast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/ios/IOSGroupcast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/ios/IOSUnicast.php');
require_once (THINK_PATH.'/Extend/Vendor/Umeng/ios/IOSCustomizedcast.php');

class Umeng {
	protected $appkey           = NULL; 
	protected $appMasterSecret  = NULL;
	protected $timestamp        = NULL;
	protected $validation_token = NULL;

	function __construct($param) {
	    $this->param           = $param;
		$this->appkey          = "595ede392ae85b5dab000073";
		$this->appMasterSecret = "uvidawwfpyreqy7x1dqckzefvjlyurrc";
		$this->timestamp       = strval(time());
	}

	/**
	 * 安卓广播消息
	 */
	function sendAndroidBroadcast() {
	    $param = $this->param;
		try {
			$brocast = new AndroidBroadcast();
			$brocast->setAppMasterSecret($this->appMasterSecret);
			$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
			$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			$brocast->setPredefinedKeyValue("ticker",           $param['ticker']);//通知栏提示文字
			$brocast->setPredefinedKeyValue("title",            $param['title']);//通知标题
			$brocast->setPredefinedKeyValue("text",             $param['text']);//通知文字描述
			$brocast->setPredefinedKeyValue("after_open",       $param['after_open']);//点击通知的后续行为
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$brocast->setPredefinedKeyValue("production_mode", "true");
			// [optional]Set extra fields
			$brocast->setExtraField("test", "helloworld");
			wqbLog("Sending broadcast notification, please wait...\r\n");
			$brocast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}
	
	/**
	 * 安卓列表广播消息
	 */
	function sendAndroidListcast() {
	    $param = $this->param;
		try {
			$listcast = new AndroidListcast();
			$listcast->setAppMasterSecret($this->appMasterSecret);
			$listcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$listcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set your device tokens here
			$listcast->setPredefinedKeyValue("device_tokens",    $param['tokens']); 
			$listcast->setPredefinedKeyValue("ticker",           $param['ticker']);//通知栏提示文字
			$listcast->setPredefinedKeyValue("title",            $param['title']);//通知标题
			$listcast->setPredefinedKeyValue("text",             $param['text']);//通知文字描述
			$listcast->setPredefinedKeyValue("after_open",       $param['after_open']);//点击通知的后续行为
			$listcast->setPredefinedKeyValue("url",              $param['url']);//after_open为go_url时跳转到URL
			$listcast->setPredefinedKeyValue("activity",         $param['activity']);//after_open为go_activity时打开特定的activity
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$listcast->setPredefinedKeyValue("production_mode", "true");
			// Set extra fields
			$listcast->setExtraField("test", "helloworld");
			wqbLog("Sending listcast notification, please wait...\r\n");
			$listcast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	/**
	 * 安卓单播消息
	 */
	function sendAndroidUnicast(){
	    $param = $this->param;
		try {
			$unicast = new AndroidUnicast();
			$unicast->setAppMasterSecret($this->appMasterSecret);
			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set your device tokens here
			$unicast->setPredefinedKeyValue("device_tokens",    $param['tokens']); 
			$unicast->setPredefinedKeyValue("ticker",           $param['ticker']);//通知栏提示文字
			$unicast->setPredefinedKeyValue("title",            $param['title']);//通知标题
			$unicast->setPredefinedKeyValue("text",             $param['text']);//通知文字描述
			$unicast->setPredefinedKeyValue("after_open",       $param['after_open']);//点击通知的后续行为
			$unicast->setPredefinedKeyValue("url",              $param['url']);//after_open为go_url时跳转到URL
			$unicast->setPredefinedKeyValue("activity",         $param['activity']);//after_open为go_activity时打开特定的activity
					
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$unicast->setPredefinedKeyValue("production_mode", "true");
			// Set extra fields
			$unicast->setExtraField("test", "helloworld");
			wqbLog("Sending unicast notification, please wait...\r\n");
			$unicast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	/**
	 * 安卓文件播，多个device_token可通过文件形式批量发送
	 */
	function sendAndroidFilecast() {
	    $param = $this->param;
		try {
			$filecast = new AndroidFilecast();
			$filecast->setAppMasterSecret($this->appMasterSecret);
			$filecast->setPredefinedKeyValue("appkey",          $this->appkey);
			$filecast->setPredefinedKeyValue("timestamp",       $this->timestamp);
			$filecast->setPredefinedKeyValue("ticker",           $param['ticker']);//通知栏提示文字
			$filecast->setPredefinedKeyValue("title",            $param['title']);//通知标题
			$filecast->setPredefinedKeyValue("text",             $param['text']);//通知文字描述
			$filecast->setPredefinedKeyValue("after_open",       $param['after_open']);//点击通知的后续行为
			$filecast->setPredefinedKeyValue("url",              $param['url']);//after_open为go_url时跳转到URL
			$filecast->setPredefinedKeyValue("activity",         $param['activity']);//after_open为go_activity时打开特定的activity
					
			wqbLog("Uploading file contents, please wait...\r\n");
			// Upload your device tokens, and use '\n' to split them if there are multiple tokens
			$filecast->uploadContents("aa"."\n"."bb");
			wqbLog("Sending filecast notification, please wait...\r\n");
			$filecast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	/**
	 * 安卓组播，按照filter筛选用户群
	 */
	function sendAndroidGroupcast() {
	    $param = $this->param;
		try {
			/* 
		 	 *  Construct the filter condition:
		 	 *  "where": 
		 	 *	{
    	 	 *		"and": 
    	 	 *		[
      	 	 *			{"tag":"test"},
      	 	 *			{"tag":"Test"}
    	 	 *		]
		 	 *	}
		 	 */
			$filter = 	array(
							"where" => 	array(
								    		"and" 	=>  array(
								    						array(
							     								"tag" => "test"
															),
								     						array(
							     								"tag" => "Test"
								     						)
								     		 			)
								   		)
					  	);
					  
			$groupcast = new AndroidGroupcast();
			$groupcast->setAppMasterSecret($this->appMasterSecret);
			$groupcast->setPredefinedKeyValue("appkey",             $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp",          $this->timestamp);
			// Set the filter condition
			$groupcast->setPredefinedKeyValue("filter",             $filter);
			$groupcast->setPredefinedKeyValue("ticker",             $param['ticker']);//通知栏提示文字
			$groupcast->setPredefinedKeyValue("title",              $param['title']);//通知标题
			$groupcast->setPredefinedKeyValue("text",               $param['text']);//通知文字描述
			$groupcast->setPredefinedKeyValue("after_open",         $param['after_open']);//点击通知的后续行为
			$groupcast->setPredefinedKeyValue("url",              $param['url']);//after_open为go_url时跳转到URL
			$groupcast->setPredefinedKeyValue("activity",         $param['activity']);//after_open为go_activity时打开特定的activity
					
			// Set 'production_mode' to 'false' if it's a test device. 
			// For how to register a test device, please see the developer doc.
			$groupcast->setPredefinedKeyValue("production_mode", "true");
			wqbLog("Sending groupcast notification, please wait...\r\n");
			$groupcast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	/**
	 * 通过alias进行推送，:alias: 对单个或者多个alias进行推送
	 */
	function sendAndroidCustomizedcast() {
	    $param = $this->param;
		try {
			$customizedcast = new AndroidCustomizedcast();
			$customizedcast->setAppMasterSecret($this->appMasterSecret);
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set your alias here, and use comma to split them if there are multiple alias.
			// And if you have many alias, you can also upload a file containing these alias, then 
			// use file_id to send customized notification.
			$customizedcast->setPredefinedKeyValue("alias",            "xx");
			// Set your alias_type here
			$customizedcast->setPredefinedKeyValue("alias_type",       "xx");
			$customizedcast->setPredefinedKeyValue("ticker",           $param['ticker']);//通知栏提示文字
			$customizedcast->setPredefinedKeyValue("title",            $param['title']);//通知标题
			$customizedcast->setPredefinedKeyValue("text",             $param['text']);//通知文字描述
			$customizedcast->setPredefinedKeyValue("after_open",       $param['after_open']);//点击通知的后续行为
			$customizedcast->setPredefinedKeyValue("url",              $param['url']);//after_open为go_url时跳转到URL
			$customizedcast->setPredefinedKeyValue("activity",         $param['activity']);//after_open为go_activity时打开特定的activity
					
			wqbLog("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	/**
	 * 通过alias进行推送，file_id: 将alias存放到文件后，根据file_id来推送
	 */
	function sendAndroidCustomizedcastFileId() {
	    $param = $this->param;
		try {
			$customizedcast = new AndroidCustomizedcast();
			$customizedcast->setAppMasterSecret($this->appMasterSecret);
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// if you have many alias, you can also upload a file containing these alias, then
			// use file_id to send customized notification.
			$customizedcast->uploadContents("aa"."\n"."bb");
			// Set your alias_type here
			$customizedcast->setPredefinedKeyValue("alias_type",       "xx");
			$customizedcast->setPredefinedKeyValue("ticker",           $param['ticker']);//通知栏提示文字
			$customizedcast->setPredefinedKeyValue("title",            $param['title']);//通知标题
			$customizedcast->setPredefinedKeyValue("text",             $param['text']);//通知文字描述
			$customizedcast->setPredefinedKeyValue("after_open",       $param['after_open']);//点击通知的后续行为
			$customizedcast->setPredefinedKeyValue("url",              $param['url']);//after_open为go_url时跳转到URL
			$customizedcast->setPredefinedKeyValue("activity",         $param['activity']);//after_open为go_activity时打开特定的activity
					
			wqbLog("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	
	function sendIOSBroadcast() {
		try {
			$brocast = new IOSBroadcast();
			$brocast->setAppMasterSecret($this->appMasterSecret);
			$brocast->setPredefinedKeyValue("appkey",           $this->appkey);
			$brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);

			$brocast->setPredefinedKeyValue("alert", "IOS 广播测试");
			$brocast->setPredefinedKeyValue("badge", 0);
			$brocast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$brocast->setPredefinedKeyValue("production_mode", "false");
			// Set customized fields
			$brocast->setCustomizedField("test", "helloworld");
			wqbLog("Sending broadcast notification, please wait...\r\n");
			$brocast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	function sendIOSUnicast() {
		try {
			$unicast = new IOSUnicast();
			$unicast->setAppMasterSecret($this->appMasterSecret);
			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set your device tokens here
			$unicast->setPredefinedKeyValue("device_tokens",    "xx"); 
			$unicast->setPredefinedKeyValue("alert", "IOS 单播测试");
			$unicast->setPredefinedKeyValue("badge", 0);
			$unicast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$unicast->setPredefinedKeyValue("production_mode", "false");
			// Set customized fields
			$unicast->setCustomizedField("test", "helloworld");
			wqbLog("Sending unicast notification, please wait...\r\n");
			$unicast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	function sendIOSFilecast() {
		try {
			$filecast = new IOSFilecast();
			$filecast->setAppMasterSecret($this->appMasterSecret);
			$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
			$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);

			$filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
			$filecast->setPredefinedKeyValue("badge", 0);
			$filecast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$filecast->setPredefinedKeyValue("production_mode", "false");
			wqbLog("Uploading file contents, please wait...\r\n");
			// Upload your device tokens, and use '\n' to split them if there are multiple tokens
			$filecast->uploadContents("aa"."\n"."bb");
			wqbLog("Sending filecast notification, please wait...\r\n");
			$filecast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	function sendIOSGroupcast() {
		try {
			/* 
		 	 *  Construct the filter condition:
		 	 *  "where": 
		 	 *	{
    	 	 *		"and": 
    	 	 *		[
      	 	 *			{"tag":"iostest"}
    	 	 *		]
		 	 *	}
		 	 */
			$filter = 	array(
							"where" => 	array(
								    		"and" 	=>  array(
								    						array(
							     								"tag" => "iostest"
															)
								     		 			)
								   		)
					  	);
					  
			$groupcast = new IOSGroupcast();
			$groupcast->setAppMasterSecret($this->appMasterSecret);
			$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
			// Set the filter condition
			$groupcast->setPredefinedKeyValue("filter",           $filter);
			$groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
			$groupcast->setPredefinedKeyValue("badge", 0);
			$groupcast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$groupcast->setPredefinedKeyValue("production_mode", "false");
			wqbLog("Sending groupcast notification, please wait...\r\n");
			$groupcast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}

	function sendIOSCustomizedcast() {
		try {
			$customizedcast = new IOSCustomizedcast();
			$customizedcast->setAppMasterSecret($this->appMasterSecret);
			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);

			// Set your alias here, and use comma to split them if there are multiple alias.
			// And if you have many alias, you can also upload a file containing these alias, then 
			// use file_id to send customized notification.
			$customizedcast->setPredefinedKeyValue("alias", "xx");
			// Set your alias_type here
			$customizedcast->setPredefinedKeyValue("alias_type", "xx");
			$customizedcast->setPredefinedKeyValue("alert", "IOS 个性化测试");
			$customizedcast->setPredefinedKeyValue("badge", 0);
			$customizedcast->setPredefinedKeyValue("sound", "chime");
			// Set 'production_mode' to 'true' if your app is under production mode
			$customizedcast->setPredefinedKeyValue("production_mode", "false");
			wqbLog("Sending customizedcast notification, please wait...\r\n");
			$customizedcast->send();
			wqbLog("Sent SUCCESS\r\n");
		} catch (Exception $e) {
			wqbLog("Caught exception: " . $e->getMessage());
		}
	}
}

// Set your appkey and master secret here
//$meng = new Umeng("your appkey", "your app master secret");
//$meng->sendAndroidUnicast();
/* these methods are all available, just fill in some fields and do the test
 * $meng->sendAndroidBroadcast();
 * $meng->sendAndroidFilecast();
 * $meng->sendAndroidGroupcast();
 * $meng->sendAndroidCustomizedcast();
 * $meng->sendAndroidCustomizedcastFileId();
 *
 * $meng->sendIOSBroadcast();
 * $meng->sendIOSUnicast();
 * $meng->sendIOSFilecast();
 * $meng->sendIOSGroupcast();
 * $meng->sendIOSCustomizedcast();
 */