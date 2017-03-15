<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\MessageSubmitItem;

/**
 * 
 * 获取留言信息
 *
 * @author WZ
 *        
 */
class MessageSubmitRequest extends Request
{

  
    public $message;


    function __construct()
    {
        parent::__construct();
        $this->message = new MessageSubmitItem();
    }
}