<?php
namespace Api3\Controller\Request;

use Api3\Controller\Common\Request;
use Api3\Controller\Item\UserDetailsItem;
/**
 * 定义接收类的属性
 *
 * @author WZ
 *
 */
class UserRequest extends Request
{

    /**
     * 用户名
     *
     * @var String
     */
    public $name;

    /**
     * 密码
     *
     * @var String
     */
    public $password;
    
    /**
     * 新密码
     * 
     * @var String
     */
    public $password_new;
    
    /**
     * 短信验证码编号
     * 
     * @var number
     */
    public $smscode_id;
    
    /**
     * 手机号码
     * 
     * @var string
     */
    public $mobile;
    
    /**
     * @var 身份证号码
     */
    public $id_number;
    
    /**
     * 用户对象
     */
    public $user;
    
    public $answers;
    
    function __construct(){
        parent::__construct();
        $key = array(
            'password_new' => 'passwordNew',
            'smscode_id' => 'smscodeId',
            'id_number' => 'idNumber'
        );
        $this->setOptions('key', $key);
        $this->user = new UserDetailsItem();
    }
}