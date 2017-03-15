<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\CommentSubmitItem;

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