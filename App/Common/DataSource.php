<?php
function get_money_log($uid)
{
    $uid = intval($uid);
    $log = array();
    if ($uid) {
        $list = M("member_moneylog")->field('type,sum(affect_money) as money')->where("uid={$uid}")->group('type')->select();
    } else {
        $list = M("member_moneylog")->field('type,sum(affect_money) as money')->group('type')->select();
    }

    foreach ($list as $v) {
        $log[$v['type']]['money'] = ($v['money'] > 0) ? $v['money'] : $v['money'] * (-1);
        $log[$v['type']]['name'] = $name[$v['type']];
    }

    return $log;
}

function ajaxmsg($msg = "", $type = 1, $is_end = true)
{
    $json['status'] = $type;
    if (is_array($msg)) {
        foreach ($msg as $key => $v) {
            $json[$key] = $v;
        }
    } elseif (!empty($msg)) {
        $json['message'] = $msg;
    }
    if ($is_end) {
        echo json_encode($json);
        exit;
    } else {
        echo json_encode($json);
        exit;
    }
}

//字段文字内容隐藏处理方法
function hidecard($cardnum, $type = 1, $default = "")
{
    if (empty($cardnum)) {
        return $default;
    }
    if ($type == 1) {
        $cardnum = substr($cardnum, 0, 3) . str_repeat("*", 12) . substr($cardnum, strlen($cardnum) - 4);
    }//身份证
    elseif ($type == 2) {
        $cardnum = substr($cardnum, 0, 3) . str_repeat("*", 5) . substr($cardnum, strlen($cardnum) - 4);
    }//手机号
    elseif ($type == 3) {
        $cardnum = str_repeat("*", strlen($cardnum) - 4) . substr($cardnum, strlen($cardnum) - 4);
    }//银行卡
    elseif ($type == 4) {
        $cardnum = substr($cardnum, 0, 3) . str_repeat("*", strlen($cardnum) - 3);
    }//用户名
    elseif ($type == 5) {
    	$length = mb_strlen($cardnum,'utf-8');
        $cardnum = mb_substr($cardnum, 0, 1, 'utf-8').str_repeat("*",$length-1);//.mb_substr($cardnum, -1, 1, 'utf-8');
    }//新用户名
    return $cardnum;
}

function setmb($size)
{
    $mbsize = $size / 1024 / 1024;
    if ($mbsize > 0) {
        list($t1, $t2) = explode(".", $mbsize);
        $mbsize = $t1 . "." . substr($t2, 0, 2);
    }

    if ($mbsize < 1) {
        $kbsize = $size / 1024;
        list($t1, $t2) = explode(".", $kbsize);
        $kbsize = $t1 . "." . substr($t2, 0, 2);

        return $kbsize . "KB";
    } else {
        return $mbsize . "MB";
    }

}

function getMoneyFormt($money)
{
    if ($money >= 100000 && $money <= 100000000) {
        $res = getFloatValue(($money / 10000), 2) . "万";
    }elseif ($money>=10000&&$money<100000){
        $res = getFloatValue(($money / 10000), 2) . "万";
    } else {
        if ($money >= 100000000) {
            $res = getFloatValue(($money / 100000000), 2) . "亿";
        } else {
            $res = getFloatValue($money, 0);
        }
    }

    return $res;
}

function getArea()
{
    $area = FS("Webconfig/area");
    if (!is_array($area)) {
        $list = M("area")->getField("id,name");
        FS("area", $list, "Webconfig/");
    } else {
        return $area;
    }
}

//信用等级图标显示
function getLeveIco($num, $type = 1)
{
    $leveconfig = FS("Webconfig/leveconfig");
    foreach ($leveconfig as $key => $v) {
        if ($num >= $v['start'] && $num <= $v['end']) {
            if ($type == 1) {
                return "/UF/leveico/" . $v['icoName'];
            } elseif ($type == 2) {
                return '<a  target="_blank" href="' . __APP__ . '/member/credit#fragment-1"><img src="' . __ROOT__ . '/UF/leveico/' . $v['icoName'] . '" title="' . $v['name'] . '"/></a>';
            } elseif ($type == 3) {
                return '<a href="' . __APP__ . '/member/credit#fragment-1">' . $v['name'] . '</a>';
            }//手机版使用
            else {
                return '<a href="' . __APP__ . '/member/credit#fragment-1"><img src="' . __ROOT__ . '/UF/leveico/' . $v['icoName'] . '" title="' . $v['name'] . '"/></a>';
            }
        }
    }
}

//投资等级图标显示
function getInvestLeveIco($num, $type = 1)
{
    $leveconfig = FS("Webconfig/leveinvestconfig");
    foreach ($leveconfig as $key => $v) {
        if ($num >= $v['start'] && $num <= $v['end']) {
            if ($type == 1) {
                return "/UF/leveico/" . $v['icoName'];
            } elseif ($type == 2) {
                return '<a target="_blabk" href="' . __APP__ . '/member/credit#fragment-2"><img src="' . __ROOT__ . '/UF/leveico/' . $v['icoName'] . '" title="' . $v['name'] . '"/></a>';
            } elseif ($type == 3) {
                return $v['name'];//手机版使用
            } else {
                return '<a href="' . __APP__ . '/member/credit#fragment-2"><img src="' . __ROOT__ . '/UF/leveico/' . $v['icoName'] . '" title="' . $v['name'] . '"/></a>';
            }
        }
    }
}

function getAgeName($num)
{
    $ageconfig = FS("Webconfig/ageconfig");
    foreach ($ageconfig as $key => $v) {
        if ($num >= $v['start'] && $num <= $v['end']) {
            return $v['name'];
        }
    }
}

function getLocalhost()
{
    $vo['id'] = 1;
    $vo['name'] = "主站";
    $vo['domain'] = "www";

    return $vo;
}

