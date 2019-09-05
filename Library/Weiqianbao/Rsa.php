<?php
namespace App\Library\Weiqianbao;

class Rsa
{

    /**
     * @param $data
     * @param $publicKey
     * @return string
     */
    public static function Encrypt($data, $publicKey)
    {
        $encrypted = "";
        $cert = file_get_contents($publicKey);
        $puKey = openssl_pkey_get_public($cert); // 这个函数可用来判断公钥是否是可用的
        openssl_public_encrypt($data, $encrypted, $puKey); // 公钥加密
        $encrypted = base64_encode($encrypted); // 进行编码
        return $encrypted;
    }
    
    /**
     * 私钥解密
     * @param  $data
     * @param  $privateKey
     * @return string
     */
    public static function Decrypt($data, $privateKey)
    {
        $decrypted = "";
        $cert = file_get_contents($privateKey);
        $prKey = openssl_get_privatekey($cert); // 这个函数可用来判断私钥是否是可用的
        
        openssl_private_decrypt(base64_decode($data), $decrypted, $prKey); // 私钥解密
        
        return $decrypted;
    }
}