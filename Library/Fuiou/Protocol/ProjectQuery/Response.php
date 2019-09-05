<?php
namespace App\Library\Fuiou\Protocol\ProjectQuery;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\ProjectQuery
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
    	"mchnt_txn_ssn",
    	"resp_code",
    	"resp_desc",
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
        "interest",
        "project_st",
        "repayed_amt",
        "repayed_interest",
        "loan_amt",
        "unrepay_amt",
        "compen_amt",
        "overdue_sum",
        "overdue_amt",
        "overdue_interest",
        "remark",
        "reserved",
    );
} 