function Fmoney($money)
{
    if (!is_numeric($money)) {
        return "0.00";
    }
    $sb = "";
    if ($money < 0) {
        $sb = "-";
        $money = $money * (-1);
    }

    $dot = explode(".", $money);
    $dot[1]	= substr($dot[1], 0,2);
    $tmp_money = strrev_utf8($dot[0]);
    $format_money = "";
    for ($i = 3; $i < strlen($dot[0]); $i += 3) {
        $format_money .= substr($tmp_money, 0, 3) . ",";
        $tmp_money = substr($tmp_money, 3);
    }
    $format_money .= $tmp_money;
    if (empty($sb)) {
        $format_money = strrev_utf8($format_money);
    } else {
        $format_money = strrev_utf8($format_money);
    }
    if ($dot[1]) {
        return $format_money . "." . $dot[1];
    } else {
        return $format_money.".00";
    }
}

function strrev_utf8($str)
{
    return join("", array_reverse(
        preg_split("//u", $str)
    ));
}

function getInvestUrl($id)
{
    return __APP__ . "/invest/{$id}" . C('URL_HTML_SUFFIX');
}

//获取管理员ID对应的名称,以id为键
function get_admin_name($id = false)
{
    $stype = "adminlist";
    $list = array();
    if (!S($stype)) {
        $rule = M('ausers')->field('id,user_name')->select();
        foreach ($rule as $v) {
            $list[$v['id']] = $v['user_name'];
        }

        S($stype, $list, 3600 * C('HOME_CACHE_TIME'));
        if (!$id) {
            $row = $list;
        } else {
            $row = $list[$id];
        }
    } else {
        $list = S($stype);
        if ($id === false) {
            $row = $list;
        } else {
            $row = $list[$id];
        }
    }

    return $row;
}


//添加会员操作记录
function addMsg($from, $to, $title, $msg, $type = 1)
{
    if (empty($from) || empty($to)) {
        return;
    }
    $data['from_uid'] = $from;
    $data['from_uname'] = M('members')->getFieldById($from, "user_name");
    $data['to_uid'] = $to;
    $data['to_uname'] = M('members')->getFieldById($to, "user_name");
    $data['title'] = $title;
    $data['msg'] = $msg;
    $data['add_time'] = time();
    $data['is_read'] = 0;
    $data['type'] = $type;
    $newid = M('member_msg')->add($data);

    return $newid;
}

//注册专用
function rand_string_reg($len = 6, $type = '1', $utype = '1', $addChars = '')
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) {//位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    $chars = str_shuffle($chars);
    $str = substr($chars, 0, $len);
    session("code_temp", $str);
    session("send_time", time());

    return $str;
}



/**
 * 过滤上传资料类型
 *
 * @param array $arr // Webconfig/integration 文件
 */
function FilterUploadType($arr)
{
    $uploadType = array();
    if (is_array($arr)) {
        foreach ($arr as $key => $val) {
            if (is_numeric($key)) {
                $uploadType[$key] = $val;
            }
        }
    }

    return $uploadType;
}

/**
 * 获取当前用户没有上传过的上传资料类型
 *
 * @param int $uid // 用户id
 */
function get_upload_type($uid)
{
    $integration = FilterUploadType(FS("Webconfig/integration"));
    $uploadType = M('member_data_info')->field('type')->where("uid='{$uid}' and status in (0,1)")->select();
    foreach ($uploadType as $row) {
        unset($integration[$row['type']]);
    }
    foreach ($integration as $key => $val) {
        $integration[$key] = $val['description'];
    }

    return $integration;
}

/****************************漫道短信接口开始****************************/
 /**
  * 手机短信漫道接口（漫道短信www.zucp.net）
  * @param 手机号码 $mob
  * @param 短信内容 $content
  * @return boolean
  */
function mdSendsms($mob, $content)
{
    $msgconfig = FS("Webconfig/msgconfig");
    $type      = $msgconfig['sms']['type'];// type=2 绿麻雀漫道短信接口
    
    //如果您的系统是utf-8,请转成GB2312 后，再提交
    $flag = 0;
    $argv = array(
        'sn'      => $msgconfig['sms']['user2'], //绿麻雀漫道帐号
        'pwd'     => $msgconfig['sms']['pass2'], //绿麻雀漫道密码需要加密 加密方式为 md5(sn+password) 32位大写
        'mobile'  => $mob,//手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
        'content' => iconv("UTF-8", "gb2312//IGNORE", $content."【福米金融】"),//短信内容
        'ext'     => '',
        'stime'   => '',//定时时间 格式为2011-6-29 11:09:21
        'rrid'    => ''
    );
    //构造要post的字符串
    foreach ($argv as $key => $value) {
        if ($flag != 0) {
            $params .= "&";
            $flag = 1;
        }
        $params .= $key . "=";
        $params .= urlencode($value);
        $flag = 1;
    }
    $length = strlen($params);
    //创建socket连接
    $fp = fsockopen("sdk2.zucp.net", 8060, $errno, $errstr, 10) or exit($errstr . "--->" . $errno);
    //构造post请求的头
    $header = "POST /webservice.asmx/mt HTTP/1.1\r\n";
    $header .= "Host:sdk2.zucp.net\r\n";
    $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
    $header .= "Content-Length: " . $length . "\r\n";
    $header .= "Connection: Close\r\n\r\n";
    //添加post的字符串
    $header .= $params . "\r\n";
    //发送post的数据
    fputs($fp, $header);
    $inheader = 1;
    while (!feof($fp)) {
        $line = fgets($fp, 1024); //去除请求包的头只显示页面的返回数据
        if ($inheader && ($line == "\n" || $line == "\r\n")) {
            $inheader = 0;
        }
        if ($inheader == 0) {
            // echo $line;
        }
    }
    $line = str_replace("<string xmlns=\"http://tempuri.org/\">", "", $line);
    $line = str_replace("</string>", "", $line);
    $result = explode("-", $line);
    if (count($result) > 1) {
        return true;
    } else {
        return true;
    }

}
/****************************漫道短信接口结束************************************/

