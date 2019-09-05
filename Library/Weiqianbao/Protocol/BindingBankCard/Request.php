<?php
namespace App\Library\Weiqianbao\Protocol\BindingBankCard;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Rsa;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property int request_no
 * @property mixed identity_id
 * @property mixed identity_type
 * @property mixed bank_code
 * @property mixed bank_account_no
 * @property mixed account_name
 * @property mixed card_type
 * @property mixed card_attribute
 * @property mixed cert_type
 * @property mixed cert_no
 * @property mixed phone_no
 * @property mixed validity_period
 * @property mixed verification_value
 * @property mixed province
 * @property mixed city
 * @property mixed bank_branch
 * @property mixed verify_mode
 * @property mixed extend_param
 * @package App\Library\Weiqianbao\Protocol\BindingBankCard
 * 绑定银行卡
 */
class Request extends BaseRequest
{
    protected $serviceName = "binding_bank_card";

    protected static $requestParam = array(
        "request_no",
        "identity_id",
        "identity_type",
        "bank_code",
        "bank_account_no",
        "account_name",
        "card_type",
        "card_attribute",
        "cert_type",
        "cert_no",
        "phone_no",
        "validity_period",
        "verification_value",
        "province",
        "city",
        "bank_branch",
        "verify_mode",
        "client_ip",
        "extend_param",
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("member_gateway");
    }

    public function getRequestParam()
    {
        $this->bank_account_no = Rsa::Encrypt($this->bank_account_no, Weiqianbao::getConfig("rsa_public_key_path"));
        $this->account_name = Rsa::Encrypt($this->account_name, Weiqianbao::getConfig("rsa_public_key_path"));
        $this->cert_no = Rsa::Encrypt($this->cert_no, Weiqianbao::getConfig("rsa_public_key_path"));
        $this->phone_no = Rsa::Encrypt($this->phone_no, Weiqianbao::getConfig("rsa_public_key_path"));
        $this->validity_period = Rsa::Encrypt($this->validity_period, Weiqianbao::getConfig("rsa_public_key_path"));
        $this->verification_value = Rsa::Encrypt($this->verification_value, Weiqianbao::getConfig("rsa_public_key_path"));
        return parent::getRequestParam();
    }
}