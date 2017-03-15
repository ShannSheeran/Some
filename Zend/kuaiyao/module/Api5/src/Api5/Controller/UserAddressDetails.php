<?php
namespace Api5\Controller;

/**
 * 查询收货地址
 */
class UserAddressDetails extends User
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array(
            'user_id' => $this->getUserId()
        );
        $info = $this->getUserAddressTable()->getOne($where);
        if (! $info) {
            // 第一次获取地址信息，自动把名片的信息加在地址信息上
            $user_info = $this->getUserTable()->getOne(array('id' => $this->getUserId()));
            $page_info = null;
            if ($user_info) {
                $page_info = $this->getViewUserPageTable()->getOne(array('page_id' => $user_info['page_id']));
            }
            
            $info = array(
                'name' => ($page_info && $page_info['name']) ? $page_info['name'] : '',
                'region_id' => ($page_info && $page_info['region_id']) ? $page_info['region_id'] : 0,
                'region_info' => ($page_info && $page_info['region_info']) ? $page_info['region_info'] : '',
                'street' => ($page_info && $page_info['street']) ? $page_info['street'] : '',
                'telephone' => ($page_info && $page_info['mobile']) ? $page_info['mobile'] : '',
                'address' => ($page_info && $page_info['address']) ? $page_info['address'] : '',
                'user_id' => $this->getUserId(),
                'timestamp' => $this->getTime(),
            );
            
            $info['id'] = $this->getUserAddressTable()->insertData($info);
        }
        
        $item = array(
            'id' => (int)$info['id'],
            'name' => (string)$info['name'],
            'regionId' => (int) $info['region_id'],
            'regionInfo' => $info['region_info'] ? json_decode($info['region_info']) : array(),
            'street' => (string) $info['street'],
            'telephone' => (string) $info['telephone']            
        );
        
        $response->address = $item;
        return $response;
    }
}
