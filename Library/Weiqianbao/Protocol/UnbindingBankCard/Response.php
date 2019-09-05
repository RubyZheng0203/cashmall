<?php
namespace App\Library\Weiqianbao\Protocol\UnbindingBankCard;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Weiqianbao\Protocol\UnbindingBankCard
 * 解绑银行卡
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "ticket",
    );
} 