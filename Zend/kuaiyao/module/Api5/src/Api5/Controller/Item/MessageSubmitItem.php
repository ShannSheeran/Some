<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;
use Api5\Controller\Common\AddressItem;

/**
 * 
 * 
 * @author WZ
 *        
 */
class MessageSubmitItem extends Item
{

    public $content;

    public $images;

    function __construct()
    {
        parent::__construct();
        $functions = array(
            'content' => array(
                'key' => 'findSensitiveWord',
                'true' => STATUS_SENSITIVE_WORD
            )
        );
        $this->setOptions('functions', $functions); // 关键字过滤
    }
}