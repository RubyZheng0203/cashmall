<?php
namespace App\Library\Weiqianbao\Notify;

use App\Library\StrictFluent;

/**
 * Class BaseNotify
 * @property mixed sign_type
 * @property mixed sign
 * @property mixed notify_type
 * @package App\Library\Weiqianbao\Notify
 */
class BaseNotify extends StrictFluent
{

    protected static $strictParam = [
        "notify_type",
        "notify_id",
        "_input_charset",
        "notify_time",
        "sign",
        "sign_type",
        "version",
        "memo",
        "error_code",
        "error_message",
    ];

} 