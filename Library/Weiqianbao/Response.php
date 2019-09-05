<?php
namespace App\Library\Weiqianbao;

use App\Library\Fluent;

/**
 * Class Response
 * @property mixed sign_type
 * @property mixed response_code
 * @package App\Library\Weiqianbao
 */
abstract class Response extends Fluent
{
    protected $responseParam = array();

    public function success()
    {
        return $this->attributes['response_code'] == 'APPLY_SUCCESS';
    }

    public function error()
    {
        return $this->attributes['response_message'];
    }

    public function errno()
    {
        return $this->attributes['response_code'];
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