//手机日志
function alogsm($type, $tid, $tstatus, $deal_info = '', $deal_user = '')
{
    $arr = array();
    $arr['type'] = $type;
    $arr['tid'] = $tid;
    $arr['tstatus'] = $tstatus;
    $arr['deal_info'] = $deal_info;

    $arr['deal_user'] = session("u_id");
    $arr['deal_ip'] = get_client_ip();
    $arr['deal_time'] = time();
    //dump($arr);exit;
    $newid = M("auser_dologs")->add($arr);

    return $newid;
}

/**
 * 添加积分记录
 * @param 借款会员 $uid
 * @param 类型 $type (1:正常还款后增加积分  2:逾期借款后扣除积分)
 * @param 积分 $integral
 * @param 备注 $info
 * @return boolean
 */
function addIntegral($uid,$type,$integral,$info="无"){
    if($integral==0) return true;
    $pre  = C('DB_PREFIX');
    $done = false;

    $Db   = new Model();
    $Db->startTrans(); //多表事务

    $Member = $Db->table($pre."members")->where("id=$uid")->find();

    $data['uid']                = $uid;
    $data['type']               = $type;
    $data['affect_integral']    = $integral;
    if($type == 2){
        $data['account_integral']   = $Member['integral']- $integral;
    }else{
        $data['account_integral']   = $integral + $Member['integral'];
    }
    $data['info']               = $info;
    $data['add_time']           = time();
    $data['add_ip']             = get_client_ip();

    $newid = $Db->table($pre.'member_integrallog')->add($data);//积分细则
    
    if($integral>0) {
        $yid = $Db->table($pre."members")->where("id=$uid")->setInc('integral',$integral);//积分总数
    }else{ 
        $yid = true;
    
    }

    if($newid && $yid){
        $Db->commit() ;
        $done = true;
    }else{
        $Db->rollback() ;
    }

    return $done;
}


/**
 * 获取积分记录
 * @param 检索条件 $map
 * @param 分组 $size
 */
function getIntegralLogList($map,$size){
    if(empty($map['uid'])) return;

    if($size){
        //分页处理
        import("ORG.Util.Page");
        $count = M('member_integrallog')->where($map)->count('id');
        $p     = new Page($count, $size);
        $page  = $p->show();
        $Lsql  = "{$p->firstRow},{$p->listRows}";
        //分页处理
    }

    $list       = M('member_integrallog')->where($map)->order('id DESC')->limit($Lsql)->select();
    $type_arr   = C("INTEGRAL_LOG");
    foreach($list as $key=>$v){
        $list[$key]['type'] = $type_arr[$v['type']];
    }

    $row = array();
    $row['list'] = $list;
    $row['page'] = $page;
    return $row;
}


/**
 * 获取省份的内容
 * @param 选项值 $id
 */
 
function get_province($id){
    $list  = M("province")->field(" province ")->where("id={$id}")->find();
    return $list['province'];
}

/**
 * 获取省份列表
 * @param 选项ID $id
 * @return 
 */
function get_province_list(){
    $options = array();
    $list  = M("province")->field(" id, province ")->select();
    foreach($list as $key => $v){
        $options[$v['id']] = $v['province'];
    }
    return $options;
}

/**
 * 获取城市的内容
 * @param 选项值 $id
 */
function get_city($id){
    $list  = M("city")->field(" city ")->where("id={$id}")->find();
    return $list['city'];
}

/**
 * 获取城市列表
 * @param 选项ID $id
 * @return
 */
function get_city_list($id){
    $options = array();
    $list  = M("city")->field(" id, city ")->where(" province_id = {$id} ")->select();
    foreach($list as $key => $v){
        $options[$v['id']] = $v['city'];
    }
    return $options;
}

/**
 * 获取银行城市的内容
 * @param 选项值 $id
 */
function get_bank_city($id){
    $list  = M("bank_city")->field(" city ")->where("id={$id}")->find();
    return $list['city'];
}

/**
 * 获取银行城市列表
 * @param 选项ID $id
 * @return
 */
function get_bank_city_list($id){
    $options = array();
    $list  = M("bank_city")->field(" city_code, city ")->where(" province_code = {$id} and status = 1 ")->select();
    foreach($list as $key => $v){
        $options[$v['city_code']] = $v['city'];
    }
    return $options;
}

/**
 * 添加短信发送数据
 * @param 手机号码 $phone
 * @param 短信内容 $content
 * @return 插入成功的ID记录
 */
function addToSms($phone,$content,$type)
{
    if (empty($phone) || empty($content)) {
        return;
    }
    if($type == 1){
        $data['type'] = 1; 
    }
    $data['phone']    = $phone;
    $data['content']  = $content;
    $data['add_time'] = time();
    $newid = M('send_sms')->add($data);

    return $newid;
}

/**
 * 检查手机是否在新浪支付已经绑定
 * @param 手机号码 $phone
 * @return 已经存在的新浪帐号$uid
 */
function checkSinaUid($phone){
    $dataname = C('DB_NAME_SINA');
    $db_host  = C('DB_HOST_SINA');
    $db_user  = C('DB_USER_SINA');
    $db_pwd   = C('DB_PWD_SINA');
    $uid = '';
    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $sql = "SELECT * FROM lzh_members WHERE user_phone = '{$phone}' ";
    foreach ($bdb->query($sql) as $row) {
        $uid = $row['id'];
    }
    return $uid;
}


/**
 * 查看老用户是否已实名
 */
function checkSinaAuth($uid){
    $uid        = substr($uid, 4);
    $dataname = C('DB_NAME_SINA');
    $db_host  = C('DB_HOST_SINA');
    $db_user  = C('DB_USER_SINA');
    $db_pwd   = C('DB_PWD_SINA');
    
    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $sql = "SELECT b.real_name,b.idcard FROM lzh_members_status a LEFT JOIN lzh_member_info b on a.uid = b.uid
    WHERE a.id_status = 1 AND b.uid = ".$uid;
    $list = $bdb->query($sql);
	
    if(count($list)>0){
        foreach ($list as $row) {
            $data['real_name'] = $row['real_name'];
            $data['id_card']   = $row['idcard'];
        }

        return $data;
    }else{
        return false;
    } 
}


