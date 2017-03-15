<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
/**
 *
 * @author 接收商品详情信息
 *        
 */
class GoodsSubmitItem extends Item
{

    /**
     * 商品id 如果有传id代表修改，没传就是添加
     *
     * @var number
     */
    public $id;

    /**
     * 商品名称
     *
     * @var string
     */
    public $name;
    
    /**
     * 商品等级
     *
     * @var string
     */
    public $level;
    
    /**
     * 上架方式：0现货  1预售
     *
     * @var number
     */
    public $salse_type;
    
    /**
     * 出货时间
     *
     * @var string
     */
    public $delivery_date;
    
    /**
     * 地区号
     *
     * @var array
     */
    public $image;

    /**
     * 商品分类id
     *
     * @var string
     */
    public $category;
    
    /**
     * 商品描述
     *
     * @var string
     */
    public $description;
    /**
     * 供应商留言
     *
     * @var string
     */
    public $message;
    /**
     * 商品规格
     *
     * @var object
     */
    public $specification;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id',
            'name' => 'name',
            'level' => 'level',
            'salse_type' => 'salseType',
            'delivery_date' => 'deliveryDate',
            'image' => 'image',
            'category' => 'category',
            'description' => 'description',
            'message' => 'message',
            'specification' => 'specification'
        );
        $this->setOptions('key', $key); // key的转换
    }
}