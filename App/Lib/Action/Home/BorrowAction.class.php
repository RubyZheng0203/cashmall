<?php
use App\Library\Weiqianbao\PayMethod\Extend\QuickPayExtend;
use App\Library\Weiqianbao\PayMethod\QuickPay;
use App\Library\Weiqianbao\Protocol\CreateHostingDeposit\Request as CreateHostingDepositRequest;
use App\Library\Weiqianbao\Protocol\CreateHostingDeposit\Response as CreateHostingDepositResponse;
use App\Library\Weiqianbao\Weiqianbao;
use App\Library\Weiqianbao\Protocol\AdvanceHostingPay\Request as AdvanceHostingPayRequest;
use App\Library\Weiqianbao\Protocol\AdvanceHostingPay\Response as AdvanceHostingPayResponse;
use App\Library\Weiqianbao\Protocol\UnbindingBankCard\Request as UnbindingBankCardRequest;
use App\Library\Weiqianbao\Protocol\UnbindingBankCard\Response as UnbindingBankCardResponse;
use App\Library\Weiqianbao\Protocol\UnbindingBankCardAdvance\Request as UnbindingBankCardAdvanceRequest;
use App\Library\Weiqianbao\Protocol\UnbindingBankCardAdvance\Response as UnbindingBankCardAdvanceResponse;
use App\Library\Weiqianbao\Protocol\CreateHostingWithdraw\Request as CreateHostingWithdrawRequest;
use App\Library\Weiqianbao\Protocol\CreateHostingWithdraw\Response as CreateHostingWithdrawResponse;
class BorrowAction extends HCommonAction{  
    
    /**
     * 借款首页
     */
    public function index() {
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $uid = $_SESSION['uid'];
        if (empty($uid)){
            $this->redirect("/Index/browser");
        }

        $type          = $_GET['type']; 

        $dateInfo = M()->query("SELECT DISTINCT duration FROM ml_borrow_item WHERE is_on = 1 AND type = {$type} ORDER BY duration ASC;");
        $moneyInfo = M()->query("SELECT DISTINCT money FROM ml_borrow_item WHERE is_on = 1 AND type = {$type} ORDER BY money ASC;");

        $this->assign('dateInfo',$dateInfo);
        $this->assign('moneyInfo',$moneyInfo); 
        $now = time();
        //$ticket   = M("member_coupon")->field('money,end_time,id')->where("uid = $uid and status = 0 and type = 1 and end_time > $now")->select();//用户优惠券信息
        //$this->assign('ticket',$ticket);
        $tick   = "CASHLOANS".time().$uid;
        $tickIn = "CASHLOANSIN".time().$uid;
        $this->assign('tick',$tick);
        $this->assign('type',$type); 
        $this->assign('tickIn',$tickIn);
        
        $globalArr = get_global_setting();//借款申请周期
        $day       = $globalArr['reapply_day'];
        $this->assign('day',$day);
        
        $this->display();
    }


    /*/////////////////////////////////
     * 上传图片
     */
    public  function upLoadImage(){
        $path = C("NARROW_PATH");
        $this->maxSize= 300;
        $this->savePathNew = $path;
        $uid = $_SESSION['uid'];
        $name = '';
        if($_FILES['file0']['tmp_name']){
            $filename = $_FILES['file0']['tmp_name'];
            $name = $uid.'-1';
        }elseif($_FILES['file1']['tmp_name']){
            $filename = $_FILES['file1']['tmp_name'];
            $name = $uid.'-2';
        }elseif($_FILES['file2']['tmp_name']){
            $filename = $_FILES['file2']['tmp_name'];
            $name = $uid.'-3';
        }else{
            $filename = '';
        }
        if($filename){
            ImageShrink($filename,350,250,$name);
            if(file_exists($path.$name.'.jpg')){
                $data['status'] = 1;
                ajaxmsg($data,1);
            }else{
                $data['status'] = 0;
                ajaxmsg($data,0);
            }
        }

    }
    
