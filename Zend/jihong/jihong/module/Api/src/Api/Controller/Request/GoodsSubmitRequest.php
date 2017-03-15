<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\GoodsSubmitItem;

  
/**
 * 提交地址接收类
 * 
 * @author WZ
 *        
 */
class GoodsSubmitRequest extends Request
{

    public $goods;

    function __construct()
    {
        parent::__construct();
        $this->goods = new GoodsSubmitItem();
    }
}
