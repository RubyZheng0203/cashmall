<?php
namespace App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "out_trade_no",
        "trade_status",
        "pay_status",
        "ticket",
    );
} 