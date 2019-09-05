<?php
namespace App\Library\Fuiou\Protocol\CreditQuery;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\CreditQuery
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "project_usage",
        "credit_crt_dt",   
        "creditor",
        "lender",
        "loan_amt",
        "repay_amt",
        "repay_interest",
        "credit_amt",
        "reserved",
    );
} 