<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
  
/**
 * 提交申请售后接收类
 * 
 * @author WZ
 *        
 */
class CustomerServiceSubmitRequest extends Request
{
    /**
     * 订单id
     * @var Number
     */
    public $id;
    /**
     * 订单商品id
     * @var Number
     */
    public $goods_id;
    /**
     * 售后类型 ：1商品质量2物流配送3其他问题
     * @var Number
     */
    public $type;
    /**
     * 售后原因
     * @var String
     */
    public $reason;
    /**
     * 图片
     * @var Array
     */
    public $image;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'action' => 'action',
            'goods_id'=>'goodsId',
            'type'=>'type',
            'reason'=>'reason',
            'image'=>'image'
        );
        $this->setOptions('key', $key);
    }
}
