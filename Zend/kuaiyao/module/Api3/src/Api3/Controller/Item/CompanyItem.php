<?php
namespace Api3\Controller\Item;

use Api3\Controller\Common\Item;

/**
 * 名片提交
 * 
 * @author HY
 *        
 */
class CompanyItem extends Item
{

    /**
     * 名片提交
     *
     * @var string
     */
    public $id;
    
    public $name;
    
    /**
     * 人脉圈id
     *
     * @var number
     */
    function __construct()
    {
        parent::__construct();

    }
}