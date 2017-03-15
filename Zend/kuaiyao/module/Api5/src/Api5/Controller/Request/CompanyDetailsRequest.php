<?php
namespace Api5\Controller\Request;

use Api5\Controller\Common\Request;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class CompanyDetailsRequest extends Request
{

    /**
     * 备注，设备信息字符串等
     *
     * @var string
     */

    function __construct()
    {
        parent::__construct();
        
    }
}