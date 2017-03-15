<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class CardDetailsRequest extends Request
{

    /**
     * 备注，设备信息字符串等
     *
     * @var string
     */
    public $uuid;
    
    public $major;
    
    public $minor;

    function __construct()
    {
        parent::__construct();
        
    }
}