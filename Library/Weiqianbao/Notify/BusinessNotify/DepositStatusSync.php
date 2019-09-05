<?php
namespace App\Library\Weiqianbao\Notify\BusinessNotify;

use App\Library\Weiqianbao\Notify\BusinessNotify;

/**
 * Class DepositStatusSync
 * @property mixed deposit_status
 * @property mixed outer_trade_no
 * @package App\Library\Weiqianbao\Notify\BusinessNotify
 */
class DepositStatusSync extends BusinessNotify
{

    const ID = "deposit_status_sync";

    protected static $strictParam = array(
        "outer_trade_no",
        "inner_trade_no",
        "deposit_status",
        "deposit_amount",
        "pay_method",
    );
}