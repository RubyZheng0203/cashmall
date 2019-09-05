<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;

class ConfirmPayExtend extends PayMethodExtend
{
    protected $accountType;
    protected $identityId;
    protected $identityType;

    /**
     * @return string
     */
    public function getExtendString()
    {
        return $this->buildExtendString(
            [
                $this->accountType,
                $this->identityId,
                $this->identityType
            ]
        );
    }
}