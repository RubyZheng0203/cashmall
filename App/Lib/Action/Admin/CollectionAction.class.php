<?php

/**
 * 报表
 * @author Rubyzheng
 *
 */
class CollectionAction extends ACommonAction
{
    public function index(){
        
        $map = array();
        $map['dd.status'] = 0 ;
        $map['dd.deadline'] = array("lt", time());
        $field ="dd.borrow_id,dd.id,dd.uid,dd.capital,dd.interest,dd.deadline,dd.charge_times,dd.hope_charge_time";
        $list  = M('borrow_detail dd')->field($field)->where($map)->order('dd.deadline')->select();
        
        $this->assign("search", $search);
        $this->assign("query", http_build_query($search));
        $this->display();
    }
    
    /**
     * 逾期借款导出
     */
    public function dueAllExport(){
        import("ORG.Io.Excel");
        $now =  time();
        $map = array();
        $map['dd.status']   = 0 ;
        $map['dd.deadline'] = array("lt",$now);
    
        $pre   = $this->pre;
        $field ="dd.borrow_id,dd.id,dd.uid";
        $list  = M('borrow_detail dd')->field($field)->where($map)->order('dd.deadline')->select();

    
        $row    = array();
        $row[0] = array('借款申请ID','账单ID','借款人编号');
        $i = 1;
        foreach($list as $v){
            $row[$i]['borrow_id']        = $v['borrow_id'];
            $row[$i]['id']               = $v['id'];
            $row[$i]['uid']              = $v['uid'];
            $i++;
        }
         
        $xls = new Excel_XML('UTF-8', false, 'datalist');
        $xls->addArray($row);
        $xls->generateXML("export");
         
        $this->display();
    }
    
    /**
     * 逾期借款导出
     */
    public function dueExport(){
        import("ORG.Io.Excel");
        $now =  time();
		$time = strtotime(date("Y-m-d",$now)." 00:00:00");
        $time = $time-7*3600*24;
        $timespan = $time.",".$now;
        $map = array();
        $map['dd.status']   = 0 ;
	    $map['dd.deadline'] = array("between",$timespan);	
        
        $pre   = $this->pre;
        $field ="dd.borrow_id,dd.id,dd.uid,dd.capital,dd.interest,dd.deadline,dd.add_time,dd.charge_times,dd.hope_charge_time,dd.renewal_id";
        $list  = M('borrow_detail dd')->field($field)->where($map)->order('dd.deadline')->select();
        
        foreach($list as $k=>$v){
            $apply = M('borrow_apply')->field('id,item_id,money,duration,repayment_type,len_time,audit_fee,created_fee,enabled_fee,pay_fee,is_new')->where(" uid = {$v['uid']} and id = {$v['borrow_id']} ")->find();
            if($apply['repayment_type'] == 1){
                $list[$k]['type']   = $apply['duration']."天";
            }else if($apply['repayment_type'] == 2){
                $list[$k]['type']   = $apply['duration']."周";
            }else if($apply['repayment_type'] == 3){
                $list[$k]['type']   = $apply['duration']."个月";
            }else if($apply['repayment_type'] == 4){
                $list[$k]['type']   = $apply['duration']."个季度";
            }else{
                $list[$k]['type']   = $apply['duration']."年";
            }
            $list[$k]['money']        = $apply['money'];
            $list[$k]['is_new']       = $apply['is_new'];
            $list[$k]['audit_fee']    = $apply['audit_fee'];
            $list[$k]['created_fee']  = $apply['created_fee'];
            $list[$k]['enabled_fee']  = $apply['enabled_fee'];
            $list[$k]['pay_fee']      = $apply['pay_fee'];
            $list[$k]['item_id']      = $apply['item_id'];
            $list[$k]['len_time']     = $apply['len_time'];
    
            $info = M('member_info')->field('iphone,real_name')->where(" uid = {$v['uid']}")->find();
            $list[$k]['iphone']     = $info['iphone'];
            $list[$k]['real_name']  = $info['real_name'];
    
        }
    
        $row    = array();
        $row[0] = array('借款申请ID','账单ID','借款人手机号码','真实姓名','还款本金（元）','借款期限','还款利息（元）','还款合计（元）','放款日期','应还日期',
            '逾期费','催收费','逾期天数','逾期总额','已扣款次数','下次扣款时间','是否续期','手机验证时间');
        $i = 1;
        foreach($list as $v){
            $due_day  = 0;
            $due_day  = get_due_day($v['deadline']);
            $due_fee  = 0;
            $due_fee  = get_due_fee($v['money'],$v['item_id'],$due_day);
            $late_fee = 0;
            $late_fee = get_late_fee($v['money'],$v['item_id'],$due_day);
    
            $row[$i]['borrow_id']        = $v['borrow_id'];
            $row[$i]['id']               = $v['id'];
            $row[$i]['iphone']           = $v['iphone'];
            $row[$i]['real_name']        = $v['real_name'];
            $row[$i]['capital']          = $v['capital'];
            $row[$i]['type']             = $v['type'];
            $row[$i]['interest']         = $v['interest'];
            if($v['is_new']==1){
                $row[$i]['total']        = getFloatValue($v['capital']+$v['interest']+$v['audit_fee']+$v['enabled_fee']+$v['created_fee']+$v['pay_fee'],2);
            }else{
                $row[$i]['total']        = getFloatValue($v['capital']+$v['interest'],2);
            }
            $row[$i]['add_time']         = date("Y-m-d H:i:s", $v['add_time']);
            $row[$i]['deadline']         = date("Y-m-d H:i:s", $v['deadline']);
            $row[$i]['due_fee']          = $due_fee;
            $row[$i]['late_fee']         = $late_fee;
            $row[$i]['due_day']          = $due_day;
            $row[$i]['fee']              = $due_fee+$late_fee;
            $row[$i]['charge_times']     = $v['charge_times'];
            $row[$i]['hope_charge_time'] = date("Y-m-d H:i:s", $v['hope_charge_time']);
            $row[$i]['xueqi']            = $v['renewal_id'] ? '是' : '否';
             
            $dataname = C('DB_NAME_RISK');
            $db_host  = C('DB_HOST_RISK');
            $db_user  = C('DB_USER_RISK');
            $db_pwd   = C('DB_PWD_RISK');
            $uid = '';
            $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
            $bdb->beginTransaction();
            $sql = "SELECT add_time FROM rs_api_carrier_log WHERE mobile_no = '{$v['iphone']}' and state = '107'  order by add_time desc limit 1";
            foreach ($bdb->query($sql) as $r) {
                $row[$i]['verify_phone'] = date("Y-m-d", $r['add_time']);
            }

            $i++;
        }
         
        $xls = new Excel_XML('UTF-8', false, 'datalist');
        $xls->addArray($row);
        $xls->generateXML("export");
         
        $this->display();
    }
    
