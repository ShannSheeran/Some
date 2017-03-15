<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\ChatCommentSubmitItem;

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