<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;


/**
 * 名片提交
 * 
 * @author WZ
 *        
 */
class ActivityListItem extends Item
{
    public $parentId;
    
    public $categoryId;
    
    public $isTop;

    function __construct()
    {
        parent::__construct();

    }
}