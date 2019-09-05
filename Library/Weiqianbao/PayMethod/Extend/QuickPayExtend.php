<?php
namespace App\Library\Weiqianbao\PayMethod\Extend;

use App\Library\Weiqianbao\Rsa;
use App\Library\Weiqianbao\Weiqianbao;

class QuickPayExtend extends PayMethodExtend
{
    public $bankCode;
    public $bankCardNum;
    public $userName;
    public $bankCardType;
    public $bankCardAttribute;
    public $idCardType;
    public $idCardNum;
    public $mobile;
    public $expireDate;
    public $cvv2;
    public $province;
    public $city;

    public function __construct()
    {
        $this->bankCardType = Weiqianbao::BANKCARD_TYPE_DEBIT;
        $this->bankCardAttribute = Weiqianbao::BANKCARD_ATTRIBUTE_C;
        $this->idCardType = Weiqianbao::CERT_TYPE_IC;
    }

    /**
     * @return string
     */
    public function getExtendString()
    {
        return $this->buildExtendString(
            [
                $this->bankCode,
                Rsa::Encrypt($this->bankCardNum, Weiqianbao::getConfig("rsa_public_key_path")),
                Rsa::Encrypt($this->userName, Weiqianbao::getConfig("rsa_public_key_path")),
                $this->bankCardType,
                $this->bankCardAttribute,
                $this->idCardType,
                Rsa::Encrypt($this->idCardNum, Weiqianbao::getConfig("rsa_public_key_path")),
                Rsa::Encrypt($this->mobile, Weiqianbao::getConfig("rsa_public_key_path")),
                Rsa::Encrypt($this->expireDate, Weiqianbao::getConfig("rsa_public_key_path")),
                Rsa::Encrypt($this->cvv2, Weiqianbao::getConfig("rsa_public_key_path")),
                $this->province,
                $this->city
            ]
        );
    }
}