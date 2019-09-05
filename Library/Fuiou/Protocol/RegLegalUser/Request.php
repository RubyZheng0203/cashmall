<?php
namespace App\Library\Fuiou\Protocol\RegLegalUser;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "regLegalUser";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"client_tp",
    	"cust_nm",
    	"artif_nm",
    	"mobile_no",
    	"certif_tp",
    	"certif_id",
    	"usr_attr",
    	"city_id ",
    	"parent_bank_id",
    	"bank_nm",
    	"busi_licence_no",
    	"auth_st",
    	"auto_lend_term",
    	"auto_lend_amt",
    	"auto_repay_term",
    	"auto_repay_amt",
    	"auto_compen_term",
    	"auto_compen_amt",
    	"auto_fee_term",
    	"auto_fee_amt",
    	"page_notify_url",
    	"back_notify_url",
    );
}