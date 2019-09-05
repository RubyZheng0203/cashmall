<?php
namespace App\Library\Fuiou\Protocol\PasswordUnFreeze;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "passwordUnfreeze";

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
        "origin_txn_ssn",
        "origin_txn_date",
        "busi_tp",
        "page_notify_url",
        "back_notify_url",
        "remark",
    );
}