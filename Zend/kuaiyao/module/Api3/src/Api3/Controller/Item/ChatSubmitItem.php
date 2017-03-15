<?php
namespace Api3\Controller\Item;

use Api3\Controller\Common\Item;
use Api3\Controller\Common\AddressItem;

/**
 * 名片提交
 * 
 * @author WZ
 *        
 */
class ChatSubmitItem extends Item
{

    public $content;
    
    //public $action;//11111
    
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