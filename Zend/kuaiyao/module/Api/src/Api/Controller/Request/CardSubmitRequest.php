<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\CardSubmitItem;

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