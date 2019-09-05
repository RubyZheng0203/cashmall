<?php
use App\Library\Weiqianbao\Weiqianbao;
use App\Library\Weiqianbao\Protocol\UnbindingVerify\Request as UnbindingVerifyRequest;
use App\Library\Weiqianbao\Protocol\UnbindingVerify\Response as UnbindingVerifyResponse;
/**
 * 借款会员
 * @author Rubyzheng
 *
 */
class MembersAction extends ACommonAction
{
    /**
     * 会员一览页面
     */
    public function index()
    {
		$map = array();
		if($_REQUEST['uid']){
		    $map['m.id']   = urldecode($_REQUEST['uid']);
		    $search['uid'] = $map['m.id'];	
		}
		
		if($_REQUEST['iphone']){
		    $map['m.iphone']   = urldecode($_REQUEST['iphone']);
		    $search['iphone'] = $map['m.iphone'];
		}
        
		if($_REQUEST['realname']){
		    $map['mi.real_name'] = urldecode($_REQUEST['realname']);
		    $search['realname']  = $map['mi.real_name'];
		}
		
		if($_REQUEST['is_black']=='yes'){
		    $map['m.is_black'] = 1;
		}elseif($_REQUEST['is_black']=='no'){
		    $map['is_black'] = 0;
		}
		
		if($_REQUEST['is_white']=='yes'){
		    $map['m.is_white'] = 1;
		}elseif($_REQUEST['is_white']=='no'){
		    $map['is_white'] = 0;
		}
		
		if($_REQUEST['is_logoff']=='yes'){
			$map['m.is_logoff'] = 1;
		}elseif($_REQUEST['is_logoff']=='no'){
			$map['m.is_logoff'] = 0;
		}
		
		if($_REQUEST['promotion_code']){
		    $map['m.promotion_code'] = urldecode($_REQUEST['promotion_code']);
		    $search['promotion_code'] = $map['m.promotion_code'];
		}
		
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time']       = array("between",$timespan);
			$search['start_time']    = urldecode($_REQUEST['start_time']);	
			$search['end_time']      = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time']       = array("gt",$xtime);
			$search['start_time']    = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time']       = array("lt",$xtime);
			$search['end_time']      = $xtime;	
		}
		$countArr = count($map);
		if($countArr>0){
			//分页处理
			import("ORG.Util.Page");
			$count = M('members m')->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->count('m.id');
			$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
			$page  = $p->show();
			$Lsql  = "{$p->firstRow},{$p->listRows}";
			
			//sql查询
			$field = 'm.id,m.iphone,m.reg_time,m.reg_ip,m.last_time,m.last_ip,m.invite_code,m.recommend_id,m.promotion_code,m.is_black,m.is_white,m.is_gray,m.is_gold,m.is_logoff,m.reg_address,m.last_address,mi.real_name,mi.education,mi.marriage,mi.kids';
			$list  = M('members m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();
			$list  = $this->_listFilter($list);
			$this->assign("list", $list);
			$this->assign("pagebar", $page);
			$this->assign("search", $search);
			$this->assign("query", http_build_query($search));
		}
        $this->display();
    }
    
    /**
     * @param  数组集合 $list
     * @return 数组 
     */
	public function _listFilter($list){
	    $marriage  = C('MARRIAGE');
	    $education = C('EDUCATION');
	    $kids      = C('KIDS');
	    
		$row = array();
		foreach($list as $key=>$v){
			if($v['recommend_id']<>0){
				$v['recommend_name'] = M("members")->getFieldById($v['recommend_id'],"iphone");
			 }else{
				$v['recommend_name'] = "<span style='color:#000'>无推荐人</span>";
			 }
			
			$v['marriaged']        = $marriage[$v['marriage']];
			$v['education']        = $education[$v['education']];
			$v['kids']             = $kids[$v['kids']];
			
			$row[$key]=$v;
		}
		return $row;
	}
	
