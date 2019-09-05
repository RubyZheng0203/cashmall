<?php
namespace App\Library\Fuiou;

use App\Library\Fluent;
use InvalidArgumentException;

/**
 * Class Request
 * @property string sign_type
 * @package App\Library\Fuiou
 */
abstract class Request extends Fluent
{
    protected $method = "post";

    /** @var string */
    protected $url;

    /** @var array */
    protected $businessParam = array();

    /** @var array */
    protected $baseParam = array();

    /** @var string */
    protected $serviceName = "";

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        if (empty($this->url)) {
            throw new InvalidArgumentException("Illegal request url");
        }

        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function getBusinessParam()
    {
        return $this->toArray();
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * @return array
     */
    public function buildBaseParameters()
    {
        $default = array(
            "ver"       => Fuiou::getConfig("ver"),
            "mchnt_cd"  => Fuiou::getConfig("mchnt_cd"),
        );
        return array_merge($default, $this->baseParam);
    }

    /**
     * @param $key
     * @param $val
     */
    public function setBaseParam($key, $val)
    {
        $this->baseParam[$key] = $val;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getBaseParam($key)
    {
        return $this->baseParam[$key];
    }

    /**
     * @return array
     */
    public function getRequestParam()
    {
        $baseParam = $this->buildBaseParameters();
        $businessParam = $this->getBusinessParam();
        $requestParam = array_merge($baseParam, $businessParam);
        return array_merge($requestParam, array("code" => $this->getServiceName()));
    }


} 