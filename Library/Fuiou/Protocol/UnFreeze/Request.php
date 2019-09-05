<?php
namespace App\Library\Fuiou\Protocol\UnFreeze;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "unfreeze";

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
        "remark",
    );
}