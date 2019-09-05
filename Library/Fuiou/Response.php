<?php
namespace App\Library\Fuiou;

use App\Library\Fluent;

/**
 * Class Response
 * @property mixed sign_type
 * @property mixed response_code
 * @package App\Library\Fuiou
 */
abstract class Response extends Fluent
{
    protected $responseParam = array();

    public function success()
    {
        return $this->attributes['resp_code'] == '0000';
    }

    public function error()
    {
        return $this->attributes['response_message'];
    }

    public function errno()
    {
        return $this->attributes['resp_code'];
    }

    public function getRawData()
    {
        return $this->toArray();
    }

    public function setOrgInput($orgResponse)
    {
        $response = json_decode(urldecode($orgResponse));
        foreach ($response as $k => $v) {
            $this->attributes[$k] = $v;
        }
    }
}