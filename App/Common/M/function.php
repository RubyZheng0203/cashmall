<?php
  //获取借款列表
function getMemberDetail($uid){
	$pre = C('DB_PREFIX');
	$map['m.id'] = $uid;
	//$field = "*";
	$list = M('members m')->field(true)->join("{$pre}member_banks mbank ON m.id=mbank.uid")->join("{$pre}member_contact_info mci ON m.id=mci.uid")->join("{$pre}member_house_info mhi ON m.id=mhi.uid")->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")->join("{$pre}member_ensure_info mei ON m.id=mei.uid")->join("{$pre}member_info mi ON m.id=mi.uid")->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")->where($map)->limit($Lsql)->find();
	return $list;
}

//获取借款列表
function getBorrowList($parm=array()){
	if(empty($parm['map'])) return;
	$map= $parm['map'];
	$orderby= $parm['orderby'];
	//$orderby = " b.borrow_status asc, b.is_tuijian desc,b.borrow_interest_rate desc ";
	if($parm['pagesize']){
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_info b')->where($map)->count('b.id');
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}
	$pre = C('DB_PREFIX');
	$suffix=C("URL_HTML_SUFFIX");
	$field = "b.id,b.borrow_name,b.borrow_type,b.reward_type,b.full_time,b.borrow_times,b.borrow_status,b.borrow_money,b.borrow_use,b.repayment_type,b.borrow_interest_rate,b.borrow_duration,b.collect_time,b.add_time,b.province,b.has_borrow,b.has_vouch,b.city,b.area,b.reward_type,b.reward_num,b.password,m.user_name,m.id as uid,m.credits,m.customer_name,b.is_tuijian,b.deadline,b.danbao,b.borrow_info,b.risk_control";
	$list = M('borrow_info b')->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
	$areaList = getArea();
	foreach($list as $key=>$v){
		$list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
		$list[$key]['biao'] = $v['borrow_times'];
		$list[$key]['need'] = $v['borrow_money'] - $v['has_borrow'];
		$list[$key]['leftdays'] = getLeftTime($v['collect_time']);
		$list[$key]['progress'] = getFloatValue($v['has_borrow']/$v['borrow_money']*100,2);
		$list[$key]['vouch_progress'] = getFloatValue($v['has_vouch']/$v['borrow_money']*100,2);
		$list[$key]['burl'] = MU("Home/invest","invest",array("id"=>$v['id'],"suffix"=>$suffix));
		
		//新加
		$list[$key]['lefttime']=$v['collect_time']-time();
				
		if($v['deadline']==0){
			$endTime = strtotime(date("Y-m-d",time()));
			if($v['repayment_type']==1) {
				$list[$key]['repaytime'] = strtotime("+{$v['borrow_duration']} day",$endTime);
			}else {
				$list[$key]['repaytime'] = strtotime("+{$v['borrow_duration']} month",$endTime);
			}
		}else{
			$list[$key]['repaytime']=$v['deadline'];//还款时间
		}

		$list[$key]['publishtime']=$v['add_time']+60*60*24*3;//预计发标时间=添加时间+1天
		
		if($v['danbao']!=0 ){
			$danbao = M('article')->field("id,title")->where("type_id =7 and id ={$v['danbao']}")->find();
			$list[$key]['danbao']=$danbao['title'];//担保机构
		}else{
			$list[$key]['danbao']='暂无担保机构';//担保机构
		}
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	return $row;
}

//获取时尚理财list
function getFashionList($parm =  array())
{
    if(empty($parm['map'])) return;
    $map        = $parm['map'];
    $orderby    = $parm['orderby'];
    $Lsql       = "{$parm['limit']}";

    $field      = "i.borrow_duration,i.borrow_interest_rate,i.per_transfer,i.id,i.fund_type";
    $list       = M("transfer_borrow_info i")->field($field)->where($map)->limit($Lsql)->order($orderby)->select();
    return $list;
}

 /**
* 格式化资金数据保持两位小数
* @desc intval $num  // 接受资金数据
*/
   function MFormt($num)
    {
    return number_format($num,2);
    } 
  
  
  
  function getArticlelist($map,$pagesize=10){ //获取微信端文章列表
      $model=M("article");
	  import("ORG.Weixin.Page");
	  $count=$model->where($map)->count("id"); 
	  $p=new Page($count,$pagesize);    
	  $Lsql ="{$p->firstRow},{$p->listRows}";
	  $list=$model->where($map)->order("id desc")->limit($Lsql)->select();
	  $data=array();
	  $data['list']=$list;
	  $data['pagebar']=$p->show();
      return $data;   
    }
 
 function getSeearticle($id=0) //获取微信端端文章详情
   {  
      $model=M("article");
      $id=intval($id);
	  $vo=$model->where("id={$id}")->find();
	  return $vo;
  }	
   
 //定投宝和企业直投
 //获取企业直投借款列表
function getTBorrowList($parm =array())
{
	if(empty($parm['map'])) return;
	$map = $parm['map'];
	//$orderby = "b.borrow_status asc ,b.borrow_interest_rate desc";//$parm['orderby'];
	//$orderby = "progress , b.borrow_status asc, b.is_tuijian desc , b.borrow_interest_rate desc,b.fund_type asc ";
	$orderby = $parm['orderby'];

	if($parm['pagesize'])
	{
		import( "ORG.Util.Page" );
		$count = M("transfer_borrow_info b")->where($map)->count("b.id");
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
	}else{
		$page = "";
		$Lsql = "{$parm['limit']}";
	}
	$pre = C("DB_PREFIX");
	$suffix =C("URL_HTML_SUFFIX");
	$field = "b.id,b.borrow_name,b.fund_type,b.borrow_status,b.borrow_money,b.repayment_type,b.min_month,b.transfer_out,b.transfer_back,b.transfer_total,b.per_transfer,b.borrow_interest_rate,b.borrow_duration,b.increase_rate,b.interest_rate_expect,b.interest_rate_float,b.reward_rate,b.deadline,b.is_show,m.province,m.city,m.area,m.user_name,m.id as uid,m.credits,m.customer_name,b.borrow_type,b.b_img,b.add_time,b.collect_day,b.danbao,b.online_time,b.sort_order,b.activity_name";
    $list = M("transfer_borrow_info b")->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
	//echo M()->getlastsql();
	$areaList = getarea();
	foreach($list as $key => $v)
	{
		$list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
		$list[$key]['progress'] = getfloatvalue( $v['transfer_out'] / $v['transfer_total'] * 100, 2);
		$list[$key]['need'] = getfloatvalue(($v['transfer_total'] - $v['transfer_out'])*$v['per_transfer'], 2 );
		$list[$key]['burl'] = MU("Home/invest_transfer", "invest_transfer",array("id" => $v['id'],"suffix" => $suffix));	
		
		$temp=floor(("{$v['collect_day']}"*3600*24-time()+"{$v['add_time']}")/3600/24);
		$list[$key]['leftdays'] = "{$temp}".'天以上';
		$list[$key]['now'] = time();
		$list[$key]['borrow_times'] = count(M('transfer_borrow_investor') -> where("borrow_id = {$list[$key]['id']}") ->select());
		$list[$key]['investornum'] = M('transfer_borrow_investor')->where("borrow_id={$v['id']}")->count("id");
		if($v['danbao']!=0 ){
			$list[$key]['danbaoid'] = intval($v['danbao']);
			$danbao = M('article')->field('id,title')->where("type_id=7 and id={$v['danbao']}")->find();
			$list[$key]['danbao']=$danbao['title'];//担保机构
		}else{
			$list[$key]['danbao']='暂无担保机构';//担保机构
		}
		//收益率
		$monthData['month_times'] = 12;
		$monthData['account'] = $v['borrow_money'];
		$monthData['year_apr'] = $v['borrow_interest_rate'];
		$monthData['type'] = "all";
		$repay_detail = CompoundMonth($monthData);	
		//if($v['borrow_duration']==1){
		   // $list[$key]['shouyi'] = $v['borrow_interest_rate'];
		//}else{
		    $list[$key]['shouyi'] = $repay_detail['shouyi'];
		//}
		//收益率结束	
	}
	$row = array();
	$row['list'] = $list;
	$row['page'] = $page;
	return $row;
}

//获取企业直投借款列表
function getTBorrowListh($parm =array())
{
	if(empty($parm['map'])) return;
	$map = $parm['map'];
	//$orderby = "b.borrow_interest_rate desc";//$parm['orderby'];
	$orderby ="b.borrow_status asc,b.id Desc";
	if($parm['pagesize'])
	{
		import( "ORG.Util.Page" );
		$count = M("transfer_borrow_info b")->where($map)->count("b.id");
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
	}else{
		$page = "";
		$Lsql = "{$parm['limit']}";
	}
	$pre = C("DB_PREFIX");
	$suffix =C("URL_HTML_SUFFIX");
	$field = "b.id,b.borrow_name,b.borrow_status,b.borrow_money,b.repayment_type,b.min_month,b.transfer_out,b.transfer_back,b.transfer_total,b.per_transfer,b.borrow_interest_rate,b.interest_rate_expect,b.interest_rate_float,b.borrow_duration,b.increase_rate,b.reward_rate,b.deadline,b.is_show,m.province,m.city,m.area,m.user_name,m.id as uid,m.credits,m.customer_name,b.borrow_type,b.b_img,b.add_time,b.collect_day,b.danbao,b.online_time";
$list = M("transfer_borrow_info b")->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->limit($Lsql)->select();
	//echo M()->getlastsql();
	$areaList = getarea();
	foreach($list as $key => $v)
	{
		$list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
		$list[$key]['progress'] = getfloatvalue( $v['transfer_out'] / $v['transfer_total'] * 100, 2);
		$list[$key]['need'] = getfloatvalue(($v['transfer_total'] - $v['transfer_out'])*$v['per_transfer'], 2 );
		$list[$key]['burl'] = MU("Home/invest_transfer", "invest_transfer",array("id" => $v['id'],"suffix" => $suffix));	
		
		$temp=floor(("{$v['collect_day']}"*3600*24-time()+"{$v['add_time']}")/3600/24);
		$list[$key]['leftdays'] = "{$temp}".'天以上';
		$list[$key]['now'] = time();
		$list[$key]['borrow_times'] = count(M('transfer_borrow_investor') -> where("borrow_id = {$list[$key]['id']}") ->select());
		$list[$key]['investornum'] = M('transfer_borrow_investor')->where("borrow_id={$v['id']}")->count("id");
		if($v['danbao']!=0 ){
			$list[$key]['danbaoid'] = intval($v['danbao']);
			$danbao = M('article')->field('id,title')->where("type_id=7 and id={$v['danbao']}")->find();
			$list[$key]['danbao']=$danbao['title'];//担保机构
		}else{
			$list[$key]['danbao']='暂无担保机构';//担保机构
		}
		//收益率
		$monthData['month_times'] = 12;
		$monthData['account'] = $v['borrow_money'];
		$monthData['year_apr'] = $v['borrow_interest_rate'];
		$monthData['type'] = "all";
		$repay_detail = CompoundMonth($monthData);	
		//if($v['borrow_duration']==1){
		   // $list[$key]['shouyi'] = $v['borrow_interest_rate'];
		//}else{
		    $list[$key]['shouyi'] = $repay_detail['shouyi'];
		//}
		//收益率结束	
	}
	$row = array();
	$row['list'] = $list;
	$row['page'] = $page;
	return $row;
}

/**
* @param intval $invest_uid // 投资人id  
* @param intval $borrow_id // 借款id
* @param intval $invest_money // 投资金额必须为整数
* @param string $paypass // 支付密码
* @param string $invest_pass='' //投资密码
*/
function checkInvest($invest_uid, $borrow_id, $invest_money, $paypass, $invest_pass='', $hbmoney=0)
{
    $borrow_id = intval($borrow_id);
    $invest_uid = intval($invest_uid);
    if(!$paypass) return(L('please_enter').L('paypass')); 
    if(!$invest_money) return(L('please_enter').L('invest_money'));
    if(!is_numeric($invest_money)) return(L('invest_money').L('only_intval'));
    $vm = getMinfo($invest_uid,'m.pin_pass,mm.account_money,mm.back_money,mm.money_collect');
    
    $pin_pass = $vm['pin_pass'];
    if(md5($paypass) != $pin_pass) return L('paypass').L('error');  // 支付密码错误
    
    if(($vm['account_money']+$vm['back_money'])< $invest_money-$hbmoney)
        return L('lack_of_balance');
    
    $borrow = M('borrow_info')
                ->field('id, borrow_uid, borrow_money, has_borrow, has_vouch, borrow_max,borrow_min, 
                            borrow_type, password, money_collect')
                ->where("id='{$borrow_id}'")
                ->find();
    if(!$borrow){ // 没有读取到借款数据
        return L('error_parameter');
    }
    $need = $borrow['borrow_money'] - $borrow['has_borrow'];
    if($borrow['borrow_uid'] == $invest_uid){// 不能投自己的标
        return L('not_cast_their_borrow');
    }
    if(!empty($borrow['password']) && $borrow['password']!= md5($invest_pass)){ // 定向密码
        return L('error_invest_password');
    }
    
    if($borrow['money_collect'] > 0 && $vm['money_collect'] < $borrow['money_collect']){  // 待收限制
        return L('amount_to_be_received');
    }
    
    if($borrow['borrow_min'] > $invest_money ){ // 最小投资
        return L('not_less_than_min').$borrow['borrow_min'].L('yuan');
    }
	if($invest_money%$borrow['borrow_min']){
		return "投标金额必须为最小投资的整数倍！";
	}
    if(($need - $invest_money) < 0 ){ // 超出了借款资金
        return L('error_max_invest_money').$need.L('yuan');
    }
	
    // 避免最后一笔投资剩余金额小于最小资金导致无法投递，再次最后一笔投资可以大于最大投资
    if($invest_money != $need && ($need-$invest_money) < $borrow['borrow_min']){ 
        return L('full_scale_investment').$need.L('yuan'); 
    }
    if($borrow['borrow_max'] && $need > ($borrow['borrow_min']*2) && $invest_money > $borrow['borrow_max']){
        return L('beyond_invest_max'); 
    }
    return 'TRUE';
}

function getTTenderList($map,$size,$limit = 10)
{
	$pre = C("DB_PREFIX");
	$Bconfig = require(C("APP_ROOT")."Conf/borrow_config.php");
	if(empty($map['i.investor_uid']))
	{
		return;
	}
	if($size)
	{
		import( "ORG.Util.PageM" );
		$count = M("transfer_borrow_investor i")->where($map)->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")->join( "{$pre}members m ON m.id=b.borrow_uid")->count("i.id");
		$p = new Page($count,$size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
	}else{
		$page = "";
		$Lsql = "{$parm['limit']}";
	}
	$type_arr = $Bconfig['BORROW_TYPE'];
	$field = "i.*,i.add_time as invest_time,m.user_name as borrow_user,b.borrow_duration,b.fund_type,b.borrow_interest_rate,b.add_time as borrow_time,b.repayment_type,b.borrow_money,b.borrow_name,m.credits,b.fashionFlg,b.sort_order";
	$list = M("transfer_borrow_investor i")->field($field)->where($map)->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")->join( "{$pre}members m ON m.id=b.borrow_uid")->order("i.deadline,i.id ")->limit($Lsql)->select();
	foreach($list as $key => $v )
	{
		if($map['i.status'] == 4 )
		{
			$list[$key]['total'] = $v['borrow_type'] == 3 ? "1" : $v['borrow_duration'];
			$list[$key]['back'] = $v['has_pay'];
		}
		if($list[$key]['fund_type'] == 1){
		    $list[$key]['fund_category'] = "福米月";
		}elseif($list[$key]['fund_type'] == 2){
		    $list[$key]['fund_category'] = "福米季";
		}elseif($list[$key]['fund_type'] == 3){
		    $list[$key]['fund_category'] = "季季福";
		}elseif($list[$key]['fund_type'] == 4){
		    $list[$key]['fund_category'] = "月月盈";
		}elseif($list[$key]['fund_type'] == 5){
			$list[$key]['fund_category'] = "福米年";
		}
	}
	$row = array();
	$row['list'] = $list;
	$row['page'] = $page;
	$row['total_money'] = M("transfer_borrow_investor i")->where($map)->join("{$pre}transfer_borrow_info b ON b.id=i.borrow_id")->join( "{$pre}members m ON m.id=b.borrow_uid")->sum("i.investor_capital");
	$row['total_num'] = $count;
	return $row;
}

function getChargeLog($map,$size,$limit=10){  //这是充值记录
	if(empty($map['uid'])) return;
	
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('member_payonline')->where($map)->count('id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}
	
	$status_arr =array('充值未完成','充值成功','签名不符','充值失败');
	$list = M('member_payonline')->where($map)->order('id DESC')->limit($Lsql)->select();
	foreach($list as $key=>$v){
		$list[$key]['status'] = $status_arr[$v['status']];
	}
	
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$map['status'] = 1;
	$row['success_money'] = M('member_payonline')->where($map)->sum('money');
	$map['status'] = array('neq','1');
	$row['fail_money'] = M('member_payonline')->where($map)->sum('money');
	return $row;
}

function getTenderList($map,$size,$limit=10){
	$pre = C('DB_PREFIX');
	$Bconfig = require C("APP_ROOT")."Conf/borrow_config.php";
	//if(empty($map['i.investor_uid'])) return;
	if(empty($map['investor_uid'])) return;
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('borrow_investor i')->where($map)->count('i.id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}
	
	$type_arr =$Bconfig['BORROW_TYPE'];
	/////////////////////////视图查询 fan 20130522//////////////////////////////////////////
	$Model = D("TenderListView");
	$list=$Model->field(true)->where($map)->order('times ASC')->group('id')->limit($Lsql)->select();
	////////////////////////视图查询 fan 20130522//////////////////////////////////////////
	foreach($list as $key=>$v){
		//if($map['i.status']==4){
		if($map['status']==4){
			$list[$key]['total'] = ($v['borrow_type']==3)?"1":$v['borrow_duration'];
			$list[$key]['back'] = $v['has_pay'];
			$vx = M('investor_detail')->field('deadline')->where("borrow_id={$v['borrowid']} and status=7")->order("deadline ASC")->find();
			$list[$key]['repayment_time'] = $vx['deadline'];
		}
        if($v['debt_time']){
            $list[$key]['borrow_interest_rate'] = $v['debt_interest_rate'];
        }
	}

	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$row['total_money'] = M('borrow_investor i')->where($map)->sum('investor_capital');
	$row['total_num'] = $count;
	return $row;
}

//借款逾期但还未还的借款列表(逾期)
function getMBreakRepaymentList($uid=0,$size=10,$Wsql=""){
	if(empty($uid)) return;
	$pre = C('DB_PREFIX');

	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M()->query("select d.id as count from {$pre}investor_detail d where d.borrow_id in(select tb.id from {$pre}borrow_info tb where tb.borrow_uid={$uid}) AND tb.borrow_status in(6,9) AND d.deadline<".time()." AND d.repayment_time=0 {$Wsql} group by d.sort_order,d.borrow_id");
		$count = count($count);
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}

	$field = "b.borrow_name,d.status,d.total,d.borrow_id,d.sort_order,sum(d.capital) as capital,sum(d.interest) as interest,d.deadline";
	$sql = "select {$field} from {$pre}investor_detail d left join {$pre}borrow_info b ON b.id=d.borrow_id where d.borrow_uid ={$uid} AND b.borrow_status in(6,9) AND d.deadline<".time()." AND d.repayment_time=0 {$Wsql} group by d.sort_order,d.borrow_id order by  d.borrow_id,d.sort_order limit {$Lsql}";

	$list = M()->query($sql);
	$status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
	$glodata = get_global_setting();
	$expired = explode("|",$glodata['fee_expired']);
	$call_fee = explode("|",$glodata['fee_call']);
	foreach($list as $key=>$v){
		$list[$key]['status'] = $status_arr[$v['status']];
		$list[$key]['breakday'] = getExpiredDays($v['deadline']);

		if($list[$key]['breakday']>$expired[0]){
			$list[$key]['expired_money'] = getExpiredMoney($list[$key]['breakday'],$v['capital'],$v['interest']);
		}

		if($list[$key]['breakday']>$call_fee[0]){
			$list[$key]['call_fee'] = getExpiredCallFee($list[$key]['breakday'],$v['capital'],$v['interest']);
		}

		$list[$key]['allneed'] = $list[$key]['call_fee'] + $list[$key]['expired_money'] + $v['capital'] + $v['interest'];
	}
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$row['count'] = $count;
	return $row;
}



//集合起每笔借款的每期的还款状态(逾期)
function getMBreakInvestList($map,$size=10){
	$pre = C('DB_PREFIX');

	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('investor_detail d')->where($map)->count('d.id');
		$p = new Page($count, $size);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}

	$field = "m.user_name as borrow_user,b.add_time as invest_time,b.borrow_interest_rate,d.borrow_id,b.borrow_name,d.status,d.total,d.borrow_id,d.sort_order,d.interest,d.capital,d.deadline,d.sort_order, mi.real_name";
	$list =M('investor_detail d')->field($field)->join("{$pre}borrow_info b ON b.id=d.borrow_id")->join("{$pre}members m ON m.id=b.borrow_uid")->join("{$pre}member_info mi ON mi.uid=b.borrow_uid")->where($map)->order('b.add_time DESC')->limit($Lsql)->select();

	$status_arr =array('还未还','已还完','已提前还款','逾期还款','网站代还本金');
	$glodata = get_global_setting();
	$expired = explode("|",$glodata['fee_expired']);
	$call_fee = explode("|",$glodata['fee_call']);
	foreach($list as $key=>$v){
		$list[$key]['status'] = $status_arr[$v['status']];
		$list[$key]['breakday'] = getExpiredDays($v['deadline']);
	}
	$row=array();
	$row['list'] = $list;
	$row['page'] = $page;
	$row['count'] = $count;
	return $row;
}

//Add By Ruby 20150928 strat
//count 单个会员投资慧理财笔数
function get_invest_counts($uid){
    $investCounts= M('borrow_investor')->where("investor_uid = '{$uid}'")->count('id');
    return $investCounts;
}

//count 单个会员投资福米钱袋or保盈计划的笔数
function get_invest_trCounts($uid){
    $investTransferCounts= M('transfer_borrow_investor')->where("investor_uid = '{$uid}'")->count('id');
    return $investTransferCounts;
}
//Add By Ruby 20150928 end


function get_share_hongbao_list_wx($map,$size){
    if(empty($map['type'])) return;
    if($size){
        //分页处理
        import("ORG.Util.Page");
        //$count = M('member_hongbao')->where($map)->group('wxid')->count('id');
        $list = M('member_hongbao')->field("DISTINCT wxid")->where($map)->limit($Lsql)->select();
        foreach($list as $k => $v){
            $count = $count+1;
        }
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('member_hongbao')->field("DISTINCT wxid")->where($map)->order('wxid Desc')->limit($Lsql)->select();
    $wx_mount = floatval(get_hongbao_config('wx_mount'));
    foreach($list as $k => $v){
        $map['wxid'] = $v['wxid'];
        $map['uid'] = array('gt',0);
        $fetched = M('member_hongbao')->where($map)->group('wxid')->count('id');
        $list[$k]['fetched'] = intval($fetched);
        $list[$k]['mount'] = $wx_mount;
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function get_share_hongbao_list($map,$uid,$Lsql){
    if(empty($map['type'])) return;

    $list = M('member_hongbao')->field("DISTINCT wxid ,sftime,sttime")->where($map)->order('sftime Desc')->limit($Lsql)->select();
    //$wx_mount = floatval(get_hongbao_config('wx_mount'));
    foreach($list as $k => $v){
        $map['wxid'] = $v['wxid'];
        $map['uid'] = array('gt',0);
        $fetched = M('member_hongbao')->where($map)->group('wxid')->count('id');
        $wx_mount = M('member_hongbao')->where("wxid = '{$v['wxid']}'")->group('wxid')->count('id');
        
        $maps['wxid'] = $v['wxid'];
        $maps['uid'] = $uid;
        $self= M('member_hongbao')->where($maps)->find();
        if($self['uid'] > 0){
            $list[$k]['self'] = 1;
            $list[$k]['selfAmount'] = $self['money'];
        }else{
            $list[$k]['self'] = 0;
            $list[$k]['selfAmount'] = 0;
        }
        
        $list[$k]['fetched'] = intval($fetched);
        $list[$k]['self'] = intval($self);
        $list[$k]['mount'] = $wx_mount;
        $list[$k]['counts']= $wx_mount-intval($fetched);
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function get_hongbaoNew_list($map,$size){
    if(empty($map['type'])) return;
    if($size){
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_hongbao')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('member_hongbao')->where($map)->order('id')->limit($Lsql)->select();
    foreach($list as $key=>$v){
        //无需处理
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function get_hongbaoNew_count($map){
   
    $count = M('member_hongbao')->where($map)->count('id');
    return $count;
}

function get_hongbaoNew_detail($map,$size){
    if($size){
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_hongbao')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }
    $wx_rate = floatval(get_hongbao_config('wx_rate'));
    $list = M('member_hongbao')->where($map)->order('id')->limit($Lsql)->select();
    foreach($list as $key=>$v){
        $list[$key]['days']=round(($v['etime']-time())/3600/24);
        if($list[$key]['type'] == 3){
            $list[$key]['minAmount']= ceil($v['money']/$wx_rate)*100;
        }else {
            $list[$key]['minAmount']= 0;
        }
    }
    
    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function getPromotionList($map,$size){
    if(empty($map['recommend_id'])) return;

    if($size){
        //分页处理
        import("ORG.Util.Page");
        $count = M('members')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('members')->where($map)->order('id DESC')->limit($Lsql)->select();
    $i = 0;
    foreach($list as $key=>$v){
        $i++;
        $list[$key]['index'] = $i;
        //实名认证
        $ms = M('members_status')->field('id_status')->where("uid={$v['id']}")->find();
        $list[$key]['id_status'] = $ms['id_status'];

        //投资奖励
        $jiangli = M('member_moneylog')->where(" target_uid={$v['id']} and type=13 ")->sum('affect_money');
        $list[$key]['jiangli'] = $jiangli;

        //投资总额
        $invest= M('borrow_investor')->where(" investor_uid = {$v['id']} and status in (4,5,6,7) ")->sum('investor_capital');
        $investT= M('transfer_borrow_investor')->where(" investor_uid = {$v['id']} ")->sum('investor_capital');
        $amount=$invest+$investT;
        $list[$key]['investAmount'] = $amount;
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function getTransactions($map,$size){
    if(empty($map['uid'])) return;

    if($size){
        //分页处理
        import("ORG.Util.PageM");
        $count = M('member_moneylog')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list = M('member_moneylog')->where($map)->order('id DESC')->limit($Lsql)->select();
    $type_arr = C("MONEY_LOG");
    foreach($list as $key=>$v){
        $list[$key]['type'] = $type_arr[$v['type']];
        /*if($v['affect_money']>0){
         $list[$key]['in'] = $v['affect_money'];
         $list[$key]['out'] = '';
         }else{
         $list[$key]['in'] = '';
         $list[$key]['out'] = $v['affect_money'];
        }*/
    }

    $row=array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}

function getTypeListActam($parm){
    $Osql="sort_order DESC";
    $field="id,type_name,type_set,add_time,type_url,type_nid,parent_id,type_img_m";
    //查询条件
    $Lsql="{$parm['limit']}";
    $pc = D('Acategory')->where("parent_id={$parm['type_id']} and model='article'")->count('id');
    if($pc>0){
        $map['is_hiden'] = 0;
        $map['parent_id'] = $parm['type_id'];
        $map['model']  = 'article';
        $data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
    }elseif(!isset($parm['notself'])){
        $map['is_hiden'] = 0;
        $map['parent_id'] = D('Acategory')->getFieldById($parm['type_id'],'parent_id');
        $map['is_show_m']= 1;
        $data = D('Acategory')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
    }

    //链接处理
    $typefix = get_type_leve_nid($parm['type_id']);
    $typeu = $typefix[0];
    $suffix=C("URL_HTML_SUFFIX");
    foreach($data as $key=>$v){
        if($v['type_set']==2){
            if(empty($v['type_url'])) $data[$key]['turl']="javascript:alert('请在后台添加此栏目链接');";
            else $data[$key]['turl'] = $v['type_url'];
        }elseif($parm['model']=='article'||($v['parent_id']==0)) $data[$key]['turl'] = MU("Home/{$v['type_nid']}/index","typelist",array("suffix"=>$suffix));
        else $data[$key]['turl'] = MU("Home/{$typeu}/{$v['type_nid']}","typelist",array("suffix"=>$suffix));
    }
    $row=array();
    $row = $data;

    return $row;
}

?>