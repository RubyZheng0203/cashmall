<?php

/**
 * 报表
 * @author Rubyzheng
 *
 */
class BorrowDaAction extends ACommonAction
{
    /**
     * 申请的借款金额
     */
    public function daApply()
    {
        $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	     
	    $map['m.add_time']       = array("gt",$time);
	    $map['m.status']         = array("in",'1,2,3,4,5');
	    $countm = M('borrow_apply m')->where($map)->sum('m.money');
	    $countc = M('borrow_apply m')->where($map)->count('m.id');
	    $avg = getFloatValue($countm/$countc,2);
	    
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avg", $avg);
	     
	    $map7['m.add_time']      = array("gt",$time7);
	    $map7['m.status']        = array("in",'1,2,3,4,5');
	    $countm7 = M('borrow_apply m')->where($map7)->sum('m.money');
	    $countc7 = M('borrow_apply m')->where($map7)->count('m.id');
	    $avgm7 = getFloatValue($countm7/7,2);
	    $avgc7 = getFloatValue($countc7/7,2);
	    $avga7 = getFloatValue($avgm7/$avgc7,2);
	    $avgt7 = getFloatValue($countm7/$countc7,2);
	    
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $map30['m.add_time']     = array("gt",$time30);
	    $map30['m.status']       = array("in",'1,2,3,4,5');
	    $countm30 = M('borrow_apply m')->where($map30)->sum('m.money');
	    $countc30 = M('borrow_apply m')->where($map30)->count('m.id');
	    $avgm30 = getFloatValue($countm30/30,2);
	    $avgc30 = getFloatValue($countc30/30,2);
	    $avga30 = getFloatValue($avgm30/$avgc30,2);
	    $avgt30 = getFloatValue($countm30/$countc30,2);
	    
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
         
        $this->display();
    }
    
