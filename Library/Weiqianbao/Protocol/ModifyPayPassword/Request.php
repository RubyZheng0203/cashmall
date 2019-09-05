<?php
namespace App\Library\Weiqianbao\Protocol\ModifyPayPassword;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * 修改支付密码重定向
 * @property mixed identity_id 用户标识信息
 * @property mixed identity_type 用户标识类型
 * @property string extend_param 扩展信息
 * @package App\Library\Weiqianbao\Protocol\ModifyPayPassword
 */
class Request extends BaseRequest
{
    protected $serviceName = "modify_pay_password";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "extend_param",
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("member_gateway");
    }
}