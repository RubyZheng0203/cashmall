<?php
namespace App\Library\Weiqianbao\Protocol\BindingBankCard;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @property mixed ticket
 * @package App\Library\Weiqianbao\Protocol\BindingBankCard
 * 绑定银行卡
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "card_id",
        "is_verified",
        "ticket",
    );
} 