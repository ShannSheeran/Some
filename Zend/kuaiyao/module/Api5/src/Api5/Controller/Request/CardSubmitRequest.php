<?php
namespace Api5\Controller\Request;

use Api5\Controller\Common\Request;
use Api5\Controller\Item\CardSubmitItem;

/**
 * AdList定义接收类的属性
 *
 * @author WZ
 *        
 */
class CardSubmitRequest extends Request
{

    /**
     * 名片提交
     *
     * @var String
     */
    public $card;
    
    function __construct()
    {
        parent::__construct();
        $this->card = new CardSubmitItem();
        
    }
}