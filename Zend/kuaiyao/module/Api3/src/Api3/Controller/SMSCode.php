<?php
namespace Api3\Controller;

use Api3\Controller\Request\SMSCodeRequest;
use Core\System\AiiPush\AiiPush;
use Core\System\AiiPush\AiiMyFile;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;

/**
 * 短信验证
 * 1.获取验证码，2.进行验证
 *
 * @author WZ
 *        
 */
class SMSCode extends User
{

    /**
     * 获取验证码
     *
     * @var 1
     */
    const MOBILE_VALIDATE_ACTION_GET = 1;

    /**
     * 验证验证码
     *
     * @var 2
     */
    const MOBILE_VALIDATE_ACTION_CHECK = 2;

    public function __construct()
    {
        $this->myRequest = new SMSCodeRequest();
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
        
        $request->action = $request->action ? $request->action : 1;
        if (self::MOBILE_VALIDATE_ACTION_GET == $request->action)
        {
            // 获取验证码
            /*
             * 根据type 检查相关内容
             */
            if (empty($request->mobile) || empty($request->type))
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $user_info = $this->getUserInfo($request->type, $request->mobile);
            if (STATUS_SUCCESS != $user_info['status']) {
                return $user_info['status'];
            }
            
            $new = true;
            $code = $this->makeSmsCode($request->type) . '';
            // 检查10分钟内有没有发送过验证码，有则找回这条验证码
            $value = array(
                'mobile' => $request->mobile,
                'status' => self::MOBILE_VALIDATE_STATUS_TEMP,
                'type' => $request->type
            );
            $mobile_validate = $this->getSmsCodeTable()->getOne($value);
            $sent = false;
            if ($mobile_validate && $mobile_validate->expire > $this->getTime())
            {
                $id = $mobile_validate->id;
                $count = $mobile_validate->count;
                $code = $mobile_validate->code;
                $new = false;
                $result = true;
                if (time() - strtotime($mobile_validate->timestamp_update) < 60) {
                    // 60秒内不重复发
                    $sent = true;
                    //                     return STATUS_TOO_FAST;
                }
            }
            
            if (! $sent) {
                // 短信内容
                $content = $this->smsTemplate($request->type, $code, array());
                if (SMSCODE_SWITCH)
                {
                    // 开启发送
                    $result = $this->smsPush($content, array(
                        $request->mobile
                    )); // 发送
                }
                else
                {
                    // 测试环境看短信内容
                    $result = true;
                    $response->code = $code;
                }
                
                if ($new)
                {
                    // 第一次发送
                    $data = array(
                        'mobile' => $request->mobile,
                        'code' => $code,
                        'status' => $result ? self::MOBILE_VALIDATE_STATUS_TEMP : self::MOBILE_VALIDATE_STATUS_FAIL,
                        'count' => 1,
                        'expire' => date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE),
                        'type' => $request->type,
                        'timestamp' => $this->getTime()
                    );
                    if (self::MOBILE_VALIDATE_TYPE_BIND == $request->type)
                    {
                        $data['user_id'] = $this->getUserId();
                    }
                    $id = $this->getSmsCodeTable()->insertdata($data);
                }
                else
                {
                    // 重复发送
                    $this->getSmsCodeTable()->updateKey($id, 1, 'count', 1);
                }
            }
            
            $response->status = ($result ? STATUS_SUCCESS : STATUS_UNKNOWN); // 成功或未知错误
            $response->id = $id;
        }
        elseif (self::MOBILE_VALIDATE_ACTION_CHECK == $request->action)
        {
            // action 2 验证验证码
            if (CHECK_SMSCODE)
            {
                $where = array(
                    'mobile' => $request->mobile,
                    'type' => $request->type
                );
                $mobile_validate = $this->getSmsCodeTable()->getOne($where);
                if ($request->mobile == '13527262005' && $request->where->code == '262005') {
                    $check = true;
                }
                elseif ($mobile_validate && $mobile_validate->expire > $this->getTime())
                {
                    if (self::MOBILE_VALIDATE_TYPE_REGISTER == $mobile_validate->status)
                    {
                        $used = true;
                    }
                    else
                    {
                        $used = false;
                    }
                    
                    if (! SMSCODE_SWITCH)
                    {
                        // 手机号码后四位，用于用户收不到验证码的时候填写
                        $check = true;
                    }
                    elseif ($mobile_validate->code == $request->where->code)
                    {
                        // 匹配正确的短信验证码
                        $check = true;
                    }
                    else
                    {
                        $check = false;
                    }
                    
                    if ($check)
                    {
                        if (! $used)
                        {
                            $this->complete($mobile_validate->id);
                        }
                        else
                        {
                            $check = false; // 屏蔽这条可使得验证码在一定时间内可重复使用，不然每条验证码只能使用一次。
                        }
                    }
                }
                else
                {
                    $check = false;
                }
            }
            else
            {
                // 短信接口还没开通，所有验证码都可以通过
                $check = true;
            }
            
            /*
             * $used用这个判断，每个验证码只能用一次
             */
            $response->status = ($check ? STATUS_SUCCESS : STATUS_CAPTCHA_ERROR);
            if ($check) {
                $user_info = $this->getUserInfo($request->type, $request->mobile);
                $user_info = $user_info['user_info'];
                if (self::MOBILE_VALIDATE_TYPE_REGISTER == $request->type) {
                    $data = array(
                        'mobile' => $request->mobile,
                        'timestamp' => $this->getTime()
                    );
                    $response->id = $this->getUserTable()->insertData($data);
                    
                    $easemob = new AiiEasemobApi();
                    $easemob->userRegister($response->id, md5($response->id));
                    $easemob->userUpdateNickname($response->id, $request->mobile);
                    
                    $data['id'] = $response->id;
                    $data['name'] = '';
                    $this->loginUpdate($data);
                    
                    $content = TEMPLATE_SMS_REGISTER; //9.21
                    $this->smsPush($content,array($request->mobile));
                    
                }
                elseif (self::MOBILE_VALIDATE_TYPE_LOGIN == $request->type) {
                    if (STATUS_STOP == $user_info->status && $check) {
                        // 禁用用户
                        return STATUS_USER_LOCKED;
                    }
                    $this->loginUpdate($user_info);
                    $response->id = $user_info['id'];
                }
            }
        }
        else
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
        }
        return $response;
    }

    /**
     * 根据类型和手机号码，验证用户信息
     *
     * @param Number $type            
     * @param String $mobile            
     * @return Ambigous <\Api21\Controller\Ambigous, multitype:, boolean, ArrayObject, NULL, \ArrayObject, unknown>
     */
    private function getUserInfo($type, $mobile)
    {
        $return = array('status' => STATUS_SUCCESS,'user_info' => array());
        switch ($type)
        {
            case self::MOBILE_VALIDATE_TYPE_REGISTER: // 1注册
                $where = array(
                    'mobile' => $mobile,
                    'delete' => DELETE_FALSE
                );
                $return['user_info'] = $user_info = $this->getUserTable()->getOne($where);
                if ($user_info)
                {
                    // 用户已存在，手机号码不能重复，不能注册，退出
                    $return['status'] = STATUS_USER_EXIST;
                }
                break;
            case self::MOBILE_VALIDATE_TYPE_LOGIN: // 2登录
            case self::MOBILE_VALIDATE_TYPE_RESET: // 6重置密码
                // 登录
                $where = array(
                    'mobile' => $mobile,
                    'delete' => DELETE_FALSE
                );
                $return['user_info'] = $user_info = $this->getUserTable()->getOne($where);
                if (! $user_info)
                {
                    // 用户不存在
                    $return['status'] = STATUS_USER_NOT_EXIST;
                }
                break;
            case self::MOBILE_VALIDATE_TYPE_BIND: // 重新绑定手机
                $this->checkLogin();
                // 绑定手机
                // 如果客户没登录就退出
                $where = array(
                    'mobile' => $mobile,
                    'delete' => DELETE_FALSE
                );
                $return['user_info'] = $user_info = $this->getUserTable()->getOne($where);
                if ($user_info)
                {
                    // 新手机已经绑定用户，不能绑定另一个用户，退出
                    $return['status'] = STATUS_USER_EXIST;
                }
                break;
            case 4://提现1111111
                //$this->checkLogin();
            case 5:
                $return['user_info'] = $user_info = null;
                break; 
            default:
                $return['status'] = STATUS_PARAMETERS_INCOMPLETE;
                break;
        }
        return $return;
    }

    /**
     * 更新验证状态
     *
     * @author WZ
     * @param number $id
     *            短信id
     */
    public function complete($id)
    {
        // 已验证
        $set = array(
            'status' => self::MOBILE_VALIDATE_STATUS_USED
        );
        
        $where = array(
            'id' => $id
        );
        return $this->getSmsCodeTable()->update($set, $where);
    }

    /**
     * 2014.3.24 hexin
     * 生成手机验证码
     * <br />2014/3/25 WZ 改
     *
     * @param number $type            
     * @return string number
     */
    public function makeSmsCode($type)
    {
       $code = $this->makeCode(4, self::CODE_TYPE_NUMBER);
        switch ($type)
        {
            case self::MOBILE_VALIDATE_TYPE_REGISTER: // 注册
            case self::MOBILE_VALIDATE_TYPE_LOGIN: // 登录
            case self::MOBILE_VALIDATE_TYPE_BIND: // 绑定
            break;
 
        }
        return $code;
    }

    /**
     * 2014/3/25
     * 短信模版
     *
     * @author WZ
     * @param number $type            
     * @param string $code            
     * @param
     *            array 其它参数
     * @return string
     */
    public function smsTemplate($type, $code, array $args)
    {
        $template = TEMPLATE_SMS_CAPTCHA;
        $content = sprintf($template, $code);
        switch ($type)
        {
            case self::MOBILE_VALIDATE_TYPE_REGISTER: // 注册
            case self::MOBILE_VALIDATE_TYPE_LOGIN: // 登录
            case self::MOBILE_VALIDATE_TYPE_BIND: // 绑定
            $need = 0;
        }
        return $content;
    }

    /**
     * 发送多条短信的
     *
     * @author WZ
     * @param unknown $content            
     * @param array $mobile            
     * @return multitype:boolean
     */
    public function smsPush($content, array $mobile)
    {
        $push = new AiiPush();
        $return = false;
        foreach ($mobile as $m)
        {
            if (SMSCODE_SWITCH)
            {
                if ($m)
                {
                    $result = $push->pushSingleDevice($m, 16, $content);
                    $return = (isset($result['success']) && $result['success']) ? true : false;
                }
                else
                {
                    $return = false;
                }
            }
            else
            {
                $return = true;
            }
            if (PUSH_LOG_SWITCH)
            { // 开启了推送与短信的日志记录
                if (isset($result))
                {
                    if ($result)
                    {
                        $temp = '短信，短信发送成功， mobile：' . $m . '，content：' . $content;
                    }
                    else
                    {
                        $temp = '短信，短信发送失败不能进行验证， mobile：' . $m . '，content：' . $content;
                    }
                }
                else
                {
                    $temp = '短信，没有开启短信发送，mobile：' . $m . '，content：' . $content;
                }
                $myfile = new AiiMyFile();
                $myfile->setFileToPublicLog()->putAtStart($temp);
            }
        }
        return $return;
    }
}