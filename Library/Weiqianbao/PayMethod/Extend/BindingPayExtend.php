<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;

class BindingPayExtend extends PayMethodExtend
{
    public $bindingBankCardId;

    public function getExtendString()
    {
        return $this->bindingBankCardId;
    }
}