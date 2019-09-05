<?php
// 本类由系统自动生成，仅供测试用途845
class SendAction extends HCommonAction {
    
    public  function  wechat(){
        $openid = "oItly1TLtOmdXTtKxVKq8XbHvK7A";
        $msg    = "_RXYP2jmfHkZMtNkxQfEqamO27Bj7SpH7KveKwXv-hU";
        
        $url    = "http://www.cashmall.com.cn/WeChat/getApiCode";
        $list = M("member_wechat_bind") -> field("openid") -> where(" 1=1 ") -> select();
        
        if (is_array($list) && !empty($list)) {
            wechatLog("微信发送开始：");
            foreach ($list as $v) {
                echo $v['openid']."<br />";
                $tempLateData = array(
                     'touser'       =>$v['openid'],
                     'template_id'  =>$msg,
                     'url'          =>$url,
                     'topcolor'=>"#777777",
                     'data'=>array(
                         'first'=>array('value'=>"喜迎国庆，现贷猫审核大放水，现撸现拿钱，疯狂放款中... ...",
                             'color'=>"#d73d3d"),
                         'keyword1'=>array('value'=> "现贷猫",
                             'color'=>"#777777"),
                         'keyword2'=>array('value'=> date("Y-m-d",time()),
                             'color'=>"#777777"),
                         'keyword3'=>array('value'=> "500-1000元",
                             'color'=>"#777777"),
                         'keyword4'=>array('value'=> "7-14天",
                             'color'=>"#777777"),
                         'remark'=>array('value'=>"低门槛，急速放款！",
                             'color'=>"#d73d3d"),
                     )
                 );
                 $res = WxSendTemplateMsg($tempLateData,1);
            }
            wechatLog("微信发送结束");
        }
    }
   
} 
