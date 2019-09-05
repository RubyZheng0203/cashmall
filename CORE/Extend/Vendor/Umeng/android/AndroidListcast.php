<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/AndroidNotification.php');

class AndroidListcast extends AndroidNotification {
	function __construct() {
		parent::__construct();
		$this->data["type"] = "listcast";
		$this->data["device_tokens"] = NULL;
	}

}