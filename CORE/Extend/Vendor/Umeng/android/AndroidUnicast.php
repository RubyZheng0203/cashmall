<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/AndroidNotification.php');

class AndroidUnicast extends AndroidNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "unicast";
		$this->data["device_tokens"] = NULL;
	}

}