    /**
     * 申请的借款金额按照地址
     */
    public function daApplyAd()
    {
        $now    = time();
        $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
        $time7  = $now - 24 * 3600 * 7;
        $time30 = $now - 24 * 3600 * 30;
    
        $countm   = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
        $countc   = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
        $avgt     = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
    
        $countm7  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
        $countc7  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
        $avgm7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
        $avgc7    = M()->query("SELECT bb.reg_address,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
        $avga7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
        $avgt7    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
        
        $countm30  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
        $countc30  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
        $avgm30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
        $avgc30    = M()->query("SELECT bb.reg_address,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
        $avga30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
        $avgt30    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
        
         
        $this->assign("countm", $countm);
        $this->assign("countc", $countc);
        $this->assign("avgt", $avgt);
        $this->assign("countm7", $countm7);
        $this->assign("countc7", $countc7);
        $this->assign("avgm7", $avgm7);
        $this->assign("avgc7", $avgc7);
        $this->assign("avga7", $avga7);
        $this->assign("avgt7", $avgt7);
         
        $this->assign("countm30", $countm30);
        $this->assign("countc30", $countc30);
        $this->assign("avgm30", $avgm30);
        $this->assign("avgc30", $avgc30);
        $this->assign("avga30", $avga30);
        $this->assign("avgt30", $avgt30);
         
        $this->display();
    }
    
    /**
     * 申请的借款金额按照性别
     */
    public function daApplySex()
    {
        $now    = time();
        $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
        $time7  = $now - 24 * 3600 * 7;
        $time30 = $now - 24 * 3600 * 30;
    
        $countm   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
        $countc   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
        $avgt     = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
        
        $countm7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
        $countc7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
        $avgm7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
        $avgc7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
        $avga7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
        $avgt7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
        
        $countm30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
        $countc30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
        $avgm30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
        $avgc30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
        $avga30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
        $avgt30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
    
         
        $this->assign("countm", $countm);
        $this->assign("countc", $countc);
        $this->assign("avgt", $avgt);
        $this->assign("countm7", $countm7);
        $this->assign("countc7", $countc7);
        $this->assign("avgm7", $avgm7);
        $this->assign("avgc7", $avgc7);
        $this->assign("avga7", $avga7);
        $this->assign("avgt7", $avgt7);
         
        $this->assign("countm30", $countm30);
        $this->assign("countc30", $countc30);
        $this->assign("avgm30", $avgm30);
        $this->assign("avgc30", $avgc30);
        $this->assign("avga30", $avga30);
        $this->assign("avgt30", $avgt30);
         
        $this->display();
    }
    
    /**
     * 申请的借款金额按照年龄
     */
    public function daApplyAge()
    {
        $now        = time();
        $time       = strtotime(date("Y-m-d",$now)." 00:00:00");
        $time7      = $now - 24 * 3600 * 7;
        $time30     = $now - 24 * 3600 * 30;
        $year       = date("Y",$now);
        $sql        = "SELECT CASE WHEN ".$year."-(substring(bb.id_card, 7,4)) < 18 THEN '18岁以下' ";
        $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>18 and 2017-(substring(id_card, 7,4)) <= 20 THEN '18-20岁（含）'  ";
        $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>20 and 2017-(substring(id_card, 7,4)) <= 22 THEN '20-22岁（含）'  ";
        $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>22 and 2017-(substring(id_card, 7,4)) <= 25 THEN '22-25岁（含）'  ";
        $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>25 and 2017-(substring(id_card, 7,4)) <= 30 THEN '25-30岁（含）' ";
        $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>30 and 2017-(substring(id_card, 7,4)) <= 40 THEN '30-40岁（含）' ";
        $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>40 and 2017-(substring(id_card, 7,4)) <= 45 THEN '40-45岁（含）' ";
        $sql        = $sql."ELSE '45岁以上' END  as age, ";
        
        $sqlm       = $sql."sum(aa.money) as money ";
        $sqlc       = $sql."count(aa.id) as count ";
        $sqlavg     = $sql."sum(aa.money)/count(aa.id) as money ";
        $sqlm7      = $sql."sum(aa.money)/7 as money ";
        $sqlc7      = $sql."count(aa.id)/7 as count ";
        $sqlavga7   = $sql."sum(aa.money)/7/(count(aa.id)/7) as money ";
        $sqlm30     = $sql."sum(aa.money)/30 as money ";
        $sqlc30     = $sql."count(aa.id)/30 as count ";
        $sqlavga30  = $sql."sum(aa.money)/30/(count(aa.id)/30) as money ";
      
        $countm     = M()->query($sqlm."from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY age ");
        $countc     = M()->query($sqlc."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY age ");
        $avgt       = M()->query($sqlavg."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time." GROUP BY age ");
    
        $countm7    = M()->query($sqlm."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ");
        $countc7    = M()->query($sqlc."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ");
        $avgm7      = M()->query($sqlm7."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ");
        $avgc7      = M()->query($sqlc7."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ");
        $avga7      = M()->query($sqlavga7."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ");
        $avgt7      = M()->query($sqlavg."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ");
        $countm30   = M()->query($sqlm."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY age ");
        $countc30   = M()->query($sqlc."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY age ");
        $avgm30     = M()->query($sqlm30."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY age ");
        $avgc30     = M()->query($sqlc30."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY age ");
        $avga30     = M()->query($sqlavga30."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) aa.and add_time >=".$time30." GROUP BY age ");
        $avgt30     = M()->query($sqlavg."from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (1,2,3,4,5) and aa.add_time >=".$time30." GROUP BY age ");
       
        $this->assign("countm", $countm);
        $this->assign("countc", $countc);
        $this->assign("avgt", $avgt);
        $this->assign("countm7", $countm7);
        $this->assign("countc7", $countc7);
        $this->assign("avgm7", $avgm7);
        $this->assign("avgc7", $avgc7);
        $this->assign("avga7", $avga7);
        $this->assign("avgt7", $avgt7);
         
        $this->assign("countm30", $countm30);
        $this->assign("countc30", $countc30);
        $this->assign("avgm30", $avgm30);
        $this->assign("avgc30", $avgc30);
        $this->assign("avga30", $avga30);
        $this->assign("avgt30", $avgt30);
         
        $this->display();
    }
    
    
	/**
	 * 批准的借款金额
	 */
	public function daApproval()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	     
	    $map['m.add_time']       = array("gt",$time);
	    $map['m.status']         = array("in",'2,3,4,5');
	    $countm = M('borrow_apply m')->where($map)->sum('m.money');
	    $countc = M('borrow_apply m')->where($map)->count('m.id');
	    $avg = getFloatValue($countm/$countc,2);
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avg", $avg);
	     
	    $map7['m.add_time']      = array("gt",$time7);
	    $map7['m.status']        = array("in",'2,3,4,5');
	    $countm7 = M('borrow_apply m')->where($map7)->sum('m.money');
	    $countc7 = M('borrow_apply m')->where($map7)->count('m.id');
	    $avgm7 = getFloatValue($countm7/7,2);
	    $avgc7 = getFloatValue($countc7/7,2);
	    $avga7 = getFloatValue($avgm7/$avgc7,2);
	    $avgt7 = getFloatValue($countm7/$countc7,2);
	    
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $map30['m.add_time']     = array("gt",$time30);
	    $map30['m.status']       = array("in",'2,3,4,5');
	    $countm30 = M('borrow_apply m')->where($map30)->sum('m.money');
	    $countc30 = M('borrow_apply m')->where($map30)->count('m.id');
	    $avgm30 = getFloatValue($countm30/30,2);
	    $avgc30 = getFloatValue($countc30/30,2);
	    $avga30 = getFloatValue($avgm30/$avgc30,2);
	    $avgt30 = getFloatValue($countm30/$countc30,2);
	    
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	     
	     
	    $this->display();
	}
	
	/**
	 * 批准的借款金额按照地址
	 */
	public function daApprovalAd()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	
	    $countm   = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
	    $countc   = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
	    $avgt     = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
	
	    $countm7  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $countc7  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avgm7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avgc7    = M()->query("SELECT bb.reg_address,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avga7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avgt7    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	
	    $countm30  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $countc30  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avgm30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avgc30    = M()->query("SELECT bb.reg_address,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avga30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avgt30    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	
	     
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	     
	    $this->display();
	}
	
	/**
	 * 批准的借款金额按照性别
	 */
	public function daApprovalSex()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	
	    $countm   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
	    $countc   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
	    $avgt     = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
	
	    $countm7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $countc7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avgm7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avgc7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avga7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avgt7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	
	    $countm30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $countc30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avgm30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avgc30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avga30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avgt30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	
	     
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	     
	    $this->display();
	}
	
	/**
	 * 批准的借款金额按照年龄
	 */
	public function daApprovalAge()
	{
	    $now        = time();
	    $time       = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7      = $now - 24 * 3600 * 7;
	    $time30     = $now - 24 * 3600 * 30;
	    $year       = date("Y",$now);
	    $sql        = "SELECT CASE WHEN ".$year."-(substring(bb.id_card, 7,4)) < 18 THEN '18岁以下' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>18 and 2017-(substring(id_card, 7,4)) <= 20 THEN '18-20岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>20 and 2017-(substring(id_card, 7,4)) <= 22 THEN '20-22岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>22 and 2017-(substring(id_card, 7,4)) <= 25 THEN '22-25岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>25 and 2017-(substring(id_card, 7,4)) <= 30 THEN '25-30岁（含）' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>30 and 2017-(substring(id_card, 7,4)) <= 40 THEN '30-40岁（含）' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>40 and 2017-(substring(id_card, 7,4)) <= 45 THEN '40-45岁（含）' ";
	    $sql        = $sql."ELSE '45岁以上' END  as age, ";
	    
	    $sqlCondition   = "from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY age ";
	    $sqlCondition7  = "from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (2,3,4,5) and aa.add_time >=".$time7." GROUP BY age ";
	    $sqlCondition30 = "from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (2,3,4,5) and aa.add_time >=".$time30." GROUP BY age ";
	     
	    
	    $sqlm       = $sql."sum(aa.money) as money ";
	    $sqlc       = $sql."count(aa.id) as count ";
	    $sqlavg     = $sql."sum(aa.money)/count(aa.id) as money ";
	    $sqlm7      = $sql."sum(aa.money)/7 as money ";
	    $sqlc7      = $sql."count(aa.id)/7 as count ";
	    $sqlavga7   = $sql."sum(aa.money)/7/(count(aa.id)/7) as money ";
	    $sqlm30     = $sql."sum(aa.money)/30 as money ";
	    $sqlc30     = $sql."count(aa.id)/30 as count ";
	    $sqlavga30  = $sql."sum(aa.money)/30/(count(aa.id)/30) as money ";
	    
	    $countm     = M()->query($sqlm." ".$sqlCondition);
	    $countc     = M()->query($sqlc." ".$sqlCondition);
	    $avgt       = M()->query($sqlavg." ".$sqlCondition);
	    
	    $countm7    = M()->query($sqlm." ".$sqlCondition7);
	    $countc7    = M()->query($sqlc." ".$sqlCondition7);
	    $avgm7      = M()->query($sqlm7." ".$sqlCondition7);
	    $avgc7      = M()->query($sqlc7." ".$sqlCondition7);
	    $avga7      = M()->query($sqlavga7." ".$sqlCondition7);
	    $avgt7      = M()->query($sqlavg." ".$sqlCondition7);
	    
	    $countm30   = M()->query($sqlm." ".$sqlCondition30);
	    $countc30   = M()->query($sqlc." ".$sqlCondition30);
	    $avgm30     = M()->query($sqlm30." ".$sqlCondition30);
	    $avgc30     = M()->query($sqlc30." ".$sqlCondition30);
	    $avga30     = M()->query($sqlavga30." ".$sqlCondition30);
	    $avgt30     = M()->query($sqlavg." ".$sqlCondition30);
	    
	     
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	     
	    $this->display();
	}
	
	
	/**
	 * 放款的借款金额
	 */
	public function daLoan()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	     
	    $map['m.add_time']       = array("gt",$time);
	    $map['m.status']         = array("in",'4,5');
	    $countm = M('borrow_apply m')->where($map)->sum('m.money');
	    $countc = M('borrow_apply m')->where($map)->count('m.id');
	    $avg = getFloatValue($countm/$countc,2);
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avg", $avg);
	     
	    $map7['m.add_time']      = array("gt",$time7);
	    $map7['m.status']        = array("in",'4,5');
	    $countm7 = M('borrow_apply m')->where($map7)->sum('m.money');
	    $countc7 = M('borrow_apply m')->where($map7)->count('m.id');
	    $avgm7 = getFloatValue($countm7/7,2);
	    $avgc7 = getFloatValue($countc7/7,2);
	    $avga7 = getFloatValue($avgm7/$avgc7,2);
	    $avgt7 = getFloatValue($countm7/$countc7,2);
	    
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	    
	    $map30['m.add_time']     = array("gt",$time30);
	    $map30['m.status']       = array("in",'4,5');
	    $countm30 = M('borrow_apply m')->where($map30)->sum('m.money');
	    $countc30 = M('borrow_apply m')->where($map30)->count('m.id');
	    $avgm30 = getFloatValue($countm30/30,2);
	    $avgc30 = getFloatValue($countc30/30,2);
	    $avga30 = getFloatValue($avgm30/$avgc30,2);
	    $avgt30 = getFloatValue($countm30/$countc30,2);
	    
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	     
	     
	    $this->display();
	}
	
	/**
	 * 放款的借款金额按照地址
	 */
	public function daLoanAd()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	
	    $countm   = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
	    $countc   = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
	    $avgt     = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time." GROUP BY bb.reg_address ");
	