	/**
	 * 
	 */
	public function mb_export(){
		import("ORG.Io.Excel");
		alogs("CapitalAccount",0,1,'执行了某会员投标记录列表导出操作！');//管理员操作日志
		
		$map = array();
		if($_REQUEST['uid']){
		    $map['m.id']   = urldecode($_REQUEST['uid']);
		    $search['uid'] = $map['m.id'];	
		}
        
		if($_REQUEST['real_name']){
		    $map['mi.real_name'] = urldecode($_REQUEST['realname']);
		    $search['realname']  = $map['mi.real_name'];
		}
		
		if($_REQUEST['is_black']=='yes'){
		    $map['m.is_black'] = 1;
		}elseif($_REQUEST['is_black']=='no'){
		    $map['is_black'] = 0;
		}
		
		if($_REQUEST['is_logoff']=='yes'){
			$map['m.is_logoff'] = 1;
		}elseif($_REQUEST['is_logoff']=='no'){
			$map['m.is_logoff'] = 0;
		}
		
		if($_REQUEST['promotion_code']){
		    $map['m.promotion_code'] = urldecode($_REQUEST['promotion_code']);
		    $search['promotion_code'] = $map['m.promotion_code'];
		}
		
		if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
			$timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time']       = array("between",$timespan);
			$search['start_time']    = urldecode($_REQUEST['start_time']);	
			$search['end_time']      = urldecode($_REQUEST['end_time']);	
		}elseif(!empty($_REQUEST['start_time'])){
			$xtime = strtotime(urldecode($_REQUEST['start_time']));
			$map['m.reg_time']       = array("gt",$xtime);
			$search['start_time']    = $xtime;	
		}elseif(!empty($_REQUEST['end_time'])){
			$xtime = strtotime(urldecode($_REQUEST['end_time']));
			$map['m.reg_time']       = array("lt",$xtime);
			$search['end_time']      = $xtime;	
		}
		
		//分页处理
		import("ORG.Util.Page");
		$count = M('members m')->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->count('m.id');
		$p     = new Page($count, C('ADMIN_PAGE_SIZE'));
		$page  = $p->show();
		$Lsql  = "{$p->firstRow},{$p->listRows}";
		
		//sql查询
		$field = 'm.id,m.iphone,m.reg_time,m.reg_ip,m.last_time,m.last_ip,m.invite_code,m.recommend_id,m.promotion_code,m.is_black,m.is_logoff,mi.real_name,mi.education,mi.marriage,mi.kids';
		$list  = M('members m')->field($field)->join("{$this->pre}member_info mi ON mi.uid=m.id")->where($map)->limit($Lsql)->order('m.id DESC')->select();
		$list  = $this->_listFilter($list);
		
