<?php
namespace App\Library\Weiqianbao\Protocol\CreateHostingDeposit;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @property mixed ticket
 * @package App\Library\Weiqianbao\Protocol\CreateHostingDeposit
 * 托管充值
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "out_trade_no",
        "deposit_status",
        "ticket",
    );
} 