<?php
namespace App\Library\Weiqianbao\Notify\BusinessNotify;

use App\Library\Weiqianbao\Notify\BusinessNotify;

/**
 * Class TradeStatusSync
 * @package App\Library\Weiqianbao\Notify\BusinessNotify
 */
class TradeStatusSync extends BusinessNotify
{

    const ID = "trade_status_sync";

    protected static $strictParam = array(
        "outer_trade_no",
        "inner_trade_no",
        "trade_status",
        "trade_amount",
        "gmt_create",
        "gmt_payment",
        "gmt_close",
        "pay_method",
    );
}