<?php
namespace App\Library\Weiqianbao\Notify\BusinessNotify;

use App\Library\Weiqianbao\Notify\BusinessNotify;

/**
 * Class WithdrawStatusSync
 * @property mixed withdraw_status
 * @property mixed outer_trade_no
 * @package App\Library\Weiqianbao\Notify\BusinessNotify
 */
class WithdrawStatusSync extends BusinessNotify
{

    const ID = "withdraw_status_sync";

    protected static $strictParam = array(
        "outer_trade_no",
        "inner_trade_no",
        "withdraw_status",
        "withdraw_amount",
    );
}