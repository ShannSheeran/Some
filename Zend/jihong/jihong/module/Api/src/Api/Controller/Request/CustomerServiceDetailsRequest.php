<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * 售后详情
 *
 * @author WZ
 *        
 */
class CustomerServiceDetailsRequest extends Request
{

    /**
     * id
     * @var Number
     */
    public $id;

    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'id' => 'id'
        );
        $this->setOptions('key', $key);
    }
}