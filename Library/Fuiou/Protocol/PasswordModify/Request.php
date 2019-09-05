<?php
namespace App\Library\Fuiou\Protocol\PasswordModify;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "passwordModify";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"client_tp",
    	"login_id",
    	"city_id",
    	"parent_bank_id",
    	"bank_nm",
    	"page_notify_url",
    	"back_notify_url",
    );
}