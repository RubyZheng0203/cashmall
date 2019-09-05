<?php
class RemindAction extends HCommonAction{
    
    /**
     * 催款微信发送(计划任务每天7点执行)
     */
	public function index(){
	    
	    //当天催款
	    $start0    = strtotime(date("Y-m-d",time())." 00:00:00");
	    $end0      = strtotime("+1 day",strtotime(date("Y-m-d",time())." 00:00:00"));
	    $list0     = M()->query(" SELECT aa.borrow_id,aa.uid,aa.capital,aa.interest,cc.real_name,cc.id_card,bb.openid,aa.deadline,MOD(substring(cc.id_card, 17, 1),2) As sex,dd.bank_card,dd.bank_name from ml_borrow_detail aa  LEFT JOIN ml_member_wechat_bind bb on aa.uid = bb.uid LEFT JOIN ml_member_info cc on cc.uid = aa.uid LEFT JOIN ml_member_bank dd on dd.uid = aa.uid where aa. `status` = 0 and dd.type = 1 and aa.deadline >= ".$start0." and aa.deadline < ".$end0);
	    if(count($list0) > 0){
	        wechatLog("微信催款信息开始发送（当天催款）：");
	        foreach($list0 as $key0=>$v0){
	            $is_new = M("borrow_apply")->field(true)->where("id = {$v0['borrow_id']} and uid = {$v0['uid']} ")->find();
	            if($is_new['is_new']==1){
	                $money0 = $v0['capital']+$v0['interest']+getFloatValue($is_new['audit_fee']+$is_new['enabled_fee']+$is_new['created_fee']+$is_new['pay_fee'],2);
	            }else{
	                $money0 = $v0['capital']+$v0['interest'];
	            }
	            $res0 = sendWxTempleteMsg7($v0['openid'],date("Y-m-d",$v0['deadline']),$money0,substr($v0['bank_card'],-4),$v0['bank_name']);
	            //App当天催款提醒
                $mwhere['uid'] = $v0['uid'];
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg8($v0['uid'],$token['token'],$v0['borrow_id']);
                }
	            wechatLog("微信催款：借款人uid-".$v0['uid']."借款编号ID-".$v0['borrow_id']."发送结果-".$res0['errmsg']);
	        }
	        wechatLog("微信催款信息发送（当天催款）结束");
	    }else{
	        wechatLog("无微信催款信息（当天催款）可发送");
	    }
	    
