<?php
namespace App\Library\Weiqianbao\Protocol\BindingVerify;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Rsa;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property mixed identity_id
 * @property mixed identity_type
 * @property mixed verify_type
 * @property mixed verify_entity
 * @property mixed extend_param
 * @package App\Library\Weiqianbao\Protocol\BindingVerify
 * 绑定认证信息
 */
class Request extends BaseRequest
{
    protected $serviceName = "binding_verify";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "verify_type",
        "verify_entity",
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

    public function getRequestParam()
    {
        $this->verify_entity = Rsa::Encrypt($this->verify_entity, Weiqianbao::getConfig("rsa_public_key_path"));
        return parent::getRequestParam();
    }
}