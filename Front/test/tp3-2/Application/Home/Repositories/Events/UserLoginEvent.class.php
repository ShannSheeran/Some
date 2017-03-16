<?php
/**
 * File: UserLoginEvent.class.php
 * User: xieguoqiu
 * Date: 2016/10/26 11:28
 */

namespace Home\Repositories\Events;

use Common\Common\Repositories\Events\EventAbstract;
use Home\Repositories\Listeners\UserLoginLog;

class UserLoginEvent extends EventAbstract
{
    protected $listeners = [
        UserLoginLog::class,
    ];

    public $user_id;

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }
    
}
