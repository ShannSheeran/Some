<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * 商品
 *
 * @author WZ
 *        
 */
class CartSubmitRequest extends Request
{

    /**
     * id
     *
     * @var String
     */
    public $goods_id;
    
    public $specification_id;
    
    public $number;
    
    public $cart_id;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'goods_id' => 'goodsId',
            'specification_id'=>'specificationId',
            'number'=>'number',
            'cart_id'=>'cartId'
        );
        $this->setOptions('key', $key);
    }
}