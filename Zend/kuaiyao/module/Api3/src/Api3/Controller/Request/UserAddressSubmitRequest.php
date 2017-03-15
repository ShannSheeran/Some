<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
use Api3\Controller\Common\AddressItem;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class UserAddressSubmitRequest extends Request
{

    /**
     * 备注，设备信息字符串等
     *
     * @var object
     */
    public $address;

    function __construct()
    {
        parent::__construct();
        $this->address = new AddressItem();
    }
}