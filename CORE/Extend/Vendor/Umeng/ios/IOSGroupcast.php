<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/IOSNotification.php');

class IOSGroupcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "groupcast";
		$this->data["filter"]  = NULL;
	}
}