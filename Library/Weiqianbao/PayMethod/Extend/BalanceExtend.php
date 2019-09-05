<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;

class BalanceExtend extends PayMethodExtend
{
    public $accountType;

    public function getExtendString()
    {
        return $this->accountType;
    }
}