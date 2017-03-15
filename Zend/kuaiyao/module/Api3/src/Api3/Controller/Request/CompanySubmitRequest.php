<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
use Api3\Controller\Item\CompanySubmitItem;

/**
 * CompanyList定义接收类的属性
 *
 * @author HY
 *        
 */
class CompanySubmitRequest extends Request
{

    /**
     * 名片提交
     *
     * @var String
     */
    public $company;

    function __construct()
    {
        parent::__construct();
        $this->company = new CompanySubmitItem();
    }
}