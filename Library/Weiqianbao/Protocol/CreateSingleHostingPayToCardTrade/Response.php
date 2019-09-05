<?php
namespace App\Library\Weiqianbao\Protocol\CreateSingleHostingPayToCardTrade;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Weiqianbao\Protocol\CreateSingleHostingPayToCardTrade
 * 代付到提现卡
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "out_trade_no",
        "withdraw_status"
    );
} 