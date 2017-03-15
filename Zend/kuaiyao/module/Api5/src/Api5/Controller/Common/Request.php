<?php
namespace Api5\Controller\Common;

/**
 * 公共Request对象配置
 *
 * @author WZ
 *
 */
class Request extends Item
{

    /**
     * 操作，一个协议可能有多个操作
     *
     * @var Number
     */
    public $action;

    /**
     * 列表查询的时候需要用到的分页信息，要看QueryTable类
     */
    public $table;
    
    /**
     * 各个协议可能有开关操作
     * @var unknown
     */
    public $open;
    
    /**
     * 各个协议有可能输入的正文；
     * 
     * @var string
     */
    public $content;
    
    public $key;
    
    function __construct() {
        parent::__construct();
        $key = array(
            'action' => 'a',
            'table' => 'ta'
        );
        $this->setOptions('key', $key);
        $this->table = new TableRequest();
    }
}