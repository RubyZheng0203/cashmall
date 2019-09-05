<?php
namespace App\Library\Fuiou\Protocol\SmsNotifyGrant;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "smsNotifyGrant";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"client_tp",
    	"login_id",
    	"page_notify_url",
    );
}