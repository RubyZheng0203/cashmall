<?php
namespace App\Library\Fuiou\Protocol\TxnQuery;

use App\Library\Fuiou\Response as BaseResponse;

/**
 * Class Response
 * @package App\Library\Fuiou\Protocol\TxnQuery
 */
class Response extends BaseResponse
{
    protected $responseParam = array(
        "mchnt_cd",
        "mchnt_txn_ssn",
        "resp_code",
        "resp_desc",
        "project_no",
        "busi_cd",
        "busi_tp",
        "total_number",
        "ext_tp",
        "txn_date",
        "txn_time",
        "mchnt_ssn",
        "txn_amt",
        "contract_no",
        "out_fuiou_acct_no",
        "out_cust_no",
        "out_artif_nm",
        "in_fuiou_acct_no",
        "in_cust_no",
        "in_artif_nm",
        "remark",
        "txn_rsp_cd",
        "rsp_cd_desc",
    );
} 