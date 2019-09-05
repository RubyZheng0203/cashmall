<?php
namespace App\Library\Weiqianbao\Notify;


use App\Library\Weiqianbao\Weiqianbao;
use ReflectionClass;

class Notify
{

    const SUCCESS_CODE = "APPLY_SUCCESS";

    /**
     * @var array
     */
    protected static $unSignParam = [
        "sign",
        "sign_type"
    ];

    /**
     * @var BaseNotify
     */
    protected $baseNotify;

    /**
     * @var BusinessNotify
     */
    protected $businessNotify;

    /**
     * @param BaseNotify $baseNotify
     * @param BusinessNotify $businessNotify
     */
    public function __construct(BaseNotify $baseNotify, BusinessNotify $businessNotify)
    {
        $this->baseNotify = $baseNotify;
        $this->businessNotify = $businessNotify;

        $this->checkNotifyOrFail();
    }

    /**
     * @return string
     */
    public function sign()
    {
        $allParam = $this->getAllParam();
        $signParam = $this->getSignParam($allParam);
        $signType = $this->baseNotify->sign_type;
        return Weiqianbao::sign($signParam, $signType);
    }

    /**
     * @return array
     */
    public function getAllParam()
    {
        return array_merge($this->baseNotify->toArray(), $this->businessNotify->toArray());
    }

    /**
     * @param array $param
     * @return array
     */
    protected function getSignParam(array $param)
    {
        foreach (static::$unSignParam as $val) {
            unset($param[$val]);
        }
        return $param;
    }

    /**
     * @return bool
     */
    public function checkSign()
    {
		if($this->baseNotify->sign_type == "RSA"){
			return true;
        }else{
            return $this->sign() == $this->baseNotify->sign;
        }
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkSignOrFail()
    {
        if (!$this->checkSign()) {
            throw new \Exception("Check sign failed.");
        }
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function checkNotifyOrFail()
    {
        $reflect = new ReflectionClass($this->businessNotify);
        $id = $reflect->getConstant("ID");
        if (empty($id) || $id != $this->baseNotify->notify_type) {
            throw new \Exception("Check notify param failed.");
        }
        return true;
    }

    /**
     * @return bool
     */
    public function success()
    {
        return $this->baseNotify->error_code == static::SUCCESS_CODE;
    }
} 