		$row   = array();
		$row[0]= array('会员编号','手机号','姓名','身份证','邮箱','婚姻状态','学历','注册日期','注册IP','注册地址');
		$i     = 1;
		foreach($list as $v){
				$row[$i]['id']          = $v['id'];
				$row[$i]['iphone']      = $v['iphone'];
				$row[$i]['real_name']   = $v['real_name'];
				$row[$i]['id_card']     = $v['id_card'];
				$row[$i]['email']       = $v['email'];
				$row[$i]['email']       = $v['email'];
				$row[$i]['email']       = $v['email'];
				
				$row[$i]['reg_time']    = date('Y-m-d H:i',$v['reg_time']);
				$row[$i]['reg_ip']      = $v['reg_ip'];
				$row[$i]['reg_address'] = $v['reg_address'];
				
				
				$i++;
			}
		$xls = new Excel_XML('UTF-8', false, 'datalist');
		$xls->addArray($row);
		$xls->generateXML("mb_export");
	}
	
	/**
	 * 会员资料详细页面
	 */
	public function member(){
	    $array             = json_decode(file_get_contents("php://input"),true);
	    $marry             = C('MARRIAGE_NAME');
	    $marryCode         = C("MARRIAGE_CODE");
	    $education         = C("EDUCATION_NAME");
	    $eduCode           = C("EDUCATION_CODE");
	    $kids              = C('KIDS');
	    $career            = C('CAREER_NAME_LIST');
	    $income            = C('MONTHLY_INCOME');
	    $relation          = C("SOCIAL_NAME");
	    $relationCode      = C("SOCIAL_CODE");
	    $uid       = intval($_GET['id']);
	    $fieldm    = "m.id,m.iphone,m.reg_time,m.reg_ip,m.last_time,m.last_ip,m.last_address,m.last_gps,m.invite_code,m.recommend_id,m.promotion_code,m.is_black,m.is_white,m.is_gray,m.is_gold,m.is_logoff,m.integral,m.sina_id";
	    $fieldi    = "mi.real_name,mi.province,mi.city,mi.address,mi.email,mi.real_name,mi.education,mi.marriage,mi.kids,mi.id_card,mi.qq_code,mi.house,mi.house_type";
        $fieldc    = "mc.job_title,mc.job_time,mc.year_income,mc.company_name,mc.company_province,mc.company_city,mc.company_address,mc.company_tel";
        $fieldb    = "mb.bank_card,mb.bank_code,mb.bank_name,mb.bank_province,mb.bank_city,mb.bank_address";
	    $fieldr    = "mr.relation1,mr.name1,mr.memo1,mr.iphone1,mr.is_black1,mr.reason1,mr.relation2,mr.name2,mr.iphone2,mr.memo2,mr.is_black2,mr.reason2,mr.relation3,mr.name3,mr.iphone3,mr.memo3,mr.is_black3,mr.reason3";
	    $fieldst   = "st.location";
	    $vm        = M('members m')->field($fieldm)->where("m.id={$uid}")->find();
	    $vi        = M('member_info mi')->field($fieldi)->where("mi.uid={$uid}")->find();
	    $vc        = M('member_company mc' )->field($fieldc)->where("mc.uid={$uid}")->find();
	    $vb        = M('member_bank mb')->field($fieldb)->where("mb.uid={$uid} and mb.type = 1")->find();
	    $vr        = M('member_relation mr')->field($fieldr)->where("mr.uid={$uid}")->find();
		
	    if($vm['recommend_id']>0){
	        $vinvite = M('members m')->field("iphone")->where("m.id={$vm['recommend_id']}")->find();
	        $vm['recommend_iphone']     = $vinvite['iphone'];
	    }else{
	        $vm['recommend_iphone']     = "";
	    }
	    
	    $vi['province']         = get_province($vi['province']);
	    $vi['city']             = get_city($vi['city']);
	    $vm['is_black']         = ($vm['is_black']==0)?"否":"<span style='color:red'>是</span>";
	    $vm['is_white']         = ($vm['is_white']==0)?"否":"<span style='color:red'>是</span>";
	    $vm['is_gray']          = ($vm['is_gray']==0)?"否":"<span style='color:red'>是</span>";
	    $vm['is_gold']          = ($vm['is_gold']==0)?"否":"<span style='color:red'>是</span>";
	    $vm['is_logoff']        = ($vm['is_logoff']==0)?"否":"<span style='color:red'>是</span>";
	    $vm['sina_id']          = ($vm['sina_id']==0)?"否":"<span style='color:red'>是</span>";
	    $vi['marriaged']        = $marry[array_search($vi['marriage'],$marryCode)];
	    $vi['education']        = $education[array_search($vi['education'],$eduCode)];
	    //$vo['kids']             = $kids[$vo['kids']];
	    //$vo['monthly_income']   = $income[$vo['monthly_income']];
	    //$vo['bank_province']    = get_province($vo['bank_province']);
	    //$vo['bank_city']        = get_bank_city($vo['bank_city']);
	    $vc['company_province'] = get_province($vc['company_province']);
	    $vc['company_city']     = get_city($vc['company_city']);
	    $vr['relation1']        = $relation[array_search($vr['relation1'],$relationCode)];

	    $sex_a = substr($vi['id_card'],16,1);
	    if (!empty($vi['id_card'])){
	        if($sex_a%2==1){
	            $vi['sex'] = '男';
	        }else if($sex_a%2==0){
	            $vi['sex'] = '女';
	        }
	    }else {
	        $vi['sex'] = '保密';
	    }
	    
	    $date = date('Y-m-d H:i:s',time());
	    $date = date("Y",strtotime($date));
	    $age  = $date - substr($vi['id_card'],6,4);
	    $vi['age'] = $age;
	    
	    
	    $borrowSum = M('borrow_detail')->where(" uid = {$uid} ")->sum("capital");
	    $repaySum  = M('borrow_detail')->where(" uid = {$uid} and status = 1")->sum("capital");
	    $pendSum   = M('borrow_detail')->where(" uid = {$uid} and status = 0")->sum("capital");
	    $dueSum    = M('borrow_detail')->where(" uid = {$uid} and status = 0 and deadline <= ".time())->sum("capital");
	    $vm['borrowSum'] = $borrowSum;
	    $vm['repaySum']  = $repaySum;
	    $vm['pendSum']   = $pendSum;
	    $vm['dueSum']    = $dueSum;
	    
	    //借款成功总次数
	    $borrowCount     = M('borrow_detail')->where(" uid = {$uid}")->group('borrow_id')->count("id");
	    $repayCount      = M('borrow_detail')->where(" uid = {$uid} and status = 1 and repayment_time = deadline and due_fee =0 and late_fee=0  ")->count("id");
	    $PrepaymentCount = M('borrow_detail')->where(" uid = {$uid} and status = 1 and repayment_time < deadline and due_fee =0 and late_fee=0 ")->count("id");
	    $dueCount        = M('borrow_detail')->where(" uid = {$uid} and status = 1 and repayment_time > deadline ")->count("id");
	    $vm['borrowCount']       = $borrowCount;
	    $vm['repayCount']        = $repayCount;
	    $vm['PrepaymentCount']   = $PrepaymentCount;
	    $vm['dueCount']          = $dueCount;
	    $this->assign("vm",$vm);
	    $this->assign("vi",$vi);
	    $this->assign("vc",$vc);
	    $this->assign("vr",$vr);
		$this->assign("vb",$vb);
	    $this->assign("user",$vm['iphone']);
	
	    $this->display();
	}
	
	/**
	 * 设置黑名单页面
	 */
	public function  black(){
	    setBackUrl();
	    $id     = intval($_GET['id']);
	    $type   = intval($_GET['type']);
	    $status = intval($_GET['status']);
	    
	    $this->assign("id",$id);
	    $this->assign("type",$type);
	    $this->assign("status",$status);
	    $this->display();
	}
	
	/**
	 * 设置黑名单
	 */
	public function  doblack(){
	    $id     = intval($_POST['id']);
	    $type   = intval($_POST['type']);
	    $status = intval($_POST['status']);
	    $reason = $_POST['reason'];
	    $mem    = attributes($id);
	    if($type==1){
	        if($status==1){
	            if($mem['is_white']==1 || $mem['is_gray']==1 || $mem['is_gold']==1){
	                $this->error("该用户已经设置为白名单,灰名单或者金名单了，请先解除");
	            }
	            if ($mem['is_black']==1){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_black'] = 1;
	        }else{
	            if ($mem['is_black']==0){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_black'] = 0;
	        }
	        $updata = M('members')->where("id={$id} ")->save($data);
	        if($updata){
	            $datab['uid']      = $id;
	            $datab['tree_id']  = 0;
	            $datab['reason']   = $reason;
	            $datab['add_time'] = time();
	            $newid = M('member_black')->add($datab);
	            if($newid){
	                $this->success("黑名单处理成功");
	            }else{
	                $this->error("黑名单记录同步插入操作失败");
	            }
	        }else{
	            $this->error("黑名单处理失败");
	        }
	    }else if($type==2){
	        if($status==1){
	            if($mem['is_black']==1 || $mem['is_gray']==1 || $mem['is_gold']==1){
	                $this->error("该用户已经设置为黑名单，灰名单或者金名单了，请先解除");
	            }
	            if ($mem['is_white']==1){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_white'] = 1;
	        }else{
	            if ($mem['is_white']==0){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_white'] = 0;
	        }
	        $updata = M('members')->where("id={$id}")->save($data);
	        if($updata){
	           $this->success("白名单处理成功");
	        }else{
	           $this->error("白名单处理失败");
	        }
	    }else if($type==3){
	        if($status==1){
	            if($mem['is_black']==1 || $mem['is_white']==1 ||$mem['is_gold']==1){
	                $this->error("该用户已经设置为黑名单，白名单或者金名单了，请先解除");
	            }
	            if ($mem['is_gray']==1){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_gray'] = 1;
	        }else{
	            if ($mem['is_gray']==0){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_gray'] = 0;
	        }
	        $updata = M('members')->where("id={$id}")->save($data);
	        if($updata){
	           $this->success("灰名单处理成功");
	        }else{
	           $this->error("灰名单处理失败");
	        }
	    }else if($type==4){
	        if($status==1){
	            if($mem['is_black']==1 || $mem['is_white']==1 || $mem['is_gray']==1){
	                $this->error("该用户已经设置为黑名单，灰名单或者白名单了，请先解除");
	            }
	            if ($mem['is_gold']==1){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_gold'] = 1;
	        }else{
	            if ($mem['is_gold']==0){
	                $this->error("请不要重复提交表单");
	            }
	            $data['is_gold'] = 0;
	        }
	        wqbLog("DD------".json_encode($data));
	        wqbLog("ID________".$id);
	        $updata = M('members')->where("id={$id}")->save($data);
	        if($updata){
	           $this->success("金名单处理成功");
	        }else{
	           $this->error("金名单处理失败");
	        }
	    }
	}
	
	/**
	 * 积分页面
	 */
	public function integral(){
	    $map = array();
	    if($_REQUEST['uid']){
	        $map['m.uid']   = urldecode($_REQUEST['uid']);
	        $search['uid']  = $map['m.uid'];
	    }
	    
	    if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
	        $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
	        $map['m.add_time']       = array("between",$timespan);
	        $search['start_time']    = urldecode($_REQUEST['start_time']);
	        $search['end_time']      = urldecode($_REQUEST['end_time']);
	    }elseif(!empty($_REQUEST['start_time'])){
	        $xtime = strtotime(urldecode($_REQUEST['start_time']));
	        $map['m.add_time']       = array("gt",$xtime);
	        $search['start_time']    = $xtime;
	    }elseif(!empty($_REQUEST['end_time'])){
	        $xtime = strtotime(urldecode($_REQUEST['end_time']));
	        $map['m.add_time']       = array("lt",$xtime);
	        $search['end_time']      = $xtime;
	    }
	    
	    $countArr = count($map);
	    if($countArr>0){
    	    //分页处理
    	    import("ORG.Util.Page");
    	    $count = M('member_integrallog m')->where($map)->count('m.id');
    	    $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
    	    $page  = $p->show();
    	    $Lsql  = "{$p->firstRow},{$p->listRows}";
    	    
    	    //sql查询
    	    $list  = M('member_integrallog m')->field(true)->where($map)->limit($Lsql)->order('m.id DESC')->select();
    	    $this->assign("list", $list);
    	    $this->assign("pagebar", $page);
    	    $this->assign("search", $search);
    	    $this->assign("query", http_build_query($search));
	    }
	    
	    
	    $this->display();
	}
	
	/**
	 * 借款会员设备指纹记录
	 */
	public function event(){
	    $map = array();
	    $uid = "";
	    if($_REQUEST['event']){
	        $map['m.maxent_id']   = urldecode($_REQUEST['event']);
	        $search['event'] = $map['m.maxent_id'];
	    }
	    
	    if($_REQUEST['moblieno']){
	        $moblieno = $_REQUEST['moblieno'];
	        $search['moblieno'] = urldecode($_REQUEST['moblieno']);
	        $mem = M("members ")->field(" id ")->where(" iphone = '{$moblieno}' ")->find();
	    }
	    
	    
	    if($_REQUEST['uid']){
	        $map['m.uid']   = urldecode($_REQUEST['uid']);
	        $search['uid']  = $map['m.uid'];
	        $uid = urldecode($_REQUEST['uid']);
	    }else{
	        if($mem['id']>0){
	           $uid = $mem['id'];
	           $map['m.uid']   = $mem['id'];
	        }
	    }
	    if($_REQUEST['eventid'] ){
	        $map['m.event'] = $_REQUEST['eventid'];
	        $search['eventid'] = $map['m.event'];
	    }
	    
	    if(!empty($_REQUEST['start_time']) && !empty($_REQUEST['end_time'])){
	        $timespan = strtotime(urldecode($_REQUEST['start_time'])).",".strtotime(urldecode($_REQUEST['end_time']));
	        $map['m.add_time']       = array("between",$timespan);
	        $search['start_time']    = urldecode($_REQUEST['start_time']);
	        $search['end_time']      = urldecode($_REQUEST['end_time']);
	    }elseif(!empty($_REQUEST['start_time'])){
	        $xtime = strtotime(urldecode($_REQUEST['start_time']));
	        $map['m.add_time']       = array("gt",$xtime);
	        $search['start_time']    = $xtime;
	    }elseif(!empty($_REQUEST['end_time'])){
	        $xtime = strtotime(urldecode($_REQUEST['end_time']));
	        $map['m.add_time']       = array("lt",$xtime);
	        $search['end_time']      = $xtime;
	    }

	    $countArr = count($map);
		if($countArr>0){
    	    //分页处理
    	    import("ORG.Util.Page");
    	    $count = M('member_device m')->where($map)->count('m.id');
    	    $p     = new Page($count, C('ADMIN_PAGE_SIZE'));
    	    $page  = $p->show();
    	    $Lsql  = "{$p->firstRow},{$p->listRows}";
    	    //sql查询
    	    $field = 'm.id,m.uid,m.event,m.maxent_id,m.add_time';
    	    $list  = M('member_device m')->field($field)->where($map)->limit($Lsql)->order('m.id DESC')->select();
    	    $list  = $this->_listFilterEvent($list);
    	    $this->assign("list", $list);
    	    $this->assign("pagebar", $page);
    	    $this->assign("search", $search);
    	    $this->assign("query", http_build_query($search));
	    }
	    $events  = C('EVENT_LIST');
	    $this->assign("eventlist",$events);
	    
	    $this->display();
	}
	
	
	/**
	 * @param  数组集合 $list
	 * @return 数组
	 */
	public function _listFilterEvent($list){
	    $events  = C('EVENT_LIST');
	    $row = array();
	    foreach($list as $key=>$v){
	        $v['evented']        = $events[$v['event']];
	        $info = M('member_info')->field(true)->where(" uid = {$v['uid']} ")->find();
	        $v['real_name']      = $info['real_name'];
	        $v['iphone']         = $info['iphone'];
	        $row[$key]=$v;
	    }
	    return $row;
	}
	
	/**
	 * 设备指纹记录详细
	 */
	public function eventView(){
	    $id          = intval($_GET['id']);
	    $vo          = M('member_device m')->field(true)->where("id = {$id}")->find();
	    $ipAddress   = get_ipAddress($vo['ip']);
	    $events      = C('EVENT_LIST');
	    $evented     = $events[$vo['event']];
	    $this->assign("vo", $vo);
	    $this->assign("ipAddress", $ipAddress);
	    $this->assign("evented", $evented);
	    $this->display();
	}
	
	/**
	 * 解绑手机认证
	 */
	public function unBindingAccount()
	{
	    $uid    = intval($_POST['uid']);
	    $unbind = intval($_POST['unbind']);
	    $oldInfo = M('members')->where("id = '{$uid}'")->find();
	    wqbLog($oldInfo['iphone']);
	    if ( empty($oldInfo) ) {
	        ajaxmsg("非法的uid".$unbind, 0);
	    }
	    if ($unbind == 1) {
	        //$wqbRequest  = new UnbindingVerifyRequest();
            //$wqbResponse = new UnbindingVerifyResponse();
			$wqbRequest = new App\Library\Weiqianbao\Protocol\UnbindingVerify\Request();
            $wqbResponse = new App\Library\Weiqianbao\Protocol\UnbindingVerify\Response();
            $wqbRequest->identity_id    = "mall".$uid;
            $wqbRequest->identity_type  = "UID";
            $wqbRequest->verify_type    = "MOBILE";
            $wqbRequest->client_ip      = get_client_ip();
            $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
            $weiqianbao->fire();
            if (!$wqbResponse->success()) {
                ajaxmsg("解绑老帐号:".$wqbResponse->error(), 0);
            }
            ajaxmsg("解绑成功");
	    }else{
	        $wqbRequest = new App\Library\Weiqianbao\Protocol\BindingVerify\Request();
	        $wqbResponse = new App\Library\Weiqianbao\Protocol\BindingVerify\Response();

	        $wqbRequest->identity_id   = "mall".$uid;
	        $wqbRequest->identity_type = "UID";
	        $wqbRequest->verify_type   = "MOBILE";
	        $wqbRequest->verify_entity = $oldInfo['iphone'];
	        $wqbRequest->client_ip = get_client_ip();
	        $weiqianbao = new Weiqianbao($wqbRequest, $wqbResponse);
	        $weiqianbao->fire();
	        if (!$wqbResponse->success()) {
	            ajaxmsg("绑定新帐号:".$wqbResponse->error(), 0);
	        }
	         
	        ajaxmsg("绑定成功");
	    }
	    
	}
	
	/**
	 * 
	 * 注册会员统计
	 */
	public function daCount()
	{
	    $now = time();
	    $time = strtotime(date("Y-m-d",$now)." 00:00:00");
	    $time7 = $now - 24 * 3600 * 7;
	    $time30 = $now - 24 * 3600 * 30;
	    
		$map['m.reg_time']       = array("gt",$time);
	    $count = M('members m')->where($map)->count('m.id');
	    
	    $map7['m.reg_time']       = array("gt",$time7);
	    
	    $count7 = M('members m')->where($map7)->count('m.id');
	    $avg7 = intval($count7/7);
	    
	    
	    $map30['m.reg_time']       = array("gt",$time30);
	    $count30 = M('members m')->where($map30)->count('m.id');
	    $avg30 = intval($count30/30);
	    
	    $this->assign("count", $count);
	    $this->assign("count7", $count7);
	    $this->assign("avg7", $avg7);
	    $this->assign("count30", $count30);
	    $this->assign("avg30", $avg30);
	    
	    
	    $this->display();
	}
	
	public function daAddress()
	{
	    $field = "m.reg_address,count(m.id) as count ";
	    $list = M('members m')->field($field)->where('1=1')->group("reg_address")->order('count desc')->select();
	    
	    $this->assign("list", $list);
	    
	    $this->display();
	}
	
	public function daSex()
	{
	    $list  = M()->query("SELECT count(uid) as count, MOD(substring(id_card, 17, 1),2) As sex from ml_member_info where id_card !='' GROUP BY sex ");
	    $list1 = M()->query("SELECT count(id) as count from ml_members aa where id not in ( SELECT uid from ml_member_info)");
	     
	    $this->assign("list", $list);
	    $this->assign("count0", $list1[0]['count']);
	     
	    $this->display();
	}
	
	public function daAge()
	{
	    $list  = M()->query("SELECT count(uid) as count, MOD(substring(id_card, 17, 1),2) As sex from ml_member_info where id_card !='' GROUP BY sex ");
	    $list1 = M()->query("SELECT count(id) as count from ml_members aa where id not in ( SELECT uid from ml_member_info)");
	
	    $this->assign("list", $list);
	    $this->assign("count0", $list1[0]['count']);
	
	    $this->display();
	}
	
	public function doApply()
	{
	    $map = array();
	    if($_POST['mobile']||$_POST['uid']){
	        if($_POST['mobile']){
	            $map['m.iphone']   = $_POST['mobile'];
	        }
	        if($_POST['uid']){
	            $map['ms.uid']   = $_POST['uid'];
	        }
	        $list  = M('member_status ms')->field(true)->join("{$this->pre}members m ON ms.uid = m.id")->join("{$this->pre}borrow_apply b ON ms.borrow_id = b.id")->where($map)->order('ms.borrow_id DESC')->select();
		    $html  = "<table id='area_list' width='100%' border='0' cellspacing='0' cellpadding='0'><tr><th class='line_l'>借款ID</th><th class='line_l'>UID</th><th class='line_l'>借款金额</th><th class='line_l'>借款状态</th><th class='line_l'>中间审核结果</th><th class='line_l'>中间审核时间</th><th class='line_l'>初审结果</th><th class='line_l'>初审时间</th><th class='line_l'>绑卡结果</th><th class='line_l'>绑卡时间</th><th class='line_l'>决策树结果</th><th class='line_l'>决策树时间</th><th class='line_l'>签约结果</th><th class='line_l'>签约时间</th><th class='line_l'>是否授信</th><th class='line_l'>授信时间</th><th class='line_l'>拍拍信结果</th><th class='line_l'>拍拍信时间</th><th class='line_l'>刷脸结果</th><th class='line_l'>刷脸时间</th><th>复审结果</th><th class='line_l'>复审时间</th><th>放款结果</th><th class='line_l'>放款时间</th></tr>";
		    foreach($list as $key=>$v){
		        $data = "<tr overstyle='on'>";
		        $data = $data."<td>".$v['borrow_id']."</td><td>".$v['uid']."</td><td>￥".$v['money']." </td>";
		        if($v['status']==99){
		            $data = $data."<td>取消</td>";
		        }else{
		            $data = $data."<td>-</td>";
		        }
		        if($v['mid_tree']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['mid_tree_time'])."</td>";
		        }else if($v['mid_tree']==2){
		            $data = $data."<td>白骑士拒绝</td><td>".date('Y年m月d日 H:i:s',$v['mid_tree_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['first_trial']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['first_trial_time'])."</td>";
		        }else if($v['first_trial']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['first_trial_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['bank_bing']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['bank_bing_time'])."</td>";
		        }else if($v['bank_bing']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['bank_bing_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['tree']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['tree_time'])."</td>";
		        }else if($v['tree']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['tree_time'])."</td>";
		        }else if($v['tree']==3){
		            $data = $data."<td>风控拒绝</td><td>-</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['signed']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['signed_time'])."</td>";
		        }else if($v['signed']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['signed_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['is_recheck']==1){
		            $data = $data."<td>是</td><td>".date('Y年m月d日 H:i:s',$v['recheck_time'])."</td>";
		        }else if($v['is_recheck']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['recheck_time'])."</td>";
		        }else{
		            $data = $data."<td>否</td><td>-</td>";
		        }
		        if($v['is_ppc']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['ppc_time'])."</td>";
		        }else if($v['is_ppc']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['ppc_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['id_verify']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['id_verify_time'])."</td>";
		        }else if($v['id_verify']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['id_verify_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['is_review']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['review_time'])."</td>";
		        }else if($v['is_review']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['review_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        }
		        if($v['pending']==1){
		            $data = $data."<td>通过</td><td>".date('Y年m月d日 H:i:s',$v['pending_time'])."</td>";
		        }else if($v['pending']==2){
		            $data = $data."<td>拒绝</td><td>".date('Y年m月d日 H:i:s',$v['pending_time'])."</td>";
		        }else{
		            $data = $data."<td>-</td><td>-</td>";
		        } 
		        $data = $data."</tr>";
		        $html = $html.$data;
		    }
		    $html = $html."</table>";
		    ajaxmsg($html, 0);
	    }else{
		    ajaxmsg("", 0);
		}
	}
	
}
?>