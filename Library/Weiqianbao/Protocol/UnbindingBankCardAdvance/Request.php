<?php
namespace App\Library\Weiqianbao\Protocol\UnbindingBankCardAdvance;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property string identity_id
 * @property mixed card_id
 * @package App\Library\Weiqianbao\Protocol\UnbindingBankCardAdvance
 * 解绑银行卡推进
 */
class Request extends BaseRequest
{
    protected $serviceName = "unbinding_bank_card_advance";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "ticket",
        "valid_code",
        "client_ip",
        "extend_param"
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("member_gateway");
    }
}