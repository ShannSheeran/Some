<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
use Api3\Controller\Item\FinancialSubmitItem;
/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author HY
 *        
 */
class FinancialSubmitRequest extends Request
{

    /**
     * 提现提交
     *
     * @var String
     */
    public $financial;
    
    function __construct()
    {
        parent::__construct();
        $this->financial = new FinancialSubmitItem();
    }
}