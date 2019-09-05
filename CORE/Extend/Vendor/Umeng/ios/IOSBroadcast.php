<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/IOSNotification.php');

class IOSBroadcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}