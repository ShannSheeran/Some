<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
/**
 *
 * @author 接收地址详情信息
 *        
 */
class AddressSubmitItem extends Item
{

    /**
     * 地址id 如果有传id代表修改，没传就是添加
     *
     * @var number
     */
    public $id;

    /**
     * 联系人
     *
     * @var string
     */
    public $name;
    
    /**
     * 联系电话
     *
     * @var number
     */
    public $mobile;
    
    /**
     * 邮政编码
     *
     * @var number
     */
    public $postcode=000000;
    
    /**
     * 默认收货地址:0否（默认）；1是
     *
     * @var number
     */
    public $type=0;
    
    /**
     * 地区号
     *
     * @var number
     */
    public $regionId;

    /**
     * 地址详情
     *
     * @var string
     */
    public $street;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id',
            'name' => 'name',
            'mobile' => 'mobile',
            'postcode' => 'postcode',
            'type' => 'type',
            'regionId' => 'regionId',
            'street' => 'street'
        );
        $this->setOptions('key', $key); // key的转换
    }
}