/**
 * 提取微信版百度统计JS
 * @return 百度JS
 */
function get_baidu()
{
    $datag = get_global_setting();
    $baidu_count = $datag['baidum_count'];

    return $baidu_count;
}


/**提取同盾JS
 * @return 同盾JS
 */
function get_tongdun_fir()
{
    $datag = get_global_setting();
    $tongdun_js_fir = $datag['td_js_fir'];

    return $tongdun_js_fir;
}

/**提取同盾JS
 * @return 同盾JS
 */
function get_tongdun_sec()
{
    $datag = get_global_setting();
    $tongdun_js_sec = $datag['td_js_sec'];

    return $tongdun_js_sec;
}

/**提取白骑士JS
 * @return 白骑士JS
 */
function get_baiqishi_fir()
{
    $datag = get_global_setting();
    $baiqishi_js_fir = $datag['bqs_js_fir'];

    return $baiqishi_js_fir;
}

/**提取白骑士JS
 * @return 白骑士JS
 */
function get_baiqishi_sec()
{
    $datag = get_global_setting();
    $baiqishi_js_sec = $datag['bqs_js_sec'];

    return $baiqishi_js_sec;
}

/**
 * 获取IP地址对应的城市
 * @param IP地址 $ip
 */
function get_ipAddress($ip)
{
    $content = file_get_contents("http://api.map.baidu.com/location/ip?ak=VeAy6iGyIC7bu0AqigPkY2E1&ip={$ip}");
    $json    = json_decode($content);
    
    return $json->{'content'}->{'address'};
}

 /**
  * 百度接口验证地址
  * @param 详细地址 $address
  * @param 城市 $city
  */
function get_Address($address,$city)
{
    $content = file_get_contents("http://api.map.baidu.com/geocoder/v2/?callback=renderOption&output=json&address=".$address."&city=".$city."&ak=VeAy6iGyIC7bu0AqigPkY2E1");
    return $content;
}


/**
 * 获取逾期天数
 * @param 还款到期日 $deadline
 * @return 天数
 */
function get_due_day($deadline)
{
    if($deadline<1000) return "数据有误";
    $deadtime   =  strtotime("+1 day",strtotime(date("Y-m-d",$deadline)." 00:00:00"));
    $starttime  = strtotime(date("Y-m-d",time())." ".date("H:i:s",$deadline));
    $endtime    = strtotime(date("Y-m-d",time())." 23:59:59");
    if(time()>$deadtime){
        if(time()>$deadline){
            if(time() < $endtime && time()> $starttime){
                return ceil( (time()-$deadline)/3600/24-1);
            }else{
                return ceil( (time()-$deadline)/3600/24);
            }
        }else{
            return 1;
        }
    }else{
        return  0;
    }
}

/**
 * 获取逾期罚息
 * @param 借款金额 $money
 * @param 产品ID $itemid
 * @param 逾期天数 $day
 * @return 费用
 */
function get_due_fee($money,$itemid,$day)
{
    $item   =  M('borrow_item ')->field('due_rate')->where("id = ".$itemid)->find();
    $amount = getFloatValue(($money*$item['due_rate']/100)*100/100,2)*$day;
    
    return $amount;
}

/**
 * 获取逾期管理费
 * @param 借款金额 $money
 * @param 产品ID $itemid
 * @param 逾期天数 $day
 * @return 费用
 */
function get_late_fee($money,$itemid,$day)
{
    $datag   = get_global_setting();
    $dueday  = $datag['dueday_max'];
    //逾期天数大于设置天数的 都变成设置天数
    if($dueday && $day > $dueday){
        $day = $dueday;
    }

    $item    = M('borrow_item ')->field('late_rate')->where("id = ".$itemid)->find();
    $amount  = getFloatValue(($money*$item['late_rate']/100)*100/100,2)*$day;
    
    return $amount;
    
}

/**
 * 当天放款金额
 */
function get_loan_money()
{
	$starttime  = strtotime(date("Y-m-d",time())." 00:00:00");
    $endtime    = strtotime(date("Y-m-d",time())." 23:59:59");
    $sql = " len_time >= ".$starttime ." and len_time <= ".$endtime." and status in (4,5) and renewal_id = 0 ";
    $amount      =  M('borrow_apply')->where($sql)->sum('loan_money');
    return $amount;
}

/***************************现贷猫的风控start*********************************/
/**
 * 是否白名单
 * @param 借款用户UID $uid
 * @return 1为白名单
 */
function isWhite($uid){
    $is_white = M('members')->getFieldById($uid,"is_white");
    return $is_white;
}

/**
 * 是否灰名单
 * @param 借款用户UID $uid
 * @return 1为灰名单
 */
function isGray($uid){
    $is_gray = M('members')->getFieldById($uid,"is_gray");
    return $is_gray;
}

/**
 * 会员属性
 * @param 借款会员编号 $uid
 * @return 会员数据集合
 */
function attributes($uid){
    $mem = M('members')->field('is_black,is_white,is_gray,is_gold')->where("id = {$uid}")->find();
    return  $mem;
}

/**
 * 注册时检测撞击黑白灰名单
 * @param 借款用户UID $uid
 * @return 会员类型 $type 1：黑名单 2：白名单 3：灰名单
 */
function memberType($iphone){
    $mem = M('member_types')->where("iphone = '{$iphone}' ")->order('add_time desc')->limit("1")->find();
    return $mem['type'];
}

/**
 * 是否黑名单
 * @param 借款用户UID $uid
 * @return 1为黑名单
 */
function isBlack($uid){
    $is_black = M('members')->getFieldById($uid,"is_black");
    return $is_black;
}

 /**
  * 年龄地域职业的风控策略
  * @param 借款会员 $uid
  * @return $flg 0:拒绝 1：通过 
  */
