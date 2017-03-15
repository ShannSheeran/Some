<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 
 * 列表类型
 *
 * @author WZ
 *        
 */
class CategoryListRequest extends Request
{

  
    public $action;


    function __construct()
    {
        parent::__construct();
        $key = array('action' => 'action');
        $this->setOptions('key', $key);
    }
}