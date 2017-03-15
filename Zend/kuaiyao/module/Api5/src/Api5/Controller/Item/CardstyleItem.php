<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;


/**
 * 名片提交
 * 
 * @author HY
 *        
 */
class CardstyleItem extends Item
{

    /**
     * 名片提交
     *
     * @var string
     */
    public $id;
    
    public $image;
    
    /**
     * 人脉圈id
     *
     * @var number
     */
    function __construct()
    {
        parent::__construct();
        $this->image = new ImageidItem();

    }
}