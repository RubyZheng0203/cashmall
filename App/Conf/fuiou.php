<?php

return array(
    "member_gateway"     => "http://cgtest.fuiou.com:8090/control.action",
    "balance_gateway"    => "http://cgtest.fuiou.com:8090/BalanceAction.action", //用户余额查询接口
    "onLine_gateway"     => "http://cgtest.fuiou.com:8090/500002.action", //网银充值接口
    "noCashier_gateway"  => "http://cgtest.fuiou.com:8090/500012.action", //无收银台网银充值接口
    "ver"                => "1.0",//版本号
    "mchnt_cd"           => "0002900F0600008",//测试商户号
    "back_url"           => "http://192.168.10.121:8013",//页面返回地址
    "notify_url"         => "http://a211o41565.imwork.net:55421/fuiou",//回调通知地址
    "rsa_sign_private_key_path" => APP_PATH. "/Key/fuiou_test_private_key.pem",
    "rsa_sign_public_key_path"  => APP_PATH. "/Key/fuiou_test_public_key.pem",
    "test" => true,
);