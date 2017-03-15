<?php
namespace Api5\Controller;
use Api5\Controller\Request\UserRequest;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;

/**
 * 修改密码
 * 
 * @author WZ
 *        
 */
class UserUpdatePassword extends User
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
        $this->checkLogin();    //  检查登录状态
                
        $user_id = $this->getUserId();
        $user_info = $this->getUserTable()->getOne(array(
            'id' => $user_id
        ));
        
        if ($user_info && $request->password_new) {
//             if ($user_info['password'] != $request->password) {
//                 $response->status = STATUS_PASSWORD_ERROR;
//             } 
//             else
//                 if ($request->password == $request->password_new) {
//                 $response->status = STATUS_NOT_UPDATE;
//             }
//             else {
                $set = array(
                    "password" => $request->password_new
                );
                $where = array(
                    "id" => $user_id
                );
                $dbResult = $this->getUserTable()->updateData($set, $where);
                $response->status = STATUS_SUCCESS;
//             }
        } else {
            $response->status = STATUS_UNKNOWN;
        }
        return $response;
    }
}