<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class SMSCodeRequest extends Request
{

    /**
     * 类型，1.注册，2.绑定手机，3.重置密码 ，4.提现申请 
     *
     * @var Number
     */
    public $type;

    /**
     * 手机号码
     *
     * @var String
     */
    public $mobile;

    /**
     * where
     */
    public $where;
    
    function __construct()
    {
        parent::__construct();
        $key = array(
            'where' => 'w'
        );
        $this->setOptions('key', $key);
        $this->where = new SMSCodeWhereRequest();
    }
}