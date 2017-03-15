<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
use Api\Controller\Item\UserDetailsItem;
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
    
    function __construct(){
        parent::__construct();
        $key = array(
            'smscode_id' => 'smscodeId',
            'password_new' =>'passwordNew'
        );
        $this->setOptions('key', $key);
        $this->user = new UserDetailsItem();
    }
}