<?php
namespace Api0\Controller;
use Api0\Controller\Request\UserRequest;

/**
 * 用户登录，返回用户id
 *
 * @author WZ
 *        
 */
class UserPartnerLogin extends User
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
        
        $keys = array(
            'open_id',
            'partner'
        );
        $where = $request->user->getValues($keys);
        if (! $where['open_id'] || ! $where['partner'])
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $user_partner = $this->getUserPartnerTable()->getOne($where);
        
        $response->id = '0';
        $response->status = STATUS_SUCCESS;
        if (! $user_partner)
        {
            // 未有授权记录
            $keys = array(
                'open_id',
                'partner',
                'nickname',
                'image_path'
            );
            $set = $request->user->getValues($keys);
            if (! $set['image_path'] || ! $set['nickname'])
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $set['image_url'] = $set['image_path'];
            unset($set['image_path']);
            $set['timestamp'] = $this->getTime();
            
            if (LOGIN_STATUS_LOGIN == $this->getUserStatus())
            {
                // 已登录，绑定授权
                $response->id = $set['user_id'] = $this->getUserId();
            }
            $this->getUserPartnerTable()->insertData($set);
            
            return $response;
        }
        elseif ($user_partner)
        {
            // 已有授权记录
            if (LOGIN_STATUS_LOGIN == $this->getUserStatus())
            {
                // 绑定授权
                $set = array(
                    'user_id' => $this->getUserId()
                );
                $this->getUserPartnerTable()->updateData($set, $where);
                $response->id = $this->getUserId();
                
                return $response;
            }
            elseif($user_partner->user_id)
            {
                // 登录
                $response->id = $user_partner->user_id;
                $user_info = $this->getUserTable()->getOne(array(
                    'id' => $user_partner->user_id
                ));
                if (DELETE_TRUE == $user_info['delete']) {
//                     return STATUS_USER_CANCEL;
                    $response->id = 0;
                }
                elseif (STATUS_STOP == $user_info['status']) {
                    return STATUS_USER_LOCKED;
                }
                $this->loginUpdate($user_info);
                
                return $response;
            }
        }
        return $response;
    }
}