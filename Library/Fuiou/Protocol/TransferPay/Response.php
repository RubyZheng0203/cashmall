<?php
namespace App\Library\Fuiou\Protocol\TransferPay;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\TransferPay
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
        "txn_date",
    	"resp_code",
    	"resp_desc",
        "reserved",
    );
} 