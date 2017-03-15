<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\WhereRequest;
use Api3\Controller\Common\AddressItem;
/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class ActivityListWhereRequest extends WhereRequest
{

    /**
     * 发送模版的参数可能由客户端提供
     *
     * @var Array
     */
    public $address;
    
    public $parentId;
    
    public $categoryId;
    
    public $isTop;
    
    function __construct()
    {
        parent::__construct();
        $this->address = new AddressItem();
    }
}