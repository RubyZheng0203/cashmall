<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;


class OfflinePayExtend extends PayMethodExtend
{
    protected $bankOrderNum;
    protected $userName;

    /**
     * @return string
     */
    public function getExtendString()
    {
        return $this->buildExtendString(
            [
                $this->bankOrderNum,
                $this->userName,
            ]
        );
    }
}