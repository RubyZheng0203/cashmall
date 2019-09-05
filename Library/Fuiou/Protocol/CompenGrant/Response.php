<?php
namespace App\Library\Fuiou\Protocol\CompenGrant;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\CompenGrant
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "auth_st",
        "auto_lend_term",
        "auto_lend_amt",
        "used_lend_amt",
        "b_auto_lend_amt",
        "auto_repay_term",
        "auto_repay_amt",
        "auto_compen_term",
        "auto_compen_amt",
        "auto_fee_term",
        "auto_fee_amt",
        "reserved",
    );
} 