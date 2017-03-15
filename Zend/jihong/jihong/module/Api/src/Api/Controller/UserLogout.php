<?php
namespace Api\Controller;
use Api\Controller\Request\UserLogoutRequest;

/**
 * 退出登录
 *
 * @author WZ
 *        
 */
class UserLogout extends User
{
    
    /**
     * 
     * @return string
     */
    public function index()
    {
        $response = $this->getAiiResponse();
        // 检查登录状态
//         $this->checkLogin();
        // 退出登录
        $this->userLogout();
        
        return STATUS_SUCCESS;
    }

    /**
     * 退出登录
     *
     * @author WZ
     */
    private function userLogout()
    {
        if (LOGIN_STATUS_LOGIN == $this->getUserStatus())
        {
            $this->clearDeviceUser($this->getUserId());
        }
                                                           
        // 再把登录表的状态改变成登出状态。
        $set = array(
            'status' => LOGIN_STATUS_LOGOUT
        );
        $where = array(
            'session_id' => $this->getSessionId()
        );
        $this->getLoginTable()->update($set, $where);
    }
}