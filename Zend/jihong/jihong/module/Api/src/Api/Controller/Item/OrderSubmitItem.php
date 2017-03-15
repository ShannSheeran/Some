<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
/**
 *
 * @author 接收地址详情信息
 *        
 */
class OrderSubmitItem extends Item
{

    /**
     * 商家id
     *
     * @var number
     */
    public $id;

    /**
     * 留言内容
     *
     * @var string
     */
    public $content;
    
    /**
     * 留言对象
     *
     * @var object
     */
    public $message;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id',
            'message' => 'message',
            'content' => 'content'
        );
        $this->setOptions('key', $key); // key的转换
    }
}