<?php
namespace App\Library\Fuiou\Protocol\AddCompenRelation;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\AddCompenRelation
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
        "project_no",
        "login_id",   
        "compen_amt",
        "retracted_compen_amt",
        "unretracted_compen_amt",
    );
} 