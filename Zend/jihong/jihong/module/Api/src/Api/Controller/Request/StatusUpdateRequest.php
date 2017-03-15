<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

use Api\Controller\Item\StatusUpdateItem;
/**
 * 商品
 *
 * @author WZ
 *        
 */
class StatusUpdateRequest extends Request
{
    /**
     * type=1时为订单状态操作，2时为发布商品状态操作
     * @var Number
     */
    public $type=1;
    /**
     * 订单id
     * @var Number
     */
    public $id;
    /**
     * 购买数量
     * @var Number
     */
    public $delay_reason;
    /**
     * 支付方式
     * @var Number
     */
    public $pay_type;
    /**
     * 凭证图片
     * @var object
     */
    public $image;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id',
            'type' => 'type',
            'delay_reason'=>'delayReason',
            'pay_type'=>'payType',
            'image'=>'image'
        );
        $this->setOptions('key', $key);
        $this->image = new StatusUpdateItem();
    }
}