function mallRisk($uid){
    $flg = 1;
    $job = 1;
    $meminfo = M('member_info')->field(true)->where("uid = {$uid} ")->find();
    $memcom  = M('member_company')->field(true)->where("uid = {$uid} ")->find();
    
    //年龄 XX岁<年龄<XX岁的算通过，之外的拒绝
    $now    = time();
    $year   = date("Y",$now);
    $birth  = substr($meminfo['id_card'], 6,4);
    $age    = $year-$birth;
 
    $datag = get_global_setting();
    $ageArr = explode(',', $datag['age_condition']);
  
    if($age>=$ageArr[0] && $age<=$ageArr[1]){
        $flg = 1;
    }else{
        $flg = 0;
    }
    
    
    //职业属于军官，警察，消防员，现役军人，学生，失业，实习生，退休的拒绝
    switch ($memcom['job_title']){
        case "军官";
            $job = 0;
        break;
        case "现役军人";
        $job = false;
        break;
        case "警察，消防员";
            $job = 0;
        break;
        case "学生";
            $job = 0;
        break;
        case "实习生";
            $job = 0;
        break;
        case "退休";
            $job = 0;
        break;
        case "失业";
            $job = 0;
        break;
        default;
        break;
    }
    
    if($job == 0){
        $flg = 0;
    }
    
    //真实姓名
    if(strlen($meminfo['real_name'])>=15){
        $flg = 0;
    }
    
    //长期居住地或者工作地的省份为新疆，西藏的拒绝
    if($meminfo['province']==30 ||$meminfo['province']==26 || $memcom['company_province']==30 || $memcom['company_province']==26){
        $flg = 0;
    }
    
    return $flg;
}

/**
 * 芝麻分策略
 * @param 借款 $uid
 * @param 类型 $type 1:白名单 2：灰名单 
 * @return $flg 0:拒绝 1：通过 
 */
function mallZhima($uid,$type){
    $datag   = get_global_setting();
    $zhima   = explode(',', $datag['zhima_condition']);
    
    $flg     = 1;
    $score   = M('zhima_score')->field("uid, score")->where("uid = {$uid} ")->limit("1")->order(" score_time Desc")->find();
    if($type == 1){
        //白名单芝麻分
        if($score['score']>=$zhima[0]){
            $flg = 1;
        }else{
            $flg = 0;
        }
    }else{
        //灰名单芝麻分
        if($score['score']>=$zhima[1]){
            $flg = 1;
        }else{
            $flg = 0;
        }
    } 
    return $flg;
}

/**
 * 超过30天的运营商数据（需要再次拉取的）
 * @param 手机号码 $mobile_no
 * @return $flg 0:没有超出 1：已经超出 
 */
function isRunCarrier($mobile_no){
    $flg = 0;
    $day = 0;
    $i   = 0;
    $dataname = C('DB_NAME_RISK');
    $db_host  = C('DB_HOST_RISK');
    $db_user  = C('DB_USER_RISK');
    $db_pwd   = C('DB_PWD_RISK');
    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $sql = "SELECT add_time  FROM rs_api_carrier_log WHERE mobile_no = {$mobile_no} and state = 107 order by add_time Desc limit 1  ";
    foreach ($bdb->query($sql) as $row) {
        $newtime = $row['add_time'];
        $day = (time()-$newtime)/86400;
        if($day>30){
            $flg = 1;
        }
        $i++;
    }
    if($i==0){
        $flg = 1;
    }
    return $flg;
}

/**
 * @param 等级类型 $type 
 * 0 暂未发现风险 1 命中信保逾期名单/网贷黑名单/老赖帐户/失信人执行人名单 2 有过被催收记录 
 * 3 存在多平台借款嫌疑 4 金融机构M1逾期5 金融机构M2逾期 6 金融机构M3逾期 7 金融机构M3+逾期 8 存在互联网信贷逾期记录
 * @return 1表示通过该等级风险
 */
function zanType($type){
    $flg   = 0;
    $datag = get_global_setting();
    if(strpos($datag['zan_type'],',')===false){
        if($datag['zan_type']==$type){
            $flg = 1;
        }
    }else{
        $zan   = explode(',', $datag['zan_type']);
        //众安风控等级，等级在此范围之类的都通过
        $isin  = in_array($type,$zan);
        if($isin){
            $flg = 1;
        }
    }
    return $flg;
}


/***************************现贷猫风控end*************************/

/**
 * 获取目前为止该借款申请有无线下还款或者续期 的状态
 * @param 账单编号 $borrow_id
 * @return number
 */
function haveOff($detail_id){
    $offcount = M('payoff_apply')->where(" detail_id = {$detail_id} and status in(0,1)")->count('id');
    if($offcount>0){
        $off = 1;
    }else{
        $off = 0;
    }
    return $off;
}

/**
 * 获取手机APP类别
 * @param APP name
 * @return $type 
 */
function getAppType($name){
    $type = 0;
    $dataname = C('DB_NAME_RISK');
    $db_host  = C('DB_HOST_RISK');
    $db_user  = C('DB_USER_RISK');
    $db_pwd   = C('DB_PWD_RISK');
    $bdb = new PDO('mysql:host='.$db_host.';dbname='.$dataname.'', ''.$db_user.'', ''.$db_pwd.'');
    $bdb->beginTransaction();
    $sql = " SELECT category_id  FROM rs_app_count_master WHERE name = '{$name}' order by id Desc limit 1 ";
    foreach ($bdb->query($sql) as $row) {
        $type = $row['category_id'];
    }
    if($type==""){
        $type = 22;
    }
    return $type;
}

/**
 *数字金额转换成中文大写金额的函数
 *String Int  $num  要转换的小写数字或小写字符串
 *return 大写字母
 *小数位为两位
 **/
function get_cny($num){
    $c1  = "零壹贰叁肆伍陆柒捌玖";
    $c2  = "分角元拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num*100;
    if (strlen($num)>10) {
        return "数据太长，没有这么大的钱吧，检查下";
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
            $c = $p1.$p2.$c;
        } else {
            $c = $p1.$c;
        }
        $i   = $i + 1;
        $num = $num/10;
        $arr = explode('.',$num);
        $num = $arr[0];
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    if (empty($c)) {
        return "零元整";
    }else{
        return $c."整";
    }
}

