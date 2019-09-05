<?php
namespace App\Library\Fuiou\Protocol\PasswordFreeze;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "passwordFreeze";

    protected static $requestParam = array(
        "ver",
        "code",
        "client_tp",
        "mchnt_cd",
        "mchnt_txn_ssn",
        "login_id",
        "amt",
        "amt_pincipal",
        "project_no",
        "login_id_in",
        "busi_tp",
        "page_notify_url",
        "back_notify_url",
        "remark",
    );
}