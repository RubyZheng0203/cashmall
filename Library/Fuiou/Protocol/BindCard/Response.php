<?php
namespace App\Library\Fuiou\Protocol\BindCard;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\BindCard
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
    	"login_id ",
    	"mobile_no",
    	"city_id",
    	"parent_bank_id",
    	"bank_nm",
    	"card_no",
    	"contract_st",
    	"reserved",
    );
} 