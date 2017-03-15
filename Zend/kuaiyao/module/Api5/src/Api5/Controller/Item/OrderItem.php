<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;

/**
 * 订单提交
 * 
 * @author WZ
 *        
 */
class OrderItem extends Item
{
    /**
     * 数量
     * @var unknown
     */
    public $number;
    
    /**
     * 优惠码数组
     * @var unknown
     */
    public $codes;

    /**
     * 支付方式，详见数据库
     * @var unknown
     */
    public $payment;

    /**
     * 发票对象
     * @var unknown
     */
    public $invoice;

    function __construct()
    {
        parent::__construct();
        $this->invoice = new InvoiceItem();
    }
}