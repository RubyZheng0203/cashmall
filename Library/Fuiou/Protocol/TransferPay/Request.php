<?php
namespace App\Library\Fuiou\Protocol\TransferPay;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "transferPay";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"project_no",
    	"login_id_out",
        "login_id_in",
        "amt",
        "amt_pincipal",
        "interest",
        "busi_tp",
        "origin_txn_ssn",
        "origin_txn_date",
        "contract_no",
        "rem",
        
    );
}