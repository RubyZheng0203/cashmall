<?php 
class CheckPhoneAction extends HCommonAction{              
                             
    /**
     * 拉取运营商数据的接口（需要短信的运营商会走第二个接口）
     */
   public function requestJavaApi(){      
       $info    = M()->query("SELECT a.real_name,a.id_card,b.iphone1,b.iphone2,b.iphone3 FROM ml_member_info a INNER JOIN ml_member_relation b ON a.uid = b.uid WHERE a.uid = {$_SESSION['uid']}");
       $info    = $info[0];
       $info['real_name'] = urlencode($info['real_name']);
       
       $where['uid']             = $_SESSION['uid'];
       $where['status']          = 1;
       $borrowInfo = M("borrow_apply")->field('id,money,add_time')->where($where)->find();   
       if($borrowInfo['id']>0){
           if (isset($_SESSION['temp_user_phone']) && !empty($_SESSION['temp_user_phone'])){
               $tempPhone = $_SESSION['temp_user_phone'];
           }else {
               $tempPhone = $_POST['phone'];
           }
           if (isset($_SESSION['temp_user_pwd']) && !empty($_SESSION['temp_user_pwd'])){
               $tempPassword = $_SESSION['temp_user_pwd'];
           }else {
               $tempPassword = $_POST['password'];
           }
            
           $operator_mh = C('OPERATOR_MH_URL'); //请求运行商魔盒的URL
           $realname    = $info['real_name'];
           $url         ="{$operator_mh}/{$tempPhone}/{$info['id_card']}/{$realname}/{$tempPassword}/{$info['iphone1']}/{$info['iphone2']}/{$info['iphone3']}";
           
           //$operator_lm = C('OPERATOR_LM_URL'); //请求运行商立木的URL
           //$url         ="{$operator_lm}/{$_POST['phone']}/{$_POST['password']}/{$info['real_name']}/{$info['id_card']}/{$info['iphone1']}/{$info['iphone2']}/{$info['iphone3']}";
           $res = http_request($url);
           if (strpos($res, 'success') !== false){
               $statusInfo = M("member_status")->field('id')->where("uid = {$_SESSION['uid']} and borrow_id = {$borrowInfo['id']}")->find();
               if (empty($statusInfo)){
                   $data['verify_phone']  = 0;
                   $data['uid']           = $_SESSION['uid'];
                   $data['borrow_id']     = $borrowInfo['id'];
                   M("member_status")->add($data);
               }else{
                   ajaxmsg('请勿重复提交',0);
               }
                
               $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$_SESSION['uid']}")->find();
               if($wxInfo['openid']!==""){
                   sendWxTempleteMsg1($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'],1);
               }
               ajaxmsg('',1);
           }else{
               $arr = explode(':', $res);
               if (strpos($res, 'error') !== false){
                   switch ($arr[1]){
                       case '01':
                           ajaxmsg("请勿重复提交",0);
                           break;
                       case '02':
                           ajaxmsg("当天请求次数已满，请明天再来",0);
                           break;
                       case '108':
                           ajaxmsg("请求手机验证码失败",0);
                           break;
                       case '112':
                           ajaxmsg("账号或密码错误",0);
                           break;
                       case '113':
                           ajaxmsg("登录失败",0);
                           break;
                       case '116':
                           ajaxmsg("身份证或姓名校验失败",0);
                           break;
                       case '124':
                           ajaxmsg("手机验证码错误或过期",0);
                           break;
                       case '2502':
                           ajaxmsg("手机号码所在区域暂不支持",0);
                           break;
                       default :
                           ajaxmsg("请求已超时，请重新提交",0);
                           break;
                   }
               }else {
                   session('tickId',$arr[1]);
                   session('temp_user_phone',$_POST['phone']);
                   session('temp_user_pwd',$_POST['password']);
                   ajaxmsg('sms',0);
               }
           }
       }else{
           ajaxmsg('请勿重复提交',0);
       }
   }
   
