<?php
namespace Api0\Controller;
use Api0\Controller\Request\UserRequest;

/**
 * 重置密码
 */
class UserResetPassword extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        // 验证短信
        $this->checkSmsComplete(self::MOBILE_VALIDATE_TYPE_RESET, $request->smscode_id, $request->mobile);
        
        $user_info = $this->getUserTable()->getOne(array(
            "mobile" => $request->mobile
        ));
        
        /* 用户不存在 */
        if (! $user_info) {
            return STATUS_USER_NOT_EXIST; // 1103
        } else {
            // 验证有没有提交密保答案
            $check_answers = true;
            $answers = json_decode($user_info['answers'], true);
            if ($answers)
            {
                if (! $request->answers || 1 != count($request->answers))
                {
                    return STATUS_SECRET_CHECK;
                }
                foreach ($request->answers as $key => $value)
                {
                    $value = $value->answer;
                    if (! isset($value->content) || ! $value->content || ! isset($value->id) || ! $value->id)
                    {
                        return STATUS_SECRET_CHECK;
                    }
                    if (isset($answers[$value->id]) && $answers[$value->id]['content'] == $value->content)
                    {
                        unset ($answers[$value->id]);
                    }
                }
                if ($answers)
                {
                    return STATUS_SECRET_CHECK;
                }
            }
            
            if ($request->password) {
                $set = array(
                    "password" => $request->password
                );
                $where = array(
                    "id" => $user_info["id"]
                );
                $this->getUserTable()->update($set, $where);
            }
//             $code = $this->makeCode(6, self::CODE_TYPE_LOWERCASE);
//             $set = array(
//                 "password" => md5($code)
//             );
//             $where = array(
//                 "id" => $user_info["id"]
//             );
//             $this->getUserTable()->update($set, $where);
//             $content = sprintf(TEMPLATE_SMS_RESET, $code);
//             $sms = new SMSCode();
//             $sms->smsPush($content, array($request->mobile));
            return STATUS_SUCCESS;
        }
        return STATUS_UNKNOWN;
    }
}