<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

//use Api\Controller\Item\OrderSubmitItem;
/**
 * 商品
 *
 * @author WZ
 *        
 */
class OrderSubmitRequest extends Request
{

    /**
     * 订单id
     * @var Number
     */
    public $order_id;
    /**
     * 商品id
     * @var Number
     */
    public $id;
    /**
     * 商品规格id
     * @var Number
     */
    public $specification_id;
    /**
     * 购买数量
     * @var Number
     */
    public $number;
    /**
     * 地址id
     * @var Number
     */
    public $contacts_id;
    /**
     * 期望发货日期
     * @var String
     */
    public $expect_date;
    /**
     * 支付方式
     * @var Number
     */
    public $pay_type;
    /**
     * 购物车id数组
     * @var Array
     */
    public $cart_ids;
    /**
     * messages
     * @var Array
     */
    public $messages;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id',
            'order_id' => 'orderId',
            'specification_id'=>'specificationId',
            'number'=>'number',
            'contacts_id'=>'contactsId',
            'specification_id'=>'specificationId',
            'pay_type'=>'payType',
            'cart_ids'=>'cartIds',
            'messages'=>'messages',
            'expect_date'=>'expectDate'
        );
        //$this->messages = new OrderSubmitItem();
        $this->setOptions('key', $key);
    }
}