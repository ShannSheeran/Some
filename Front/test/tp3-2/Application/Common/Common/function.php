<?php
/**
 * File: function.php
 * User: xieguoqiu
 * Date: 2016/8/10 17:56
 */

/**
 * @param \Common\Common\Repositories\Events\EventAbstract $eventAbstract
 */
function event(\Common\Common\Repositories\Events\EventAbstract $eventAbstract)
{
    static $event = null;

    if (!$event) {
        $event = new \Common\Common\Repositories\Events\Event();
    }

    $event->fire($eventAbstract);
}

function dd()
{
    array_map(function ($x) {
        (new \Illuminate\Support\Debug\Dumper())->dump($x);
    }, func_get_args());

    die(1);
}
 