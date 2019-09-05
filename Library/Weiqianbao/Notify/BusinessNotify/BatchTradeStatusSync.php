<?php
namespace App\Library\Weiqianbao\Notify\BusinessNotify;

use App\Library\Weiqianbao\Notify\BusinessNotify;

class BatchTradeStatusSync extends BusinessNotify
{

    const ID = "batch_trade_status_sync";

    protected static $strictParam = array(
        "outer_batch_no",
        "inner_batch_no",
        "batch_quantity",
        "batch_amount",
        "batch_status",
        "trade_list",
        "gmt_create",
        "gmt_finished",
    );
}