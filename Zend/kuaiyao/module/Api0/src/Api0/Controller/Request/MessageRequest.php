<?php
namespace Api0\Controller\Request;

use Api0\Controller\Common\Request;
use Api0\Controller\Item\MessageItem;
/**
 * 定义接收类的属性
 * 继承基础Request
 * 
 * @author WZ
 *        
 */
class MessageRequest extends Request
{
    /**
     * 留言对象
     */
    public $message;
    
    function __construct() {
        parent::__construct();
        $this->message = new MessageItem();
    }
}