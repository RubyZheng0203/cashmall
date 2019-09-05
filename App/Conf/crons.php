<?php
// 系统默认的核心行为扩展列表文件
return array(
	'cron_1' => array('autorepayment', 1),   //这里的意思是每隔1秒，执行一次autorepayment.php文件
	'cron_2' => array('automemberslogin', 3600*24,strtotime(date("Y-m-d",time())+3600*24)), //这里的意思是每隔1天，执行一次automemberslogin.php文件
);
?>