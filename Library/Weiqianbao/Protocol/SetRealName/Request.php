<?php
namespace App\Library\Weiqianbao\Protocol\SetRealName;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Rsa;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property int identity_id
 * @property mixed identity_type
 * @property mixed real_name
 * @property mixed extend_param
 * @property mixed need_confirm
 * @property mixed cert_no
 * @property string cert_type
 * @package App\Library\Weiqianbao\Protocol\SetRealName
 * 设置实名信息
 */
class Request extends BaseRequest
{
    protected $serviceName = "set_real_name";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "real_name",
        "cert_type",
        "cert_no",
        "client_ip",
        "need_confirm",
        "extend_param"
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
        $this->real_name = Rsa::Encrypt($this->real_name, Weiqianbao::getConfig("rsa_public_key_path"));
        $this->cert_no = Rsa::Encrypt($this->cert_no, Weiqianbao::getConfig("rsa_public_key_path"));
        return parent::getRequestParam();
    }
}