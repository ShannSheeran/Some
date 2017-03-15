<?php
namespace Api3\Controller\Item;

use Api3\Controller\Common\Item;
use Api3\Controller\Common\AddressItem;

/**
 * 名片提交
 * 
 * @author WZ
 *        
 */
class CompanySubmitItem extends Item
{

    /**
     * 名片提交
     *
     * @var string
     */
    public $name;

    public $id;

    public $category_id;

    public $id_image;

    public $signature;

    public $scale;

    public $home;

    public $telephone;
    
    public $address;

    function __construct()
    {
        parent::__construct();
        $this->address = new AddressItem();
        
        $key = array(
            'id_image' => 'imageId',
            'category_id' => 'categoryId',
        );
        $this->setOptions('key', $key); // key的转换
    }
}