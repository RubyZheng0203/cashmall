<?php
namespace App\Library\Weiqianbao\Notify\BusinessNotify;

use App\Library\Weiqianbao\Notify\BusinessNotify;

/**
 * Class RefundStatusSync
 * @package App\Library\Weiqianbao\Notify\BusinessNotify
 */
class RefundStatusSync extends BusinessNotify
{

    const ID = "refund_status_sync";

    protected static $strictParam = array(
        "orig_outer_trade_no",
        "outer_trade_no",
        "inner_trade_no",
        "refund_amount",
        "refund_status",
        "gmt_refund",
    );
}