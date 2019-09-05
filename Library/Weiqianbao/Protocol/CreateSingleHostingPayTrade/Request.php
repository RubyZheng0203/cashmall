<?php
namespace App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade;

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
 * @package App\Library\Weiqianbao\Protocol\CreateSingleHostingPayTrade
 */
class Request extends BaseRequest
{
    protected $serviceName = "create_single_hosting_pay_trade";

    protected static $requestParam = array(
        "out_trade_no",
        "out_trade_code",
        "payee_identity_id",
        "payee_identity_type",
        "account_type",
        "amount",
        "split_list",
        "summary",
        "user_ip",
        "extend_param",
        "goods_id",
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("acquire_gateway");
    }
}