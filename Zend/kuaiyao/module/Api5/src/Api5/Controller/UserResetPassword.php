<?php
namespace Api5\Controller;
use Api5\Controller\Request\UserRequest;

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
            if ($request->password) {
                $set = array(
                    "password" => $request->password
                );
                $where = array(
                    "id" => $user_info["id"]
                );
                $this->getUserTable()->update($set, $where);
            }
            return STATUS_SUCCESS;
        }
        return STATUS_UNKNOWN;
    }
}