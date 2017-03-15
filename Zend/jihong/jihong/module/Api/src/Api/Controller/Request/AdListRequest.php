<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *        
 */
class AdListRequest extends Request
{

    /**
     * 广告位id
     *
     * @var String
     */
    public $position_id;
    
    function __construct()
    {
        parent::__construct();
        $key = array('position_id' => 'positionId');
        $this->setOptions('key', $key);
    }
}