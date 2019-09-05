<?php
namespace App\Library\Weiqianbao\Protocol\QueryVerify;

use App\Library\Weiqianbao\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Weiqianbao\Protocol\QueryVerify
 * 查询认证信息
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "verify_entity",
        "verify_time",
        "extend_param",
    );
} 