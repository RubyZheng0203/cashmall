<?php
// 本类由系统自动生成，仅供测试用途
class WelcomeAction extends ACommonAction {

	var $justlogin = true;
	
    public function index(){
		
        /*$now = time();
        $time = strtotime(date("Y-m-d",$now)." 00:00:00");
        $time7 = $now - 24 * 3600 * 7;
        $time30 = $now - 24 * 3600 * 30;*/
        
        //注册用户总数
        /*$count = M('members m')->count('m.id');
        $this->assign("count", $count);
        
        //申请借款用户总数
        $mapb['m.status'] = array("lt",96);
        $countBorrow = M('borrow_apply m')->field("DISTINCT uid ")->where($mapb)->select();
        $this->assign("countBorrow",count($countBorrow));
        
        //放款总金额
        $sumloan           = M()->query("SELECT sum(bb.loan_money) AS loanMoney  from  ml_borrow0_detail  aa LEFT JOIN ml_borrow_apply bb on aa.borrow_id = bb.id ");
        $sumloan_renewal   = M()->query("SELECT sum(bb.loan_money) AS loanMoney  from  ml_borrow_detail  aa LEFT JOIN ml_borrow_apply bb on aa.borrow_id = bb.id where aa.renewal_id > 0 ");
        
        $this->assign("loanMoney", $sumloan['loanMoney']);
        $this->assign("renewalMoney", $sumloan_renewal['loanMoney']);
        
        //回款到账总金额
        $mapr['m.status']       = 1;
        $repayment = M('borrow_detail m')->where($mapr)->sum('m.capital+m.interest');
        $this->assign("repayment", $repayment);*/
        
		$this->getServiceInfo();
        $this->getAdminInfo();
		$this->display();
    }
	
	private function getServiceInfo()
    {
        $service['service_name'] = php_uname('s');//服务器系统名称
        $service['service'] = $_SERVER['SERVER_SOFTWARE'];   //服务器版本
        $service['zend'] = 'Zend '.Zend_Version();    //zend版本号
        $service['ip'] = GetHostByName($_SERVER['SERVER_NAME']); //服务器ip
        $service['mysql'] = mysql_get_server_info();
        $service['filesize'] = ini_get("upload_max_filesize");
        
        $this->assign('service', $service);
    }
	
    private function getAdminInfo()
    {
        $id = $_SESSION['admin_id'];
        $userinfo = M('ausers a')
                    ->field('a.user_name, c.groupname')
                    ->join(C('DB_PREFIX').'acl as c on a.u_group_id = c.group_id')
                    ->where(" a.id={$id}")
                    ->find();                      
        $userinfo['last_log_time'] = $_SESSION['admin_last_log_time'];
        $userinfo['last_log_ip'] = $_SESSION['admin_last_log_ip'];
        $this->assign('user',$userinfo);
    }
	
}