<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
//use Api\Controller\Common\WhereRequest;
/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class TrackingRequest extends Request//WhereRequest
{
    /**
     * 类型:0订单 1商品发布
     * @var 上下架
     */
    public $type=0;
    /**
     * 类型:type=0时为订单id，type=1时为商品id
     * @var 上下架
     */
    public $id=0;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id',
            'type'=>'type'
        );
        $this->setOptions('key', $key);
    }
}