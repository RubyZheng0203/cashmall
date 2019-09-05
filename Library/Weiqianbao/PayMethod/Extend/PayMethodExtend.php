<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;

use InvalidArgumentException;

abstract class PayMethodExtend
{
    const SPLIT = ",";

    abstract public function getExtendString();

    protected function buildExtendString($array)
    {
        return implode(static::SPLIT, $array);
    }

    /**
     * @param $payWay
     * @return QuickPayExtend
     * @throws \InvalidArgumentException
     */
    public static function factory($payWay)
    {
        switch ($payWay) {
            case "online_bank":
                return new OnlineBankExtend();
            case "balance":
                return new BalanceExtend();
            case "binding_pay":
                return new BindingPayExtend();
            case "quick_pay":
                return new QuickPayExtend();
            case "confirm_pay":
                return new ConfirmPayExtend();
            case "offline_pay":
                return new OfflinePayExtend();
        }
        throw new InvalidArgumentException("Illegal pay way extend.");
    }

    public function setAttribute(array $param)
    {
        foreach ($param as $k => $v) {
            $this->$k = $v;
        }
    }
}