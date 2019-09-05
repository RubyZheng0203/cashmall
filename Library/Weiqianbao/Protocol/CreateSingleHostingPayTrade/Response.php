<?php
namespace App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "out_trade_no",
        "trade_status"
    );
} 