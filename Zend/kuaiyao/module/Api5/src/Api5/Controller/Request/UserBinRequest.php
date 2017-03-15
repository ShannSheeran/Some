<?php
namespace Api5\Controller\Request;

use Api5\Controller\Common\Request;
use Api5\Controller\Item\UserdeviceItem;
/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class UserBinRequest extends Request
{

    public $device;
    
    function __construct(){
        parent::__construct();
       
        $this->device = new UserdeviceItem();
    }
}