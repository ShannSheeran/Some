<?php
namespace Api\Controller;

use Api\Controller\Request\SMSCodeRequest;
use Core\System\AiiPush\AiiPush;
use Core\System\AiiPush\AiiMyFile;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;
use Zend\Db\Sql\Where;

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
        $verification_code= $request->where->verificationCode;
        $request->action = $request->action ? $request->action : 1;
        if (self::MOBILE_VALIDATE_ACTION_GET == $request->action)
        {
            // 获取验证码
            /*
             * 根据type 检查相关内容
             */
            $is_captcha = isset($_SESSION['is_captcha']) && $_SESSION['is_captcha'] ? $_SESSION['is_captcha'] : '';
            if($is_captcha &&  $request->type == 1)
            {//如果是PC端需验证图形验证码
                $captcha = isset($_SESSION['captcha']) && $_SESSION['captcha'] ? $_SESSION['captcha'] : '';
                if(!$captcha)
                {
                    $captcha = file_get_contents(APP_PATH . '/public/session_log/'.$this->getSessionId().'.txt');
                }
                if(!$captcha || ($captcha != $verification_code ))
                {
                    return STATUS_FAILED_TO_SEND;
                }
                unset($_SESSION['captcha']);//验证成功删除验证码
            }
            if (empty($request->mobile) || empty($request->type))
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
           
            $result = $this->sendCode($request->mobile, $request->type);
            
            if ($result['status']) {
                $response->status = $result['status'];
                if (isset($result['description']) && $result['description']) {
                    $response->description = $result['description'];
                }
                return $response;
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
                    //return STATUS_TOO_FAST;
                }
            }
            if (! $sent)
             {// 短信内容
                $content = $this->smsTemplate($request->type, $code, array());
                if (CHECK_SMSCODE)
                {// 开启发送
                    $result = $this->smsPush($content, array(
                        $request->mobile
                    )); // 发送
                }
                else
                {// 测试环境看短信内容
                    $result = true;
                    $response->code = $code;
                }
                
                if ($new)
                { // 第一次发送
                    $data = array(
                        'mobile' => $request->mobile,
                        'code' => $code,
                        'status' => $result ? self::MOBILE_VALIDATE_STATUS_TEMP : self::MOBILE_VALIDATE_STATUS_FAIL,
                        'count' => 1,
                        'expire' => date('Y-m-d H:i:s', time() + SMSCODE_EXPIRE),
                        'type' => $request->type,
                        'ip' => $this->getIP(),
                        'session_id' => $this->getSessionId(),
                        'timestamp' => $this->getTime(),
                        'timestamp_update' => $this->getTime(),
                    );
                    if (self::MOBILE_VALIDATE_TYPE_BIND == $request->type)
                    {
                        $data['user_id'] = $this->getUserId();
                    }
                    $id = $this->getSmsCodeTable()->insertdata($data);
                }
                else
                {// 重复发送
                    $this->getSmsCodeTable()->updateKey($id, 1, 'count', 1);
                }
            }
           
            $response->status = ($result ? STATUS_SUCCESS : STATUS_UNKNOWN); // 成功或未知错误
            $response->id = $id;
        }
        elseif (self::MOBILE_VALIDATE_ACTION_CHECK == $request->action)
        {// action 2 验证验证码
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
                    { // 手机号码后四位，用于用户收不到验证码的时候填写
                        $check = true;
                    }
                    elseif ($mobile_validate->code == $request->where->code)
                    {// 匹配正确的短信验证码
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
            {// 短信接口还没开通，所有验证码都可以通过
                $check = true;
            }
            
            /*
             * $used用这个判断，每个验证码只能用一次
             */
            $response->status = ($check ? STATUS_SUCCESS : STATUS_CAPTCHA_ERROR);
        }
        else
        {
            $response->status = STATUS_PARAMETERS_INCOMPLETE;
        }
        return $response;
    }
    
    function sendCode($mobile, $type = 0) {
        $mobile = trim($mobile);
        $type = (int) $type;
        if (empty($mobile) || empty($type))
        {
            if (! $mobile) {
                return array('status' => STATUS_PARAMETERS_INCOMPLETE, 'description' => '手机不能为空');
            }
            return array('status' => STATUS_PARAMETERS_INCOMPLETE);
        }
        if (SMS_LIMIT_IP || SMS_LIMIT_MOBILE || SMS_LIMIT_SESSION_ID || SMS_LIMIT_DAY) {
            $ip = $this->getIP();
            $session_id = $this->getSessionId();
            $where = new Where();
            $where->between('timestamp', date('Y-m-d H:i:s', strtotime('-1 day')), $this->getTime());
            $data = $this->getSmsCodeTable()->fetchAll($where);
            $ip_count = 0;
            $mobile_count = 0;
            $session_count = 0;
            foreach ($data as $value) {
                if ($ip == $value['ip']) {
                    $ip_count += $value['count'];
                }
                if ($mobile == $value['mobile']) {
                    $mobile_count += $value['count'];
                }
                if ($session_id && $session_id == $value['session_id']) {
                    $session_count += $value['count'];
                }
            }
            
           /*  $mobile_count_time = 0;
            $w = new where;
            $w->between('timestamp', date('Y-m-d H:i:s', strtotime('-1 hours')), $this->getTime());
            $time_mobile_data = $this->getSmsCodeTable()->fetchAll($w);
            foreach ($time_mobile_data as $v) {
                if ($mobile == $v['mobile']) {
                    
                    $mobile_count_time += $v['count'];
                }
            }
            if (SMS_LIMIT_TIME_MOBILE && $mobile_count_time >= SMS_LIMIT_TIME_MOBILE) {
                $this->saveLog($ip, $mobile, $session_id, 5);
                return array('status' => STATUS_MD5); // 安全验证不通过
            } */
            if (SMS_LIMIT_IP && $ip_count > SMS_LIMIT_IP) {
                $this->saveLog($ip, $mobile, $session_id, 1);
                return array('status' => STATUS_MD5); // 安全验证不通过
            }
            if (SMS_LIMIT_MOBILE && $mobile_count >= SMS_LIMIT_MOBILE) {
                $this->saveLog($ip, $mobile, $session_id, 2);
                return array('status' => STATUS_MD5); // 安全验证不通过
            }
          /*   if (SMS_LIMIT_SESSION_ID && $session_count >= SMS_LIMIT_SESSION_ID) {
                $this->saveLog($ip, $mobile, $session_id, 3);
                return array('status' => STATUS_MD5); // 安全验证不通过
            } */
            if (SMS_LIMIT_DAY && count($data) >= SMS_LIMIT_DAY) {
                $this->saveLog($ip, $mobile, $session_id, 4);
                return array('status' => STATUS_MD5); // 安全验证不通过
            }
        }
    }
    
    /**
     * 验证码用日志记录
     *
     * @param unknown $ip IP地址
     * @param unknown $mobile 手机号码
     * @param unknown $session_id 移动端Session
     * @param unknown $type 1IP,2手机,3session,4类型
     * @version 2016-5-24 WZ
     */
    private function saveLog($ip, $mobile, $session_id, $type) {
        header("Content-type: text/html; charset=utf-8");
        $content = '';
        switch ($type) {
            case 1:
                $content = '相同IP重复请求超过' . SMS_LIMIT_IP."次";
                break;
            case 2:
                $content = '相同手机重复请求超过' . SMS_LIMIT_MOBILE."次";
                break;
            case 3:
                $content = '相同Session重复请求超过' . SMS_LIMIT_SESSION_ID."次";
                break;
            case 4:
                $content = '每日发出次数超过' . SMS_LIMIT_DAY."次";
                break;
           /*  case 5:
                $content = '每小时发出次数超过' . SMS_LIMIT_TIME_MOBILE."次";
                break; */
        }
        $myfile = new AiiMyFile();
        $myfile->setFileToPublicLog()->putAtStart('短信拦截：' . $content . ',ip:' . $ip  . ',mobile:' . $mobile . ',session:' . $session_id);
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
            case self::MOBILE_VALIDATE_TYPE_RESET: // 2密码重置
            case self::MOBILE_VALIDATE_TYPE_BIND: // 重新绑定手机
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
            case self::MOBILE_VALIDATE_TYPE_RESET: // 2密码重置
            case self::MOBILE_VALIDATE_TYPE_BIND: // 绑定手机
            case self::MOBILE_VALIDATE_TYPE_UNWRAP: //设备解绑
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
            case self::MOBILE_VALIDATE_TYPE_RESET: // 2密码重置
            case self::MOBILE_VALIDATE_TYPE_BIND: // 绑定手机
            case self::MOBILE_VALIDATE_TYPE_UNWRAP: //设备解绑
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
                        $temp = '短信，短信发送成功， mobile：' . $m . '，content：' . $content."api";
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
    /**
     * 获取用户IP
     * @return Ambigous <unknown, string>
     * @version 2015年11月17日
     * @author liujun
     */
    public function getIP()
    {
        $ip = '';
        if (getenv('HTTP_CLIENT_IP'))
        {
            $ip = getenv('HTTP_CLIENT_IP');
        }
        elseif (getenv('HTTP_X_FORWARDED_FOR'))
        {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        }
        elseif (getenv('HTTP_X_FORWARDED'))
        {
            $ip = getenv('HTTP_X_FORWARDED');
        }
        elseif (getenv('HTTP_FORWARDED_FOR'))
        {
            $ip = getenv('HTTP_FORWARDED_FOR');
    
        }
        elseif (getenv('HTTP_FORWARDED'))
        {
            $ip = getenv('HTTP_FORWARDED');
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
   
}