   /**
    * 请求运营商登录
    */
   public function requestPhoneLogin(){
       $operator_mh = C('OPERATOR_MH_IN_URL'); //请求运行商魔盒的URL
       $url         = "{$operator_mh}/{$_SESSION['tickId']}/{$_POST['vcode']}"; 
       
       //$operator_lm = C('OPERATOR_LM_IN_URL'); //请求运行商立木的URL
       //$url = "{$operator_lm}/{$_SESSION['tickId']}/{$_POST['vcode']}";
       
       $res = http_request($url);
       if (strpos($res, 'success')!==false){
           $arr = explode(':', $res);
           $members = M("members")->field('id')->where("iphone = '{$arr[1]}'")->find();
           $where['uid']             = $members['id'];
           $where['status']          = array('not in','5,96,97,98,99');
           $borrowInfo = M("borrow_apply")->field('id,money,add_time')->where($where)->find();
           $info = M("member_status")->field('id')->where("uid = {$members['id']} and borrow_id = {$borrowInfo['id']}")->find();
           wqbLog("运营商登录用户uid".$members['id']."--".$arr[1]);
           if (empty($info)){
               $data['verify_phone']   = 0;
               $data['uid']            = $members['id'];
               $data['borrow_id']      = $borrowInfo['id'];
               M("member_status")->add($data);
           }
           $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
           if($wxInfo['openid']!==""){
               sendWxTempleteMsg1($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'],1);
           }
           session('tickid',null);
           ajaxmsg('',1);
       }else{
           ajaxmsg('',0);
       }
   } 
   
   /**
    * 运营商数据异步通知
    * 通知的文本信息｛result=success:158xxxxxxxx,310xxxxxxxxxxx,张三 or result=error:158xxxxxxxx｝
    */
   public function noticeVerifyPhone(){
       $result  = $_POST['result'];
       $arr     = explode(':', $result);
       $arrInfo = explode(',', $arr[1]);
       $members = M("members")->field('id,is_white')->where("iphone = '{$arrInfo[0]}'")->find();
       
       $where['uid']             = $members['id'];
       $where['status']          = 1;
       $borrowInfo = M("borrow_apply")->field('id,status,audit_status,coupon_id,add_time,money')->where($where)->order("id desc")->limit("1")->find();
       
       if($borrowInfo['id'] > 0){
           if($borrowInfo['status']==1 && $borrowInfo['audit_status'] == 1){
               //调用成功
               if (strpos($arr[0], 'success')!==false){
                   //验证手机用户身份的真假，即是否是实名对应的用户
                   $checkResult = $this->checkUserRealInfo($members['id'], $arrInfo[2], $arrInfo[1]);
                   //身份验证通过
                   if ($checkResult){
                       $res = $this->updateUserTables($members['id'], $borrowInfo['id']);
                       if ($res){
                           $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
                           if($wxInfo['openid']!==""){
                               sendWxTempleteMsg3($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'],1);
                           }
                           //发送App推送通知初审通过
                           $mwhere['uid'] = $members['id'];
                           $token = M('member_umeng')->where($mwhere)->field(true)->find();
                           if(!empty($token['token'])){
                              AndroidTempleteMsg($members['id'],$token['token'],$borrowInfo['id']);
                           }
                           wqbLog("运营商数据拉取异步接口返回结果成功------".$result);
                       }
                   }else {
                       $this->updateFalseUserTables($members['id'], $borrowInfo['id'], $borrowInfo['coupon_id']);
                       $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
                       if($wxInfo['openid']!==""){
                           sendWxTempleteMsg2($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'],1);
                       }
                       //发送App推送通知初审拒绝
                       $mwhere['uid'] = $members['id'];
                       $token = M('member_umeng')->where($mwhere)->field(true)->find();
                       if(!empty($token['token'])){ 
                            AndroidTempleteMsg2($members['id'],$token['token'],$borrowInfo['id']);
                       }
                       wqbLog("运营商数据拉取异步接口返回结果成功，但是身份验证不过------".$result);
                   }
           
               }else { 
                       $this->updateFalseUserTables($members['id'], $borrowInfo['id'], $borrowInfo['coupon_id']);
                       $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$members['id']}")->find();
                       if($wxInfo['openid']!==""){
                           sendWxTempleteMsg2($wxInfo['openid'], $borrowInfo['money'], $borrowInfo['add_time'],1);
                       }
                       //发送App推送通知初审拒绝
                       $mwhere['uid'] = $members['id'];
                       $token = M('member_umeng')->where($mwhere)->field(true)->find();
                       if(!empty($token['token'])){
                            AndroidTempleteMsg2($members['id'],$token['token'],$borrowInfo['id']);
                       }
                   wqbLog("运营商数据拉取异步接口返回结果失败-----".$result);
               }
           }else{
               wqbLog("会员".$arrInfo[0]."的运营商数据已经获取过了");
           } 
       }  
   }
    
   /**
    * 手机验证成功更新用户借款状态
    * @param 借款会员UID $uid
    * @param 借款申请ID $bid
    */
   public function updateUserTables($uid,$bid){
       
       $sdata['verify_phone']   = 1;
       $sdata['first_trial']    = 1;
       $sdata['first_trial_time'] = time();
       $res = M("member_status")->where("uid = $uid and borrow_id = $bid")->save($sdata);
       
       $bdata['status']         = 2;
       $res2 = M("borrow_apply")->where("id = $bid")->save($bdata);
       if($res!==FALSE && $res2 !==FALSE){
           return true;
       }else {
           return FALSE;
       }
   }
    
    /**
     * 手机验证失败更新借款状态
     * @param 借款会员 $uid
     * @param 借款申请ID $bid
     * @param 借款优惠券 $coupon_id
     */
   public function updateFalseUserTables($uid,$bid,$coupon_id){
       
       $sdata['verify_phone']     = 2;
       $sdata['first_trial']      = 2;
       $sdata['first_trial_time'] = time();
       M("member_status")->where("uid = $uid and borrow_id = $bid")->save($sdata);
       
       $bdata['status'] = 98;
       $bdata['refuse_time'] = time();
       M("borrow_apply")->where("id = $bid")->save($bdata);
       
       //借款优惠券返还
       $cdata['status'] = 0;
       $cdata['id']     = $coupon_id;
       M("member_coupon")->save($cdata);
       
       delUserOperation($uid,$bid);
       
   }
   
   /**
    * 验证用户身份信息真伪
    * @param int $uid
    * @param unknown $realName
    * @param unknown $idCard
    */
   private function checkUserRealInfo($uid,$realName,$idCard){
       $memberInfo = M("member_info")->field('id_card,real_name')->where("uid = $uid")->find();
       if ($memberInfo['id_card'] == $idCard && $memberInfo['real_name'] == $realName){
           return true;
       }else {
           if ((strpos($idCard, '*') !== false)&&(strpos($realName, '*') !== false)){
               $tempCard = substr($idCard, -4);
               $tempName = mb_substr($realName, -1,'utf-8');
               if ((strpos($memberInfo['id_card'], $tempCard) !== false) && (strpos($memberInfo['real_name'], $tempName) !== false)){
                   return true;
               }else {
                   return false;
               }
           }else {
               if (strpos($idCard, '*') === false){
                   if ($idCard == $memberInfo['id_card']){
                       return true;
                   }else {
                       return false;
                   }
               }else {
                   if (strpos($memberInfo['id_card'], substr($idCard, -4))!==false && $realName == $memberInfo['real_name']){
                       return true;
                   }else {
                       return false;
                   }
               }
           }
       }
   }  
}
?>