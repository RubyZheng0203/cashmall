<?php
namespace App\Library\Weiqianbao\Protocol\UnbindingVerify;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property string identity_id
 * @property string identity_type
 * @property string verify_type
 * @property string extend_param
 * @package App\Library\Weiqianbao\Protocol\UnbindingVerify
 * 解绑认证信息
 */
class Request extends BaseRequest
{
    protected $serviceName = "unbinding_verify";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "verify_type",
        "client_ip",
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