    /**
     * 费用计算
     */
    public function countMoney(){
        $money =  $_POST['money'];
        $day   =  $_POST['day'];
        $type  =  $_POST['type'];
        $ticket = empty($_POST['ticket']) ? 0 : $_POST['ticket'];
        $uid = $_SESSION['uid'];
        $where['money']    = $money;
        $where['duration'] = $day;
        $where['type']     = $type;
        $where['is_on']    = 1;
        $itemInfo = M("borrow_item")->field('*')->where($where)->find();
        $map['uid'] = $uid;
        $map['status'] = 5;
        $info      = M("borrow_apply")->field('id')->where($map)->find();
        
        $checkCost = round($money*$itemInfo['audit_rate']/100*$day,2);//贷后管理费
        $usedMoney = $itemInfo['enabled_rate'];//账户管理费
        $interest  = round($money*$itemInfo['rate']/100*$day/360,2);//利息
        $pay       = $itemInfo['pay_fee'];//支付服务费
        $total     = $interest+$money+$checkCost+$usedMoney+$pay;
        if (empty($info)){
            $createMoney = $itemInfo['created_rate'];//技术服务费
            $total = $total+$createMoney;
            $str = $checkCost.",".$usedMoney.",".$interest.",".$total.",".$pay.",".$createMoney;
            ajaxmsg($str,5);
        }else {
            $str = $checkCost.",".$usedMoney.",".$interest.",".$total.",".$pay;
            ajaxmsg($str,4);
        }      
        
    }
    
    
    /**
     * 插入用户借款申请
     */
    public function addBorrowInfo(){
        //查询用户是否有申请在身
        $uid                = $_SESSION['uid'];
        //查询用户是否三天内已有被拒借款申请
        $globalArr        = get_global_setting();//借款申请周期
        $loanPeriod       = $globalArr['reapply_day'];
        $now              = time();
        $binfo = M()->query("SELECT id ,refuse_time  FROM ml_borrow_apply WHERE uid = {$uid} AND `status` in (94, 95, 96, 97, 98) AND (refuse_time + $loanPeriod*3600*24) > $now ORDER BY refuse_time DESC LIMIT 1 ");

        //用户等级
        $user = M("members")->where("id = {$uid}")->field("is_black,is_white,is_gold,is_gray")->find();
        if($user['is_white'] == 1){//白名单-中级
            $map['uid']         = $uid;
            $map['status']      = array('in','0,1,2,3,4');
            $info = M("borrow_apply")->field('id')->where($map)->find();
            if($info['id']>0){
                ajaxmsg('您当前有一笔借款正在进行，取消或者还请后方可再申请！',0);
            }
        }elseif($user['is_gold'] == 1){//金名单-高级
            $count1 = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` in (0,1,2,3) and bb.type = 1 ")->count('aa.id');
            $count2 = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` in (0,1,2,3) and bb.type = 2 ")->count('aa.id');
            $len1   = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` = 4 and bb.type = 1 ")->count('aa.id');
            $len2   = M("borrow_apply aa")->join("ml_borrow_item bb on aa.item_id = bb.id")->where("aa.uid = {$uid} and aa.`status` = 4 and bb.type = 2 ")->count('aa.id');
            
            if($count1>0 || $count2>0){
                ajaxmsg('您当前有一笔借款正在进行，取消或者还请后方可再申请！',0);
            }
            
            if($len1>0 && $len2>0){
                ajaxmsg('您当前已经有两笔借款正在进行，还请后方可再申请！',0);
            }
        }else{//无名单or黑名单-灰名单
            if($_POST['type']==2){
                ajaxmsg('信用额度不足，不能申请此借款，保持良好的还款有助于提额！',0);
            }
            $map['uid']             = $uid;
            $map['status']          = array('in','0,1,2,3,4');
            $info = M("borrow_apply")->field('id')->where($map)->find();
            if($info['id']>0){
                ajaxmsg('您当前有一笔借款正在进行，请还请后再申请！',0);
            }
        }

        if (empty($binfo)){
            $str = explode('&',$_POST['str']);
            $result = insertCashLoanOrder($uid, $_POST['money'], $_POST['day'], $_POST['tickId'], $_POST['tickMoney'],$_POST['type']);
            if($result!==false){
                if (!empty($_POST['tickId'])){
                    $tickData['status'] = 1;
                    $tickData['id']     = $_POST['tickId'];
                    M("member_coupon")->save($tickData);
                }
                session('bid',$result);
                //用户操作记录
                $data['operation'] = "/Borrow/userBaseInfo";
                $data['orderNum']  = 2;
                $data['borrow_id'] = $result;
                $data['uid'] = $uid;
                $id = M("user_operation")->where("uid = {$uid}")->add($data);
                ajaxmsg('提交申请成功',1);
            }else {
                ajaxmsg('提交申请失败',0);
            } 
        }else {
            $day = $loanPeriod-intval(($now-$binfo[0]['refuse_time'])/24/3600);
            ajaxmsg("{$day}",2);
        }            
        
    }
    
    /**
     * 借款第二步,用户基本信息
     */
    public  function userBaseInfo() {
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        if (!isset($_SESSION['bid'])){
            session('bid',$_GET['bid']);
        }else{
            session('bid',$_SESSION['bid']);
        }
        $cashLoans  = C("CASHLOANS_USE");//职业类型
        $path       = C("NARROW_PATH");
        $uid        = $_SESSION['uid']; 
        $province   = M("province")->field(true)->select();
        $this->assign('province',$province);     
        $info = M("member_info")->field('id_card,real_name,register_province,register_city,register_address')->where("uid = $uid")->find();
        $tick = "REALNAME".time().$uid;
        $tickIn = "REALNAMEIN".time().$uid;
        //判断是否需要再传照片
        $filename1 = $path.$uid.'-1.jpg';
        $filename2 = $path.$uid.'-2.jpg';
        $filename3 = $path.$uid.'-3.jpg';
        $image_info =  M("borrow_apply")->field(true)->where("uid = {$uid} and is_full = 1 and up_bid > 0 and status in (4,5) and len_time >= '1515751200'")->find();
        if(file_exists($filename1) && file_exists($filename2) && file_exists($filename3) && $image_info){
            $is_pic = 1;
        }else{
            $is_pic = 0;
        }
        $user = M("members")->where("id = {$uid}")->field("fuiou_id")->find();
        $this->assign('fuiou_id',$user['fuiou_id']);
        $this->assign('is_pic',$is_pic);
        $this->assign('tick',$tick);
        $this->assign('tickIn',$tickIn);
        $this->assign("info",$info);
        $this->assign('cashLoans',json_encode(array_values($cashLoans)));
        $this->display();
    }
    
    /**
     * 插入用户基本信息
     */
    /*public function addUserInfo(){
        $idcardL = substr($_POST['idCard'], -1);
        if($idcardL=="x"){
            $idcard = substr($_POST['idCard'], 0,16)."X";
        }else{
            $idcard = $_POST['idCard'];
        }
        $data['id_card']            = $idcard;
        $data['real_name']          = $_POST['realName'];
        $data['register_province']  = $_POST['pro'];
        $data['register_city']      = $_POST['city'];
        $data['register_address']   = $_POST['cardInfo'];    
        $data['add_time']           = time();
        $data['uid']                = $_SESSION['uid'];
        $map['uid']                 = $_SESSION['uid'];
        $sinaInfo   = M("members")->field("sina_id,iphone,is_white,is_gold")->where("id = {$_SESSION['uid']}")->find();
        $orSina     = checkSinaAuth($sinaInfo['sina_id']);
        $info       = M("member_info")->field('id,real_name,id_card')->where($map)->find();
        $where['uid']             = $_SESSION['uid'];
        $where['id']              = $_SESSION['bid'];
        $where['status']          = array('not in','4,5,94,95,96,97,98,99');
        $binfo = M("borrow_apply")->field('id,money,coupon_id,add_time')->where($where)->find();
        session('bid',$binfo['id']);
        if (empty($info['real_name'])&&empty($info['id_card'])){
            if (!$orSina){
                if (!sinaNameVerify($_SESSION['uid'],$_POST['realName'],$_POST['idCard'])){
                    ajaxmsg('实名认证失败',0);
                }
            }else {
                if ($orSina['real_name'] !== $_POST['realName'] || $orSina['id_card'] !== $_POST['idCard']){
                    ajaxmsg('实名认证失败',0);
                }
            }            
            $data['iphone']         = $sinaInfo['iphone']; 
            $res = M("member_info")->add($data);
            $sdata['purpose'] = $_POST['cashUse'];
            $res2 = M("borrow_apply")->where("uid = {$_SESSION['uid']} AND id = {$binfo['id']}")->save($sdata);
            if ($res && $res2 !==false){
                //会员登录风控接口
                $model = new CheckUserAction();
                $model->requestRegistApi($_SESSION['uid']);
                set_member_invite_code($_SESSION['uid']);
                $type  = getZhongan($_POST['idCard'],$sinaInfo['iphone'],$_POST['realName']);
               
                //非白名单众安风控不通过后初审拒绝
                if($sinaInfo['is_white']==0 && $sinaInfo['is_gold']==0){
                    //请求众安数据
                    $iszan = zanType($type);
                    if($iszan==0){
                        
                        $data['first_trial']       = 2;
                        $data['first_trial_time']  = time();
                        $data['mid_tree']          = 2;
                        $data['mid_tree_time']     = time();
                        $data['uid']               = $_SESSION['uid'];
                        $data['borrow_id']         = $binfo['id'];
                        M("member_status")->add($data);
                        
                        $ubdata['status']      = "98";
                        $ubdata['refuse_time'] = time();
                        delUserOperation($_SESSION['uid'],$binfo['id']);
                        M("borrow_apply")->where("uid = {$_SESSION['uid']} and id = {$binfo['id']}")->save($ubdata);
                    
                        //初审失败推送
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg2($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                        }

                        //发送App推送通知初审拒绝
                        $mwhere['uid'] =$_SESSION['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($_SESSION['uid'],$token['token'],$binfo['id']);
                        }

                        ajaxmsg('初审拒绝',2);
                    }else{
                        ajaxmsg('信息提交成功',1);
                    }
                }else{
                    //用户操作记录
                    $data['operation'] = "/Borrow/verifyUserStatus";
                    $data['orderNum']  = 3;
                    $id = M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$binfo['id']}")->save($data);
                    ajaxmsg('信息提交成功',1);
                }
                
            }
        }else {
            $data['mod_time'] = time();
            M("member_info")->where("uid = {$_SESSION['uid']}")->save($data);
            $sdata['purpose'] = $_POST['cashUse'];
            $res2 =  M("borrow_apply")->where("uid = {$_SESSION['uid']} AND id = {$binfo['id']}")->save($sdata);
            if ($res2 !== false){
                //请求众安数据
                $type  = getZhongan($info['id_card'],$sinaInfo['iphone'],$info['real_name']);
                
                //非白名单众安风控不通过后初审拒绝
                if($sinaInfo['is_white']==0 && $sinaInfo['is_gold']==0){
                    $iszan = zanType($type);
                    if($iszan==0){
                        
                        $data['first_trial']       = 2;
                        $data['first_trial_time']  = time();
                        $data['mid_tree']          = 2;
                        $data['mid_tree_time']     = time();
                        $data['uid']               = $_SESSION['uid'];
                        $data['borrow_id']         = $binfo['id'];
                        M("member_status")->add($data);

                        $ubdata['status']      = "98";
                        $ubdata['refuse_time'] = time();
                        delUserOperation($_SESSION['uid'],$binfo['id']);
                        M("borrow_apply")->where("uid = {$_SESSION['uid']} and id = {$binfo['id']}")->save($ubdata);

                        //初审失败推送
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg2($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                        }

                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $_SESSION['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($_SESSION['uid'],$token['token'],$binfo['id']);
                        }
                        ajaxmsg('初审拒绝',2);
                    }else{
                        ajaxmsg('信息已提交',1);
                    }
                }else{
                    //用户操作记录
                    $data['operation'] = "/Borrow/verifyUserStatus";
                    $data['orderNum']  = 3;
                    $id = M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$binfo['id']}")->save($data);
                    ajaxmsg('信息已提交',1);
                }
                
            }else {
                ajaxmsg('信息提交失败',0);
            }    
        }
    }*/
            
    /**
     * 插入用户基本信息(富友)
     */
    public function addUserInfo(){
        $idcardL = substr($_POST['idCard'], -1);
        if($idcardL=="x"){
            $idcard = substr($_POST['idCard'], 0,16)."X";
        }else{
            $idcard = $_POST['idCard'];
        }
        $data['id_card']            = $idcard;
        $data['real_name']          = $_POST['realName'];
        $data['register_province']  = $_POST['pro'];
        $data['register_city']      = $_POST['city'];
        $data['register_address']   = $_POST['cardInfo'];    
        $data['add_time']           = time();
        $data['uid']                = $_SESSION['uid'];
        $map['uid']                 = $_SESSION['uid'];
        $sinaInfo   = M("members")->field("sina_id,iphone,is_white,is_gold,fuiou_id")->where("id = {$_SESSION['uid']}")->find();
        $info       = M("member_info")->field('id,real_name,id_card')->where($map)->find();
        $where['uid']             = $_SESSION['uid'];
        $where['id']              = $_SESSION['bid'];
        $where['status']          = array('not in','4,5,94,95,96,97,98,99');
        $binfo = M("borrow_apply")->field('id,money,coupon_id,add_time')->where($where)->find();
        session('bid',$binfo['id']);        
        $data['iphone']           = $sinaInfo['iphone'];

        //更新用户身份信息 
        if (empty($info['real_name'])&&empty($info['id_card'])){ 
            $res = M("member_info")->add($data);
        }else{
            $data['mod_time'] = time();
            M("member_info")->where("uid = {$_SESSION['uid']}")->save($data);
        }

        $sdata['purpose'] = $_POST['cashUse'];
        $res2 = M("borrow_apply")->where("uid = {$_SESSION['uid']} AND id = {$binfo['id']}")->save($sdata);
        if($sinaInfo['fuiou_id'] != 0){
            if($res2 !==false){
                //会员登录风控接口
                $model = new CheckUserAction();
                $model->requestRegistApi($_SESSION['uid']);
                set_member_invite_code($_SESSION['uid']);
                $type  = getZhongan($_POST['idCard'],$sinaInfo['iphone'],$_POST['realName']);
                //非白名单众安风控不通过后初审拒绝
                if($sinaInfo['is_white']==0 && $sinaInfo['is_gold']==0){
                    //请求众安数据
                    $iszan = zanType($type);
                    if($iszan==0){
                        
                        $data['first_trial']       = 2;
                        $data['first_trial_time']  = time();
                        $data['mid_tree']          = 2;
                        $data['mid_tree_time']     = time();
                        $data['uid']               = $_SESSION['uid'];
                        $data['borrow_id']         = $binfo['id'];
                        M("member_status")->add($data);
                        
                        $ubdata['status']      = "98";
                        $ubdata['refuse_time'] = time();
                        delUserOperation($_SESSION['uid'],$binfo['id']);
                        M("borrow_apply")->where("uid = {$_SESSION['uid']} and id = {$binfo['id']}")->save($ubdata);
                    
                        //初审失败推送
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg2($wxInfo['openid'], $binfo['money'], $binfo['add_time'],1);
                        }

                        //发送App推送通知初审拒绝
                        $mwhere['uid'] =$_SESSION['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($_SESSION['uid'],$token['token'],$binfo['id']);
                        } 
                        ajaxmsg('初审拒绝',2);
                    }else{
                        //用户操作记录
                        $data['operation'] = "/Borrow/verifyUserStatus";
                        $data['orderNum']  = 3;
                        $id = M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$binfo['id']}")->save($data);
                        ajaxmsg('信息提交成功',1);
                    }
                }else{
                    //用户操作记录
                    $data['operation'] = "/Borrow/verifyUserStatus";
                    $data['orderNum']  = 3;
                    $id = M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$binfo['id']}")->save($data);
                    ajaxmsg('信息提交成功',1);
                }
            }
        }else{
            ajaxmsg('信息提交成功',1);
        }
    }


    /**
     * 赠送优惠券页
     */
    public function ticket(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $global = get_global_setting();
	    $ticArr = explode('|', $global['coupon_register']);
	    $money = $ticArr[0];
        $this->assign('money',$money);
        $this->display();
    }
    
    /**
     * 借款第三步,验证手机
     */
    public function verifyPhone(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $phones  = M("members")->field('iphone')->where("id = {$_SESSION['uid']}")->find();
        $where['uid']             = $_SESSION['uid'];
        if($_SESSION['bid']>0){
            $where['id']              = $_SESSION['bid'];
        }else{
            $where['id']              = $_GET['bid'];
        }
        $borrowInfo = M("borrow_apply")->field('id,uid,status,add_time,money')->where($where)->find();
        
        if($borrowInfo['id']>0){
            if($borrowInfo['status']==1){
                //有效期天数以内无需拉取运营商
                if(isRunCarrier($phones['iphone'])==0){
                    $status     = M("member_status")->field('id')->where(" borrow_id={$_SESSION['bid']} and uid = {$_SESSION['uid']} ")->find();
                    if($status['id']>0){
                        //更新状态为手机验证
                        $sdata['verify_phone']     = 1;
                        $sdata['first_trial']      = 1;
                        $sdata['first_trial_time'] = time();
                        M("member_status")->where(" borrow_id={$_SESSION['bid']} and uid = {$_SESSION['uid']} ")->save($sdata);
                    }else{
                        //更新状态为手机验证
                        $sdata['verify_phone']     = 1;
                        $sdata['first_trial']      = 1;
                        $sdata['first_trial_time'] = time();
                        $sdata['uid']              = $_SESSION['uid'];
                        $sdata['borrow_id']        = $borrowInfo['id'];
                        M("member_status")->add($sdata);
                    }
                     
                    $bdata['status']           = 2;
                    $bdata['audit_status']     = 1;
                    $res2 = M("borrow_apply")->where("id = {$borrowInfo['id']} ")->save($bdata);
            
            
                    //发送微信通知初审通过
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
                    if($wxInfo['openid']!==""){
                        sendWxTempleteMsg3($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'],1);
                    }
                    
                    //发送App推送通知初审通过
                    $mwhere['uid'] = $_SESSION['uid'];
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg($_SESSION['uid'],$token['token'],$borrowInfo['id']);
                    }

                    $data['operation'] = "/Borrow/msgCheck";
                    $data['orderNum']  = 5;
                    $id = M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$borrowInfo['id']}")->save($data);
                    
                    $this->redirect('/Borrow/msgCheck');
                }else{
                    //发送App推送通知初审拒绝
                    $mwhere['uid'] = $array['uid'];
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg2($array['uid'],$token['token'],$borrowInfo['id']);
                    }
                }
            }
        }
        
        
        $map['uid']        = $borrowInfo['uid'];
        $map['borrow_id']  = $borrowInfo['id'];
        $info              = M("member_status")->field('verify_phone')->where($map)->find();
        
        if($info['verify_phone']==1){
            $data['operation'] = "/Borrow/msgCheck";
            $data['orderNum']  = 5;
            M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$borrowInfo['id']}")->save($data);
        }

        session('bid',$borrowInfo['id']);
        $tick              = "VERIFYTEL".time().$_SESSION['uid'];
        $tickIn            = "VERIFYTELIN".time().$_SESSION['uid'];
        $this->assign('tick',$tick);
        $this->assign('tickIn',$tickIn);
        $this->assign('phone',$phones['iphone']);
        $this->assign('info',$info);
        $this->assign('borrow',$borrowInfo);
        $this->display();
    }
    
    /**
     * 输入验证码页
     */
    public function verifyPhoneTwo(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $this->display();
    }
    
    /**
     * 借款第四步,验证身份
     */
    public function verifyUserStatus(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        if (!isset($_SESSION['bid'])){
            session('bid',$_GET['bid']);
        }else{
            session('bid',$_SESSION['bid']);
        }
        
        $education  = C("EDUCATION_NAME");
        $eduCode    = C("EDUCATION_CODE");
        $marry      = C("MARRIAGE_NAME");
        $marryCode  = C("MARRIAGE_CODE");
        $incomeInfo = C("YEAR_INCOME");
        $workInfo   = C("POSITION_NAME");
        $companyJob = C("CAREER_NAME_LIST");
        $companyPosition = C("COMPANY_INDUSTRY");
        $companyInfo = C("CAREER_NAME");
        $relation   = C("SOCIAL_NAME");
        $relationCode= C("SOCIAL_CODE");
        $exp        = C("WORK_TIME");
        $house      = C("HOUSE");
        $houseType  = C("HOUSE_TYPE");
        $debt       = C("DEBT_LIST");
        $province   = M("province")->field('*')->select();
        
        $this->assign('debt',json_encode(array_values($debt)));
        $this->assign('province',$province);
        $this->assign('house',json_encode(array_values($house)));
        $this->assign('houseType',json_encode(array_values($houseType)));
        $this->assign('exp',json_encode(array_values($exp)));//工作时间
        $this->assign('companyInfo',json_encode(array_values($companyInfo)));//公司性质
        $this->assign('companyPosition',json_encode(array_values($companyPosition)));//公司行业
        $this->assign('companyJob',json_encode(array_values($companyJob)));//公司职位
        $this->assign('incomeInfo',json_encode(array_values($incomeInfo)));//收入
        $this->assign('workInfo',json_encode(array_values($companyJob)));//职业类型
        $this->assign('education',json_encode(array_values($education)));//学历
        $this->assign('marry',json_encode(array_values($marry)));//婚姻状况
        $arr = array_chunk($relation, 5,true);
        $this->assign('relation',json_encode(array_values($arr[1])));//社会关系
        $this->assign('family',json_encode(array_values($arr[0])));//家庭关系
        $uid = $_SESSION['uid'];
        $info = M("member_info")->field('id_card,real_name')->where("uid = $uid")->find();
        $info['id_card'] = substr_replace($info['id_card'],'********',6,8);
        
        $member_info   = M("member_info")->field('*')->where("uid = $uid")->find();
        $company_info  = M("member_company")->field('*')->where("uid = $uid")->find();
        $relation_info = M("member_relation")->field('*')->where("uid = $uid")->find();
        $user          = M("members")->field("fuiou_id,usr_attr")->where("id = {$uid}")->find();
        $tick   = "USERINFO".time().$uid;
        $tickIn = "USERINFOIN".time().$uid;
        $member_info['education']   = $education[array_search($member_info['education'], $eduCode)];
        $member_info['marriage']    = $marry[array_search($member_info['marriage'], $marryCode)];
        $relation_info['relation1'] = $relation[array_search($relation_info['relation1'], $relationCode)];

        $checkGrant = checkGrant($uid,$user['usr_attr'],$user['fuiou_id']);//判断用户是否授权
        $this->assign('checkGrant',$checkGrant);
        $this->assign('memberInfo',$member_info);
        $this->assign('companyInfo',$company_info);
        $this->assign('relationInfo',$relation_info);
        $this->assign('tick',$tick);
        $this->assign('tickIn',$tickIn);
        $this->assign('info',$info);
        $this->display();
    }

    /**
     * 授信页面(流程)
     */
    public function shouxin(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $bid = session('bid');
        if(empty($bid)){
            $this->redirect('/Member/currborrow');  
        }
        //判断是否缴纳授信费
        $bidwhere['borrow_id'] = $bid;
        $is_recheck = M('member_status')->where($bidwhere)->field('is_recheck')->find();
    
        if($is_recheck['is_recheck'] == 1){
            $this->redirect('/Borrow/msgcheck');
        }else if(empty($is_recheck)){
            $this->redirect('/Borrow/msgcheck');
        }
    
        S('global_setting',NULL);//测试
        $global   = get_global_setting();
        $amount   = $global['credit_amount'];//原价
        $discount = $global['credit_discount'];//折扣价
    
        $this->assign('credit_amount',$amount);
        $this->assign('bid',$bid);
        $this->assign('credit_discount',$discount);
        $this->display();
    }
    

    /**
     * 授信支付页
     */
    public function shouxinPay(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        
        $uid = $_SESSION['uid'];

        //判断是否缴纳授信费
        $bidwhere['borrow_id'] = session('bid');
        $is_recheck = M('member_status')->where($bidwhere)->field('is_recheck')->find();
    
        if($is_recheck['is_recheck'] == 1){
            $this->redirect('/Borrow/msgcheck');
        }else if(empty($is_recheck)){
            $this->redirect('/Borrow/msgcheck');
        }

        $bankCard = M("member_bank")->field('bank_card,id')->where("uid = $uid and type=2")->select();
        foreach ($bankCard as $row){
            $row['bank_card'] = "尾号(".substr($row['bank_card'], -4).")";
            $data[] = $row;
        }

        $amount = $_POST['amount'];
        
        $this->assign('bid',session('bid'));
        $this->assign('money',$amount);
        $this->assign('bankcard',$data);
        $this->display();
    }
    
    /**
     * 授信支付
     */
    public function payCredit(){
        $array   = $_POST;
        $uid     = $_SESSION['uid'];
        $bid     = $_SESSION['bid'];
        $bankId  = $array['bankId'];
        $status  = M("member_status")->field(true)->where("uid = {$uid} and borrow_id = {$bid}")->find();
        if($status['signed']==1){
            if($status['is_recheck']==0){
                $global   = get_global_setting();
                $amount   = $global['credit_amount'];
                $discount = $global['credit_discount'];
                if($discount==0){
                    $total = $amount;
                }else{
                    $total = $discount;
                }
                wqblog('授信支付金额'.$total);
                //宝付授信支付
                $model = new PaymentAction();
                $res   = $model->creditApi($uid, $bid, $total ,$bankId);
                wqblog('授信支付结果'.$res);
                //$res = true;
                if(is_bool($res) && $res){
                    //走拍拍信风控
                    $isPpc = isPpc($uid);
                    //风控拍拍信验证
                    if($isPpc==1){
                        $updata['is_ppc']     = 1;
                        $updata['ppc_time']   = time();
                        $isIdVerify = isIdVerify($uid);
                        //人脸免验证检查
                        if($isIdVerify == 1){
                            $updata['id_verify']        = 1;
                            $updata['id_verify_time']   = time();
                            //人脸识别成功后自动复查通过
                            $auto_review = $global['auto_review'];
                            if($auto_review==1){
                                $updata['is_review']    = 1;
                                $updata['review_time']  = time();
                                //复查通过后自动上标到福米金融
                                $is_aotu = $global['is_aotu_bid'];
                                if($is_aotu == 1){
                                    $upbid = createFumiBid($bid,0);
                                }
                            }
                        }
                    }else{
                        $updata['is_ppc']     = 2;
                        $updata['ppc_time']   = time();
                    }
                    $updata['is_recheck']   = 1;
                    $updata['recheck_time'] = time();
                    $updata['calm']         = 1;
                    $updata['calm_time']    = time();
                    $up = M("member_status")->where("uid = {$uid} and borrow_id = {$bid}")->save($updata);
                        
                    if($isPpc==1){
                        ajaxmsg('支付成功',1);
                    }else{
                        //更新状态
                        $data['status']          = 95;
                        $data['refuse_time']     = time();
                        M('borrow_apply')->where("id={$bid} and uid = {$uid}")->save($data);
                        //删除option信息
                        delUserOperation($uid,$bid);
                        //发送App推送通知复审失败
                        $mwhere['uid'] = $uid;
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg3($uid,$token['token'],$bid);
                        }
                        ajaxmsg('评分不足',2);
                    }
                }else{
                    ajaxmsg('支付失败',0);
                }
            }else{
                ajaxmsg('请刷新借款状态',0);
            }
        }else{
            ajaxmsg('请先去签约',0);
        }
    }

    /**
     * 授信微信支付
     */
    public function paycrWechat(){
        $uid     = $_SESSION['uid'];
        $bid     = $_SESSION['bid'];
        $status  = M("member_status")->field(true)->where("uid = {$uid} and borrow_id = {$bid}")->find();
        $appply  = M("borrow_apply")->field('item_id')->where("id = {$bid}")->find();
        if($status['signed']==1){
            if($status['is_recheck']==0){
                $global   = get_global_setting();
                $amount   = $global['credit_amount'];
                $discount = $global['credit_discount'];
                if($discount==0){
                    $total = $amount;
                }else{ 
                    $total = $discount;
                }
                wqblog('授信支付金额'.$total);

                //微信授信支付
                $res = wechatPay($uid,$bid,0,$total,3,1);
                wqblog('授信支付结果'.$res);
                
                if($res){
                    ajaxmsg($res,1);//返回支付二维码url
                }
            }else{
                ajaxmsg('请刷新借款状态',0);
            }
        }else{
            ajaxmsg('请先去签约',0);
        }
    }

    /**
     * 授信其他银行卡付款
     */
    public function shouxinOthercard(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $bankList = C("SINA_BANK_NAME");
        $length = count($bankList);
        for ($i=1 ; $i<$length;$i++){
            $str .= "<option value='{$bankList[$i]}'>".$bankList[$i]."</option>";
        }
        $this->assign('str',$str);
        $this->display(); 
    }

    /**
     * 授信报告书 
     */
    public function shouxinReport(){
        $uid = $_SESSION['uid'];
        if(empty($uid)){
            $uid = $_GET['uid'];
        }
        if(empty($uid)){
            $this->redirect('/Member/regist');
        }

        $map['uid'] = $uid;
        $info = getPpcrate($uid);
        $this->assign('info',$info);
        $this->display();
    }
    /**
     * 获取二级城市
     */
    public function getCity(){
        $id = $_POST['id'];
        $city = M("city")->field('id,city')->where("province_id = $id")->select();
        $str = "";
        foreach ($city as $row){
            $cityId = $row['id'];
            $cityName = $row['city'];
            $str.="<option value='{$cityId}'>{$cityName}</option>";
        }
        ajaxmsg($str,1);
    }
    
    /**
     * 插入用户详细数据
     */
    public function addAll(){
        $bid   = $_SESSION['bid'];
        $apply = M("borrow_apply")->field(true)->where("id = {$bid} ")->find();
        session('bid',$bid);
        if($apply['status']!=0){
            ajaxmsg('请刷新后确认借款进度',0);
        }else{
            $education       = C("EDUCATION_NAME");
            $eduCode         = C("EDUCATION_CODE");
            
            $marry           = C("MARRIAGE_NAME");
            $marryCode       = C("MARRIAGE_CODE");
            
            $house           = C("HOUSE");
            $houseType       = C("HOUSE_TYPE");
            
            $workInfo        = C("CAREER_NAME_LIST");
            
            $companyJob      = C("CAREER_NAME_LIST");
            $companyPosition = C("COMPANY_INDUSTRY");
            
            $companyInfo     = C("CAREER_NAME");
            
            $relation        = C("SOCIAL_NAME");
            $relationCode    = C("SOCIAL_CODE");
            $exp             = C("WORK_TIME");
            $debt            = C("DEBT_LIST");
            
            $map['uid']      = $_SESSION['uid'];
            //个人信息
            $infoData['uid']        = $_SESSION['uid'];
            $infoData['qq_code']    = $_POST['qq'];//QQ
            $infoData['education']  = $eduCode[array_search($_POST['education'], $education)];//学历
            $infoData['marriage']   = $marryCode[array_search($_POST['marry'], $marry)];//婚姻状态
            $infoData['email']      = $_POST['email'];//邮箱
            $infoData['province']   = $_POST['province2'];//常住地址
            $infoData['city']       = $_POST['city2'];//常住地址
            $infoData['address']    = $_POST['address'];//常住地址
            $infoData['house']      = $_POST['house'];
            $infoData['house_type'] = $_POST['house_type'];
            $memberInfo = M("member_info")->field('id')->where($map)->find();
            //公司信息
            $companyData['job_title']       = $_POST['job'];//职业类型
            $companyData['year_income']     = $_POST['salary'];
            $companyData['company_name']    = $_POST['companyName'];
            $companyData['company_province']= $_POST['province'];
            $companyData['company_city']    = $_POST['city'];
            $companyData['company_address'] = $_POST['addressInfo'];
            $companyData['company_tel']     = $_POST['tel_phone'];
            $companyData['job_time']        = $_POST['job_time'];
            $companyData['debt']            = $_POST['debt'];
            $memberCompany = M("member_company")->field('id')->where($map)->find();
            //社会信息
            $relationData['relation1']  = $relationCode[array_search($_POST['family'], $relation)];
            $relationData['name1']      = $_POST['relationName'];
            $relationData['iphone1']    = $_POST['familyPhone'];
            $relationData['name2']      = $_POST['relation'];
            $relationData['iphone2']    = $_POST['relationPhone'];
            $relationData['name3']      = $_POST['friend'];
            $relationData['iphone3']    = $_POST['friendPhone'];
            //接收亲属姓名
            $relationData['relationName'] = $_POST['relationName'];
            $memberRelation = M("member_relation")->field('id')->where($map)->find();
            if (empty($memberInfo)){
                $infoData['uid']      = $_SESSION['uid'];
                $infoData['add_time'] = time();
                $res = M("member_info")->add($infoData);
            }else {
                $infoData['mod_time'] = time();
                $res = M("member_info")->where($map)->save($infoData);
            }
            if (empty($memberCompany)){
                $companyData['uid']    = $_SESSION['uid'];
                $companyData['add_time']   = time();
                $res2 = M("member_company")->add($companyData);
            }else {
                $companyData['mod_time'] = time();
                $res2 = M("member_company")->where($map)->save($companyData);
            }
            if (empty($memberRelation)){
                $relationData['uid']    = $_SESSION['uid'];
                $relationData['add_time'] = time();
                $res3 = M("member_relation")->add($relationData);
            } else {
                $relationData['mod_time'] = time();
                $res3 = M("member_relation")->where($map)->save($relationData);
            }
            if ($res&&$res2&&$res3){
                //默认芝麻授权通过
                updateMemStatus($_SESSION['uid'],$bid);
                $uid = $_SESSION['uid'];
                $members = M("members")->field(true)->where("id = {$uid}")->find();
                //白骑士策略
                $model    = new  CheckUserAction();
                //灰名单用户
                if($members['is_gray']==1){
                    $checkRes = $model->requestApi($members['id'], $bid,5);//单独白骑士策略
                }else if($members['is_gold']==0&&$members['is_white']==0&&$members['is_black']==0){
                    $checkRes = $model->requestApi($members['id'], $bid,5);//单独白骑士策略
                }else if($members['is_gold']==1 || $members['is_white']==1){
                    $checkRes = 1;
                }else if($members['is_black']==1){
                    $checkRes = 0;
                }
                if($checkRes==0){
                    $mid_tree = 2;
                }else{
                    $mid_tree = 1;
                    $status   = mallRisk($_SESSION['uid']);//年龄、职业、地区审核
                    if($status==0&&$members['is_white']==0&&$members['is_gold']==0){
                        $checkRes = 0;
                    }
                }
                if($members['is_black']==1){
                    $bdata['status']      = 98;
                    $bdata['refuse_time'] = time();
                    M("borrow_apply")->where("id = {$bid}")->save($bdata);
                    
                    $data['first_trial']       = 2;
                    $data['first_trial_time']  = time();
                    $data['mid_tree']          = $mid_tree;
                    $data['mid_tree_time']     = time();
                    $data['uid']               = $_SESSION['uid'];
                    $data['borrow_id']         = $bid;
                    M("member_status")->add($data);
                    
                    //发送微信推送通知初审拒绝
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
                    if($wxInfo['openid']!==""){
                        sendWxTempleteMsg2($wxInfo['openid'], $apply['money'], $apply['add_time'],1);
                    }
                    //发送App推送通知初审拒绝
                    $mwhere['uid'] = $members['id'];
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg2($members['id'],$token['token'],$bid);
                    }
                    
                    delUserOperation($_SESSION['uid'],$bid);
                    ajaxmsg('审核不通过',2);
                }else{
                    if($checkRes==0 && $members['is_white']==0 && $members['is_gold']==0){
                        $bdata['status']      = 98;
                        $bdata['refuse_time'] = time();
                        M("borrow_apply")->where("id = {$bid}")->save($bdata);
                        
                        $data['first_trial']       = 2;
                        $data['first_trial_time']  = time();
                        $data['mid_tree']          = $mid_tree;
                        $data['mid_tree_time']     = time();
                        $data['uid']               = $_SESSION['uid'];
                        $data['borrow_id']         = $bid;
                        M("member_status")->add($data);
                        
                        //发送微信推送通知初审拒绝
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg2($wxInfo['openid'], $apply['money'], $apply['add_time'],1);
                        }
                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $members['id'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($members['id'],$token['token'],$bid);
                        }
                        
                        delUserOperation($_SESSION['uid'],$bid);
                        ajaxmsg('审核不通过',2);
                    }else{
                        $bdata['status']       = 1;
                        $bdata['audit_status'] = 1;
                        M("borrow_apply")->where("id = {$bid}")->save($bdata);
                        ajaxmsg('提交申请成功',1);
                    }
                }
            }else {
                ajaxmsg('提交申请失败',0);
            }
        }      
    }
    
    
    /**
     *借款第五步，等待审核 
     */
    public function msgCheck(){
        $web    = C('WEBSITE_URL');
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $where['uid']             = $_SESSION['uid'];
        if($_SESSION['bid']>0){
            $where['id']          = $_SESSION['bid'];
        }else{
            $where['id']          = $_GET['bid'];
        }
        session('bid',$where['id']);
        $borrowInfo = M("borrow_apply")->field('id,status')->where($where)->find();
        if ($borrowInfo['status']<5){
            if ($borrowInfo['status'] == 4){
                $this->redirect('/Repayment/index');
            }
        }else if($borrowInfo['status'] == 96){
            session('bid',"");
            delUserOperation($_SESSION['uid'],$_SESSION['bid']);
            $this->redirect('/borrow/loanFalse');
        }else if($borrowInfo['status'] == 99){
                session('bid',"");
                delUserOperation($_SESSION['uid'],$_SESSION['bid']);
                $this->redirect('/borrow/index');
        }else if($borrowInfo['status'] == 94){
                session('bid',"");
                delUserOperation($_SESSION['uid'],$_SESSION['bid']);
                $this->redirect('/borrow/refuse');
        }else{
            session('bid',"");
            delUserOperation($_SESSION['uid'],$_SESSION['bid']);
            $this->redirect('/borrow/refuse');
        }
        $map['uid']       = $_SESSION['uid'];
        $map['borrow_id'] = $borrowInfo['id'];
        $info       = M("member_status")->field(true)->where($map)->find();
        $memberInfo = M("member_info")->field("id,id_card,real_name")->where("uid = {$_SESSION['uid']}")->find();
        
        if($info['first_trial']==1){//初审通过
            if ($info['bank_bing'] ==1){//绑卡通过
                if($info['signed'] == 1){//签约通过
                    if($info['calm'] ==1){//冷静期通过
                        if($info['is_recheck'] == 1){//授信通过
                            if($info['id_verify'] ==1){//身份确认通过
                                if($info['is_review'] == 1){
                                    $msg = "资金筹集中";
                                }else{
                                    $msg = "待复审";
                                }
                            }else{
                                $msg = "身份确认";
                                $realurl = "/Borrow/face"; 
                                $this->assign('out',0);
                            } 
                        }else{
                            $msg = "身份确认";
                            $realurl = "/Borrow/shouxin";
                        }
                    }else{
                        $mem_status['uid']       = $_SESSION['uid'];
                        $mem_status['borrow_id'] = $_SESSION['bid'];
                        $time = M("member_status")->field('id,signed_time')->where($mem_status)->find();
                        if($time['id']>0){
                            $datag            = get_global_setting();
                            $chill_time       = $datag['calm_time'];
                            $counttime        = $chill_time*60*60;
                            $countDownTime    = $counttime + (int)$time['signed_time'];
                            $now              = time();
                            $this->assign("current_time", $now);
                            $this->assign("countDownTime", $countDownTime);
                            $this->assign("chill_time", $chill_time);
                            if($now<$countDownTime){
                                $msg = "需要冷静";
                            }else{
                                $realurl = "/Borrow/calmNew";
                                $this->assign('out',0);
                                $msg = "我已冷静，申请放款";
                            }
                            
                        }else{
                            $this->redirect('/Member/currborrow');
                        }
                    } 
                }else{
                    $msg = "等待签约";
                    $realurl = "/Borrow/sign";
                    $this->assign('out',0);
                }
                    
                /*
                    $msg = "芝麻认证";
                    $this->assign('out',1);                       
                    $openid = M("members")->field('zhima_openid')->where("id = {$_SESSION['uid']}")->find();
                    if (!empty($openid['zhima_openid'])){
                        $res = bqszhimaSearch($openid['zhima_openid']);
                        if ($res == "false"){
                            $callUrl = $web."/baiqishiId/".$borrowInfo['id'].".html";
                            $realurl = bqszhima($memberInfo['id_card'], $memberInfo['real_name'], "app", 

$callUrl);
                        }else {
                            $realurl = "/borrow/dealAcceptUser?uid={$_SESSION['uid']}&type=1&bid={$borrowInfo

['id']}";
                        }
                    }else {
                        $callUrl = $web."/baiqishiId/".$borrowInfo['id'].".html";
                        $realurl = bqszhima($memberInfo['id_card'], $memberInfo['real_name'], "app", $callUrl);
                    }
                    
                } */             
             }else {
                 $msg = "确认银行卡 ";
                 $realurl = "/Borrow/bindBankCard";
                 $this->assign('out',0);
            }
        }else {
             $msg = "等待初审";
        }
        
        //判断是否缴纳授信费
        $bidwhere['borrow_id'] = $where['id'];
        $is_recheck = M('member_status')->where($bidwhere)->field('is_recheck')->find();
        
        $isIdVerify = isIdVerify($_SESSION['uid']);
        $this->assign('isIdVerify',$isIdVerify);
        $zmTick = "ZHIMA".time().$_SESSION['uid'];
        $alTick = "ALIPAY".time().$_SESSION['uid'];
        $this->assign('is_recheck',$is_recheck['is_recheck']);
        $this->assign('zmTick',$zmTick);
        $this->assign('alTick',$alTick);
        $this->assign('infoId',$memberInfo['id']);
        $this->assign('info',$info);
        $this->assign('msg',$msg);
        $this->assign('realurl',$realurl);
        $this->display();
    }

    /**
     * 检查是否可以授信支付(流程页)
     */
    public function checkUserShouxin(){
        //(暂时废弃)
        /*$uid = $_SESSION['uid'];
        $demo = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['bid']} and scene = 3 and status = 0")->count('id');
        if(!empty($demo)){
            ajaxmsg('请等待',1);//支付处理中
        }else{
            $demo2 = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['bid']} and scene = 3 and status = 1")->count('id');
            if(!empty($demo2)){
                $status = M("member_status")->where("borrow_id = {$_POST['bid']}")->field('is_recheck')->find();
                if($status['is_recheck'] == 1 ){
                    $url = "/Borrow/face";
                }else{
                    $url = "/Borrow/shouxin";
                }
            }else{
                $url = "/Borrow/shouxin";
            }
            ajaxmsg($url,2); //有处理结果
        }*/
        //判断跳转地址
        $status = M("member_status")->where("borrow_id = {$_POST['bid']}")->field('is_recheck,id_verify')->find();
        if($status['is_recheck'] == 1 && $status['id_verify'] == 0){
            $url = "/Borrow/face";
        }else{
            $url = "/Borrow/shouxin";
        }
        ajaxmsg($url,2);
    }

    /**
     * 检查是否可以授信支付(页面)
     */
    public function checkUserShouxin2(){
        $uid = $_SESSION['uid'];
        $demo = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['bid']} and scene = 3 and status = 0")->count('id');
        if(!empty($demo)){
            ajaxmsg('请等待',1);//支付处理中
        }else{
            $demo2 = M("transfer_order_pay")->where("uid = {$uid} and borrow_id = {$_POST['bid']} and scene = 3 and status = 1")->count('id');
            if(!empty($demo2)){
                $status = M("member_status")->where("borrow_id = {$_POST['bid']}")->field('is_recheck')->find();
                if($status['is_recheck'] == 1){
                    ajaxmsg('此单已经支付完成,清勿重复提交',2);
                }else{
                    ajaxmsg('',0);
                }
            }else{
                ajaxmsg('',0);
            }
        }
    }


    /**
     * 处理授权过用户(老的已经授权过的用户)
     */
    public function dealAcceptUser(){
        $flg = 1;
        if(intval($_GET['type'])==1){
            $sdata['zhima_auth'] = 1;
            $sdata['zhima_time'] = time();
            $map['uid']          = intval($_GET['uid']);
            $map['borrow_id']    = intval($_GET['bid']);
            $res      = M("member_status")->where($map)->save($sdata);
    
            $cinfo    = M("borrow_apply")->field(true)->where("uid = {$_GET['uid']} and id = {$_GET['bid']}")->find();
    
            $model    = new  CheckUserAction();
            if (isWhite($_GET['uid'])==1){//白名单用户
                $checkRes = $model->requestApi($_GET['uid'],$_GET['bid'],5);//白骑士一般策略
                $udata['tree'] = "0";
                M("member_status")->where("uid = {$_GET['uid']} and borrow_id = {$_GET['bid']}")->save($udata);
    
            }else{
                if(isGray($_GET['uid'])==1){//灰名单用户
                    $checkRes = $model->requestApi($_GET['uid'],$_GET['bid'],5);//白骑士灰名单策略
                    $status   = mallRisk($_GET['uid']);//年龄、职业、地区审核
                    $reszhima = mallZhima($_GET['uid'],2);//灰名单芝麻分审核
                }else{
                    $checkRes = $model->requestApi($_GET['uid'],$_GET['bid'],5);//白骑士一般策略
                    $status   = mallRisk($_GET['uid']);//年龄、职业、地区审核
                    $reszhima = mallZhima($_GET['uid'],1);//一般用户芝麻分审核
                }
                if(isBlack($_GET['uid'])==1){//黑名单用户
                    $flg = 0;
                    wqbLog("会员".$_GET['uid']."为黑名单用户直接初审拒绝！");
                }else{
                    if ($checkRes == 1){
                        if ($status == 0 || $reszhima==0){
                            $flg = 0;
                            wqbLog("会员".$_GET['uid']."年龄、职业、地区或者芝麻分拒绝！");
                        }
                    }else{
                        $flg = 0;
                        wqbLog("会员".$_GET['uid']."白骑士拒绝！");
                    }
                }
    
                if($flg==0){
                    if ($cinfo['coupon_id'] > 0){
                        $cstatus['status'] = 0 ;
                        M("member_coupon")->where("id = {$cinfo['coupon_id']}")->save($cstatus);
                    }
                    $udata['tree'] = "3";
                    M("member_status")->where("uid = {$_GET['uid']} and borrow_id = {$_GET['bid']}")->save($udata);
                    $ubdata['refuse_time'] = time();
    
                    $ubdata['status']      = "98";
                    delUserOperation($_GET['uid']);
                    M("borrow_apply")->where("uid = {$_GET['uid']} and id = {$_GET['bid']}")->save($ubdata);
    
                    //初审失败推送
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_GET['uid']}")->find();
                    if($wxInfo['openid']!==""){
                        sendWxTempleteMsg2($wxInfo['openid'], $cinfo['money'], $cinfo['add_time'],1);
                    }

                    //发送App推送通知初审拒绝
                    $mwhere['uid'] = $_GET['uid'];
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg2($_GET['uid'],$token['token'],$_GET['bid']);
                    }
                }
            }
            if(isset($_GET['plat'])){
                $this->redirect('/Borrow/idsure?plat=app&bid='.$_GET['bid']);
            }else {
                $this->redirect('/Borrow/idsure?plat=zhima&bid='.$_GET['bid']);
            }
        }else {
            // 芝麻授权失败
            if(isset($_GET['plat'])){
                $this->redirect('/Borrow/idfalse?plat=app');
            }else {
                $this->redirect('/Borrow/idfalse?plat=zhima');
            }
        }
    
    }
    
       
    /**
     * 取消申请
     */
    public function delOrders(){
        $data['id'] = $_POST['id'];
        $apply      = M("borrow_apply")->field(' id,uid,coupon_id,money,add_time,duration ')->where("id = {$_POST['id']} and status < 4 ")->find();
        $detail     = M("borrow_detail")->field(' id ')->where(" borrow_id = {$_POST['id']} ")->find();
        if($detail['id']>0){
            ajaxmsg('已经放款无法取消',0);
        }else{
            $flg        = $apply['status'];
            if($apply['id']>0){
                $status      = M("member_status")->field('id,calm,first_trial,is_recheck')->where("borrow_id = {$_POST['id']} ")->find();
                if($apply['status']==3&&$status['is_recheck']==1){
                    ajaxmsg('已经确认等待放款无法取消',0);
                }
                if($status['is_recheck']==1){
                    ajaxmsg('已经支付授信费无法取消',0);
                }
                if($status['id']>0&&$status['first_trial']==0){
                    ajaxmsg('验证手机处理中无法取消'.$_POST['id'],0);
                }else{
                    $data['status'] = 99;
                    $save = M("borrow_apply")->save($data);
                    if($save !== false){
                        if (!empty($apply['coupon_id'])){
                            $sdata['status'] = 0;
                            $sdata['id']     = $apply['coupon_id'];
                            M("member_coupon")->save($sdata);
                        }
                        M("user_operation")->where("uid = {$_SESSION['uid']} AND borrow_id = {$_POST['id']}")->delete();
                        session('bid','');
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg4($wxInfo['openid'], $apply['money'], $apply['add_time'], $apply['duration']);
                        }
                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $apply['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg9($apply['uid'],$token['token'],$_POST['id']);
                        }
                        ajaxmsg('取消成功',1);
                    }else {
                        ajaxmsg('取消失败',0);
                    }
                }
            }else{
                ajaxmsg('该借款申请不能取消，请及时确认申请进度',0);
            }
        }
        
    }
    
    /**
     * 绑定银行卡页面
     */
    public function bindBankCard(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        
        $province   = M("province")->field(true)->select();
        $this->assign('province',$province);
        
        $uid = $_SESSION['uid'];
        $memberInfo = M("members")->field('sina_id,iphone,fuiou_id')->where("id = $uid")->find();       
        $info = M("fuiou_user_bank")->field('*')->where("uid = {$uid}")->find();
        $code   = city_Code($info['city_code'],$info['parent_bank_code']);
        $info['city'] = $code['city'];//市
        $info['province']  = $code['province'];//省
        $info['bank_name'] = $code['bank_name'];//行别
        if($res >0){
            if($info['bank_id']==$res){
                $bankFlg = 1;
            }else{
                $bankFlg = 0;
            }
        }
        $this->assign('info',$info);
        $bankList = C("SINA_BANK_NAME");
        $length = count($bankList);
        for ($i=1 ; $i<$length;$i++){
            $str .= "<option value='{$bankList[$i]}'>".$bankList[$i]."</option>";
        }
        $tick = "BINDBANK".time().$uid;
        $tickIn = "BINDBANKIN".time().$uid;
        $this->assign('iphone',$memberInfo['iphone']);
        $this->assign('fuiou_id',$memberInfo['fuiou_id']);
        $this->assign('tick',$tick);
        $this->assign('tickIn',$tickIn);
        $this->assign('old',$old);
        $this->assign('bankFlg',$bankFlg);
        $this->assign('str',$str);
        $this->display();
    }
    
    /**
     * 新浪绑卡(新银行卡绑定)
     */
    public function bindBank(){
        $code    = C("SINA_BANK_CODE");
        $message = sinaBindBankCard($_POST['ticket'],$_POST['verifyCode']);
        $where['uid']             = $_SESSION['uid'];
        $where['status']          = array('lt',4);
        $borrowInfo = M("borrow_apply")->field('id,status,audit_status,coupon_id')->where($where)->find();
        if (is_bool($message)){           
            $data['uid']           = $_SESSION['uid'];
            $data['bank_card']     = $_POST['bankCard'];
            $data['bank_code']     = array_search($_POST['bankCode'], $code);
            $data['bank_name']     = $_POST['bankCode'];
            $data['bank_province'] = $_POST['province'];
            $data['bank_city']     = $_POST['city'];
            $data['bank_id']       = getSinaBindBankCardId($_SESSION['uid']);
            $data['type']          = '1';
            $res = M("member_bank")->add($data);
            $members = M("members")->field('id,is_white,is_gray,is_black')->where("id = '{$_SESSION['uid']}'")->find();
            if($res){
                $map['uid']              = $_SESSION['uid'];
                $map['borrow_id']        = $borrowInfo['id'];
                $sdata['bank_bing']      = 1;
                $sdata['bank_bing_time'] = time();
                $result = M("member_status")->where($map)->save($sdata);
                
                $bdata['audit_status'] = 2;
                M("borrow_apply")->where("id = {$borrowInfo['id']}")->save($bdata);
                
                if ($result!==false){
                    ajaxmsg('绑卡成功',1);
                }else {
                    ajaxmsg('绑卡失败',0);
                }
            }
        }else {
            ajaxmsg($message,0);
        }   
    }

    /**
     * 获取绑卡验证码
     */
    public function getCode() {
        $bankCode = C("SINA_BANK_CODE");
        $code = array_search($_POST['bankCode'], $bankCode);
        $uid = $_SESSION['uid'];
        $data = sinaGetBindBankCode($uid, $code, $_POST['bankCard'], $_POST['phone'], $_POST['province'], $_POST['city']);
        if ($data['status'] == 1){
            ajaxmsg($data['message'],1);
        }else{
            if($data['message'] =="验签未通过"){
                ajaxmsg("网络有异常请重试一下",0);
            }else{
                ajaxmsg($data['message'],0);
            }
        }
    }
    
    /**
     * 获取新浪解绑银行卡验证码
     */
    public function getUnBindBankCardCode(){
        $uid    = $_SESSION['uid'];
        $cardId = getSinaBindBankCardId($uid);
        $res    = sinaUnBindBankCard($uid,$cardId);
        if ($res['status']){
            ajaxmsg($res['ticket'],1);
        }else {
            ajaxmsg($res['message'],0);
        }
    }
    
    /**
     * 解绑银行卡第二步
     */
    public function unBindBankCard(){
        $uid    = $_SESSION['uid'];
        $ticket = $_POST['ticket'];
        $code   =$_POST['code'];
        $where['uid']             = $_SESSION['uid'];
        $where['status']          = array('not in','5,93,94,95,96,97,98,99,0');
        $borrowInfo = M("borrow_apply")->field('id')->where($where)->find();
        $res = unBindSinaBankCard($ticket, $code, $uid);
        if ($res){
            M("member_bank")->where("uid = $uid")->delete();
            $data['bank_bing'] = 0;
            M("member_status")->where("uid = {$_SESSION['uid']} and borrow_id = {$borrowInfo['id']}")->save($data);
            ajaxmsg('解绑成功',1);
        }else {
            ajaxmsg('解绑失败',0);
        }
    }
    
    /**
     * 用户确认绑卡(老用户走绑定银行卡)
     */
    public function checkBankCard(){
        $bid            = $_SESSION['bid'];
        $uid            = $_SESSION['uid'];
        
        $where['uid']   = $uid;
        $where['id']    = $bid;
        $info = M("borrow_apply")->field('id,status,audit_status,coupon_id')->where($where)->find();
        if($info['audit_status'] <2){
                $map['uid']              = $uid;
                $map['borrow_id']        = $bid;
                $sdata['bank_bing']      = 1;
                $sdata['bank_bing_time'] = time();
                $result = M("member_status")->where($map)->save($sdata);
                
                $bdata['audit_status']   = 2;
                M("borrow_apply")->where("id = {$bid}")->save($bdata);
                
                if ($result!==false){
                    ajaxmsg('绑卡成功',1);
                }else {
                    ajaxmsg('绑卡失败',0);
                }
        }else{
            ajaxmsg('请确认借款申请的进度',0);
        }
    }
    
    /**
     * 人脸识别
     */
    public function creditSeameAuth($uid,$bid){ 
        $web    = C('AUTH_ALIPAY_URL');
        $userInfo = M("member_info")->field('id,id_card,real_name')->where("uid = {$uid}")->find();
        $str = strtoupper("FUMIJINRONG".date('YmdHis',time()));
        vendor('Alipay.AopSdk');
        $aop = new \AopClient ();
        $aop->gatewayUrl        = 'https://openapi.alipay.com/gateway.do';//正式https://openapi.alipay.com/gateway.do
        $aop->appId             = '2017031406219300';//正式2017031406219300
        //$aop->rsaPrivateKey 	= 'MIIEpAIBAAKCAQEA6xckANrm0rBODlNmFyjDfsEihKwkhGT/AFQ2reW4tsF8jv2in6yTkAKbvJ23rOb/NwFRvjKfq6i8uJs3FLYn4+Zjm2gx4ZdKI39CmTffV7EYJxrPehFalycQIPHWcbYJ9dh3zVGHfWK4r5zRs8u8+wjtWTU79VxMrOLfb+6ATVVgYxpkcqausT5TNwr19dZ2yvPG3JlKIBNMjMAHxylcnFzXGWuxBeG+Vo3cBuLxNXCbdywYN0rr7nu4BWM1YeQwXLf6xSGA4Xsfar0vIJqyHmpDoMInyaZMAYhJSsbbzKXyMNK9CpzmFP5F1yU6uXqTTjJCYpDTFYMBmcO75pYl8QIDAQABAoIBABjfi+mjP4TPLpMJc+XDZFvG53YOGJcBcJGCV36yrYZ4lksyvsASLFKyU/Py4/ohPqN9Oj7bcFVjNwR5N6yzUSkluSg6L/zkWu3CizNW3ASVMi8BAS6zI1iWw3gY3k2NqyQd0R8iHym/BjdeajmRtt8ApMwpe7yMFXi9UFKxc1W7HNDKRM3gsAHjvz3orjyoprP9jjtG8lvgumSDBd4GcyE9p/Z35A1NfkQJznK4ZLxOXWhMzzV3hbVq1XMly6Q2gAZMs/GWMAYAX1SCRbcVoPqNbxoV9q/kGnvWuEgYvcOzD7Mh5SQ2mORJRfvi7sZWDG9JsobDPl0xsIS9nrz4FaECgYEA/6Cc0m5NdGWDQyfl3LYAfNI6fZNoXui2VuIPU8DY3Qh1lSsTbfSlsayqo2bWp7VNTwbgcOpF7EPy6YpfFS1cNIlzwTxfYHvNvnracJmRn7SkrmlgefUkeFTVNfHz3WuK27HI0T6K2A2ReZ78mrgjr4EeWmz9IHzXyJZCVgStGlUCgYEA627dWser7oQBUNQmfwYif00cvUGdsno4nu8WN8nnA3c5iyy+goZcL3WIdH09HWSOJ36cvzbqJtcKERWD6X16nCyVu+xcwopvOxlS+KmdyAzw3YXCYTDG2j+YRH5e2ZdY1unmGVoSms6142+T9myF+IAh8T77QJKcr+DxlChlcS0CgYEA7C7xtlvDpK/GP941O1/Nw4ZaFHyGCmrpTg1ALBoaRN70BQbvxMAt96OZZbA21G1nKIUqCinqwQlm/vCiOWbXspLtKLUnSnYY+s131mGNcwnsvlkOkqA1OhjnhhgvQX2Dpsj0yqAGmOiwaLbEng9UqWubJ+FbXxD6LpLmZ6OabKECgYEAmpVNpCiMnGxyTLcvm6HUjs97+kwWFjUn7js/1FuuYlkrIFW9tQgxiTvb39jHgwAeUpJQq0CMV/pD8tm0pl+sXNTtb9mPQnEQ/bXwA7OjFNJiaKFF8vjK6ExvVzG+Z5J3U416dtTseFcXmIEPnvK8uGCxaKyY3lolpVnGZShtCvUCgYBuMMCyHD+BPIHX+/LH7iyCCmV5+z/uIsKwe5gp55JV8OJvNQBv7MYfViA+w9AzYiiXkkNUBZArGLP1Ioj5bG5vteBxoETMZOUHhtlmfNRx2uTuO1WEht285wqgFMMNL6AN7p1uGb6oZW3hporPYqv5NVzCKg+5jTFn2xlyeMVV9w==';
        //$aop->alipayrsaPublicKey= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo3C4eHz4ysY+fy3rj5yinPq4w0QltmX0dVUmDTdgj5+j50Th7jfn4eaj13l8S1vOPYQeBmAyIxyZEWfffV83i0ii3MMW3R4+F5f8z0KIeKXNsgyRdWMZ7/NV2Lme16AxZKq77SLeBVM+KAR/vMTpTbz/Iof9GUMr3JU+E2NqS+f/BhOahIgCtD/xV/bG0MaYIgYxzyQWhQzOOU6N29EUPflYYXmUFvIZL8FdC53bfsTlDZJcErG4sQjATPmdDXA307e6fBYJTk4VrqObc0wm8446uBVVaeLsklfqbB/gbxAXvcKdPSns9wjwaQLnPd6QLKyF5CW576qKqJI155q+jQIDAQAB';
        $aop->rsaPrivateKey 	= 'MIIEpAIBAAKCAQEAwhAkXUccQR4nu0U+Pj1I9rWQhn4tIxR3GIu7A+hY8ZXz3LNJEstI6b+W+zvPP/lkLDzffmP2ONcDGDCHf23PUxpVE765IK9CHrZxLhIi7uxHBmsQ5+BNU5uKcprXv5WuNdk5tYZ9i/FpfjQ2KxsiibYyyjWU1OXTl3iFjXXvBmZ3vZCwwtTAxENtCUzjHoAkXNv5oJfgeRKDmZ7XZu6mwq3/mapu7YMN5nUvEfRZxt9bhHG6yGztiP0krgxLzV1fy2xpzXzKHCTWxx5FAU3FcLqCjla+Cp60hD5kRxOjfDH32jvEShtOzm2Egep+FOtM3IC08trajcwKtTMb7NhvSwIDAQABAoIBAHLFJyx/TMd6NRc9GVWn2woFUUcpQjqX9ONwaBckh83A2GtzIMlbrnCHnZxRv/1e2g6LpcXTCqCNEMhykwAbCl1kmmJGDqi03c7aKU+M7FoPJOY31dS8xB6pQ5UJ3ITy2ggAw2+G7aMhEDnSWSLfNmrpdVo/nBjZH21amumRkN5gHb5eVhFYE3rRXNh81+1vwR8IkuIW+i21yTVNIdnW98tl//9fTFkcD4fTxpilUQA90YH6JjV3QU0gR3UGdM5zY5Tj3tXeBMVoIGsVSr5vXWm0M6grNRZLgu8Q7R1v8J9PiggqmGiY/BB6QWE202H3g5HZwrZBK3wE7+RQ3Hv5RHkCgYEA5PHcTqt1OcwKjCgZ5z24afj5Vy4FY/ssgCUIxPaORLPU73hT5EWKaua74ucCGX78mDyxkIUh5nf5ddk/XMUORf0PW/+5eHKaHkIzhDcH9JAAvYJf9iD68wsyHC4AHhVKc4h33pdIki1IaQN6S3Iw/jPs3Tvz9ebpiOcOx+a+cy0CgYEA2P8GOMlIQl8kG7KM+SDo0KZwPpptXWk+wflDeEzZjyx8S1jzEstD5UDrP2rgONVYMXwTurH0lX+983Kedm8S2AuzS8/9Yp1B5Wejql5EtRIX5o7BZfVaU869uLdL8//prleB4khSqcGUX/G0uEmbuRMUYkJVH9ZZ/0imy+XHV1cCgYBlTsD17tkIokloi7YqpR0dh5aOBUdNXq/qZHjk71U1AX6QGObGdB8z/rXVSfOb5J1RoEnScZb2rNAuXduz6V7PiZNqWlZv/gZErXauYsuaZL5vHWnpN97T/XhHD/PtW+5+AT1Juhfa1z7beTZ8fCNB2ShPHWBux6c/dT8NJgXQYQKBgQCzoYmZAKccqn3Nn+UrZp7T+rP4XwYhybn2AU5lC9kduIm8JgoyiKnP4gncGbE72wCDFl+OsTnzeCfanAuppxFGX4kxPSBYvi2KplzJ7/eYnT3D0nEu4gjAT+imPLZXaoYbmL0ggRZNCL63HfOoiuaqMq0xGhA1ptAhkBbju3dlSQKBgQCiDxdWYlWvNAwg6eAC0kM2/tsucwk9rByVFOYYjdRD6muhV8kIh9n50GoYmUFU+sBCWdKLs0/P0p7eIWciHXtv6IS4Cjnumc7SPfbIYHDVZwVuVi+U6sdnKHXi/asCJ7D5vA+gsm9f53v7Q9GOi7D+tt3v8WkfLmAAAGUmyjWjPw==';
        $aop->alipayrsaPublicKey= 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlPt7PNDOGCpfIGrnsKpoCt9sUPYpDiQkOekshgVsVjGuwcz8+qt4QL7xizqOmuiV1R/msgNTFtZT47F7vOVp2KlodPLiBCqgrSVeNzqioZhi9m3Uu4Uf9V8dFoPoMsRrbTpC8WdHSbxp2J1aYKvWDUX8Gxmsa0+Bc6/4BxfFWgnpvJAVrBGJALYaSQotz6a79rYkEBEEomYFunnTguGKvKDc6HicXfno2G/ecaL/BjcGfyeTCrxgM55ejXOq84FkFOx5KGbVu6Z0I2ZctFIw+D+uUN4OEUwn/iK/vtJnHD1YDewcT2rWl/75LUcR+jAsZNHEfCgdfEU533pQs1ePQwIDAQAB';
        $aop->apiVersion 		= '1.0';
        $aop->signType 			= 'RSA2';
        $aop->postCharset		= 'UTF-8';
        $aop->format            = 'json';
        $request = new \ZhimaCustomerCertificationInitializeRequest();
        $request->setBizContent("{" .
            "    \"transaction_id\":\"{$str}\"," .
            "    \"product_code\":\"w1010100000000002978\"," .
            "    \"biz_code\":\"FACE\"," .
            "    \"identity_param\":\"{\\\"identity_type\\\":\\\"CERT_INFO\\\",\\\"cert_type\\\":\\\"IDENTITY_CARD\\\",\\\"cert_name\\\":\\\"{$userInfo['real_name']}\\\",\\\"cert_no\\\":\\\"{$userInfo['id_card']}\\\"}\"," .
            "  }");
        $result = $aop->execute($request);
         
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode   = $result->$responseNode->code;
        $biz_no       = $result->$responseNode->biz_no;
        $data['alipay_biz_no'] = $biz_no;
        M("member_status")->where("uid = $uid AND borrow_id = $bid")->save($data);
        if(!empty($resultCode)&&$resultCode == 10000){
            $zhiMaCustom = new \ZhimaCustomerCertificationCertifyRequest();
            $zhiMaCustom->setBizContent("{"."\"biz_no\":\"{$biz_no}\""."}");
            $zhiMaCustom->setReturnUrl($web."/Borrow/checkResult");
            $zhiMaResponse = $aop->pageExecute($zhiMaCustom,'get');
            return $zhiMaResponse;          
        } else {
            return false;
        }
    }
    
    /**
     *人脸识别结果处理
     */
    public function checkResult(){
        $idcheck    = false;
        $arr        = json_decode($_GET['biz_content'],true);
        $borrowInfo = M("member_status")->field('borrow_id,uid,id_verify')->where("alipay_biz_no = '{$arr['biz_no']}'")->find();
        $apply      = M("borrow_apply")->field(true)->where("id = {$borrowInfo['borrow_id']}")->find();
        if ($arr['passed'] === 'true' && $borrowInfo['id_verify'] !== 2){
            $idcheck = true;
        }else {
            wqbLog("认证失败");
            $idcheck = false;
            //$this->redirect('/Borrow/idfalse');
        }    
        $this->assign('idcheck',$idcheck);
        $this->assign('biz',$arr['biz_no']);
        $this->assign('bid',$borrowInfo['borrow_id']);
        $this->display();
    }
    
    /**
     *人脸识别成功后
     */
    public function idsure_detel(){
        $plat       = $_GET['plat'];
        //微信人脸识别
        if ($plat !== 'app' &&  $plat !== 'zhima'){
            $biz        = $_GET['biz'];
            $bid        = $_GET['bid'];
            $borrowInfo = M("member_status")->field('borrow_id,uid,id_verify')->where("alipay_biz_no = '{$biz}' ")->find();
            if($borrowInfo['borrow_id']>0){
                $binfo      = M("borrow_apply")->field('id, status, audit_status, coupon_id')->where("id = {$borrowInfo['borrow_id']}")->find();
                if($binfo['status']>2 || $binfo['audit_status']>2){
                    wqbLog("已经处理过身份证或无需处理");
                    
                }else{
                    
                    //更新身份认证状态以及决策审核状态
                    $bdata['audit_status'] = 3;
                    M("borrow_apply")->where("id = {$borrowInfo['borrow_id']}")->save($bdata);
                    
                    $sdata['id_verify']      = 1;
                    $sdata['id_verify_time'] = time();
                    $sdata['tree']           = 1;
                    $sdata['tree_time']      = time();
                    $result = M("member_status")->where("alipay_biz_no = '{$biz}'")->save($sdata);
                    
                    $is_white   = M("members")->field('is_white, zhima_openid,is_gold')->where("id = {$borrowInfo['uid']}")->find();
                    
                    //正常非白用户
                    if ($is_white['is_white'] == 0 && $is_white['is_gold'] == 0){
                        $zhima      = bqszhimaSearch($is_white['zhima_openid']);
                        //芝麻授权用户
                        if($zhima){
                            //走决策树
                            $model = new  CheckUserAction();
                            wqbLog("开始贷款决策树---------");
                            $checkRes = $model->requestApi($borrowInfo['uid'],$borrowInfo['borrow_id'],3);
                            wqbLog("结束贷款决策树---------".$checkRes);
                            
                            //决策树拒绝
                            if ($checkRes == 0){
                                //随机放款
                                /*$number = mt_rand(1,200);
                                 if ($number == 6){
                                 $start = mktime(0,0,0,date('m'),date('d'),date('y'));
                                 $end   = mktime(23,59,59,date('m'),date('d'),date('y'));
                                 $loans = M("random_lending")->field('id')->where("type = 1 and date between $start and $end")->select();
                                 if (empty($loans)){
                                 $info = M("random_lending")->field('money')->where("type = 0")->select();
                                 foreach ($info as $row){
                                 $data['money']  = $row['money'];
                                 $data['date']   = time();
                                 $data['status'] = 0;
                                 $data['type']   = 1;
                                 M("random_lending")->add($data);
                                 }
                                 }
                                 randomLoans($borrowInfo['uid'], $borrowInfo['borrow_id']);
                                }else {*/
                                
                                //还原借款优惠券
                                if (!empty($binfo['coupon_id'])){
                                    $cdata['status'] = 0;
                                    $cdata['id']     = $binfo['coupon_id'];
                                    M("member_coupon")->save($cdata);
                                }
                                
                                //更新为决策树不通过
                                $sdata['tree']          = 2;
                                $sdata['tree_time']     = time();
                                $result = M("member_status")->where("alipay_biz_no = '{$biz}'")->save($sdata);
                                
                                
                                //更新为签约拒绝
                                $appdata['status']       = 97;
                                $appdata['refuse_time']  = time();
                                M("borrow_apply")->where("id = {$borrowInfo['borrow_id']}")->save($appdata);
                                delUserOperation($borrowInfo['uid'],$bid);
                                
                                //发送拒绝短信（推广中银）
                                /*$smsTxt = FS("Webconfig/smstxt");
                                $mem    = M(' members ')->field('iphone')->where(" id = {$borrowInfo['uid']} ")->find();
                                addToSms($mem['iphone'], $smsTxt['loan_ad']);
                                */
                                
                                //发送拒绝微信
                                $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$borrowInfo['uid']}")->find();
                                if($wxInfo['openid']!==""){
                                    sendWxTempleteMsg20($wxInfo['openid']);
                                }
                                
                                //发送App推送通知初审拒绝
                                $mwhere['uid'] = $borrowInfo['uid'];
                                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                                if(!empty($token['token'])){
                                    AndroidTempleteMsg2($borrowInfo['uid'],$token['token'],$borrowInfo['borrow_id']);
                                }
                                //}
                            }
                        }else{
                            //芝麻授权取消的用户
                            wqbLog("用户：".$borrowInfo['uid']."因为取消芝麻授权被拒绝！");
                            
                            //还原借款优惠券
                            if (!empty($binfo['coupon_id'])){
                                $cdata['status'] = 0;
                                $cdata['id']     = $binfo['coupon_id'];
                                M("member_coupon")->save($cdata);
                            }
                            
                            //更新为签约拒绝
                            $appdata['status']       = 97;
                            $appdata['refuse_time']  = time();
                            M("borrow_apply")->where("id = {$borrowInfo['borrow_id']}")->save($appdata);
                            delUserOperation($borrowInfo['uid'],$bid);
                            
                            //发送拒绝短信（推广中银）
                            /*$smsTxt = FS("Webconfig/smstxt");
                            $mem    = M(' members ')->field('iphone')->where(" id = {$borrowInfo['uid']} ")->find();
                            addToSms($mem['iphone'], $smsTxt['loan_ad']);*/
                            
                            //发送拒绝微信
                            $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$borrowInfo['uid']}")->find();
                            if($wxInfo['openid']!==""){
                                sendWxTempleteMsg20($wxInfo['openid']);
                            }

                            //发送App推送通知初审拒绝
                            $mwhere['uid'] = $borrowInfo['uid'];
                            $token = M('member_umeng')->where($mwhere)->field(true)->find();
                            if(!empty($token['token'])){
                                AndroidTempleteMsg2($borrowInfo['uid'],$token['token'],$borrowInfo['borrow_id']);
                            }
                        }
                    }
                
                }
                
            }
            
        }

        
        $this->display();
    }
    
    
    /**
     * 芝麻信用获取用户OPENID  PC端调用
     */
    public function getUserOpenIdPC(){
        vendor('CreditSesame.ZmopSdk');
        $api_charset             = "UTF-8";
        $zhima_gateway_url       = "https://zmopenapi.zmxy.com.cn/openapi.do";
        $zhima_private_key_file  = APP_PATH."/key/rsa_private_key.pem";
        $zhima_public_key_file   = APP_PATH."/key/rsa_public_key.pem";
        $zhima_app_id            = "1002309";
        error_reporting("E_ALL");
        $client = new \ZmopClient($zhima_gateway_url,$zhima_app_id,$api_charset,$zhima_private_key_file,$zhima_public_key_file);
        $request = new \ZhimaAuthInfoAuthorizeRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setIdentityType("2");// 必要参数
        $request->setIdentityParam("{\"name\":\"赵匡龙\",\"certType\":\"IDENTITY_CARD\",\"certNo\":\"411481198810085135\"}");// 必要参数
        $request->setBizParams("{\"auth_code\":\"M_APPPC_CERT\",\"channelType\":\"apppc\",\"state\":\"商户自定义\"}");//
        $url = $client->generatePageRedirectInvokeUrl($request);
        wqbLog($url);
        header("Location:{$url}");
    }
    
    /**
     * 芝麻信用获取用户OPENID  H5手机端调用
     */
    public function getUserOpenIdH5(){
        vendor('CreditSesame.ZmopSdk');
        $info = M("member_info")->field('real_name,id_card')->where("uid = {$_GET['id']}")->find();
        wqbLog($info);
        $api_charset             = "UTF-8";
        $zhima_gateway_url       = "https://zmopenapi.zmxy.com.cn/openapi.do";
        $zhima_private_key_file  = APP_PATH."/key/rsa_private_key.pem";
        $zhima_public_key_file   = APP_PATH."/key/rsa_public_key.pem";
        $zhima_app_id            = "1002309";
        $client = new \ZmopClient($zhima_gateway_url,$zhima_app_id,$api_charset,$zhima_private_key_file,$zhima_public_key_file);
        $request = new \ZhimaAuthInfoAuthorizeRequest();
        $request->setChannel("apppc");
        $request->setPlatform("zmop");
        $request->setIdentityType("2");// 必要参数
        $request->setIdentityParam("{\"name\":\"{$info['real_name']}\",\"certType\":\"IDENTITY_CARD\",\"certNo\":\"{$info['id_card']}\"}");// 必要参数
        $request->setBizParams("{\"auth_code\":\"M_H5\",\"channelType\":\"app\",\"state\":\"{$_GET['id']}\"}");//
        $url = $client->generatePageRedirectInvokeUrl($request);
        header("Location:{$url}");
    }
    
    /**
     * 处理芝麻信用授权回调数据
     */
    public function apiResult(){
        $web    = C('WEBSITE_URL');
        vendor('CreditSesame.ZmopSdk');
        $api_charset             = "UTF-8";
        $zhima_gateway_url       = "https://zmopenapi.zmxy.com.cn/openapi.do";
        $zhima_private_key_file  = APP_PATH."/key/rsa_private_key.pem";
        $zhima_public_key_file   = APP_PATH."/key/rsa_public_key.pem";
        $zhima_app_id            = "1002309";
        $client     = new \ZmopClient($zhima_gateway_url,$zhima_app_id,$api_charset,$zhima_private_key_file,$zhima_public_key_file);
        $params     = strstr (  $_GET['params'], '%' ) ? urldecode ( $_GET['params'] ) :  $_GET['params'];
        $sign       = strstr ( $_GET['sign'], '%' ) ? urldecode ( $_GET['sign'] ) : $_GET['sign'];
        $result     = $client->decryptAndVerifySign ($params, $sign);
        $resultArr  = explode('&', $result);
        $length     = count($resultArr);
        for ($i=0;$i<$length;$i++){
            $tmpArr = explode('=', $resultArr[$i]);
            $res[$tmpArr[0]] = $tmpArr[1];
        }
        $open_id 	   = $res['open_id'];
        $error_message = $res['error_message'];
        $error_code    = $res['error_code'];
        $success	   = $res['success'];
        $uid           = $res['state'];
        
        if($success){
            $flg=1;
            $data['zhima_openid']   = $open_id;
            M("members")->where("id = $uid")->save($data);
           
            $sdata['zhima_auth']    = 1;
            $sdata['zhima_time']    = time();
            M("member_status")->where("uid = $uid and pending = 0")->save($sdata);
           
           
            $cinfo    = M("borrow_apply")->field(true)->where("uid = {$uid} and id = {$_GET['bid']}")->find();
           
            $model    = new  CheckUserAction();
            if (isWhite($uid)==1){//白名单用户
                $checkRes = $model->requestApi($uid,$_GET['bid'],5);//单独白骑士策略
            }else{
                if(isGray($uid)==1){//灰名单用户
                    $checkRes = $model->requestApi($uid,$_GET['bid'],5);//白骑士单独白骑士策略
                    $status   = mallRisk($uid);//年龄、职业、地区审核
                    $reszhima = mallZhima($uid,2);//灰名单芝麻分审核
                }else{
                    $checkRes = $model->requestApi($_GET['uid'],$_GET['bid'],5);//白骑士一般策略
                    $status   = mallRisk($uid);//年龄、职业、地区审核
                    $reszhima = mallZhima($uid,1);//一般用户芝麻分审核
                }
                if(isBlack($uid)==1){//黑名单用户
                    $flg = 0;
                    wqbLog("会员".$uid."为黑名单用户直接初审拒绝！");
                }else{
                    if ($checkRes == 1){
                        if ($status == 0 || $reszhima==0){
                            $flg = 0;
                            wqbLog("会员".$uid."年龄、职业、地区或者芝麻分拒绝！");
                        }
                    }else{
                        $flg = 0;
                        wqbLog("会员".$uid."白骑士拒绝！");
                    }
                }
           
                if($flg==0){
                    if ($cinfo['coupon_id'] > 0){
                        $cstatus['status'] = 0 ;
                        M("member_coupon")->where("id = {$cinfo['coupon_id']}")->save($cstatus);
                    }
                    $udata['tree'] = "3";
                    M("member_status")->where("uid = {$uid} and borrow_id = {$_GET['bid']}")->save($udata);
                    $ubdata['refuse_time'] = time();
           
                    $ubdata['status']      = "98";
                    delUserOperation($_GET['uid'],$_GET['bid']);
                    M("borrow_apply")->where("uid = {$uid} and id = {$_GET['bid']}")->save($ubdata);
           
                    //初审失败推送
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                    if($wxInfo['openid']!==""){
                        sendWxTempleteMsg2($wxInfo['openid'], $cinfo['money'], $cinfo['add_time'],1);
                    }

                    //发送App推送通知初审拒绝
                    $mwhere['uid'] = $uid;
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg2($uid,$token['token'],$_GET['bid']);
                    }
                }
            }

            $url = $web."/Borrow/idsure?plat=zhima";
            header("Location: {$url}");
        }else {
            exit("错误码：".$error_code.",错误信息：".$error_message);
        }
    }
    
    /**
     * 签约页面
     */
    public function sign(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $map['uid']         = $_SESSION['uid'];
        $map['id']          = $_SESSION['bid'];
        $map['status']      = 2;
        $info               = M("borrow_apply")->field(true)->where($map)->find();
        $memberInfo          = M("member_info")->field(true)->where("uid = {$_SESSION['uid']}")->find();
        $tick                = "SIGNING".time().$_SESSION['uid'];
        $tickIn              = "SIGNINGIN".time().$_SESSION['uid'];
        $item                = M("borrow_item")->field(true)->where(" id = {$info['item_id']} ")->find();
        //综合费用
        $info['fee']         = round($info['audit_fee']+$info['enabled_fee']+$info['created_fee']+$info['pay_fee'],2);
        //综合费用
        $info['due_rate']    = "借款本金".$item['due_rate']."%/天 ";
        //综合费用
        $info['late_rate']   = "借款本金".$item['late_rate']."%/天 ";
        //技术服务费年化率
        $info['created']     = $item['created'];
        //帐号管理费年化率
        $info['enbled']      = $item['enbled'];
        //贷后管理费年化率
        $info['audit']       = $item['audit'];
        //支付服务费年化率
        $info['pay_rate']    = $item['pay_rate'];
        //综合年化率
        $info['total_rate']  = $item['total_rate'];
        //还款金额
        $info['total']       = $info['money']+$info['interest']+$info['fee'];
        
        $this->assign('tick',$tick);
        $this->assign('tickIn',$tickIn);
        $this->assign('member',$memberInfo);
        $this->assign('info',$info);
        $this->assign('bid',$_SESSION['bid']);
        $this->display();
    }
    
    /**
     * 签约方法
     */
    public function doSign(){
        $where['uid']             = $_SESSION['uid'];
        $where['id']              = $_POST['bid'];
        $borrowInfo = M("borrow_apply")->field('id,uid,status,coupon_id')->where($where)->find();
        if($borrowInfo['id']>0){
            if($borrowInfo['status'] >3){
                ajaxmsg('您已签约，请不要重复签约！',1);
            }else{
                $members = M("members")->field('id,is_white,is_gray,is_black,is_gold,iphone')->where("id = '{$_SESSION['uid']}'")->find();
                $model = new  CheckUserAction();
                wqbLog("开始贷款决策树---------");
                if($members['is_gold']==0 &&$members['is_white']==0 &&$members['is_black']==0 ){
                    $checkRes = $model->requestApi($_SESSION['uid'],$borrowInfo['id'],3);
                }else{
                    $checkRes = 0;
                }
                wqbLog("结束贷款决策树---------".$checkRes);
                if($checkRes==0){
                    if($members['is_white']==0 && $members['is_gold']==0){//决策树不通过
                        //还原优惠券状态
                        updateCoupon($borrowInfo['coupon_id']);
                        //更新member_status状态
                        $map['uid']             = $_SESSION['uid'];
                        $map['borrow_id']       = $_POST['bid'];
                        $data['signed']         = 2;
                        $data['signed_time']    = time();
                        $data['tree']           = 2;
                        $data['tree_time']      = time();
                        $res = M("member_status")->where($map)->save($data);
                        
                        //更新为签约拒绝
                        $appdata['status']       = 97;
                        $appdata['refuse_time']  = time();
                        M("borrow_apply")->where("id = {$_POST['bid']}")->save($appdata);
                        
                        delUserOperation($_SESSION['uid'],$_POST['bid']);
                        
                        //发送拒绝微信
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg20($wxInfo['openid']);
                        }
                        //发送App推送通知初审拒绝
                        $mwhere['uid'] = $_SESSION['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg2($_SESSION['uid'],$token['token'],$_POST['bid']);
                        }
                        ajaxmsg('签约失败',2);
                    }else{
                        //更新member_status状态
                        $map['uid']             = $_SESSION['uid'];
                        $map['borrow_id']       = $borrowInfo['id'];
                        $data['signed']         = 1;
                        $data['signed_time']    = time();
                        $data['tree']           = 2;
                        $data['tree_time']      = time();
                        $res = M("member_status")->where($map)->save($data);
                        
                        $mapb['uid']            = $_SESSION['uid'];
                        $mapb['id']             = $borrowInfo['id'];
                        $bdata['status']        = 3;
                        $bdata['audit_status']  = 4;
                        M("borrow_apply")->where($mapb)->save($bdata);

                        //生成短信提醒记录
                        $add['borrow_id'] = $borrowInfo['id'];
                        $add['uid']       = $_SESSION['uid'];
                        $add['mobile_no'] = $members['iphone'];
                        $add['sign_time'] = time();
                        M('send_signed')->add($add);
                        ajaxmsg('您已完成签约，请等待复审通知',1);
                    } 
                }else{
                    
                    //更新member_status状态
                    $map['uid']             = $_SESSION['uid'];
                    $map['borrow_id']       = $borrowInfo['id'];
                    $data['signed']         = 1;
                    $data['signed_time']    = time();
                    $data['tree']           = 1;
                    $data['tree_time']      = time();
                    $res = M("member_status")->where($map)->save($data);
                    
                    $mapb['uid']            = $_SESSION['uid'];
                    $mapb['id']             = $borrowInfo['id'];
                    $bdata['status']        = 3;
                    $bdata['audit_status']  = 4;
                    M("borrow_apply")->where($mapb)->save($bdata);

                    //生成短信提醒记录
                    $add['borrow_id'] = $borrowInfo['id'];
                    $add['uid']       = $_SESSION['uid'];
                    $add['mobile_no'] = $members['iphone'];
                    $add['sign_time'] = time();
                    M('send_signed')->add($add);
                    ajaxmsg('您已完成签约，请等待复审通知！',1);
                }
            }
        }else{
            ajaxmsg('没有可操作的签约，请确认借款进度！',0);
        }
        
    }
               
    /**
     * 新浪代付到用户提现卡
     */
    public function withDrawal($uid,$bid){
        $flg        = false;
        $smsTxt     = FS("Webconfig/smstxt");
        $smsTxt     = de_xie($smsTxt);
        $memberInfo = M("members")->field('iphone')->where("id = $uid")->find();
        $bankInfo   = M("member_bank")->field('bank_id')->where("uid = $uid")->find();
        $info       = M("borrow_apply")->field('*')->where("id = $bid")->find();
        if($info['status']>=4){
            $flg = true;
        }else {
            if (sinaCreateBidInfo($info)){
                if (sinaCreateSingleHostingPayToCardTrade($info,$bankInfo['bank_id'],$info['trade_no'])){
                    
                    //更新借款状态
                    $mdata['pending']      = 1;
                    $mdata['pending_time'] = time();
                    M("member_status")->where("uid = {$uid} AND borrow_id = $bid")->save($mdata);
                    
                    $sdata['status']       = 4;
                    $sdata['audit_status'] = 5;
                    $sdata['len_time']     = time();
                    $deadline = time()+3600*24*($info['duration']);
                    $sdata['deadline']     = $deadline;
                    M("borrow_apply")->where("uid = {$uid} AND id = $bid")->save($sdata);
                    
					//新建账单表
					wqbLog("Wechat放款开始");
                    createRepayOrder($info,$deadline);
                    wqbLog("Wechat放款成功");

                    //发送微信
                    $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
                    if($wxInfo['openid']!==""){
                        sendWxTempleteMsg5($wxInfo['openid'], $info['money'], $info['add_time'], $sdata['len_time'],$info['loan_money']);
                    }
                    //addToSms($memberInfo['iphone'],str_replace(array("#DATE#", "#MONEY#"), array(date('Y-m-d',time()), $info['loan_money']), $smsTxt['loan_success']));
                    
                    //发放还款优惠券
                    $count = M("borrow_detail")->where("uid = {$uid} ")->count('id');
                    if($count==1){
                        $global = get_global_setting();
                        $ticArr = explode('|', $global['coupon_register']);
                        $ticData['uid']        = $uid;
                        $ticData['money']      = $ticArr[0];
                        $ticData['title']      = "还款优惠券";
                        $ticData['type']       = '2';
                        $ticData['status']     = 0;
                        $ticData['add_time']   = time();
                        $ticData['start_time'] = time();
                        $ticData['end_time']   = $deadline+3600*24*$ticArr[1];
                        $res = M("member_coupon")->add($ticData);
                    }
                    //发送App推送通知放款成功
                    $mwhere['uid'] = $uid;
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg5($uid,$token['token'],$bid);
                    }
                    
                    wqbLog("放款成功");
                }else{
                    //发送App推送通知放款失败
                    $mwhere['uid'] = $uid;
                    $token = M('member_umeng')->where($mwhere)->field(true)->find();
                    if(!empty($token['token'])){
                        AndroidTempleteMsg6($uid,$token['token'],$bid);
                    }
                    wqbLog("放款失败");
                }
            }else {
                wqbLog("录入失败,借款id".$bid);
            }
        }
        return $flg;
        
    }
    
    /**
     * 设置新浪支付密码
     */
    public function setsinapass(){
        $uid = $_SESSION['uid'];
        if($uid> 0){
            sinaSetPayPassword($uid);
        }
    }
    
    /**
     * 修改新浪支付密码
     */
    public function modsinapass(){
        $uid = $_SESSION['uid'];
        if($uid> 0){
            sinaModifyPayPassword($uid);
        }
    }
    
    /**
     * 忘记新浪支付密码
     */
    public function findsinapass(){
        $uid = $_SESSION['uid'];
        if($uid> 0){
            sinaFindPayPassword($uid);
        }
    }
    
    
    
    public function pay(){
        $this->display();
    }
    
    
    
    public function addCard(){
        if (!isset($_SESSION['uid'])){
            $this->redirect('/Member/regist');
        }
        $bankList = C("SINA_BANK_NAME");
        $length = count($bankList);
        for ($i=1 ; $i<$length;$i++){
            $str .= "<option value='{$bankList[$i]}'>".$bankList[$i]."</option>";
        }
        $this->assign('str',$str);
        $this->display();
    }
    
    
    
    public function addOtherCard() {
        $code = C("SINA_BANK_CODE");
        $data['uid']            = $_SESSION['uid'];
        $data['bank_card']      = $_POST['bankCard'];
        $data['bank_code']      = array_search($_POST['bankName'], $code);
        $data['bank_name']      = $_POST['bankName'];
        $data['bank_province']  = $_POST['province'];
        $data['bank_city']      = $_POST['city'];
        $data['type']           = 2;
        $map['uid']             = $_SESSION['uid'];
        $map['bank_card']       = $_POST['bankCard'];
        $map['bank_name']       = $_POST['bankName'];
        $map['bank_code']       = $data['bank_code'];
        $info = M("member_bank")->field('id')->where($map)->find();
        if (empty($info)){
            $res = M("member_bank")->add($data);
            if ($res){
                ajaxmsg('添加成功',1);
            }else {
                ajaxmsg('添加失败',0);
            }
        }else {
            ajaxmsg('已有银行卡信息',0);
        }
    }
    
    public function addShouCard() {
        $code = C("SINA_BANK_CODE");
        $data['uid']            = $_SESSION['uid'];
        $data['bank_card']      = $_POST['bankCard'];
        $data['bank_code']      = array_search($_POST['bankName'], $code);
        $data['bank_name']      = $_POST['bankName'];
        $data['bank_province']  = $_POST['province'];
        $data['bank_city']      = $_POST['city'];
        $data['type']           = 2;
        $map['uid']             = $_SESSION['uid'];
        $map['bank_card']       = $_POST['bankCard'];
        $map['bank_name']       = $_POST['bankName'];
        $map['bank_code']       = $data['bank_code'];
        $info = M("member_bank")->field('id')->where($map)->find();
        if (empty($info)){
            $res = M("member_bank")->add($data);
            if ($res){
                ajaxmsg('添加成功',1);
            }else {
                ajaxmsg('添加失败',0);
            }
        }else {
            ajaxmsg('已有银行卡信息',1);
        }
    }

    /**
     * 冷静期页面
     */
    public function  calm(){
        
        $where['uid']       = $_SESSION['uid'];
        $where['borrow_id'] = $_SESSION['bid'];
        $time = M("member_status")->field('id,signed_time')->where($where)->find();
        if($time['id']>0){
            $datag            = get_global_setting();
            $chill_time       = $datag['calm_time'];
            $counttime        = $chill_time*60*60;
            $countDownTime    = $counttime + (int)$time['signed_time'];
            $now              = time();
            $this->assign("current_time", $now);
            $this->assign("countDownTime", $countDownTime);
            $this->assign("chill_time", $chill_time);
            $this->assign("bid", $where['borrow_id']);
        }else{
            $this->redirect('/Borrow/msgCheck');
        }
        
        $this->display();
    }
    
    /**
     * 确认冷静
     */
    public function doCalm(){
    
        $where['uid']       = $_SESSION['uid'];
        $where['borrow_id'] = $_POST['bid'];
        $status = M("member_status")->field('calm')->where($where)->find();
        
        if($status['calm']==0){
            $map['uid']       = $_SESSION['uid'];
            $map['id']        = $_POST['bid'];
            $apply = M("borrow_apply")->field('id,status,audit_status')->where($map)->find();
            if($apply['status']==3 && $apply['audit_status']==4){
                $datag        = get_global_setting();
                $is_aoto_loan = $datag['is_aoto_loan'];
                $loan_onoff   = $datag['loan_onoff'];
                $loan_day     = $datag['loan_day'];
                
                $data['calm']      = 1;
                $data['calm_time'] = time();
                $res = M("member_status")->where($where)->save($data);
                
                if($res){
                    ajaxmsg('资金筹集中',1);
                    /*if($loan_onoff == "1"){
                        ajaxmsg('',1);
                    }else{
                        if ($is_aoto_loan == "1"){
                            if(get_loan_money()>$loan_day){
                                ajaxmsg('资金筹集中',1);
                            }else{
                                $flg = $this->withDrawal($_SESSION['uid'], $apply['id']);
                                if($flg){
                                    ajaxmsg('资金筹集中',1);
                                }else{
                                    ajaxmsg('资金筹集中',1);
                                }
                                ajaxmsg('资金筹集中',1);
                            }
                        }else{
                            ajaxmsg('资金筹集中',1);
                        }
                    }*/
                }else{
                    ajaxmsg("请确认借款申请的状态",0);
                }
            }else{
                ajaxmsg("请确认借款申请的状态",0);
            }
            
        }else{
            ajaxmsg("请确认借款申请的状态",0);
        }
    }
    
    
    /**
     * 确认冷静
     */
    public function calmNew(){
        $where['uid']       = $_SESSION['uid'];
        $where['borrow_id'] = $_SESSION['bid'];
        $status = M("member_status")->field('calm')->where($where)->find();
        if($status['calm']==0){
            $map['uid']       = $_SESSION['uid'];
            $map['id']        = $_SESSION['bid'];
            $apply = M("borrow_apply")->field('id,status,audit_status')->where($map)->find();
            if($apply['status']==3 && $apply['audit_status']==4){
                $data['calm']      = 1;
                $data['calm_time'] = time();
                $res = M("member_status")->where($where)->save($data);
            }
        }
        $this->redirect('/Borrow/msgcheck');
    }
    
    
    /**
     * Face++ 人脸识别请求
     */
    public function face(){
        $uid        = $_SESSION['uid'];
        $borrow_id  = $_SESSION['bid'];
        $status     = M("member_status")->field('id,signed,calm,is_recheck')->where("borrow_id = {$borrow_id} and uid = {$uid}")->find();
        if($status['signed']==1 && $status['calm']==1 && $status['is_recheck']==1){
            
            //有在人脸确认中的则需要等待认证结束
            /*$face = M('borrow_face')->field("id,status")->where(" uid = {$uid}  and borrow_id = {$borrow_id} ")->order('id desc')->find();
            if($face['id']>0 && $face['status']==0){
                $this->redirect('/Borrow/msgcheck');
            }*/
            
            //已经在人脸识别有效期内成功认证过的无需验证
            $mem = M("member_info")->field('id,id_card,real_name')->where(" uid = {$uid}")->find();
            //请求face++接口
            $biz_no     = $borrow_id."ML".date("YmdHis");
            $res        = faceGetToken($biz_no,$mem['real_name'],$mem['id_card']);
            //请求成功
            if(empty($res['error_message'])){
                insertFace($uid,$borrow_id,$biz_no,$res['token'],$res['request_id'],$res['biz_id']);
                wqblog("Face++token请求成功");
                //人脸识别开始
                header("Location: https://api.megvii.com/faceid/lite/do?token=".$res['token']);
            }else{
                wqblog("Face++token请求失败，失败原因：".json_encode($res));
            }
        }else{
            $this->redirect('/Borrow/msgcheck');
        }
    }
    
    public function face_return(){
        $data       = $_POST['data'];
        $sign       = $_POST['sign'];
        $config     = require(APP_PATH . "/Conf/face.php");
        $api_secret = $config["api_secret"];
        $signcheck  = sha1($api_secret.$data);
        wqbLog("Face++return回调：".$data);
        //回调签名正确
        if($signcheck==$sign){
            $data   = json_decode($data,true);
            $biz_no = $data['biz_info']['biz_no'];
            $face   = M("borrow_face")->field('id,uid,borrow_id,biz_id')->where(" biz_no = '{$biz_no}'")->find();
            $score  = $data['verify_result']['result_faceid']['confidence'];
            if($face['status']==0){
                if($data['status']=="OK"){//人脸识别验证操作完成
                    $datag  = get_global_setting();
                    if($score>=$datag['face_sorce']){
                        $status = 1;
                        $sdata['id_verify']        = 1;
                        $sdata['id_verify_time']   = time();
                        $result = M("member_status")->where("borrow_id = {$face['borrow_id']}")->save($sdata);
                        //人脸识别成功后自动复查通过
                        $auto_review = $datag['auto_review'];
                        if($auto_review==1){
                            $sdata['is_review']    = 1;
                            $sdata['review_time']  = time();
                            //复查通过后自动上标到福米金融
                            $is_aotu      = $datag['is_aotu_bid'];
                            if($is_aotu == 1){
                                $upbid = createFumiBid($face['borrow_id'],0);
                            }
                            //发送App推送通知复审成功
                            $mwhere['uid'] = $array['uid'];
                            $token = M('member_umeng')->where($mwhere)->field(true)->find();
                            if(!empty($token['token'])){
                                AndroidTempleteMsg4($array['uid'],$token['token'],$face['borrow_id']);
                            }
                        }
                    }else{
                        $status = 2;
                        $bdata['status']         = 93;
                        $bdata['refuse_time']    = time();
                        M("borrow_apply")->where("id = {$face['borrow_id']}")->save($bdata);
                
                        $sdata['id_verify']      = 2;
                        $sdata['id_verify_time'] = time();
                        $result = M("member_status")->where("borrow_id = {$face['borrow_id']} ")->save($sdata);
                
                        delUserOperation($face['uid'],$face['borrow_id']);
                        //还原优惠券状态
                        $binfo = M("borrow_apply")->field("coupon_id")->where("id = {$face['borrow_id']}")->find();
                        updateCoupon($binfo['coupon_id']);
                        //发送微信
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$face['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg22($wxInfo['openid']);
                        }

                        //发送App推送通知复审失败
                        $mwhere['uid'] = $face['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg3($face['uid'],$token['token'],$face['borrow_id']);
                        }
                    }
                    updateFace($biz_no,$status,1,$data);
                    get_face_pic($face['uid'],$face['borrow_id']);
                }else{
                    $status = 0;
                } 
            }else{
                $status = 3;
            }
            if($status==1){
                $this->redirect('/Borrow/idsure');
            }elseif($status==2){
                $this->redirect('/Borrow/refuse');
            }elseif($status==3){
                $this->redirect('/Borrow/iddone');
            }else{
                $this->redirect('/Borrow/idclose');
            }
        }else{
            $this->redirect('/Borrow/idclose');
        }
        
    }
    
    public function face_notify(){
        $data       = $_POST['data'];
        $sign       = $_POST['sign'];
        $config     = require(APP_PATH . "/Conf/face.php");
        $api_secret = $config["api_secret"];
        $signcheck  = sha1($api_secret.$data);
        wqbLog("Face++notify回调：".$data);
        //回调签名正确
        if($signcheck==$sign){
            $data   = json_decode($data,true);
            $biz_no = $data['biz_info']['biz_no'];
            $face   = M("borrow_face")->field('id,uid,borrow_id,biz_id')->where(" biz_no = '{$biz_no}'")->find();
            $score  = $data['verify_result']['result_faceid']['confidence'];
            if($face['status']==0){
                if($data['status']=="OK"){//人脸识别验证操作完成
                    $datag  = get_global_setting();
                    if($score>=$datag['face_sorce']){
                        $status = 1;
                        $sdata['id_verify']        = 1;
                        $sdata['id_verify_time']   = time();
                        $result = M("member_status")->where("borrow_id = {$face['borrow_id']}")->save($sdata);
                        //人脸识别成功后自动复查通过
                        $auto_review = $datag['auto_review'];
                        if($auto_review==1){
                            $sdata['is_review']    = 1;
                            $sdata['review_time']  = time();
                            //复查通过后自动上标到福米金融
                            $is_aotu      = $datag['is_aotu_bid'];
                            if($is_aotu == 1){
                                $upbid = createFumiBid($face['borrow_id'],0);
                            }
                        }
                    }else{
                        $status = 2;
                        $bdata['status']         = 93;
                        $bdata['refuse_time']    = time();
                        M("borrow_apply")->where("id = {$face['borrow_id']}")->save($bdata);
                
                        $sdata['id_verify']      = 2;
                        $sdata['id_verify_time'] = time();
                        $result = M("member_status")->where("borrow_id = {$face['borrow_id']} ")->save($sdata);
                
                        delUserOperation($face['uid'],$face['borrow_id']);
                        //还原优惠券状态
                        $binfo = M("borrow_apply")->field("coupon_id")->where("id = {$face['borrow_id']}")->find();
                        updateCoupon($binfo['coupon_id']);
                        //发送微信
                        $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$face['uid']}")->find();
                        if($wxInfo['openid']!==""){
                            sendWxTempleteMsg22($wxInfo['openid']);
                        }
                        //发送App推送通知复审失败
                        $mwhere['uid'] = $face['uid'];
                        $token = M('member_umeng')->where($mwhere)->field(true)->find();
                        if(!empty($token['token'])){
                            AndroidTempleteMsg3($face['uid'],$token['token'],$face['borrow_id']);
                        }
                    }
                    updateFace($biz_no,$status,1,$data);
                    get_face_pic($face['uid'],$face['borrow_id']);
                }else{
                    $status = 0;
                } 
            }
        }
        wqbLog("Face++notify回调结束：".$status);
    }
    
    /**
     * 汇潮异步回调结果（只返回成功的支付）
     */
    function alipaybuy_notify(){
        if(IS_POST){
            $content = $_POST;
            $config  = require(APP_PATH . "/Conf/huichao.php");
        	$key     = $config["key"];
            $signstr = 'merchantOutOrderNo='.$content['merchantOutOrderNo'].'&merid='.$content['merid'].'&msg='.$content['msg'].'&noncestr='.$content['noncestr'].'&orderNo='.$content['orderNo'].'&payResult='.$content['payResult'];
            $signstr.= '&key='.$key;
            $sign    = md5($signstr);
            if($sign==$content['sign']){
                if($content['payResult']==1){
                    $order = M("transfer_order_pay")->field('id,borrow_id,item_id,scene,status,amount')->where("outer_orderId ='{$content['merchantOutOrderNo']}'")->find();
                    if($order['id']>0&&$order['status']==0){
                        updatehuichaoOrders($order,$content,$content['payResult'],time());
                    }
                }
                echo "success";
                return "success";
            } 
        }
    }
} 
