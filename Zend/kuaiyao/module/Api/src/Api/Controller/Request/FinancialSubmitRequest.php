<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\FinancialSubmitItem;
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