    /**
     * 逾期借款导出(朋友联络方式)
     */
    public function dueDetailExport(){
        import("ORG.Io.Excel");
        $now =  time();
		$time = strtotime(date("Y-m-d",$now)." 00:00:00");
        $time = $time-7*3600*24;
        $timespan = $time.",".$now;
        $map = array();
        $map['dd.status']   = 0 ;
	    $map['dd.deadline'] = array("between",$timespan);	
    
        $pre   = $this->pre;
        $field ="dd.borrow_id,dd.id,dd.uid,dd.capital,dd.interest,dd.deadline,dd.add_time,dd.charge_times,dd.hope_charge_time,dd.renewal_id";
        $list  = M('borrow_detail dd')->field($field)->where($map)->order('dd.deadline')->select();
             
        foreach($list as $k=>$v){
            $apply = M('borrow_apply')->field('id,item_id,money,duration,repayment_type,len_time,audit_fee,created_fee,enabled_fee,pay_fee,is_new')->where(" uid = {$v['uid']} and id = {$v['borrow_id']} ")->find();
            if($apply['repayment_type'] == 1){
                $list[$k]['type']   = $apply['duration']."天";
            }else if($apply['repayment_type'] == 2){
                $list[$k]['type']   = $apply['duration']."周";
            }else if($apply['repayment_type'] == 3){
                $list[$k]['type']   = $apply['duration']."个月";
            }else if($apply['repayment_type'] == 4){
                $list[$k]['type']   = $apply['duration']."个季度";
            }else{
                $list[$k]['type']   = $apply['duration']."年";
            }
            $list[$k]['money']        = $apply['money'];
            $list[$k]['is_new']       = $apply['is_new'];
            $list[$k]['audit_fee']    = $apply['audit_fee'];
            $list[$k]['created_fee']  = $apply['created_fee'];
            $list[$k]['enabled_fee']  = $apply['enabled_fee'];
            $list[$k]['pay_fee']      = $apply['pay_fee'];
            $list[$k]['item_id']      = $apply['item_id'];
            $list[$k]['len_time']     = $apply['len_time'];
            
            $info = M('member_info')->field('iphone,real_name')->where(" uid = {$v['uid']}")->find();
            $list[$k]['iphone']     = $info['iphone'];
            $list[$k]['real_name']  = $info['real_name'];
            
            $relation = M('member_relation')->field('iphone1,relation1,name2,iphone2,name3,iphone3')->where(" uid = {$v['uid']}")->find();
            $list[$k]['iphone1']    = $relation['iphone1'];
            $list[$k]['relation1']  = $relation['relation1'];
            $list[$k]['iphone2']    = $relation['iphone2'];
            $list[$k]['name2']      = $relation['name2'];
            $list[$k]['iphone3']    = $relation['iphone3'];
            $list[$k]['name3']      = $relation['name3'];
            
        }
        
                        
       $row    = array();
       $row[0] = array('借款申请ID','账单ID','借款人手机号码','真实姓名','还款本金（元）','借款期限','还款利息（元）','还款合计（元）','放款日期','应还日期',
           '逾期费','催收费','逾期天数','逾期总额','已扣款次数','下次扣款时间','是否续期','亲属姓名','手机号码','关系','朋友姓名','朋友手机号码','同事姓名','同事手机号码');
       $i = 1;
       foreach($list as $v){
            $due_day  = 0;
            $due_day  = get_due_day($v['deadline']);
            $due_fee  = 0;
            $due_fee  = get_due_fee($v['money'],$v['item_id'],$due_day);
            $late_fee = 0;
            $late_fee = get_late_fee($v['money'],$v['item_id'],$due_day);
            
            $row[$i]['borrow_id']        = $v['borrow_id'];
            $row[$i]['id']               = $v['id'];
            $row[$i]['iphone']           = $v['iphone'];
            $row[$i]['real_name']        = $v['real_name'];
            $row[$i]['capital']          = $v['capital'];
            $row[$i]['type']             = $v['type'];
            $row[$i]['interest']         = $v['interest'];
            if($v['is_new']==1){
                $row[$i]['total']        = getFloatValue($v['capital']+$v['interest']+$v['audit_fee']+$v['enabled_fee']+$v['created_fee']+$v['pay_fee'],2);
            }else{
                $row[$i]['total']        = getFloatValue($v['capital']+$v['interest'],2);
            }
            $row[$i]['add_time']         = date("Y-m-d H:i:s", $v['add_time']);
            $row[$i]['deadline']         = date("Y-m-d H:i:s", $v['deadline']);
            $row[$i]['due_fee']          = $due_fee;
            $row[$i]['late_fee']         = $late_fee;
            $row[$i]['due_day']          = $due_day;
            $row[$i]['fee']              = $due_fee+$late_fee;
            $row[$i]['charge_times']     = $v['charge_times'];
            $row[$i]['hope_charge_time'] = date("Y-m-d H:i:s", $v['hope_charge_time']);
            $row[$i]['xueqi']            = $v['renewal_id'] ? '是' : '否';
            $row[$i]['name1']            = $v['name1'];
            $row[$i]['iphone1']          = $v['iphone1'];
            $row[$i]['relation1']        = $v['relation1'];
            $row[$i]['name3']            = $v['name3'];
            $row[$i]['iphone3']          = $v['iphone3'];
            $row[$i]['name2']            = $v['name2'];
            $row[$i]['iphone2']          = $v['iphone2'];
    			
    	    $i++;
        }
                   
        $xls = new Excel_XML('UTF-8', false, 'datalist');
        $xls->addArray($row);
        $xls->generateXML("export");
         
        $this->display();
    }
    
