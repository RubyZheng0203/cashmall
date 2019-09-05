<?php
namespace App\Library\Weiqianbao\PayMethod;

use App\Library\Weiqianbao\PayMethod\Extend\PayMethodExtend;
use InvalidArgumentException;

abstract class PayMethod
{
    const SPLIT = "^";

    /** @var  string */
    protected $name;

    /** @var  float */
    protected $amount;

    /** @var  PayMethodExtend */
    protected $extend;

    public function __construct(PayMethodExtend $extend)
    {
        $this->extend = $extend;
    }

    /**
     * @param $payWay
     * @param PayMethodExtend $extend
     * @return PayMethod
     * @throws InvalidArgumentException
     */
    public static function factory($payWay, PayMethodExtend $extend)
    {
        switch ($payWay) {
            case "online_bank":
                return new OnlineBank($extend);
            case "balance":
                return new Balance($extend);
            case "binding_pay":
                return new BindingPay($extend);
            case "quick_pay":
                return new QuickPay($extend);
            case "confirm_pay":
                return new ConfirmPay($extend);
            case "offline_pay":
                return new OfflinePay($extend);
        }
        throw new InvalidArgumentException("Illegal pay way.");
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getExtendString()
    {
        return $this->extend->getExtendString();
    }

    /**
     * @param PayMethodExtend $extend
     */
    public function setExtend($extend)
    {
        $this->extend = $extend;
    }

    /**
     * @return PayMethodExtend
     */
    public function getExtend()
    {
        return $this->extend;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return implode(
            static::SPLIT,
            [
                $this->getName(),
                $this->getAmount(),
                $this->getExtendString(),
            ]
        );
    }
}