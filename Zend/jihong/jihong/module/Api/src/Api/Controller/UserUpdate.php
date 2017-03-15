<?php
namespace Api\Controller;

use Api\Controller\Request\UserRequest;
/**
 * 用户，更新个人信息
 *
 * @author
 *         WZ
 *        
 */
class UserUpdate extends User
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
        $this->checkLogin();
        $user = $request->user;    
        $Where = array();
        $Where['id'] = $this->getUserId();
        $region = $this->getRegionInfoArray($user->region_id); // 弄出region_info    
        // 弄出数据库的address
        $set_address = $this->getAdminController()->getProvinceCityCountryName($region['region_info']) . $user->street;
        $user_data = array(
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
            'timestamp_update' => $this->getTime()
        );
        $user_id = $this->getUserTable()->updateData($user_data,$Where); // 更新到用户表信息
        return STATUS_SUCCESS;
    }
}