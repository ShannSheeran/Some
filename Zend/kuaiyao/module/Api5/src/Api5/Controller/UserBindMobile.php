<?php
namespace Api5\Controller;
use Api5\Controller\Request\UserRequest;

/**
 * 绑定手机
 */
class UserBindMobile extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    /**
     * 
     * @return string
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $this->checkLogin();
        // 验证短信
        if (! $request->mobile) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        
        if (true == SMSCODE_SWITCH)
        {
            $this->checkSmsComplete(self::MOBILE_VALIDATE_TYPE_BIND, $request->smscode_id, $request->mobile);
        }
        
        $user = $this->getUserTable()->getOne(array(
            'id' => $this->getUserId()
        ));
        if(! $user) {
            return STATUS_NODATA;
        }
        
//         if($request->password != $user['password']) {
//             return STATUS_PASSWORD_ERROR;
//         }
        
        $set = array(
            'mobile' => $request->mobile
        );
        $where = array(
            'id' => $this->getUserId()
        );
        $this->getUserTable()->update($set, $where);
        
        return STATUS_SUCCESS;
    }
}
