<?php
namespace Api5\Controller;

/**
 * 检查
 */
class CheckAction extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $request->action = $request->action ? $request->action : 1;
        $user_id = $this->getUserId();
        if ($request->action==1)
        {
            $code = trim($request->content);
            if (! $code) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $code_where = array('code' => $code, 'status' => 0);
            $code_info = $this->getInvitationCodeTable()->getOne($code_where);
        
            if (! $code_info) {
                return array('status' => STATUS_UNKNOWN, 'description' => '优惠码无效');
            }
            if ($code_info['user_id'] == $user_id) {
                return array('status' => STATUS_UNKNOWN, 'description' => '用户您自己的优惠码只能分享给朋友使用');
            }
        }
        return STATUS_SUCCESS;
    }
}
