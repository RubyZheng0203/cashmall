<?php
namespace App\Library\Weiqianbao\Protocol\CreateSingleHostingPayToCardTrade;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * @property mixed out_trade_no
 * @property mixed out_trade_code
 * @property mixed payee_identity_id
 * @property mixed payee_identity_type
 * @property mixed account_type
 * @property mixed amount
 * @property mixed summary
 * @package App\Library\Weiqianbao\Protocol\CreateSingleHostingPayToCardTrade
 * 代付到提现卡
 */
class Request extends BaseRequest
{
    protected $serviceName = "create_single_hosting_pay_to_card_trade";

    protected static $requestParam = array(
        "out_trade_no",
        "out_trade_code",
        "collect_method",
        "amount",
        "summary",
        "payto_type",
        "extend_param",
        "goods_id",
        "trade_related_no",
        "creditor_info_list",
        "user_ip",
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("acquire_gateway");
    }
}