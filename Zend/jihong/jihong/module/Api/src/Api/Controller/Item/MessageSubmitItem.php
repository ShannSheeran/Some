<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
/**
 *
 * @author WZ
 *        
 */
class MessageSubmitItem extends Item
{

    /**
     * 内容
     *
     * @var string
     */
    public $content;

    /**
     * type
     *
     * @var number
     */
    public $type;


    function __construct()
    {
        parent::__construct();
        $key = array(
            'content' => 'content',
            'type' => 'type'
        );
        $this->setOptions('key', $key); // key的转换
    }
}