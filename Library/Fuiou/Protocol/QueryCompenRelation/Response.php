<?php
namespace App\Library\Fuiou\Protocol\QueryCompenRelation;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\QueryCompenRelation
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "login_id",
        "project_no",
        "compen_amt",
        "retracted_compen_amt",
        "unretracted_compen_amt",
    );
} 