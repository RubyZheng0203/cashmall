<?php
namespace App\Library\Weiqianbao\Protocol\QueryBankcard;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @property mixed card_list
 * @package App\Library\Weiqianbao\Protocol\QueryBankcard
 * 查询绑定的银行卡
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "card_list"
    );
} 