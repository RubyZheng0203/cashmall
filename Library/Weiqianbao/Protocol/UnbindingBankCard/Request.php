<?php
namespace App\Library\Weiqianbao\Protocol\UnbindingBankCard;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property string identity_id
 * @property mixed card_id
 * @package App\Library\Weiqianbao\Protocol\UnbindingBankCard
 * 解绑银行卡
 */
class Request extends BaseRequest
{
    protected $serviceName = "unbinding_bank_card";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "card_id",
        "advance_flag",
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