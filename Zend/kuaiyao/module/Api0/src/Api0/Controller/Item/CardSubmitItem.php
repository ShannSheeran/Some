<?php
namespace Api0\Controller\Item;

use Api0\Controller\Common\Item;
use Api0\Controller\Common\AddressItem;

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
    public $name;

    public $english_name;

    public $mobile;

    public $id_image;

    public $signature;

    public $qq;

    public $email;

    public $telephone;

    public $wechat;

    public $weibo;

    public $company;

    public $job;

    public $show;

    public $address;

    /**
     * 人脉圈id
     *
     * @var number
     */
    function __construct()
    {
        parent::__construct();
        $this->address = new AddressItem();
        
        $key = array(
            'english_name' => 'englishName',
            'id_image' => 'idImage',
            'company' => 'companyName'
        );
        $this->setOptions('key', $key); // key的转换
    }
}