<?php

return array(
    "member_gateway" => "https://testgate.pay.sina.com.cn/mgs/gateway.do",
    "acquire_gateway" => "https://testgate.pay.sina.com.cn/mas/gateway.do",
    "version" => "1.0",
    //"partner_id" => "200004227922",
    "partner_id" => "200004595271",//直连的测试帐号
    "fumi_email" => "sinaweibopay_zg@weibopay.com",
    "input_charset" => "utf-8",
    "sign_type" => "RSA",
    "md5_sign_key" => "1234567890qwertyuiopasdfghjklzxc",
    "rsa_sign_private_key_path" => APP_PATH. "/Key/rsa_sign_private_test.pem",
    "rsa_sign_public_key_path" => APP_PATH. "/Key/rsa_sign_public_test.pem",
    "rsa_public_key_path" => APP_PATH . "/Key/rsa_public.pem",

    "sftp_host" => "222.73.39.37",
    "sftp_port" => "50022",
    "sftp_user" => "200004227922",
    "sftp_public_key_path" => APP_PATH . "/Key/id_rsa.pub",
    "sftp_private_key_path" => APP_PATH . "/Key/id_rsa",

	
    "test" => true,
);