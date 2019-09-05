<?php
require_once (THINK_PATH.'/Extend/Vendor/Umeng/AndroidNotification.php');

class AndroidGroupcast extends AndroidNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "groupcast";
		$this->data["filter"]  = NULL;
	}
}