/**
 *压缩图片
 *$imgfile 值为$_FILES['tmp_name']
 *$minx  压缩的宽
 *$miny  压缩的高
 *$name  新生成图片的命名
 *$narrow_path //缩略图路径
 **/
function ImageShrink($imgfile,$minx,$miny,$name){
    //获取大图信息
    $imgarr  = getimagesize($imgfile);
    $maxx    = $imgarr[0];//宽
    $maxy    = $imgarr[1];//长
    $maxt    = $imgarr[2];//格式
    $maxm    = $imgarr['mime'];//mime类型
    $imgType = image_type_to_extension($maxt, false);
    $fun     = "imagecreatefrom{$imgType}";
    //大图资源
    $maxim   = $fun($imgfile);

    //缩放判断
    if(($minx/$maxx)>($miny/$maxy)){
        $scale  = $miny/$maxy;
    }else{
        $scale  = $minx/$maxx;
    }

    //对所求值进行取整
    $minx   = floor($maxx*$scale);
    $miny   = floor($maxy*$scale);

    //添加小图
    $minim  = imagecreatetruecolor($minx,$miny);

    //缩放函数
    imagecopyresampled($minim,$maxim,0,0,0,0,$minx,$miny,$maxx,$maxy);
    //小图输出
    header("content-type:{$maxm}");

    //判断图片类型
    switch($maxt){
        case 1:
            $imgout = "imagegif";
            break;
        case 2:
            $imgout = "imagejpeg";
            break;
        case 3:
            $imgout = "imagepng";
            break;
    }
    //缩略图路径
    $narrow_path = C('NARROW_PATH'); 
    //变量函数
    $imgout($minim,$narrow_path.$name.'.jpg');
    //释放资源
    imagedestroy($maxim);
    imagedestroy($minim);
}

/**
 * 更新借款状态中芝麻授权后的全通过（银行卡绑定后默认）
 * @param 借款申请单号 $id
 */
function updateMemStatus($uid,$id){
    $sdata['zhima_auth']     = 1;
    $sdata['zhima_time']     = time();
    $result = M("member_status")->where("borrow_id = {$id} ")->save($sdata);
    
    //走决策树
    /*$model = new  CheckUserAction();
    wqbLog("开始贷款决策树---------");
    $checkRes = $model->requestApi($uid,$id,3);
    wqbLog("结束贷款决策树---------".$checkRes);
    return  $result;*/
}


/**
 * 检查是否需要验证人脸识别
 * @param 会员编号 $uid
 * @return 1为不需要  0为需要 $flg
 */
function isIdVerify($uid){
    $flg   = 0;
    //$count = M('borrow_detail')->where(" uid = {$uid}")->count('id');
    //if($count>0){//已经放过款的无需验证
     //   $flg = 1;
    //}else{
        //已经在人脸识别有效期内成功认证过的无需验证
        $face = M('borrow_face')->field("id,finish_time")->where(" uid = {$uid}  and status = 1 ")->order('finish_time desc')->find();
        if($face['id']>0){
            $datag = get_global_setting();
            $day   = (time()-$face['finish_time'])/86400;
            if($day<$datag['face_day']){
                $flg = 1;
            }
        }
    //}
    return $flg;
}

 /**
  * H5添加face++请求记录
  * @param 会员编号 $uid
  * @param 借款申请编号 $borrow_id
  * @param 人脸识别请求编号 $biz_no
  * @param token $token
  * @param 人脸识别请求返回编号 $request_id
  * @param 人脸识别业务编号 $biz_id
  * @return 新增记录ID
  */
function insertFace($uid,$borrow_id,$biz_no,$token,$request_id,$biz_id){
    $data['uid']        = $uid;
    $data['borrow_id']  = $borrow_id;
    $data['biz_no']     = $biz_no;
    $data['request_id'] = $request_id;
    $data['biz_id']     = $biz_id;
    $data['token']      = $token;
    $data['add_time']   = time();
    $data['type']       = 1;
    $newid = M('borrow_face')->add($data);
    return $newid; 
}

/**
 * 获取Face++图片（H5版本）
 * @param 用户编号 $uid
 * @param 借款申请单号 $bid
 */
function get_face_pic($uid,$bid){
    $path_face = C("Face_PIC_PATH");
    $file      = str_replace("\\","/",$_SERVER['DOCUMENT_ROOT']).$path_face.$uid.'.jpeg';
    //检查face++有无图片
    if(!file_exists($file)){
        $face = M('borrow_face')->field("id,biz_id")->where("uid = {$uid} and borrow_id = {$bid} and status = 1 and type = 1")->order("finish_time desc")->limit(1)->find();
        if($face['id']>0){
            $res      = faceGetResult($face['biz_id']);
            $best     = $res['images']['image_best'];
            $base_img = str_replace('data:image/jpeg;base64,', '', $best);
            $filename = $uid.'.jpeg';
            base64_image_content($base_img,$filename);
        }
    }
}

/**
 * 解码base64为图片（人脸识别最佳图片上传）
 * @param 图片编码 $base_img
 * @param 用户编号 $uid
 */
function base64_image_content($base_img,$filename){
    $path = C("Face_PIC_PATH");
    $path = str_replace("\\","/",$_SERVER['DOCUMENT_ROOT']).$path.$filename;
    file_put_contents($path, base64_decode($base_img));
}

/**
 * 解码base64为图片(身份证上传)
 * @param 图片编码 $base_img
 * @param 用户编号 $uid
 */
function base64_image_id($base_img,$filename){
    $path = C("MEM_PATH");
    $path = str_replace("\\","/",$_SERVER['DOCUMENT_ROOT']).$path.$filename;
    file_put_contents($path, base64_decode($base_img));
}

 /**
  * 
  * @param 我司人脸识别请求编号 $biz_no
  * @param 类型 $type 等于1时 需要收费的错误异常
  * @param 人脸识别返回参数 $parm
  * @return unknown
  */
