<?php
namespace Api\Controller;

use Api\Controller\Request\AddressSubmitRequest;
 
/**
 * 新增(修改)我的地址
 */
class AddressSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new AddressSubmitRequest();
        parent::__construct();
    }
    public function index()
    {
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $address = $request->address;
        $this->checkLogin();
        
        if ($address->id)
        {
            // 判断当前地址id是否存在
            $info = $this->getContactsTable()->getOne(array('id' => $address->id, 'user_id' => $this->getUserId()));
            if (!$info)
            {
                return STATUS_NODATA;
            }
        }
        $region = $this->getRegionInfoArray($address->regionId); // 弄出region_info
                                                                  
        // 弄出数据库的address
        $set_address = $this->getAdminController()->getProvinceCityCountryName($region['region_info']) . $address->street;
        $set = array(
            'name' => $address->name,
            'mobile' => $address->mobile,
            'postcode' => $address->postcode,
            'type' => $address->type,
            'user_id' => $this->getUserId(),
            'region_id' => $address->regionId,
            'region_info' => $region['region_info'],
            'street' => $address->street,
            'address' => $set_address,
            'delete' => DELETE_FALSE,
            'timestamp' => $this->getTime()
        );
        
        if ($address->type)
        {
            // 如果这条记录设为默认,原默认就要变成非默认
            $clear_set = array(
                'type' => 0
            );
            $clear_where = array(
                'type' => 1,
                'user_id' => $this->getUserId()
            );
            $this->getContactsTable()->update($clear_set, $clear_where);
        }
        
        if ($address->id)
        {
            // 有id就是修改
            $where = array(
                'id' => $address->id
            );
            $this->getContactsTable()->update($set, $where);
            $id = $address->id;
        }
        else
        {
            // 没id就是插入新的
            $id = $this->getContactsTable()->insertData($set);
        }
        
        $response->status = STATUS_SUCCESS;
        $response->id = $id;
        return $response;
    }
}
