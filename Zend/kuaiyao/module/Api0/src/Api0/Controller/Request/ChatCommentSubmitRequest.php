<?php
namespace Api0\Controller\Request;

use Api0\Controller\Common\Request;
use Api0\Controller\Item\ChatCommentSubmitItem;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class ChatCommentSubmitRequest extends Request
{

    /**
     *
     * @var string
     */
    public $chatComment;
    
    function __construct() {
        parent::__construct();
        $this->chatComment = new ChatCommentSubmitItem();
    }
    
}