	    $countm7  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $countc7  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avgm7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avgc7    = M()->query("SELECT bb.reg_address,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avga7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	    $avgt7    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY bb.reg_address ");
	
	    $countm30  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $countc30  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avgm30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avgc30    = M()->query("SELECT bb.reg_address,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avga30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	    $avgt30    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY bb.reg_address ");
	
	
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	
	    $this->display();
	}
	
	/**
	* 放款的借款金额按照性别
	*/
	public function daLoanSex()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	
	    $countm   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time." GROUP BY sex ");
	    $countc   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time." GROUP BY sex ");
	    $avgt     = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
	
	    $countm7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $countc7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avgm7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avgc7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avga7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	    $avgt7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY sex ");
	
	    $countm30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $countc30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avgm30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avgc30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avga30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	    $avgt30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY sex ");
	
	
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	
	    $this->display();
	}
	
	/**
	 * 放款的借款金额按照年龄
	 */
	public function daLoanAge()
	{
	    $now        = time();
	    $time       = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7      = $now - 24 * 3600 * 7;
	    $time30     = $now - 24 * 3600 * 30;
	    $year       = date("Y",$now);
	    $sql        = "SELECT CASE WHEN ".$year."-(substring(bb.id_card, 7,4)) < 18 THEN '18岁以下' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>18 and 2017-(substring(id_card, 7,4)) <= 20 THEN '18-20岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>20 and 2017-(substring(id_card, 7,4)) <= 22 THEN '20-22岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>22 and 2017-(substring(id_card, 7,4)) <= 25 THEN '22-25岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>25 and 2017-(substring(id_card, 7,4)) <= 30 THEN '25-30岁（含）' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>30 and 2017-(substring(id_card, 7,4)) <= 40 THEN '30-40岁（含）' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>40 and 2017-(substring(id_card, 7,4)) <= 45 THEN '40-45岁（含）' ";
	    $sql        = $sql."ELSE '45岁以上' END  as age, ";
	     
	    $sqlCondition   = "from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (4,5) and aa.add_time >=".$time." GROUP BY age ";
	    $sqlCondition7  = "from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (4,5) and aa.add_time >=".$time7." GROUP BY age ";
	    $sqlCondition30 = "from ml_borrow_apply   aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status` in (4,5) and aa.add_time >=".$time30." GROUP BY age ";
	    
	     
	    $sqlm       = $sql."sum(aa.money) as money ";
	    $sqlc       = $sql."count(aa.id) as count ";
	    $sqlavg     = $sql."sum(aa.money)/count(aa.id) as money ";
	    $sqlm7      = $sql."sum(aa.money)/7 as money ";
	    $sqlc7      = $sql."count(aa.id)/7 as count ";
	    $sqlavga7   = $sql."sum(aa.money)/7/(count(aa.id)/7) as money ";
	    $sqlm30     = $sql."sum(aa.money)/30 as money ";
	    $sqlc30     = $sql."count(aa.id)/30 as count ";
	    $sqlavga30  = $sql."sum(aa.money)/30/(count(aa.id)/30) as money ";
	     
	    $countm     = M()->query($sqlm." ".$sqlCondition);
	    $countc     = M()->query($sqlc." ".$sqlCondition);
	    $avgt       = M()->query($sqlavg." ".$sqlCondition);
	     
	    $countm7    = M()->query($sqlm." ".$sqlCondition7);
	    $countc7    = M()->query($sqlc." ".$sqlCondition7);
	    $avgm7      = M()->query($sqlm7." ".$sqlCondition7);
	    $avgc7      = M()->query($sqlc7." ".$sqlCondition7);
	    $avga7      = M()->query($sqlavga7." ".$sqlCondition7);
	    $avgt7      = M()->query($sqlavg." ".$sqlCondition7);
	     
	    $countm30   = M()->query($sqlm." ".$sqlCondition30);
	    $countc30   = M()->query($sqlc." ".$sqlCondition30);
	    $avgm30     = M()->query($sqlm30." ".$sqlCondition30);
	    $avgc30     = M()->query($sqlc30." ".$sqlCondition30);
	    $avga30     = M()->query($sqlavga30." ".$sqlCondition30);
	    $avgt30     = M()->query($sqlavg." ".$sqlCondition30);
	     
	    
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	    
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	    
	    $this->display();
	}
	
	/**
	 * 逾期的借款金额
	 */
	public function daDue()
	{
	    
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	    
	    $map['m.deadline']       = array("lt",$time);
	    $map['m.status']         = 4;
	    $map['m.repayment_time'] = 0;
	    $countm = M('borrow_apply m')->where($map)->sum('m.money');
	    $countc = M('borrow_apply m')->where($map)->count('m.id');
	    $avg = getFloatValue($countm/$countc,2);
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avg", $avg);
	     
	    
	    $map7['m.deadline']       = array("lt",$time7);
	    $map7['m.status']         = 4;
	    $map7['m.repayment_time'] = 0;
	    $avgm7 = getFloatValue($countm7/7,2);
	    $avgc7 = getFloatValue($countc7/7,2);
	    $avga7 = getFloatValue($avgm7/$avgc7,2);
	    $avgt7 = getFloatValue($countm7/$countc7,2);
	     
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	     
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $map30['m.deadline']       = array("lt",$time30);
	    $map30['m.status']         = 4;
	    $map30['m.repayment_time'] = 0;
	    $avgm30 = getFloatValue($countm30/30,2);
	    $avgc30 = getFloatValue($countc30/30,2);
	    $avga30 = getFloatValue($avgm30/$avgc30,2);
	    $avgt30 = getFloatValue($countm30/$countc30,2);
	     
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	     
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	    
	    $this->display();
	}
	
	/**
	 * 逾期的借款金额按照地址
	 */
	public function daDueAd()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	
	    $countm   = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY bb.reg_address ");
	    $countc   = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY bb.reg_address ");
	    $avgt     = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY bb.reg_address ");
	
	    $countm7  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY bb.reg_address ");
	    $countc7  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY bb.reg_address ");
	    $avgm7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY bb.reg_address ");
	    $avgc7    = M()->query("SELECT bb.reg_address,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY bb.reg_address ");
	    $avga7    = M()->query("SELECT bb.reg_address,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY bb.reg_address ");
	    $avgt7    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY bb.reg_address ");
	
	    $countm30  = M()->query("SELECT bb.reg_address,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY bb.reg_address ");
	    $countc30  = M()->query("SELECT bb.reg_address,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY bb.reg_address ");
	    $avgm30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY bb.reg_address ");
	    $avgc30    = M()->query("SELECT bb.reg_address,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY bb.reg_address ");
	    $avga30    = M()->query("SELECT bb.reg_address,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY bb.reg_address ");
	    $avgt30    = M()->query("SELECT bb.reg_address,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY bb.reg_address ");
	
	
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	
	    $this->display();
	}
	
	/**
	 * 逾期的借款金额按照性别
	 */
	public function daDueSex()
	{
	    $now    = time();
	    $time   = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7  = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	
	    $countm   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY sex ");
	    $countc   = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY sex ");
	    $avgt     = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status` in (2,3,4,5) and aa.add_time >=".$time." GROUP BY sex ");
	
	    $countm7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY sex ");
	    $countc7  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY sex ");
	    $avgm7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY sex ");
	    $avgc7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/7 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY sex ");
	    $avga7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/7/(count(aa.id)/7) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY sex ");
	    $avgt7    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time7." GROUP BY sex ");
	
	    $countm30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY sex ");
	    $countc30  = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id) as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY sex ");
	    $avgm30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30 as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY sex ");
	    $avgc30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,count(aa.id)/30 as count from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY sex ");
	    $avga30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/30/(count(aa.id)/30) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY sex ");
	    $avgt30    = M()->query("SELECT MOD(substring(bb.id_card, 17, 1),2) As sex,sum(aa.money)/count(aa.id) as money from ml_borrow_apply  aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time30." GROUP BY sex ");
	
	
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	
	    $this->display();
	}
	
	/**
	 * 逾期的借款金额按照年龄
	 */
	public function daDueAge()
	{
	    $now        = time();
	    $time       = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7      = $now - 24 * 3600 * 7;
	    $time30     = $now - 24 * 3600 * 30;
	    $year       = date("Y",$now);
	    $sql        = "SELECT CASE WHEN ".$year."-(substring(bb.id_card, 7,4)) < 18 THEN '18岁以下' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>18 and 2017-(substring(id_card, 7,4)) <= 20 THEN '18-20岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>20 and 2017-(substring(id_card, 7,4)) <= 22 THEN '20-22岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>22 and 2017-(substring(id_card, 7,4)) <= 25 THEN '22-25岁（含）'  ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>25 and 2017-(substring(id_card, 7,4)) <= 30 THEN '25-30岁（含）' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>30 and 2017-(substring(id_card, 7,4)) <= 40 THEN '30-40岁（含）' ";
	    $sql        = $sql."WHEN ".$year."-(substring(bb.id_card, 7,4))>40 and 2017-(substring(id_card, 7,4)) <= 45 THEN '40-45岁（含）' ";
	    $sql        = $sql."ELSE '45岁以上' END  as age, ";
	    
	    $sqlCondition   = "from ml_borrow_apply aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY age ";
	    $sqlCondition7  = "from ml_borrow_apply aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY age ";
	    $sqlCondition30 = "from ml_borrow_apply aa LEFT JOIN ml_member_info bb on aa.uid = bb.uid  where aa.`status`  = 4 and aa.repayment_time = 0 and aa.deadline >=".$time." GROUP BY age ";
	     
	    
	    $sqlm       = $sql."sum(aa.money) as money ";
	    $sqlc       = $sql."count(aa.id) as count ";
	    $sqlavg     = $sql."sum(aa.money)/count(aa.id) as money ";
	    $sqlm7      = $sql."sum(aa.money)/7 as money ";
	    $sqlc7      = $sql."count(aa.id)/7 as count ";
	    $sqlavga7   = $sql."sum(aa.money)/7/(count(aa.id)/7) as money ";
	    $sqlm30     = $sql."sum(aa.money)/30 as money ";
	    $sqlc30     = $sql."count(aa.id)/30 as count ";
	    $sqlavga30  = $sql."sum(aa.money)/30/(count(aa.id)/30) as money ";
	    
	    $countm     = M()->query($sqlm." ".$sqlCondition);
	    $countc     = M()->query($sqlc." ".$sqlCondition);
	    $avgt       = M()->query($sqlavg." ".$sqlCondition);
	    
	    $countm7    = M()->query($sqlm." ".$sqlCondition7);
	    $countc7    = M()->query($sqlc." ".$sqlCondition7);
	    $avgm7      = M()->query($sqlm7." ".$sqlCondition7);
	    $avgc7      = M()->query($sqlc7." ".$sqlCondition7);
	    $avga7      = M()->query($sqlavga7." ".$sqlCondition7);
	    $avgt7      = M()->query($sqlavg." ".$sqlCondition7);
	    
	    $countm30   = M()->query($sqlm." ".$sqlCondition30);
	    $countc30   = M()->query($sqlc." ".$sqlCondition30);
	    $avgm30     = M()->query($sqlm30." ".$sqlCondition30);
	    $avgc30     = M()->query($sqlc30." ".$sqlCondition30);
	    $avga30     = M()->query($sqlavga30." ".$sqlCondition30);
	    $avgt30     = M()->query($sqlavg." ".$sqlCondition30);
	    
	     
	    $this->assign("countm", $countm);
	    $this->assign("countc", $countc);
	    $this->assign("avgt", $avgt);
	    $this->assign("countm7", $countm7);
	    $this->assign("countc7", $countc7);
	    $this->assign("avgm7", $avgm7);
	    $this->assign("avgc7", $avgc7);
	    $this->assign("avga7", $avga7);
	    $this->assign("avgt7", $avgt7);
	     
	    $this->assign("countm30", $countm30);
	    $this->assign("countc30", $countc30);
	    $this->assign("avgm30", $avgm30);
	    $this->assign("avgc30", $avgc30);
	    $this->assign("avga30", $avga30);
	    $this->assign("avgt30", $avgt30);
	     
	    $this->display();
	}
}
?>