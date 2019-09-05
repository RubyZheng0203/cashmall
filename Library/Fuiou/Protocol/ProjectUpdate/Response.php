<?php
namespace App\Library\Fuiou\Protocol\ProjectUpdate;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\ProjectUpdate
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "project_no",
        "project_usage",
        "project_st",
        "project_no",
        "reserved",
    );
} 