<?php
namespace Api\Controller;

/**
 * 地址列表
 */
class MyAddressList extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $where['user_id']=$this->getUserId();
        $where['delete'] = DELETE_FALSE; 
        $list = $this->getAll($this->getContactsTable(),$where);
        $total = $list['total'];
        $list_array=array();
        $item=array();
        foreach ($list['list'] as $v)
        {
            $item['id']=$v->id;
            $item['name']=$v->name;
            $item['mobile']=$v->mobile;
            $item['postcode']=$v->postcode;
            $item['type']=$v->type;
            $item['regionId']=$v->region_id;
            $item['regionInfo']=$v->region_info &&  json_decode($v->region_info) ? json_decode($v->region_info) : array();
            $item['street']=$v->street;
            $item['address']=$v->address;
            $item['timestamp']=$v->timestamp; 
            $list_array[]['address']=$item;
        }
        $response->total = $total;
        $response->addresses = $list_array;
        return $response;
    }
}
