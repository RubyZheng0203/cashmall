<?php
namespace App\Library\Weiqianbao\Protocol\QueryBidInfo;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Rsa;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @package App\Library\Weiqianbao\Protocol\QueryBidInfo
 * 查询标的信息
 */
class Request extends BaseRequest
{
    protected $serviceName = "query_bid_info";

    protected static $requestParam = array(
        "out_bid_no"
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("acquire_gateway");
    }
}