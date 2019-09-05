<?php
namespace App\Library\Weiqianbao\Protocol\CreateHostingDeposit;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request
 * 托管充值
 * @property string out_trade_no 交易订单号
 * @property mixed summary 摘要
 * @property mixed identity_id 用户标识信息
 * @property mixed identity_type 用户标识类型
 * @property mixed account_type 账户类型
 * @property mixed amount 金额
 * @property mixed user_fee 用户手续费
 * @property mixed payer_ip 付款用户IP地址
 * @property mixed pay_method 支付方式  收银台支付时需为网银支付，且传入银行为SINAPAY。
 * @property mixed extend_param 扩展信息
 * @package App\Library\Weiqianbao\Protocol\CreateHostingDeposit
 * 托管充值
 */
class Request extends BaseRequest
{
    protected $serviceName = "create_hosting_deposit";

    protected static $requestParam = array(
        "out_trade_no",
        "summary",
        "identity_id",
        "identity_type",
        "account_type",
        "amount",
        "user_fee",
        "payer_ip",
        "pay_method",
        "extend_param"
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("acquire_gateway");
    }
}