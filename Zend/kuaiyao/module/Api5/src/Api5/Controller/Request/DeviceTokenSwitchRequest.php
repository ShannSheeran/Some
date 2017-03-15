<?php
namespace Api5\Controller\Request;

use Api5\Controller\Common\Request;
use Api5\Controller\Item\StyleItem;

/**
 * DeviceTokenSwitch定义接收类的属性
 * 继承基础Request
 *
 * @author WZ
 *        
 */
class DeviceTokenSwitchRequest extends Request
{

    /**
     * 设备号，根据设备号查找表，执行开关操作
     *
     * @var String
     */
    public $device_token;

    /**
     * 1开启，2关闭；
     *
     * @var Number
     */
    public $open;

    public $style;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'device_token' => 'deviceToken'
        );
        $this->setOptions('key', $key);
        $this->style = new StyleItem();
    }
}