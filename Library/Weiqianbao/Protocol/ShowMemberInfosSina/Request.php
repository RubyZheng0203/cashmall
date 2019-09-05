<?php
namespace App\Library\Weiqianbao\Protocol\ShowMemberInfosSina;

use App\Library\Weiqianbao\Request as BaseRequest;

/**
 * Class Request
 * @package App\Library\Weiqianbao\Protocol\ShowMemberInfosSina
 */
class Request extends BaseRequest
{
    protected $serviceName = "show_member_infos_sina";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "resp_method",
        "extend_param"
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return config("weiqianbao.member_gateway");
    }
}