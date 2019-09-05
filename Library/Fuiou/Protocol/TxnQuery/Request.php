<?php
namespace App\Library\Fuiou\Protocol\TxnQuery;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "txnQuery";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"project_no",
        "busi_cd",
        "busi_tp",
        "start_day", 
        "end_day", 
        "txn_ssn",
        "login_id",
        "txn_st",
        "remark",
        "page_no",
        "page_size",
    	
    );
}