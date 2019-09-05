<?php
namespace App\Library\Fuiou;

use App\Library\Http;
use LogicException;

class Fuiou
{
    /** @var Request */
    protected $request;

    /** @var  Response */
    protected $response;

    /** @var  array */
    protected static $config;
    

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * 直连
     * @param 请求的链接  $url
     * @return 接口返回的数组 
     */
    public function genForward($url){
        $requestParam = $this->request->getRequestParam();
        $requestParam["signature"] = $this->sign($requestParam);
        $res = http_fuiou_request($url,$requestParam);
        return $res;
    }
    
    /**
     * 直连
     * @param 请求的链接  $url
     * @return 接口返回的数组
     */
    public function genForwardto($url){
        $requestParam = $this->request->getRequestParam();
        $msg = array(
                    "code"          => $requestParam['code'],
                    "mchnt_cd"      => $requestParam['mchnt_cd'],
                    "mchnt_txn_ssn" => $requestParam['mchnt_txn_ssn'],
                    "list"          => $requestParam['list'],
                );
        $sign    = $this->signkkk(json_encode($msg));
		wqbLog(json_encode($msg));
        $jData   = array(
            "msg"       => $msg,
            "signature" => $sign,);
        $jsonArr = array(
            'jData' => json_encode($jData)
        );
        wqbLog(json_encode($jData));
        $res = http_fuiou_request($url, $jsonArr);
        return $res;
    }

	public static function signkkk($payParams)
    {
        $signMsg   = "";
        
        $privKey   = file_get_contents(realpath(static::getConfig("rsa_sign_private_key_path")));
        $pKeyId    = openssl_pkey_get_private($privKey);
        openssl_sign($payParams, $signMsg, $pKeyId, OPENSSL_ALGO_SHA1);
        openssl_free_key($pKeyId);
        $signMsg = base64_encode($signMsg);
        return $signMsg;
    }
    
    
    
    /**
     * 网页连接
     * @param 请求的链接  $url
     * @return 网页跳转页面的html
     */
    public function genForwardHtml($url)
    {
        $requestParam = $this->request->getRequestParam();
        $requestParam["signature"] = $this->sign($requestParam);
        $charset = "utf-8";
        $html = "<html>";
        $head = "<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$charset."\" pageEncoding=\"".$charset."\" />";
        $html .= $head;
        $html .= "<title>loading</title>";
        $html .= "<style type=\"text/css\">";
        $html .= "body{margin:200px auto;font-family: \"宋体\", Arial;font-size: 12px;color: #369;text-align: center;}";
        $html .= "#1{height:auto; width:78px; margin:0 auto;}";
        $html .= "#2{height:auto; width:153px; margin:0 auto;}";
        $html .= "vertical-align: bottom;}";
        $html .= "</style>";
        $html .= "</head>";
        $html .= "<body>";
        $html .= "<div id=\"3\">交易处理中...</div>";
        $html .= "<form name=\"forwardForm\" action=\"".$url."\" method=\"POST\">";
        foreach ($requestParam as $key=>$param) {
            $html .= "  <input type=\"hidden\" name=\"".$key."\" value=\"".$param."\"/>";
        }
        $html .= "</form>";
        $html .= "<SCRIPT LANGUAGE=\"Javascript\">";
        $html .= "    document.forwardForm.submit();";
        $html .= "</SCRIPT>";
        $html .= "</body>";
        $html .= "</html>";
        return $html;
    }

    /**私钥加密
     * @param array $payParams
     * @param $signType
     * @return string
     */
    public static function sign($payParams = array())
    {
        ksort($payParams);
        $paramsStr = "";
        $signMsg   = "";
        foreach ($payParams as $key => $val) {
            if ($key != "signature" && isset ($val)) {
                $paramsStr .=  $val . "|";
            }
        }
        $paramsStr = substr($paramsStr, 0, -1);
        $privKey   = file_get_contents(realpath(static::getConfig("rsa_sign_private_key_path")));
        $pKeyId    = openssl_pkey_get_private($privKey);
        openssl_sign($paramsStr, $signMsg, $pKeyId, OPENSSL_ALGO_SHA1);
        openssl_free_key($pKeyId);
        $signMsg = base64_encode($signMsg);
        return $signMsg;
    }

    /**公钥加密
     * @param array $pay_params
     * @param $sign_type
     * @return bool
     */
    public function checkSign($pay_params = array())
    {
        ksort($pay_params);
		$params_str = "";
        foreach ($pay_params as $key => $val) {
            if ($key != "signature"&& !is_null($val) && @$val != "") {
                $params_str .= "|". $val;
            }
        }
        if ($params_str) {
            $params_str = substr($params_str, 1);
        }
        $cert       = file_get_contents(static::getConfig("rsa_sign_public_key_path"));
        $pubKeyId   = openssl_pkey_get_public($cert);
        $ok         = openssl_verify($params_str, base64_decode($pay_params ['signature']), $cert, OPENSSL_ALGO_SHA1);
        $return     = $ok == 1 ? true : false;
        openssl_free_key($pubKeyId);
        
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
     * @return \App\Library\Fuiou\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \App\Library\Fuiou\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \App\Library\Fuiou\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \App\Library\Fuiou\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}