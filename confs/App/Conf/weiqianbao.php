<?php

return array(
    "member_gateway"            => "https://gate.pay.sina.com.cn/mgs/gateway.do",//
    "acquire_gateway"           => "https://gate.pay.sina.com.cn/mas/gateway.do",//
    "version"                   => "1.0",//
    "partner_id"                => "200012391559",//
	"fumi_email"                => "henryli@fumi88.com",
    "input_charset"             => "utf-8",//
    "sign_type"                 => "RSA",//
    "md5_sign_key"              => "0wagCAJqCno2SXbLgxEU41j4N7EPtlgM",//
    "rsa_sign_private_key_path" => APP_PATH. "/Key/rsa_sign_private.pem",
    "rsa_sign_public_key_path"  => APP_PATH. "/Key/rsa_sign_public.pem",
    "rsa_public_key_path"       => APP_PATH . "/Key/200012391559_encrypt_pub.pem",

    "sftp_host"                 => "180.153.89.72",//
    "sftp_port"                 => "50022",
    "sftp_user"                 => "200012391559",
    "sftp_public_key_path"      => APP_PATH . "/Key/id_fumi_rsa.pub",//
    "sftp_private_key_path"     => APP_PATH . "/Key/id_fumi_rsa",
	
    "test" => false,

);