<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/IOSNotification.php');

class IOSUnicast extends IOSNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "unicast";
		$this->data["device_tokens"] = NULL;
	}

}