	    //提前两天的催款  
	    $start    = strtotime("+2 day",strtotime(date("Y-m-d",time())." 00:00:00"));
	    $end      = strtotime("+3 day",strtotime(date("Y-m-d",time())." 00:00:00"));
	    $list     = M()->query(" SELECT aa.borrow_id,aa.uid,aa.capital,aa.interest,cc.real_name,cc.id_card,bb.openid,aa.deadline,MOD(substring(cc.id_card, 17, 1),2) As sex,dd.bank_card,dd.bank_name from ml_borrow_detail aa  LEFT JOIN ml_member_wechat_bind bb on aa.uid = bb.uid LEFT JOIN ml_member_info cc on cc.uid = aa.uid LEFT JOIN ml_member_bank dd on dd.uid = aa.uid where aa. `status` = 0 and dd.type = 1 and aa.deadline >= ".$start." and aa.deadline < ".$end);
	    if(count($list) > 0){
	        wechatLog("微信催款信息开始发送（两天后的催款）：");
	        foreach($list as $key=>$v){
	            if($v['sex']==0){
	                $name = $v['real_name']."女士";
	            }else{
	                $name = $v['real_name']."先生";
	            }
	            $is_new = M("borrow_apply")->field(true)->where("id = {$v['borrow_id']} and uid = {$v['uid']} ")->find(); 
	            if($is_new['is_new']==1){
	               $money = $v['capital']+$v['interest']+getFloatValue($is_new['audit_fee']+$is_new['enabled_fee']+$is_new['created_fee']+$is_new['pay_fee'],2);
	            }else{
	               $money = $v['capital']+$v['interest'];
	            }
	            $res = sendWxTempleteMsg6($v['openid'],$name,date("Y-m-d",$v['deadline']),$money,substr($v['bank_card'],-4),$v['bank_name']);
	            //App提前催款提醒
                $mwhere['uid'] = $v['uid'];
                $token = M('member_umeng')->where($mwhere)->field(true)->find();
                if(!empty($token['token'])){
                    AndroidTempleteMsg7($v['uid'],$token['token'],$v['borrow_id']);
                }

	            wechatLog("微信催款：借款人uid-".$v['uid']."借款编号ID-".$v['borrow_id']."发送结果-".$res['errmsg']);
	       }
	       wechatLog("微信催款信息发送结束");
        }else{
           wechatLog("无微信催款信息可发送");
        }
	}

	/**
	 * 催款短信发送(计划任务每天8点执行)
	 */
	public function sendSMS(){
	    $smsTxt = FS("Webconfig/smstxt");

	    //当天催款
	    $start    = strtotime(date("Y-m-d",time())." 00:00:00");
	    $end      = strtotime("+1 day",strtotime(date("Y-m-d",time())." 00:00:00"));
	    $list     = M()->query(" SELECT aa.borrow_id,aa.uid,aa.capital,aa.interest,cc.real_name,cc.id_card,cc.iphone, aa.add_time,
	        aa.deadline,MOD(substring(cc.id_card, 17, 1),2) As sex,dd.bank_card,dd.bank_name from ml_borrow_detail aa
	        LEFT JOIN ml_member_info cc on cc.uid = aa.uid 
	        LEFT JOIN ml_member_bank dd on dd.uid = aa.uid 
	        where aa.borrow_id = 51405 and aa. `status` = 0 and dd.type = 1 and aa.deadline >= ".$start." and aa.deadline < ".$end);
	    
	    if(count($list) > 0){
	        wechatLog("短信催款信息开始发送（当天催款）：");
	        foreach($list as $key=>$v){
	
	            $money = $v['capital']+$v['interest'];
	            $res   = addToSms($v['iphone'],str_replace(array("#DATE#", "#MONEY#", "#BANK#","#BANKNAME#", "#AMOUNT#"), array(date("Y-m-d",$v['add_time']),$v['capital'],substr($v['bank_card'],-4),$v['bank_name'],$money),$smsTxt['repayment_before']));
	             
	            wechatLog("短信催款：借款人uid-".$v['uid']."借款编号ID-".$v['borrow_id']);
	        }
	        wechatLog("短信催款信息发送（当天催款）结束");
	    }else{
	        wechatLog("无短信催款信息（当天催款）可发送");
	    }
	}
	
	
	/**
	 * 30天以内未申请到待放款的用户取消借款申请(计划任务每天6点执行)
	 */    
    public function delOrders(){
        $start    = strtotime("-15 day",strtotime(date("Y-m-d",time())." 00:00:00"));
        $list     = M()->query(" SELECT id,uid,coupon_id,money,add_time,duration from ml_borrow_apply where status < 3  and len_time = 0 and add_time < ".$start);
        if(count($list) > 0){
            wechatLog("过期借款取消处理开始：".$sql);
            foreach($list as $key=>$v){
                $data['status'] = 99;
                $status = M("borrow_apply")->where("id = {$v['id']}")->save($data);
                if($status !== false){
                    if (!empty($v['coupon_id'])){
                        $sdata['status'] = 0;
                        M("member_coupon")->where("id = {$v['coupon_id']}")->save($sdata);
                    }
                    M("user_operation")->where("uid = {$v['uid']} and borrow_id = {$v['id']}")->delete();
                
                    /*$wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$v['uid']}")->find();
                    if($wxInfo['openid']!==""){
                        sendWxTempleteMsg4($wxInfo['openid'], $v['money'], $v['add_time'], $v['duration']);
                    }
                    //白骑士芝麻数据反馈接口
                    $datag        = get_global_setting();
                    $zhima        = $datag['zhima_data'];
                    if($zhima == 1){
                        bqszhimaOrder($v['id'],$v['uid'],3,"");
                    }*/
                }
                wechatLog("过期借款取消处理：借款人uid-".$v['uid']."借款编号ID-".$v['id']);
            }
            wechatLog("过期借款取消处理结束");
        }else{
	        wechatLog("无过期借款可取消");
	    }
	    
	    $listid     = M()->query(" SELECT aa.id,aa.uid,aa.coupon_id,aa.money,aa.add_time,aa.duration from ml_borrow_apply aa LEFT JOIN ml_member_status bb on aa.id = bb.borrow_id where aa.`status` = 3  and aa.len_time = 0 and bb.is_recheck = 0 and aa.add_time < ".$start);
	    if(count($listid) > 0){
	        wechatLog("过期借款取消处理开始：".$sql);
	        foreach($listid as $key=>$v){
	            $data['status'] = 99;
	            $status = M("borrow_apply")->where("id = {$v['id']}")->save($data);
	            if($status !== false){
	                M("user_operation")->where("uid = {$v['uid']} and borrow_id = {$v['id']}")->delete();
	            }
	            wechatLog("过期待授信的借款取消处理：借款人uid-".$v['uid']."借款编号ID-".$v['id']);
	        }
	        wechatLog("过期待授信的借款取消处理结束");
	    }else{
	        wechatLog("无过期待授信的借款可取消");
	    }
    }
    
    /**
     * 逾期借款用户的黑白名单更新
     */
    public function setBlack(){
        //提前两天的催款
        $due7    = strtotime("-5 day",strtotime(date("Y-m-d",time())." 00:00:00"));
        $due3    = strtotime("-2 day",strtotime(date("Y-m-d",time())." 00:00:00"));
        $today   = strtotime(date("Y-m-d",time())." 00:00:00");
        //逾期7天处理为黑名单
        $sql7    = "SELECT aa.uid,bb.is_black,bb.is_gray,bb.is_white from  ml_borrow_detail aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` = 0 and bb.is_black =0 and aa.deadline < ".$due7;
        $list7   = M()->query($sql7);
		$i = 0;
        $j = 0;
		if(count($list7) > 0){
            wechatLog("逾期5天更新成黑名单");
            foreach($list7 as $key=>$v7){
                M()->query("update ml_members set is_black = 1,is_gray = 0,is_white = 0,is_gold = 0  where id = {$v7['uid']} ");
                wechatLog("逾期5天更新黑名单UID------".$v7['uid']);
                $i++;
            }
			wechatLog("逾期5天更新黑名单结束");
        }else{
            wechatLog("逾期5天暂无黑名单更新");
        }
        wechatLog("共更新黑名单：".$i);
        
        //逾期3天的解除白名单
        $sql3    = "SELECT aa.uid,bb.is_black,bb.is_gray,bb.is_white,bb.is_gold from  ml_borrow_detail aa LEFT JOIN ml_members bb on aa.uid = bb.id where aa.`status` = 0 and (bb.is_white =1 or bb.is_gold =1 ) and aa.deadline < ".$due3;
        $list3   = M()->query($sql3);
        if(count($list3) > 0){
            wechatLog("逾期2天解除名单");
            foreach($list3 as $key=>$v3){
                M()->query("update ml_members set is_white = 0,is_gold = 0 where id = {$v3['uid']} ");
                wechatLog("逾期2天解除白金名单UID------".$v3['uid']);
                $j++;
            }
			wechatLog("逾期2天解除白金名单结束");
        }else{
            wechatLog("逾期2天暂无需要解除白金名单");
        }
        wechatLog("共解除白金名单：".$j);
    }
    
    /**
     * 更新汇潮支付的订单状态(更新支付请求5分钟后没有处理的交易)
     */
    public function updateOrder(){
        $start    = time()-300;
        $list     = M()->query(" SELECT id,outer_orderId,noncestr,scene,uid,borrow_id,item_id,amount from ml_transfer_order_pay where status = 0  and addtime < ".$start." order by addtime limit 5");
        if(count($list) > 0){
            wechatLog("汇潮支付交易状态处理开始：");
            foreach($list as $key=>$v){
                $res = queryOrder($v['outer_orderId'],$v['noncestr']);
                if($res['payResult']==1){
                    $status        = 1;
                }else{
                    $status        = 2;
                }
                updatehuichaoOrders($v,$res,$status,strtotime($res['payTime']));
                wechatLog("借款申请编号-".$v['borrow_id']."的状态为".$status."已更新完成");
            }
            wechatLog("汇潮支付交易状态处理结束：");
        }
        
    }

    //提醒用户缴取授信费(发短信)
    public function signed(){ 
        $now  = time();
        $one  = $now - 3600; //1小时
        $two  = $now - 86400;//24小时
        $tree = $now -259200;//72小时
        $text = "您在现贷猫的借款已通过审核，需付费完成信用报告以完成最终审核，付费完成100%放款，放款失败全额退款，请立即前往完成操作。";
        
        //发送短信1个小时
		$sig1     = M()->query(" SELECT * from ml_send_signed where paystatus = 0 AND send_one = 0 and sign_time<= ".$one." and sign_time> ".$two." order by sign_time limit 10");
        if(!empty($sig1)){
            foreach($sig1 as $key => $val){
                $status = M('member_status')->where("borrow_id = {$val['borrow_id']}")->field("is_recheck")->find();
                $apply  = M('borrow_apply')->where("id = {$val['borrow_id']}")->field("status")->find();
                $user = M('members')->where("id = {$val['uid']}")->field('iphone')->find();//用户信息
                $s = (int)$apply['status']; 

                if((int)$status['is_recheck'] == 1){
                    $up['paystatus'] = 1;//已授信
                    M('send_signed')->where("id = {$val['id']}")->save($up);
                }elseif($s > 90){
                    $ups['paystatus'] = 2;//已取消或失败的借款
                    M('send_signed')->where("id = {$val['id']}")->save($ups);
                }
                if((int)$status['is_recheck'] != 1 && $s < 90){
                    if($now > $one && $val['send_one'] == 0){
                        addToSms($user['iphone'],$text,1);//1个小时之后发送短信
                        $save['send_one'] = 1;
                        M('send_signed')->where("id = {$val['id']}")->save($save);
                    }
                }
            }
        }

        //发送短信24小时
		$sig2     = M()->query(" SELECT * from ml_send_signed where paystatus = 0 AND send_two = 0 and sign_time<= ".$two." and sign_time> ".$tree." order by sign_time limit 10");
        if(!empty($sig2)){
            foreach($sig2 as $key => $val){
                $status = M('member_status')->where("borrow_id = {$val['borrow_id']}")->field("is_recheck")->find();
                $apply  = M('borrow_apply')->where("id = {$val['borrow_id']}")->field("status")->find();
                $user = M('members')->where("id = {$val['uid']}")->field('iphone')->find();//用户信息
                $s = (int)$apply['status'];
                if((int)$status['is_recheck'] == 1){
                    $up['paystatus'] = 1;//已授信
                    M('send_signed')->where("id = {$val['id']}")->save($up);
                }elseif($s > 90){
                    $ups['paystatus'] = 2;//已取消或失败的借款
                    M('send_signed')->where("id = {$val['id']}")->save($ups);
                }
                if((int)$status['is_recheck'] != 1 && $s < 90){
                    if($now > $two && $val['send_two'] == 0){
                        addToSms($user['iphone'],$text,1);//24个小时之后发送短信
                        $save['send_two'] = 1;
                        M('send_signed')->where("id = {$val['id']}")->save($save);
                    }
                }
            }
        }

        //发送短信72小时
		$sig3     = M()->query(" SELECT * from ml_send_signed where paystatus = 0 AND send_tree = 0 and sign_time<= ".$tree." order by sign_time limit 10");
        if(!empty($sig3)){
            foreach($sig3 as $key => $val){
                $status = M('member_status')->where("borrow_id = {$val['borrow_id']}")->field("is_recheck")->find();
                $apply  = M('borrow_apply')->where("id = {$val['borrow_id']}")->field("status")->find();
                $user = M('members')->where("id = {$val['uid']}")->field('iphone')->find();//用户信息
                $s = (int)$apply['status'];
                if((int)$status['is_recheck'] == 1){
                    $up['paystatus'] = 1;//已授信
                    M('send_signed')->where("id = {$val['id']}")->save($up);
                }elseif($s > 90){
                    $ups['paystatus'] = 2;//已取消或失败的借款
                    M('send_signed')->where("id = {$val['id']}")->save($ups);
                }
                if((int)$status['is_recheck'] != 1 && $s < 90){
                    if($now > $tree && $val['send_tree'] == 0){
                        addToSms($user['iphone'],$text,1);//72小时之后发送短信
                        $save['send_tree'] = 1;
                        M('send_signed')->where("id = {$val['id']}")->save($save);
                    }
                }
            }
        }
    }

    //提醒用户缴取授信费(微信&&App推送)
    public function sigwx(){ 
        $now  = time();
        $one  = $now - 518400; //6天
        $two  = $now - 691200;//8天
        $tree = $now - 950400;//11天
        
        //发送推送6天
        $sig1  = M()->query("SELECT a.is_recheck,b.add_time,a.uid,b.id from ml_member_status a LEFT JOIN ml_borrow_apply b on a.borrow_id = b.id where b.status = 3 AND a.is_recheck = 0 AND a.signed_time<= ".$one." and a.signed_time> ".$two." order by signed_time");
        if(!empty($sig1)){
            foreach($sig1 as $key => $val){
                $user    = M('member_info')->where("uid = {$val['uid']}")->field('real_name')->find();//用户信息
                $wxInfo  = M("member_wechat_bind")->field('openid')->where("uid = {$val['uid']}")->find();//openid
                $token   = M('member_umeng')->where("uid = {$val['uid']}")->field(true)->find();//app token
                $addtime = date("Y年m月d日",$val['add_time']);
                //发送微信推送
                if(!empty($wxInfo['openid'])){
                    $this->sendWxTemcheckoneMsg($wxInfo['openid'],$user['real_name'],$addtime);
                }
                //发送App推送
                if(!empty($token['token'])){
                    $this->AndroidTempleteMsgD($val['uid'],$token['token'],$val['id'],13);
                }
            }
        }

        //发送推送8天
        $sig2  = M()->query("SELECT a.is_recheck,b.add_time,a.uid,b.id from ml_member_status a LEFT JOIN ml_borrow_apply b on a.borrow_id = b.id where b.status = 3 AND a.is_recheck = 0 AND a.signed_time<= ".$two." and a.signed_time> ".$tree." order by signed_time");
        if(!empty($sig2)){
            foreach($sig2 as $key => $val){
                $user    = M('member_info')->where("uid = {$val['uid']}")->field('real_name')->find();//用户信息
                $wxInfo  = M("member_wechat_bind")->field('openid')->where("uid = {$val['uid']}")->find();//openid
                $token   = M('member_umeng')->where("uid = {$val['uid']}")->field(true)->find();//app token
                $addtime = date("Y年m月d日",$val['add_time']);
                //发送微信推送
                if(!empty($wxInfo['openid'])){
                    $this->sendWxTemcheckoneMsg($wxInfo['openid'],$user['real_name'],$addtime);
                }
                //发送App推送
                if(!empty($token['token'])){
                    $this->AndroidTempleteMsgD($val['uid'],$token['token'],$val['id'],13);
                }
            }
        }

        //发送推送11天
        $sig3  = M()->query("SELECT a.is_recheck,b.add_time,a.uid,b.id from ml_member_status a LEFT JOIN ml_borrow_apply b on a.borrow_id = b.id where b.status = 3 AND a.is_recheck = 0 AND a.signed_time<= ".$tree." order by signed_time");
        if(!empty($sig3)){
            foreach($sig3 as $key => $val){
                $user    = M('member_info')->where("uid = {$val['uid']}")->field('real_name')->find();//用户信息
                $wxInfo  = M("member_wechat_bind")->field('openid')->where("uid = {$val['uid']}")->find();//openid
                $token   = M('member_umeng')->where("uid = {$val['uid']}")->field(true)->find();//app token
                $addtime = date("Y年m月d日",$val['add_time']);
                //发送微信推送
                if(!empty($wxInfo['openid'])){
                    $this->sendWxTemchecktwoMsg($wxInfo['openid'],$user['real_name'],$addtime);
                }
                //发送App推送
                if(!empty($token['token'])){
                    $this->AndroidTempleteMsgD($val['uid'],$token['token'],$val['id'],14);
                }
            }
        }
    }



    //-------------------------------------------------------------------封装开始-------------------------------------------------------//
   /**
   * 签约后11天后提醒用户授信的微信模板
   * @param 微信openId $openid
   * @param 姓名 $name
   * @param 申请日期 $date
   * @return
   */
    public function sendWxTemchecktwoMsg($openid,$name,$date){
        $wxInfo = M("wechat_msg")->field(true)->where(" scene = 26 ")->find();
        $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $name,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $date,
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> $wxInfo['keyword3'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wechatLog("第一次&&第二次微信推送".$res);
     return $res;
    }

    /**
     * 签约后6天&&8天后提醒用户授信的微信模板
     * @param 微信openId $openid
     * @param 姓名 $name
     * @param 申请日期 $date
     * @return
     */
    public function sendWxTemcheckoneMsg($openid,$name,$date){
        $wxInfo = M("wechat_msg")->field(true)->where(" scene = 25 ")->find();
        $tempLateData = array(
         'touser'       =>$openid,
         'template_id'  =>$wxInfo['msg_id'],
         'url'          =>$wxInfo['url'],
         'topcolor'=>"#777777",
         'data'=>array(
             'first'=>array('value'=>$wxInfo['first'],
                 'color'=>"#777777"),
             'keyword1'=>array('value'=> $name,
                 'color'=>"#777777"),
             'keyword2'=>array('value'=> $date,
                 'color'=>"#777777"),
             'keyword3'=>array('value'=> $wxInfo['keyword3'],
                 'color'=>"#777777"),
             'remark'=>array('value'=>$wxInfo['remark'],
                 'color'=>"#777777"),
         )
     );
     $res = WxSendTemplateMsg($tempLateData,1);
     wechatLog("第三次微信推送".$res);
     return $res;
    }

    //App授信提醒推送
    public function AndroidTempleteMsgD($uid,$token,$bid,$mid){
         $where['id'] = $mid;
         $msg = M('ad_msg_tpl')->where($where)->field(true)->find();
         //初始化
         $go_url = "";
         $activity = "";
         if($msg['after_open'] == 'go_url'){//跳转H5页面
             $go_url = $msg['url'];
         }
         if($msg['after_open'] == 'go_activity'){//跳转APP页面
             $activity = $msg['url'];
         }
         $param = array(
             "uid"          => $uid,
             "bid"          => $bid,//多个推送的时候为0，单个推送的时候需要填写申请单号
             "chaining"     => $msg['chaining'],
             "ticker"       => $msg['ticker'],  //通知栏提示文字
             "title"        => $msg['title'],//通知标题
             "text"         => $msg['text'],//通知文字描述
             "after_open"   => $msg['after_open'],//点击通知的后续行为
             "url"          => $go_url,//go_url时跳转到URL
             "activity"     => $activity,//go_activity时打开特定的activity
             "tokens"       => $token //发送对象的token
         );
     
         $return = ad_unicast($param);//单个推送
         appLog($return);
    }
}