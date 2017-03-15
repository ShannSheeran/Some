<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
use Api3\Controller\Item\ChatSubmitItem;
/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *        
 */
class ChatSubmitRequest extends Request
{

    /**
     * 名片提交
     *
     * @var String
     */
    public $chat;
    
    function __construct()
    {
        parent::__construct();
        $this->chat = new ChatSubmitItem();
    }
}