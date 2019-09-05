<?php
/*array(菜单名，菜单url参数，是否显示)*/
$i=0; 
$j=0;
$menu_left =  array();
$menu_left[$i]=array('全局','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('全局设置','#',1);
$menu_left[$i][$i."-".$j][] = array('欢迎页',U('/admin/welcome/index'),1);
$menu_left[$i][$i."-".$j][] = array('网站设置',U('/admin/global/websetting'),1);
$menu_left[$i][$i."-".$j][] = array('广告管理',U('/admin/ad/'),1);
$menu_left[$i][$i."-".$j][] = array("后台日志",U("/admin/global/adminlog"),1);
$menu_left[$i][$i."-".$j][] = array('授信通过率',U('/admin/credit/index'),1);

/*$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('参数管理','#',1);
$menu_left[$i][$i."-".$j][] = array('年龄别称',U('/admin/age/index'),1);*/

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('权限管理',"#",1); 
$menu_left[$i][$i."-".$j][] = array('管理员管理',U('/admin/Adminuser/'),1);
$menu_left[$i][$i."-".$j][] = array('用户组权限',U('/admin/acl/'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('缓存管理','#',1);
$menu_left[$i][$i."-".$j][] = array('清空缓存',U('/admin/global/cleanall'),1);

$i++;
$menu_left[$i]= array('会员','#',1); 
$menu_left[$i]['low_title'][$i."-".$j] = array('会员','#',1);
$menu_left[$i][$i."-".$j][] = array('会员列表',U('/admin/members/index'),1);
$menu_left[$i][$i."-".$j][] = array('会员进度',U('/admin/members/apply'),1);
$menu_left[$i][$i."-".$j][] = array('会员奖励',U('/admin/coupon/index'),1);
$menu_left[$i][$i."-".$j][] = array('会员积分',U('/admin/members/integral'),1);
$menu_left[$i][$i."-".$j][] = array('会员事件',U('/admin/members/event'),1);
$menu_left[$i][$i."-".$j][] = array('手机认证',U('/admin/members/unaccount'),1);
$menu_left[$i][$i."-".$j][] = array('用户信息查询',U('/admin/sina/memberinfo'),1);
$menu_left[$i][$i."-".$j][] = array("芝麻分查询", U("/admin/zhimascore/index"), 1);


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('会员统计','#',1);
$menu_left[$i][$i."-".$j][] = array('注册统计',U('/admin/members/daCount'),1);
$menu_left[$i][$i."-".$j][] = array('地区统计',U('/admin/members/daAddress'),1);
$menu_left[$i][$i."-".$j][] = array('性别统计',U('/admin/members/daSex'),1);
$menu_left[$i][$i."-".$j][] = array('年龄统计',U('/admin/members/daAge'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('黑名单会员','#',1);
$menu_left[$i][$i."-".$j][] = array('随机放款金额',U('/admin/black/random'),1);
$menu_left[$i][$i."-".$j][] = array('接口随机放行设置',U('/admin/black/setup'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('黑白灰名单','#',1);
$menu_left[$i][$i."-".$j][] = array('列表',U('admin/wbgray/index'),1);

$i++;
$menu_left[$i]= array('借款','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('借款产品','#',1);
$menu_left[$i][$i."-".$j][] = array('借款产品Ⅰ类',U('/admin/item/index'),1);
$menu_left[$i][$i."-".$j][] = array('借款产品Ⅱ类',U('/admin/item/itemsec'),1);
$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('借款审核','#',1);
$menu_left[$i][$i."-".$j][] = array('待初审',U('/admin/borrow/index'),1);
$menu_left[$i][$i."-".$j][] = array('待签约',U('/admin/borrow/signing'),1);
$menu_left[$i][$i."-".$j][] = array('待身份确认',U('/admin/borrow/unidVerify'),1);
$menu_left[$i][$i."-".$j][] = array('待放款',U('/admin/borrow/pending'),1);


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('借款查询','#',1);
//$menu_left[$i][$i."-".$j][] = array('未提交申请',U('/admin/borrow/notApply'),1);
//$menu_left[$i][$i."-".$j][] = array('已初审',U('/admin/borrow/firstTrial'),1);
//$menu_left[$i][$i."-".$j][] = array('已绑卡',U('/admin/borrow/bingBank'),1);
//$menu_left[$i][$i."-".$j][] = array('已芝麻授权',U('/admin/borrow/zhima'),1);
//$menu_left[$i][$i."-".$j][] = array('已身份确认',U('/admin/borrow/idVerify'),1);
//$menu_left[$i][$i."-".$j][] = array('已决策树确认',U('/admin/borrow/risk'),1);
//$menu_left[$i][$i."-".$j][] = array('已签约',U('/admin/borrow/signed'),1);
$menu_left[$i][$i."-".$j][] = array('已放款',U('/admin/borrow/pended'),1);
//$menu_left[$i][$i."-".$j][] = array('已取消',U('/admin/borrow/cancled'),1);
//$menu_left[$i][$i."-".$j][] = array('已拒绝放款',U('/admin/borrow/pendNg'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('还款','#',1);
$menu_left[$i][$i."-".$j][] = array('待还款',U('/admin/borrow/repayment'),1);
$menu_left[$i][$i."-".$j][] = array('已逾期',U('/admin/borrow/due'),1);
$menu_left[$i][$i."-".$j][] = array('已还款',U('/admin/borrow/repaymented'),1);
$menu_left[$i][$i."-".$j][] = array('线下还款审核',U('/admin/payoff/index'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('交易记录','#',1);
$menu_left[$i][$i."-".$j][] = array('宝付支付成功',U('/admin/borrow/trades'),1);
$menu_left[$i][$i."-".$j][] = array('新浪支付成功',U('/admin/borrow/tradesSina'),1);
$menu_left[$i][$i."-".$j][] = array('宝付支付失败',U('/admin/borrow/tradesno'),1);
$menu_left[$i][$i."-".$j][] = array('新浪支付失败',U('/admin/borrow/tradesSinano'),1);
$menu_left[$i][$i."-".$j][] = array('汇潮支付处理中',U('/admin/huichaopay/index'),1);
$menu_left[$i][$i."-".$j][] = array('汇潮支付成功',U('/admin/huichaopay/trades'),1);
$menu_left[$i][$i."-".$j][] = array('汇潮支付失败',U('/admin/huichaopay/tradesNo'),1);


$j++; 
$menu_left[$i]['low_title'][$i."-".$j] = array('借款统计','#',1);
$menu_left[$i][$i."-".$j][] = array('申请借款金额',U('/admin/borrowDa/daApply'),1);
$menu_left[$i][$i."-".$j][] = array('申请借款金额地址',U('/admin/borrowDa/daApplyAd'),1);
$menu_left[$i][$i."-".$j][] = array('申请借款金额性别',U('/admin/borrowDa/daApplySex'),1);
$menu_left[$i][$i."-".$j][] = array('申请借款金额年龄',U('/admin/borrowDa/daApplyAge'),1);
$menu_left[$i][$i."-".$j][] = array('批准借款金额',U('/admin/borrowDa/daApproval'),1);
$menu_left[$i][$i."-".$j][] = array('批准借款金额地址',U('/admin/borrowDa/daApprovalAd'),1);
$menu_left[$i][$i."-".$j][] = array('批准借款金额性别',U('/admin/borrowDa/daApprovalSex'),1);
$menu_left[$i][$i."-".$j][] = array('批准借款金额年龄',U('/admin/borrowDa/daApprovalAge'),1);
$menu_left[$i][$i."-".$j][] = array('放款借款金额',U('/admin/borrowDa/daLoan'),1);
$menu_left[$i][$i."-".$j][] = array('放款借款金额地址',U('/admin/borrowDa/daLoanAd'),1);
$menu_left[$i][$i."-".$j][] = array('放款借款金额性别',U('/admin/borrowDa/daLoanSex'),1);
$menu_left[$i][$i."-".$j][] = array('放款借款金额年龄',U('/admin/borrowDa/daLoanAge'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款金额',U('/admin/borrowDa/daDue'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款金额地址',U('/admin/borrowDa/daDueAd'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款金额性别',U('/admin/borrowDa/daDueSex'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款金额年龄',U('/admin/borrowDa/daDueAge'),1);


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('催收统计','#',1);
$menu_left[$i][$i."-".$j][] = array('客户照片',U('/admin/collection/getPic'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款(全部)',U('/admin/collection/dueAllExport'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款',U('/admin/collection/dueExport'),1);
$menu_left[$i][$i."-".$j][] = array('逾期借款（带联络信息）',U('/admin/collection/dueDetailExport'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('退款查询','#',1); 
$menu_left[$i][$i."-".$j][] = array('拍拍信失败',U('/admin/bid/reviews4'),1);
$menu_left[$i][$i."-".$j][] = array('人脸失败',U('/admin/bid/reviews3'),1);
$menu_left[$i][$i."-".$j][] = array('复审失败',U('/admin/bid/reviews'),1);
$menu_left[$i][$i."-".$j][] = array('资金筹集失败',U('/admin/bid/reviews2'),1);

$i++; 
$menu_left[$i]= array('活动','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('SMS','#',1);
$menu_left[$i][$i."-".$j][] = array('SMS参数',U('/admin/message/index'),1);
$menu_left[$i][$i."-".$j][] = array('SMS模板',U('/admin/message/templet'),1);
$menu_left[$i][$i."-".$j][] = array('发送SMS',U('/admin/message/send'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('Wechat','#',1);
$menu_left[$i][$i."-".$j][] = array('Wechat模板',U('/admin/wechat/index'),1);

$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('App','#',1);
$menu_left[$i][$i."-".$j][] = array('安卓模板',U('/admin/apppush/index'),1);
$menu_left[$i][$i."-".$j][] = array('发送安卓消息',U('/admin/apppush/send'),1);


$i++;
$menu_left[$i]= array('福米上标','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('上标','#',1);
$menu_left[$i][$i."-".$j][] = array('待授信的借款',U('/admin/bid/recheck'),1);
$menu_left[$i][$i."-".$j][] = array('待复审的借款',U('/admin/bid/review'),1);
$menu_left[$i][$i."-".$j][] = array('可上标的借款',U('/admin/bid/index'),1);
$menu_left[$i][$i."-".$j][] = array('拍拍信查询',U('/admin/ppclist/index'),1);
$menu_left[$i][$i."-".$j][] = array('续期的借款',U('/admin/bid/renewal'),1);
$menu_left[$i][$i."-".$j][] = array('已上标的借款',U('/admin/bid/upList'),1);


$j++;
$menu_left[$i]['low_title'][$i."-".$j] = array('费用调整','#',1);
$menu_left[$i][$i."-".$j][] = array('续期转账',U('/admin/bid/transferList'),1);
/*$menu_left[$i][$i."-".$j][] = array('未结算的费用',U('/admin/bid/unbalance'),1);
$menu_left[$i][$i."-".$j][] = array('已结算的费用',U('/admin/bid/balance'),1);*/


$i++;
$menu_left[$i]= array('统计','#',1);
$menu_left[$i]['low_title'][$i."-".$j] = array('统计','#',1);
$menu_left[$i][$i."-".$j][] = array('注册信息',U('/admin/statistics/register'),1);
$menu_left[$i][$i."-".$j][] = array('申请信息',U('/admin/statistics/apply'),1);
$j++;

$i++;
$menu_left[$i] = array('财务报表', '#', 1);
$menu_left[$i]['low_title'][$i . "-" . $j] = array('财务报表', '#', 1);
$menu_left[$i][$i . "-" . $j][] = array('待还款列表', U('/admin/cashbill/wait'), 1);
$menu_left[$i][$i . "-" . $j][] = array('已还款列表', U('/admin/cashbill/has'), 1);
$j++;


$j++;

?>

