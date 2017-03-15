<?php
namespace Api\Controller;
use Api\Controller\Request\UserRequest;

/**
 * 用户登录，返回用户id
 *
 * @author WZ
 *
 */
class UserLogin extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    /**
     * 
     * @return string|\Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $session_id = $this->getSessionId();
        if(! $session_id) {
            return STATUS_SESSION_EMPTY;
        }
        
        $where = array(
        	'mobile' => $request->name
        );
        $user_info = $this->getUserTable()->getOne($where);

        if(! $user_info) {
            return STATUS_USER_NOT_EXIST;
        }elseif(DELETE_TRUE == $user_info['delete']) {
            return STATUS_USER_CANCEL;
        }elseif(STATUS_STOP == $user_info['status']) {
            return STATUS_USER_LOCKED;
        }elseif($request->password != $user_info['password']) {
            return STATUS_PASSWORD_ERROR;
        }else {
            // 更新各个表
            $this->loginUpdate($user_info);
            $response->status = STATUS_SUCCESS;
            $response->id = $user_info['id'];
        }
        return $response;
    }
}