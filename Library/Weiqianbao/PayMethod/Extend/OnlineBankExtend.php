<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;

class OnlineBankExtend extends PayMethodExtend
{
    public $bankCode;
    public $bankCardType;
    public $bankCardAttribute;

    /**
     * @return string
     */
    public function getExtendString()
    {
        return $this->buildExtendString(
            [
                $this->bankCode,
                $this->bankCardType,
                $this->bankCardAttribute
            ]
        );
    }
}