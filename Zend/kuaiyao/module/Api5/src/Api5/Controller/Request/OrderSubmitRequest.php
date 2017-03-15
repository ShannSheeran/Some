<?php
namespace Api5\Controller\Request;

use Api5\Controller\Common\Request;
use Api5\Controller\Item\OrderItem;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class OrderSubmitRequest extends Request
{

    /**
     * 备注，设备信息字符串等
     *
     * @var object
     */
    public $order;

    function __construct()
    {
        parent::__construct();
        $this->order = new OrderItem();
    }
}