<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;
/**
 *
 * @author WZ
 *        
 */
class MessageItem extends Item
{

    /**
     * 正文
     *
     * @var string
     */
    public $content;

    /**
     * 用户id
     *
     * @var number
     */
    public $user_id;

    /**
     * 说话人 id（0 表示管理员）
     * 
     * @var number
     */
    public $admin_id;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'user_id' => 'userId',
            'admin_id' => 'adminId'
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