<?php
namespace App\Library\Fuiou\Protocol\Freeze;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "freeze";

    protected static $requestParam = array(
        "ver",
        "code",
        "client_tp",
        "mchnt_cd",
        "mchnt_txn_ssn",
        "login_id",
        "amt",
        "amt_pincipal",
        "origin_txn_ssn",
        "origin_txn_date",
        "project_no",
        "login_id_in",
        "busi_tp",
        "remark",
    );
}