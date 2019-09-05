<?php
namespace App\Library\Weiqianbao\Protocol\QueryBidInfo;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Weiqianbao\Protocol\QueryBidInfo
 * 查询标的信息
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "out_bid_no",
        "inner_bid_no",
        "web_site_name",
        "bid_name",
        "bid_type",
        "bid_status",
        "bid_amount",
        "bid_year_rate",
        "bid_duration",
        "repay_type",
        "protocol_type"
    );
} 