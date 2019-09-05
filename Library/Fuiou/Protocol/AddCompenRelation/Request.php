<?php
namespace App\Library\Fuiou\Protocol\AddCompenRelation;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "addCompenRelation";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"project_no",
    	"login_id",
    );
}