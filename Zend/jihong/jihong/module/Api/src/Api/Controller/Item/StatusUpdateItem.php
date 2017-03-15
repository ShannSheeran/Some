<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
/**
 *
 * @author 接收地址详情信息
 *        
 */
class StatusUpdateItem extends Item
{

    /**
     * 地址id 如果有传id代表修改，没传就是添加
     *
     * @var number
     */
    public $id;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id'
        );
        $this->setOptions('key', $key); // key的转换
    }
}