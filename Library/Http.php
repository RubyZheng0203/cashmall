<?php
namespace App\Library;

use App\Library\Weiqianbao\Weiqianbao;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class Http
{
    protected $cookie;

    public function createCurlData($pay_params = array())
    {
        $params_str = "";
        foreach ($pay_params as $key => $val) {
            if (isset ($val) && !is_null($val) && @$val != "") {
                $params_str .= "&" . $key . "=" . urlencode(urlencode(trim($val)));
            }
        }
        if ($params_str) {
            $params_str = substr($params_str, 1);
        }

        return $params_str;
    }

    /**
     * @param $url
     * @param $parameter
     * @return mixed
     */
    public function post($url, $parameter)
    {

        $urlParse = parse_url($url);
        $data = $this->createCurlData($parameter);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);

        if (isset($urlParse["scheme"]) && $urlParse["scheme"] == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        $data = curl_exec($ch);
        if (($errno = curl_errno($ch))) {
            $error = curl_error($ch);
            throw new \Exception("curl error({$errno}):{$error}");
        }
        curl_close($ch);

        return $data;
    }

    /**
     * @param $url
     * @param array $parameter
     * @return mixed
     */
    public function get($url, $parameter = array())
    {
        $urlParse = parse_url($url);
        isset($urlParse["query"]) && parse_str($urlParse["query"], $tmp);
        !empty($tmp) && $parameter = array_merge($tmp, $parameter);
        $url = $url . "?" . http_build_query($parameter);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);

        if (isset($urlParse["scheme"]) && $urlParse["scheme"] == "https") {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        }
        $data = curl_exec($curl);
        if (($errno = curl_errno($curl))) {
            $error = curl_error($curl);
            throw new \Exception("curl error({$errno}):{$error}");
        }
        curl_close($curl);

        return $data;
    }

    /**
     * @param $method
     * @param $url
     * @param array $parameter
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function request($method, $url, $parameter = array())
    {
        if (strtoupper($method) == "GET") {
            return $this->get($url, $parameter);
        } elseif (strtoupper($method) == "POST") {
            return $this->post($url, $parameter);
        } else {
            throw new InvalidArgumentException("Invalid http request method.");
        }
    }

    public function setCookie(array $cookie)
    {
        //@todo
    }
}