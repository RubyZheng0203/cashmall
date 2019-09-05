<?php
namespace App\Library;

class StrictFluent extends Fluent
{
    protected static $strictParam = array();

    public function __set($key, $value)
    {
        if (in_array($key, static::$strictParam)) {
            $this->attributes[$key] = $value;
        }
    }
} 