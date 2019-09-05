<?php
namespace App\Library\Weiqianbao\Notify\BusinessNotify;

use App\Library\Weiqianbao\Notify\BusinessNotify;

class AuditStatusSync extends BusinessNotify
{

    const ID = "audit_status_sync";

    protected static $strictParam = array(
        "audit_order_no",
        "inner_order_no",
        "audit_status",
        "audit_message",
    );
}