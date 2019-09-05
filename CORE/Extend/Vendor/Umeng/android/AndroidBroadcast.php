<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/AndroidNotification.php');

class AndroidBroadcast extends AndroidNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}