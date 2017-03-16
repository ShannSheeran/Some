<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;
use Api5\Controller\Common\AddressItem;
/**
 * 名片提交
 * 
 * @author WZ
 *        
 */
class CardSubmitItem extends Item
{

    /**
     * 名片提交
     *
     * @var string
     */
    public $cardName;
    
    public $name;

    public $englishName;

    public $mobile;

    public $image;
    
    public $wxImage;

    public $signature;

    public $qq;

    public $email;
    
    public $job;

    public $company;

    public $style;
    
    public $address;
    
    public $categoryId;
    
    /**
     * 人脉圈id
     *
     * @var number
     */
    function __construct()
    {
        parent::__construct();
        $this->company = new CompanyItem();
        $this->style = new CardstyleItem();
        $this->wxImage = new Item();
        $this->address = new AddressidItem();
        $this->image =new Item();
        
//         $key = array(
//             'id_image' => 'idImage',
//         );
//        $this->setOptions('key', $key); // key的转换
    }
}