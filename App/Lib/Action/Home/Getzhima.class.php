<?php 
class GetzhimaAction extends HCommonAction{

    /**
     * 请求贷前决策树
     * @param 会员编号 $uid
     * @param 借款ID $borrowId
     * @param 数据集 $data
     * @return $flg 1：通过 0：拒绝
     */
    public function requestApi($uid,$borrowId,$data=array()) {
       $url = C('RISK_URL'); //风控请求接口
       if (empty($data)){
           //白骑士贷款策略参数
           $data['zmCreScore']  = array(
               'account'   => $members['iphone'],//手机号
               'name'      => "",//姓名
               'email'     => "",//邮箱
               'mobile'    => "",//手机号
               'certNo'    => "",//身份证
               'bankCardNo'=> "", //银行卡卡号
               'amount'    => "", //借款金额
               'zmOpenId'  => $members['zhima_openid'],//芝麻openId
               'address'                => "",//用户住址
               'addressCity'            => "",//用户所在城市
               'organizationAddress'    => "",//用户工作单位地址
               'marriage'               => "",//是否已婚
               'residence'              => "",//户籍所在地
               'tokenKey'               => session_id(),
               'ip'                     => get_client_ip(),
               'isPass'                 => 'false'
           );
          
       }
       $res = http_request($url,json_encode($data));
       wqbLog($data);
       $flg = 0;

       return $flg;
    }
}
?>