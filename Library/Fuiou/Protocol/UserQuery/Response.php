<?php
namespace App\Library\Fuiou\Protocol\UserQuery;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\UserQuery
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "mchnt_nm",
        "mobile_no",
        "login_id",
    	"cust_nm",
    	"artif_nm",
        "certif_id",
        "email",
        "city_id",
        "parent_bank_id ",
        "bank_nm",
        "card_no",
        "id_nm_verify_st",
        "contract_st",
        "auth_st",
        "auto_lend_term",
        "auto_lend_amt",
        "used_lend_amt",
        "used_lend_amt",
        "b_auto_lend_amt",
        "auto_repay_term",
        "auto_repay_amt",
        "auto_compen_term",
        "auto_compen_amt",
        "auto_fee_term",
        "auto_fee_amt",
        "user_st",
        "usr_attr",
        "audit_st",
        "b_credit_limit_amt",
        "reserved",
    );
} 