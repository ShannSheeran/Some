<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;

/**
 * 用户注册协议
 *
 * @author WZ
 *        
 */
class UserRegister extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
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
        $user = $request->user;
        
        // 如果设置注册验证手机号码，要去验证短信
        if (true == SMSCODE_SWITCH)
        {
            $this->checkSmsComplete(self::MOBILE_VALIDATE_TYPE_REGISTER, $request->smscode_id, $user->mobile); // 注册，检查是否有效，无效返回1010，请求超时
        }

        $region = $this->getRegionInfoArray($user->region_id); // 弄出region_info
        
        // 弄出数据库的address
        $set_address = $this->getAdminController()->getProvinceCityCountryName($region['region_info']) . $user->street;
        $user_data = array(
            'type' => $user->type,
            'mobile' => $user->mobile,
            'company_name' => $user->company_name,
            'contacts_name' => $user->contacts_name,
            'fax' => $user->fax,
            'qq' => $user->qq,
            'email' => $user->email,
            'description' => $user->description,
            'region_id' => $user->region_id,
            'region_info' => $region['region_info'],
            'street' => $user->street,
            'address' => $set_address,    
            'status' => 1,
            'register_status'=>1,
            'delete' => DELETE_FALSE,
            'timestamp_update' => $this->getTime(),
            'timestamp' => $this->getTime()
        );
        $user_id = $this->getUserTable()->insertData($user_data); // 插入到用户表
        return $response;
    }
}
