<?php
namespace App\Library\Weiqianbao\Protocol\QueryVerify;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Rsa;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @package App\Library\Weiqianbao\Protocol\QueryVerify
 * 查询认证信息
 */
class Request extends BaseRequest
{
    protected $serviceName = "query_verify";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "verify_type",
        "is_mask",
        "extend_param"
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("member_gateway");
    }
}