<?php
namespace Api0\Controller\Request;

use Api0\Controller\Common\Request;
use Api0\Controller\Item\MessageSubmitItem;
/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *        
 */
class MessageSubmitRequest extends Request
{

    /**
     * 名片提交
     *
     * @var String
     */
    public $message;
    
    function __construct()
    {
        parent::__construct();
        $this->message = new MessageSubmitItem();
    }
}