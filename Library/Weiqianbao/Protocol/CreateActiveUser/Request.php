<?php
namespace App\Library\Weiqianbao\Protocol\CreateActiveUser;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property string identity_id
 * @property string identity_type
 * @property string member_type
 * @property string extend_param
 * @package App\Library\Weiqianbao\Protocol\CreateActiveUser
 * 创建激活会员
 */
class Request extends BaseRequest
{
    protected $serviceName = "create_activate_member";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "member_type",
        "client_ip",
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