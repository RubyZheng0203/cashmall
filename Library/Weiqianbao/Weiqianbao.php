<?php
namespace App\Library\Weiqianbao;

use App\Library\Http;
use LogicException;

class Weiqianbao
{
    /** @var Request */
    protected $request;

    /** @var  Response */
    protected $response;

    /** @var  array */
    protected static $config;
    

    const BANKCARD_TYPE_DEBIT = "DEBIT";
    const BANKCARD_TYPE_CREDIT = "CREDIT";

    const BANKCARD_ATTRIBUTE_C = "C";
    const BANKCARD_ATTRIBUTE_B = "B";

    const CERT_TYPE_IC = "IC";
    const CERT_TYPE_PP = "PP";
    const CERT_TYPE_HMP = "HMP";

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function fire()
    {
        $requestParam = $this->request->getRequestParam();
        $requestParam["sign"] = $this->sign($requestParam, $requestParam["sign_type"]);
        $http = new Http();
        $orgResponse = $http->request($this->request->getMethod(), $this->request->getUrl(), $requestParam);
        $this->response->setOrgInput($orgResponse);
        if (!$this->checkSign($this->response->toArray(), $this->response->sign_type)) {
            wqbLog("check sign faild:".$orgResponse);
            throw new LogicException("failed to check weiqianbao response sign.");
        }
        return $this->response;
    }
    
    /**
     * @return Response
     */
    public function fireNew()
    {
        $requestParam = $this->request->getRequestParam();
        $requestParam["sign"] = $this->sign($requestParam, $requestParam["sign_type"]);
        $http = new Http();
        $orgResponse = $http->request($this->request->getMethod(), $this->request->getUrl(), $requestParam);
        echo $orgResponse;
        return $this->response;
    }

    /**
     * @param array $payParams
     * @param $signType
     * @return string
     */
    public static function sign($payParams = array(), $signType)
    {
        ksort($payParams);
        $paramsStr = "";
        $signMsg = "";

        foreach ($payParams as $key => $val) {
            if ($key != "sign" && $key != "sign_type" && $key != "sign_version" && isset ($val) && @$val != "") {
                $paramsStr .= $key . "=" . $val . "&";
            }
        }
        $paramsStr = substr($paramsStr, 0, -1);
        switch (@$signType) {
            case 'RSA' :
                $privKey = file_get_contents(realpath(static::getConfig("rsa_sign_private_key_path")));
                $pKeyId = openssl_pkey_get_private($privKey);
                openssl_sign($paramsStr, $signMsg, $pKeyId, OPENSSL_ALGO_SHA1);
                openssl_free_key($pKeyId);
                $signMsg = base64_encode($signMsg);
                break;
            case 'MD5' :
            default :
                $paramsStr = $paramsStr . static::getConfig("md5_sign_key");
                $signMsg = strtolower(md5($paramsStr));
                break;
        }
        return $signMsg;
    }

    /**
     * @param array $pay_params
     * @param $sign_type
     * @return bool
     */
    public function checkSign($pay_params = array(), $sign_type)
    {
        ksort($pay_params);
        $params_str = "";
        foreach ($pay_params as $key => $val) {
            if ($key != "sign" && $key != "sign_type" && $key != "sign_version" && !is_null($val) && @$val != "") {
                $params_str .= "&" . $key . "=" . $val;
            }
        }
        if ($params_str) {
            $params_str = substr($params_str, 1);
        }
        switch (@$sign_type) {
            case 'RSA' :
                $cert = file_get_contents(static::getConfig("rsa_sign_public_key_path"));
                $pubKeyId = openssl_pkey_get_public($cert);
                $ok = openssl_verify($params_str, base64_decode($pay_params ['sign']), $cert, OPENSSL_ALGO_SHA1);
                $return = $ok == 1 ? true : false;
                openssl_free_key($pubKeyId);
                break;
            case 'MD5' :
            default :
                $params_str = $params_str . static::getConfig("md5_sign_key");
                $signMsg = strtolower(md5($params_str));
                $return = (@$signMsg == @strtolower($pay_params ['sign'])) ? true : false;
                break;
        }
        return $return;
    }

    /**
     * @param $data
     * @return string
     */
    public static function encrypt($data)
    {
        return Rsa::Encrypt($data, static::getConfig("rsa_public_key_path"));
    }

    /**
     * @param $config
     * @return $this
     */
    public static function setConfig(array $config)
    {
        self::$config = $config;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getConfig($key)
    {
        return isset(self::$config[$key]) ? self::$config[$key] : null;
    }

    /**
     * @return \App\Library\Weiqianbao\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \App\Library\Weiqianbao\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \App\Library\Weiqianbao\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \App\Library\Weiqianbao\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}