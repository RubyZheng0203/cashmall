<?php
namespace App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade;

use App\Library\Weiqianbao\Request as BaseRequest;
use App\Library\Weiqianbao\Weiqianbao;

/**
 * Class Request 
 * 创建托管代收交易
 * @property mixed out_trade_no 交易订单号
 * @property mixed out_trade_code 交易码
 * @property mixed summary  摘要
 * @property mixed trade_close_time  交易关闭时间
 * @property mixed can_repay_on_failed  支付失败后是否可以再次支付
 * @property extend_param 扩展信息
 * @property goods_id 商户标的ID
 * @property mixed payer_id 付款用户ID
 * @property mixed payer_identity_type 标识类型
 * @property string pay_method 支付方式  收银台支付时，需为网银支付，且传入银行为SINAPAY
 * @package App\Library\Weiqianbao\Protocol\CreateHostingCollectTrade
 */
class Request extends BaseRequest
{
    protected $serviceName = "create_hosting_collect_trade";

    protected static $requestParam = array(
        "out_trade_no",
        "out_trade_code",
        "summary",
        "trade_close_time",
        "can_repay_on_failed",
        "extend_param",
        "goods_id",
        "payer_id",
        "payer_identity_type",
        "payer_ip",
        "pay_method",
    );

    /**
     * @return string
     */
    public function getUrl()
    {
        return Weiqianbao::getConfig("acquire_gateway");
    }
}