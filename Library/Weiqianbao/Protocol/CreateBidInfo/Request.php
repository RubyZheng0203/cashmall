<?php
namespace App\Library\Weiqianbao\Protocol\CreateBidInfo;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property string identity_id
 * @property string identity_type
 * @property string member_type
 * @property string extend_param
 * @package App\Library\Weiqianbao\Protocol\CreateBidInfo
 * 标的录入
 */
class Request extends BaseRequest
{
    protected $serviceName = "create_bid_info";

    protected static $requestParam = array(
        "out_bid_no",
        "web_site_name",
        "bid_name",
        "bid_type",
        "bid_amount",
        "bid_year_rate",
        "bid_duration",
        "repay_type",
        "protocol_type",
        "bid_product_type",
        "recommend_inst",
        "limit_min_bid_copys",
        "limit_per_copy_amount",
        "limit_max_bid_amount",
        "limit_min_bid_amount",
        "summary",
        "url",
        "begin_date",
        "term",
        "guarantee_method",
        "extend_param",
        "borrower_info_list"
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("acquire_gateway");
    }
}