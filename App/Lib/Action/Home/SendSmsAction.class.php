<?php
class SendSmsAction extends HCommonAction{
    
    /**
     * 发送短信
     */
	public function index(){
	    $msgconfig = FS("Webconfig/msgconfig");
	    $onoff = $msgconfig['sms']['onoff'];
	    $type  = $msgconfig['sms']['type'];
	    //短信发送开关没有关闭
	    if($onoff==0){
	        $list = M('send_sms')->field(true)->where(" status = 0 ")->select();
	        if($list){
	            foreach($list as $k => $v){
	                $phone   = $v['phone'];
	                $content = $v['content'];
	                if($type==2){
	                    $res = mdSendsms($phone, $content);//绿麻雀
	                }elseif($type==1){
	                    if($v['type']==1){
	                        $res = dhSendsmsC($phone, $content);//大汉三通
	                    }else{
	                        $res = dhSendsms($phone, $content);//大汉三通
	                    }
	                }
	                $res = 1;
	                if($res){
	                    $updata['status'] = "1";
	                    M("send_sms")->where(" id= '{$v['id']}' ")->save($updata);
	                }
	            }
	        }
	    }
	    
	   
	    
	}
}