<?php
namespace App\Library\Weiqianbao\Protocol\BindingBankCardAdvance;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property mixed valid_code
 * @property string extend_param
 * @property string ticket
 * @package App\Library\Weiqianbao\Protocol\BindingBankCardAdvance
 * 绑定银行卡推进
 */
class Request extends BaseRequest
{
    protected $serviceName = "binding_bank_card_advance";

    protected static $requestParam = array(
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