function updateFace($biz_no,$status,$type,$parm){

    if($type==1){
        $score             = 0;
        $data['status']    = 1;
    }else{
        $score = $parm['verify_result']['result_faceid']['confidence'];
        $data['status']    = $status;
    }
    $data['score']         = $score;
    $data['finish_time']   = time();
    $update = M('borrow_face')->where("biz_no = '{$biz_no}'")->save($data);
    

    /*$data['biz_no']       = $biz_no;
    $data['type']         = $type;
    $data['up_times']     = $biz_id;
    $data['face_no_found']      = $token;
    $data['add_time']   = time();
    $newid = M('borrow_face_detail')->add($data);*/
    return $update;
}

  /**
   * 还原优惠券状态为0
   * @param 优惠券ID  $coupon_id
   */
  function updateCoupon($coupon_id){
    if ($coupon_id>0){
        $cdata['status'] = 0;
        $cdata['id']     = $coupon_id;
        M("member_coupon")->save($cdata);
    }
  }


/**
 * 验证拍拍信是否通过
 * @param 会员编号 $uid
 * @return $flg true为通过，false为不通过(白，金名单直接默认通过)
 */
function isPpc($uid){
    $flg = 0;
    $mem = attributes($uid);
    if($mem['is_white']==0 && $mem['is_gold']==0){
        $info = M('member_info')->field("iphone,id_card,real_name")->where("uid = {$uid} ")->find();
        $res  = getppc($info['id_card'],$info['iphone'],$info['real_name']);
        $flg  = $res;
    }else{
        $flg = 1;
    }
    return $flg;
}

/**
 * 微信分享
 * @param 要分享的页面URL$url
 * @param 分享页面的标题   $title
 * @param 分享页面的描述   $des
 * @param 分享页面的图片    $pic_url  300*300
 * @return 数组
 */
function wxShare($url,$title,$des,$pic_url){ 
	    $wx             = C("WEIXIN");
	    $appid      	= $wx['app_id'];//微信APPID
	    $noncestr		= createNonceStr();//生成签名的随机串
	    $jsapi_ticket	= getJsTicket(3);
	    $timestamp		= time();
	    $local_url		= $url;
	    $signStr 		= "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$local_url";
	    $signStr 		= sha1($signStr);
	    $signPackage  = array(
	        "appId"      => $appid,
	        "nonceStr"   => $noncestr,
	        "timestamp"  => $timestamp,
	        "signature"  => $signStr,
	        "title" 	 => $title,
	        "description"=> $des,
	        "pic_url" 	 => $pic_url,
	        "url" 		 => $url
	    );
    return $signPackage;
}

/**
 * 判断升级白或者金名单
 * @param 借款会员编号 $uid
 */
function upLevel($uid){
    $mem  = M('members')->field("is_gold,is_white")->where("id = {$uid} ")->find();
    $flag = 1;
    $i    = 0;
    $apply = M('borrow_detail')->field("id,deadline,repayment_time,renewal_id")->where("uid = {$uid} and status = 1")->order("id desc")->limit(3)->select();
    foreach($apply as $key=>$v){
        if($v['renewal_id'] >0){//有续期
            $flag = 0;
        }
        $due = strtotime(date('Y-m-d',$v['deadline'])."23:59:59");
        if($v['repayment_time']>$due){//有逾期
            $flag = 0; 
        }
        $i++;
    }
    //还款不到三单
    if($i<3){
        $flag = 0;
    }
    if($flag==1){
        if($mem['is_white']==0 && $mem['is_gold']==0){//升级为白名单
            echo "TT";
            $sdata['is_white']     = 1;
            $sdata['is_gray']      = 0;
            $sdata['is_black']     = 0;
            $result = M("members")->where("id = {$uid}")->save($sdata);
        }
        if($mem['is_white']==1 && $mem['is_gold']==0){//升级为金名单
            echo "BB";
            $sdata['is_white']     = 0;
            $sdata['is_gold']      = 1;
            $result = M("members")->where("id = {$uid}")->save($sdata);
        }
    }  
}

/**
 * 获取授信报告信息
 * @param 会员编号 $uid
 * @return 返回集合
 */
function getPpcrate($uid){
    $score      = 0;
    $mem_info   = M('member_info')->field("uid,real_name,id_card,iphone")->where("uid = {$uid}")->find();
    $dbms       = C('DB_TYPE_RISK'); //数据库类型
    $host       = C('DB_HOST_RISK'); //数据库主机名
    $dbName     = C('DB_NAME_RISK'); //使用的数据库
    $username   = C('DB_USER_RISK'); //数据库连接用户名
    $passwd     = C('DB_PWD_RISK');  //对应的密码
    $dsn        = "$dbms:host=$host;dbname=$dbName";
    $link       = new PDO($dsn, $username, $passwd);
    $sql        = "SELECT * from rs_ppc_list where id_card = '".$mem_info['id_card']."' ORDER BY id DESC limit 1";
    $res        = $link->query($sql);
    if ($res){
        foreach ($res as $row) {
            $score = $row['scoresma'];
        }
    }
    if($score==0){
        $rate   = M('ppc_passing_rate')->field("rate")->where("score_from = 0")->find();
    }else{
        $rate   = M('ppc_passing_rate')->field("rate")->where(" {$score}>=score_from and {$score}<score_to")->find();
    }
    $mem_info['score']    = $score;
    $mem_info['real_name']= $mem_info['real_name'];
    $mem_info['id_card']  = substr_replace($mem_info['id_card'],'********',6,8);
    $mem_info['iphone']   = substr_replace($mem_info['iphone'],'****',3,4);
    $mem_info['rate']     = $rate['rate'];
    $mem_info['add_time'] = date("Y-m-d",time());
    $mem_info['dis_time'] = date("Y-m-d",strtotime("+14 day",strtotime(date("Y-m-d",time())." 00:00:00")));
    
    return $mem_info;
}

/**
 * 借款授信付款成功的订单状态更新
 * @param unknown $uid
 * @param unknown $bid
 */
