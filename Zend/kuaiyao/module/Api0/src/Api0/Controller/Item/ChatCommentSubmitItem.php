<?php
namespace Api0\Controller\Item;

use Api0\Controller\Common\Item;
/**
 *
 * @author WZ
 *        
 */
class ChatCommentSubmitItem extends Item
{

    /**
     * 正文
     *
     * @var string
     */
    public $content;

    /**
     * 人脉圈id
     *
     * @var number
     */
    public $chat_id;

    /**
     * 回复用户id
     * 
     * @var number
     */
    public $user_id_to;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'chat_id' => 'chatId',
            'user_id_to' => 'userIdTo'
        );
        $this->setOptions('key', $key); // key的转换
        $functions = array(
            'content' => array(
                'key' => 'findSensitiveWord',
                'true' => STATUS_SENSITIVE_WORD
            )
        );
        $this->setOptions('functions', $functions); // 关键字过滤
    }
}