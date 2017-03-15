<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * 商品
 *
 * @author WZ
 *        
 */
class GoodsDetailsRequest extends Request
{

    /**
     * id
     * @var Number
     */
    public $id;
    /**
     * 规格id
     * @var Number
     */
    public $specification_id;
    
    function __construct()
    {
        parent::__construct();
        $key = array('id' => 'id','specification_id'=>'specificationId');
        $this->setOptions('key', $key);
    }
}