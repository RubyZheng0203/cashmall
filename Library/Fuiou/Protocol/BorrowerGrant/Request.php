<?php
namespace App\Library\Fuiou\Protocol\BorrowerGrant;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "borrowerGrant";

    protected static $requestParam = array(
        "ver",
    	"code",
        "client_tp",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"login_id",
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
        "back_notify_url ",
    	
    );
}