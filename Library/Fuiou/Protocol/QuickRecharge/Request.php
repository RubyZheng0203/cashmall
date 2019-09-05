<?php
namespace App\Library\Fuiou\Protocol\QuickRecharge;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "quickRecharge";

    protected static $requestParam = array(
        "ver",
        "code",
        "client_tp",
        "mchnt_cd",
        "mchnt_txn_ssn",
        "login_id",
        "amt",
        "page_notify_url",
        "back_notify_url",
    );
}