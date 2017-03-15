<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;

/**
 * DeviceTokenSwitch定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *        
 */
class SwitchRequest extends Request
{

    /**
     * 1开启，2关闭；
     *
     * @var Number
     */
    public $open;
}