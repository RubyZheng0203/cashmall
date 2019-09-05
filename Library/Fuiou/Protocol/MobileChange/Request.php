<?php
namespace App\Library\Fuiou\Protocol\MobileChange;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "mobileChange";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"client_tp",
    	"mchnt_cd  ",
    	"mchnt_txn_ssn",
    	"login_id ",
    	"page_notify_url",
    );
}