function updaterecheck($uid,$bid){
        $flg = 0;
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
                
                $global   = get_global_setting();
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
            $flg = 1;
        }else{
            //更新状态
            $data['status']          = 95;
            $data['refuse_time']     = time();
            M('borrow_apply')->where("id={$bid} and uid = {$uid}")->save($data);
            //删除option信息
            delUserOperation($uid,$bid);
            
            //发送微信推送
            $wxInfo = M("member_wechat_bind")->field('openid')->where("uid = {$uid}")->find();
            if($wxInfo['openid']!==''){
                $global   = get_global_setting();
                $amount   = $global['credit_amount'];
                $discount = $global['credit_discount'];
                if($discount==0){
                    $total = $amount;
                }else{
                    $total = $discount;
                }
                sendWxTempleteMsg23($wxInfo['openid'], $total, date("Y-m-d",time()));
            }
            
            //发送App推送通知授信支付成功
            $mwhere['uid'] = $uid;
            $token = M('member_umeng')->where($mwhere)->field(true)->find();
            if(!empty($token['token'])){
                AndroidTempleteMsgC($uid,$token['token'],$bid,10);
            }
        }
        
        return $flg;
}

/**
 * 开户行省市
 * city_id //关联开户区县id
 * bank_code //关联行别id
 */
function city_Code($city_id,$bank_code){
    $bankcode   = M("fuiou_city a")->where("a.code = '{$city_id}'")->join("ml_fuiou_province b ON a.province_id = b.id","left")->field("a.city,b.province")->find();
    $fuiou_bank = M("fuiou_bank")->where("code = {$bank_code}")->field("bank_name")->find();
    $bankcode['bank_name'] = $fuiou_bank['bank_name'];
    return $bankcode;
}

/**
 * 判断用户授权
 * @param 会员编号 $uid
 * @param 会员属性  $usr_attr
 * @param 富友会员编号 $fuiou_id
 * @return number
 */
function checkGrant($uid,$usr_attr,$fuiou_id){
    if($uid>0){ 
        if($fuiou_id>0){
            //营销户，手续费户，平台自有资金账户，担保方手续费户无需授权
            if($usr_attr==3||$usr_attr==4||$usr_attr==7||$usr_attr==9){
                $is_auth_st = 4;
            }else{
                //用户授权表信息获取
                $is_ustatus = M("fuiou_user_status")->where("uid = {$uid}")->field(true)->find();
                if(empty($is_ustatus)){
                    $res = userQuery($uid,$fuiou_id);
                    if($res['auth_st'] == "000000000000"){
                        $is_auth_st = 2;//未授权去授权
                    }else{
                        if($usr_attr == 2){//借款人
                            if($res['auth_st'] == "010100000000"){
                                if(getFloatValue($res['auto_repay_amt']/100,2) < 9999999.99 || $res['auto_repay_term'] < 20991231 || getFloatValue($res['auto_fee_amt']/100,2) < 9999999999.99 || $res['auto_fee_term'] < 20991231){
                                    $is_auth_st = 3; //已授权去修改
                                }else{
                                    //更新用户信息
                                    fuiou_status($uid,$res);
                                    $is_auth_st = 4;//已授权
                                }
                            }else{
                                $is_auth_st = 3; //已授权去修改
                            }
                            
                        }
                    }
                }else{
                    if($is_ustatus['auth_st'] == "000000000000"){
                        $is_auth_st = 2;//未授权去授权
                    }else{
                        if($usr_attr == 2){//借款人
                            if($is_ustatus['auth_st'] == "010100000000"){
                                if(getFloatValue($is_ustatus['auto_repay_amt']/100,2) < 9999999.99 || $is_ustatus['auto_repay_term'] < 20991231 || getFloatValue($is_ustatus['auto_fee_amt']/100,2) < 9999999999.99 || $is_ustatus['auto_fee_term'] < 20991231){
                                    $is_auth_st = 3; //已授权去修改
                                }else{
                                    //更新用户信息
                                    $is_auth_st = 4;//已授权
                                }
                            }else{
                                $is_auth_st = 3; //已授权去修改
                            }
                        }
                    }
                }
            }
        }else{
            $is_auth_st = 1;//已注册未开户
        }
    }else{
        $is_auth_st = 0;//未登录
    }
    return $is_auth_st; 
}

/**
 * 富友用户可用余额
 * @param 会员id $uid
 */
 function up_money($uid){
    //同步账户余额
    if(!empty($uid)){
        userMoney($uid);
        $fsum   = M("fuiou_user_money")->where("uid = {$uid}")->field(true)->find();
        $fsum['ky_balance'] = getFloatValue($fsum['ca_balance'] - $fsum['freeze']-$fsum['w_freeze'],2);//目前可用余额
        return $fsum;
    }
}

/**
 * 更新富友用户表数据
 * @param 会员编号 $uid
 * @return 生成成功
 */
function fuiou_status($uid,$arr){
    $status  = M("fuiou_user_status")->where("uid = '{$uid}'")->field('id')->find();

    $data['uid']                = $uid;
    $data['fuiou_id']           = $arr['login_id'];
    $data['contract_st']        = 1;
    $data['auth_st']            = $arr['auth_st'];
    $data['usr_attr']           = $arr['usr_attr'];
    $data['auto_lend_term']     = $arr['auto_lend_term'];
    $data['auto_lend_amt']      = $arr['auto_lend_amt'];
    $data['used_lend_amt']      = $arr['used_lend_amt'];
    $data['b_auto_lend_amt']    = $arr['b_auto_lend_amt'];
    $data['auto_repay_term']    = $arr['auto_repay_term'];
    $data['auto_repay_amt']     = $arr['auto_repay_amt'];
    $data['auto_compen_term']   = $arr['auto_compen_term'];
    $data['auto_compen_amt']    = $arr['auto_compen_amt'];
    $data['auto_fee_term']      = $arr['auto_fee_term'];
    $data['auto_fee_amt']       = $arr['auto_fee_amt'];
    $data['add_time']           = time();
    if(empty($status)){
        M("fuiou_user_status")->add($data);
    }else{
        M("fuiou_user_status")->where("uid = '{$uid}'")->save($data);
    }
}