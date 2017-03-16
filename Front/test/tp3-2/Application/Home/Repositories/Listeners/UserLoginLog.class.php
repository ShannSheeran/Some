<?php
/**
 * File: UserLoginLog.class.php
 * User: xieguoqiu
 * Date: 2016/10/26 11:29
 */

namespace Home\Repositories\Listeners;

use Common\Common\Repositories\Events\EventAbstract;
use Common\Common\Repositories\Listeners\ListenerInterface;
use Think\Log;

class UserLoginLog implements ListenerInterface
{

    /**
     * @param \Home\Repositories\Events\UserLoginEvent $event
     */
    public function handle(EventAbstract $event)
    {
        // log something
        $event->user_id;
    }

}
