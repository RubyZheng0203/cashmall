<?php
namespace App\Library\Weiqianbao\Notify;

use App\Library\StrictFluent;
use App\Library\Weiqianbao\Notify\BusinessNotify\AuditStatusSync;
use App\Library\Weiqianbao\Notify\BusinessNotify\BatchTradeStatusSync;
use App\Library\Weiqianbao\Notify\BusinessNotify\DepositStatusSync;
use App\Library\Weiqianbao\Notify\BusinessNotify\RefundStatusSync;
use App\Library\Weiqianbao\Notify\BusinessNotify\TradeStatusSync;
use App\Library\Weiqianbao\Notify\BusinessNotify\WithdrawStatusSync;
use InvalidArgumentException;

abstract class BusinessNotify extends StrictFluent
{
    const ID = "";

    public static function factory($notifyType, array $param)
    {
        switch ($notifyType) {
            case AuditStatusSync::ID:
                return new AuditStatusSync($param);
            case BatchTradeStatusSync::ID:
                return new BatchTradeStatusSync($param);
            case DepositStatusSync::ID:
                return new DepositStatusSync($param);
            case RefundStatusSync::ID:
                return new RefundStatusSync($param);
            case TradeStatusSync::ID:
                return new TradeStatusSync($param);
            case WithdrawStatusSync::ID:
                return new WithdrawStatusSync($param);
        }
        throw new InvalidArgumentException("Illegal weiqianbao notify type.");
    }

} 