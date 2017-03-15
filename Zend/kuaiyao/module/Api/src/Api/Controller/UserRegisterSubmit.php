<?php
namespace Api\Controller;
use Api\Controller\Request\UserRequest;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;

/**
 * 用户注册协议
 *
 * @author WZ
 *        
 */
class UserRegisterSubmit extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    /**
     * 返回一个数组或者Result类
     *
     * @return \Api21\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $user = $request->user;
        
        // 如果设置注册验证手机号码，要去验证短信
        if (true == SMSCODE_SWITCH)
        {
            $this->checkSmsComplete(self::MOBILE_VALIDATE_TYPE_REGISTER, $request->smscode_id, $user->mobile); // 注册，检查是否有效，无效返回1010，请求超时
        }
        
        if ($user->mobile)
        {
            $check_mobile = $this->getUserTable()->getOne(array(
                'delete' => DELETE_FALSE,
                'mobile' => $user->mobile
            ));
        }
        if ($check_mobile)
        {
            // 找到匹配的手机号码 用户已存在
            return array(
                'status' => STATUS_USER_EXIST
            );
        }
        
        // 验证有没有提交密保答案
        if (! $request->answers || 1 != count($request->answers))
        {
            return STATUS_SECRET_SUBMIT;
        }
        $answers = array();
        foreach ($request->answers as $key => $value)
        {
            $value = $value->answer;
            if (! isset($value->content) || ! $value->content || ! isset($value->id) || ! $value->id)
            {
                return STATUS_SECRET_SUBMIT;
            }
            $value->timestamp = $this->getTime();
            $answers[$value->id] = $value;
        }
        if (1 != count($answers))
        {
            return STATUS_SECRET_SUBMIT;
        }
        
        $user_data = array(
            'password' => $user->password,
            'mobile' => $user->mobile,
            'answers' => json_encode($answers),
            'status' => 1,
            'timestamp' => $this->getTime()
        );
        $number = $this->createNumber(2,'user');
        if ($number)
        {
            $user_data['id'] = $number;
        }
        $user_id = $this->getUserTable()->insertData($user_data); // 插入到用户表
        
        if ($user_id)
        {
            // 环信用户注册
            $easemob = new AiiEasemobApi();
            $easemob->userRegister($user_id, md5($user_id));
            
            $user_data['id'] = $user_id;
            $user_data['nickname'] = '';
            $this->loginUpdate($user_data); // 更新三表状态
            
            $response->status = STATUS_SUCCESS;
            $response->id = $user_id;
        }
        else
        {
            $response->status = STATUS_UNKNOWN;
        }
        return $response;
    }
}
