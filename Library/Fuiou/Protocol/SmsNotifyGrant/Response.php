<?php
namespace App\Library\Fuiou\Protocol\SmsNotifyGrant;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\SmsNotifyGrant
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "czTxSmsNotify",
        "amtOutSmsNotify",
        "amtInSmsNotify",
    	"reserved",
    );
} 