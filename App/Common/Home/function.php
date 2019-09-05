<?php
//获取借款列表
function getBorrowList($parm=array()){
	if(empty($parm['map'])) return;
	$map= $parm['map'];
	$orderby= $parm['orderby'];
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
	$field = "b.id,b.borrow_name,b.borrow_type,b.full_time,b.reward_type,b.borrow_times,b.borrow_max,b.borrow_status,b.borrow_money,b.borrow_use,b.repayment_type,b.borrow_interest_rate,b.borrow_duration,b.collect_time,b.add_time,b.province,b.has_borrow,b.has_vouch,b.city,b.area,b.reward_type,b.reward_num,b.password,m.user_name,m.id as uid,m.credits,m.customer_name,b.is_tuijian,b.deadline,b.danbao,b.borrow_info,b.risk_control";
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

//获取特定栏目下文章列表
function getArticleList($parm){
	if(empty($parm['type_id'])) return;
	//$map['type_id'] = $parm['type_id'];
   $type_id= intval($parm['type_id']);
   $Allid = M("article_category")->field("id")->where("parent_id = {$type_id}")->select();
   $newlist = array();
   array_push($newlist,$parm['type_id']);
  
   foreach ($Allid as $ka => $v) {
	   array_push($newlist,$v["id"]);
   }
   $map['type_id']= array("in",$newlist);
   
	$Osql="sort_order desc,id DESC";//id DESC,
	$field="id,title,art_set,art_time,art_url,art_img,art_info";
	//查询条件 
	if($parm['pagesize']){
		//分页处理
		import("ORG.Util.Page");
		$count = M('article')->where($map)->count('id');
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}else{
		$page="";
		$Lsql="{$parm['limit']}";
	}

	$data = M('article')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();

	$suffix=C("URL_HTML_SUFFIX");
	$typefix = get_type_leve_nid($map['type_id']);
	$typeu = implode("/",$typefix);
	foreach($data as $key=>$v){
		if($v['art_set']==1) $data[$key]['arturl'] = (stripos($v['art_url'],"http://")===false)?"http://".$v['art_url']:$v['art_url'];
		//elseif(count($typefix)==1) $data[$key]['arturl'] = 
		else $data[$key]['arturl'] = MU("Home/{$typeu}","article",array("id"=>$v['id'],"suffix"=>$suffix));
	}
	$row=array();
	$row['list'] = $data;
	$row['page'] = $page;
	
	return $row;
}

//Add By Ruby 20151010 Start 
//获取特定栏目下文章列表
function getArticleWeb($parm){    
    if(empty($parm['map'])) return;
    $map = $parm['map'];
    $orderby ="sort_order Desc,id Desc";
    $field="id,title";
    $Lsql = "{$parm['limit']}";
    $data = M('article')->field(true)->where($map)->order($orderby)->limit($Lsql)->select();
    foreach($data as $key=>$v){
        
	}
	$row=array();
	$row['list'] = $data;
	return $row;
	
}

//Add By Ruby 20151010 End


function getCommentList($map,$size){
	$Osql="id DESC";
	$field=true;
	//查询条件 
	if($size){
		//分页处理
		import("ORG.Util.Page");
		$count = M('comment')->where($map)->count('id');
		$p = new Page($count, $size);
		$p->parameter .= "type=commentlist&";
		$p->parameter .= "id={$map['tid']}&";
		$page = $p->show_comment();
		$Lsql = "{$p->firstRow},{$p->listRows}";
		//分页处理
	}

	$data = M('comment')->field($field)->where($map)->order($Osql)->limit($Lsql)->select();
	foreach($data as $key=>$v){
	    $data[$key]['user_name'] =substr($v['uname'],0,1).'****'.substr($v['uname'],7,4);
	}
	$row=array();
	$row['list'] = $data;
	$row['page'] = $page;
	$row['count'] = $count;
	
	return $row;
}
//排行榜
function getRankList($map,$size)
{
	$field = "investor_uid,sum(investor_capital) as total";
	$list = M("borrow_investor")->field($field)->where($map)->group("investor_uid")->order("total DESC")->limit($size)->select();
	foreach($list as $k=>$v )
	{
		$list[$k]['user_name'] = M("members")->getFieldById($v['investor_uid'],"user_name");
	}
	return $list;
}

//获取借款列表
function getMemberDetail($uid){
	$pre = C('DB_PREFIX');
	$map['m.id'] = $uid;
	//$field = "*";
	$list = M('members m')->field(true)->join("{$pre}member_banks mbank ON m.id=mbank.uid")->join("{$pre}member_contact_info mci ON m.id=mci.uid")->join("{$pre}member_house_info mhi ON m.id=mhi.uid")->join("{$pre}member_department_info mdpi ON m.id=mdpi.uid")->join("{$pre}member_ensure_info mei ON m.id=mei.uid")->join("{$pre}member_info mi ON m.id=mi.uid")->join("{$pre}member_financial_info mfi ON m.id=mfi.uid")->where($map)->limit($Lsql)->find();
	return $list;
}
//获取企业直投借款列表
function getTBorrowList($parm =array())
{
	if(empty($parm['map'])) return;
	$map = $parm['map'];
	
	if($parm['duration']==1){
		$map['b.repayment_type'] = 1;
		$map['b.borrow_duration'] = array('elt',30);
		
		$map2 = $parm['map'];
		$map2['b.repayment_type'] = array('neq',1);
		$map2['b.borrow_duration'] = array('elt',1);
		$map2['_complex'] = $map;
		$map2['_logic'] = 'or';

	}else if($parm['duration']==3){
		$map['b.repayment_type'] = 1;
		$map['b.borrow_duration'] = array('elt',90);
		
		$map2 = $parm['map'];
		$map2['b.repayment_type'] = array('neq',1);
		$map2['b.borrow_duration'] = array('elt',3);
		$map2['_complex'] = $map;
		$map2['_logic'] = 'or';

	}
	$orderby = $parm['orderby'];
	if($parm['pagesize'])
	{
		import( "ORG.Util.Page" );
		$count = M("transfer_borrow_info b")->where($map)->count("b.id");
		$p = new Page($count, $parm['pagesize']);
		$page = $p->show();
		//if ($parm['p']){
		//    $Lsql = "{$parm['p']},{$parm['pagesize']}";
		//}else {
		    $Lsql = "{$p->firstRow},{$p->listRows}";
		//}
	}else{
		$page = "";
		$Lsql = "{$parm['limit']}";
	}
	$pre = C("DB_PREFIX");
	$suffix =C("URL_HTML_SUFFIX");
	$field = "if(b.repayment_type=1,b.borrow_duration,b.borrow_duration*30) as duration,CAST((transfer_out/transfer_total)*100 AS DECIMAL(2)) as progresses,b.id,b.borrow_name,b.fund_type,b.borrow_status,b.borrow_money,b.repayment_type,b.borrow_max,b.min_month,b.transfer_out,b.transfer_back,b.transfer_total,b.per_transfer,b.borrow_interest_rate,b.interest_rate_expect,b.interest_rate_float,b.borrow_duration,b.increase_rate,b.reward_rate,b.deadline,b.is_show,b.is_tuijian,m.province,m.city,m.area,m.user_name,m.id as uid,m.credits,m.customer_name,b.borrow_type,b.b_img,b.add_time,b.collect_day,b.danbao,b.online_time,b.sort_order,b.activity_name";
    //if ($parm['limit']){
        $list = M("transfer_borrow_info b")->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->limit($Lsql)->order($orderby)->select();
    //}else {
    //    $list = M("transfer_borrow_info b")->field($field)->join("{$pre}members m ON m.id=b.borrow_uid")->where($map)->order($orderby)->select();
    //}
	
	$areaList = getarea();
	$i=1;
	foreach($list as $key => $v)
	{
		$list[$key]['location'] = $areaList[$v['province']].$areaList[$v['city']];
		$list[$key]['progress'] = getfloatvalue( $v['transfer_out'] / $v['transfer_total'] * 100, 2);
		$list[$key]['need'] = getfloatvalue(($v['transfer_total'] - $v['transfer_out'])*$v['per_transfer'], 2 );
		$list[$key]['burl'] = MU("Home/invest_transfer", "invest_transfer",array("id" => $v['id'],"suffix" => $suffix));	
		$list[$key]['duration']=$key;
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
//在线客服
function get_qq($type){
    $list = M('qq')->where("type = $type and is_show = 1")->order("qq_order DESC")->select();
	return $list;
}

//手机专用
function getleixing($map){
	
	if($map['borrow_type']==2) $str=4;//担保标
	elseif($map['borrow_type']==3) $str=5;//秒还标
	elseif($map['borrow_type']==4) $str=6;//净值标
	elseif($map['borrow_type']==1) $str=3;//信用标
	elseif($map['borrow_type']==5) $str=7;//抵押标
	return $str;
} 

//Add By Ruby 20150921 strat
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
//Add By Ruby 20150921 end


function get_hongbaoNew_detail($map,$size){
    /*if($size>0){
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_hongbao')->where($map)->count('id');
        $p = new Page($count, $size);
        $page = $p->show();
        $Lsql = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }*/
    $wx_rate = floatval(get_hongbao_config('wx_rate'));
    $list = M('member_hongbao')->where($map)->order('id')->limit($Lsql)->select();
    foreach($list as $key=>$v){
        //$list[$key]['days']=round(($v['etime']-time())/3600/24);
        if($list[$key]['type'] == 3){
            $list[$key]['minAmount']= ceil($v['money']/$wx_rate)*100;
        }else {
            $list[$key]['minAmount']= 0;
        }
    }

    $row=array();
    $row['list'] = $list;
    //$row['page'] = $page;
    return $row;
}


//投资排行版Top5
function getRankingList($limit){
    $pre = C('DB_PREFIX');
    $map['b.type'] = array('in','6,37');
    $map['m.is_transfer'] = 0;
    $order = "capital DESC";
    $field = " m.user_name ,abs(sum(b.affect_money)) as capital ";
    $list = M("member_moneylog b")->join("{$pre}members m ON m.id=b.uid")->field($field)->order('capital DESC')->where($map)->limit($limit)->group('b.uid')->select();
    
    return $list;
}
?>