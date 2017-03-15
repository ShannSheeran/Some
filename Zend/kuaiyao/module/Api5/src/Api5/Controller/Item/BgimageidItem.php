<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;

/**
 * 名片提交
 * 
 * @author HY
 *        
 */
class BgimageidItem extends Item
{

    /**
     * 背景图片id
     *
     * @var string
     */
    public $id;
  
    function __construct()
    {
        parent::__construct();
    }
}