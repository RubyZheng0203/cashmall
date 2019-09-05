<?php
namespace App\Library\Weiqianbao\Protocol\QueryBankCard;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property string identity_id
 * @property string identity_type
 * @property int card_id
 * @package App\Library\Weiqianbao\Protocol\QueryBankCard
 * 查询绑定的银行卡
 */
class Request extends BaseRequest
{
    protected $serviceName = "query_bank_card";

    protected static $requestParam = array(
        "identity_id",
        "identity_type",
        "card_id",
        "extend_param",
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("member_gateway");
    }
}