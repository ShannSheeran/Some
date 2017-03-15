<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;
use Api5\Controller\Common\AddressItem;
use Api5\Controller\Common\provideTagsItem;
use Api5\Controller\Common\needsTagsItem;

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
    
    public $license_Image;
    
    public $faren_id_Image;

    public $signature;

    public $scale;

    public $home;

    public $telephone;
    
    public $address;
    public $provideTags;
    public $needsTags;

    function __construct()
    {
        parent::__construct();
        $this->address = new AddressItem();

        $key = array(
            'id_image' => 'imageId',
            'category_id' => 'categoryId',
            'license_Image' => 'licenseImage',
            'faren_id_Image' => 'idImage',
            
        );
        $this->setOptions('key', $key); // key的转换
    }
}