    public function dopic(){
        $uid = "";
        if($_POST['mobile']||$_POST['uid']){
            if($_POST['uid']){
                $uid = $_POST['uid'];
            }else{
                if($_POST['mobile']){
                    $info = M('member_info')->field('uid')->where("iphone = '{$_POST['mobile']}' ")->find();
                    $uid  = $info['uid'];
                }
           }
            //图片信息
            $path = C("MEM_PATH");
            $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            $filename1 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-1.jpg';
            $filename2 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-2.jpg';
            $filename3 = $http_type.$_SERVER['HTTP_HOST'].$path.$uid.'-3.jpg';
            
            //身份验证最佳图片获取
            $path_face = C("Face_PIC_PATH");
            $filename4 = $http_type.$_SERVER['HTTP_HOST'].$path_face.$uid.'.jpeg';
            $filename5 = $http_type.$_SERVER['HTTP_HOST'].$path_face.$uid.'-1.jpg';
            
            $html = "<div id='tab_1'><dl class=''><dt><dd><ul id='jq22'>";
            $html = $html."<li>身份证正面照：<img data-original='{$filename1}' src='{$filename1}' alt=''></li>";
            $html = $html."<li>身份证反面照：<img data-original='{$filename2}' src='{$filename2}' alt=''></li>";
            $html = $html."<li>自拍照：<img data-original='{$filename3}' src='{$filename3}' alt=''></li>";
            $html = $html."<li>身份验证照（微信）：<img data-original='{$filename4}' src='{$filename4}' alt=''></li>";
            $html = $html."<li>身份验证照（APP）：<img data-original='{$filename5}' src='{$filename5}' alt=''></li>";
            $html = $html."</ul></dd></dt></dl></div>";
            ajaxmsg($html, 0);
        }else{
            ajaxmsg("", 0);
        }
    }
    
}
?>