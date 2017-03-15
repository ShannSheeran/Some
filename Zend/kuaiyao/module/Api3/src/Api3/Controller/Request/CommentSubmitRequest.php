<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
use Api3\Controller\Item\CommentSubmitItem;

/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class CommentSubmitRequest extends Request
{

    /**
     *
     * @var string
     */
    public $comment;
    
    function __construct()
    {
        parent::__construct();
        $this->comment = new CommentSubmitItem();
    }
    
}