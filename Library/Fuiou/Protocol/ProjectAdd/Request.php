<?php
namespace App\Library\Fuiou\Protocol\ProjectAdd;

use App\Library\Fuiou\Request as BaseRequest;
use App\Library\Fuiou\Fuiou;

/**
 * 
 * @author Rubyzheng
 *
 */
class Request extends BaseRequest
{
    protected $serviceName = "projectAdd";

    protected static $requestParam = array(
        "ver",
    	"code",
    	"mchnt_cd",
    	"mchnt_txn_ssn",
    	"project_nm",
    	"project_no",
    	"project_usage",
    	"amt",
        "return_rate",
        "raise_days",
        "start_dt",
        "end_dt",
        "project_days",
        "repay_type",
        "num_periods",
        "bor_login_id",
        "bor_nm",
        "project_memo",
        "business_nm",
        "business_login_id",
        "attach1",
        "attach2",
    );
}