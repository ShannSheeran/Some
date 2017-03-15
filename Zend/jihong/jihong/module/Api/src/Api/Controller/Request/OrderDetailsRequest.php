<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * 订单
 *
 * @author WZ
 *        
 */
class OrderDetailsRequest extends Request
{

    /**
     * id
     *
     * @var String
     */
    public $id;
    
    function __construct()
    {
        parent::__construct();
        $key = array('id' => 'id');
        $this->setOptions('key', $key);
    }
}