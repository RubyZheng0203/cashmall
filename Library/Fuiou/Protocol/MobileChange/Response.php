<?php
namespace App\Library\Fuiou\Protocol\MobileChange;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\MobileChange
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
    	"login_id ",
    	"new_mobile",
    	"reserved",
    );
} 