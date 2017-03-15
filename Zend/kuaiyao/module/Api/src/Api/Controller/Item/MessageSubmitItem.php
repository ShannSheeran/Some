<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
use Api\Controller\Common\AddressItem;

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