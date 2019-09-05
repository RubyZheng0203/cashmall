<?php
namespace App\Library\Weiqianbao\Protocol\BindingBankCardAdvance;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @property mixed card_id
 * @property mixed is_verified
 * @package App\Library\Weiqianbao\Protocol\BindingBankCardAdvance
 * 绑定银行卡推进
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "card_id",
        "is_verified",
    );
} 