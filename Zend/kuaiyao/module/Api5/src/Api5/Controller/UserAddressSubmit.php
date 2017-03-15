<?php
namespace Api5\Controller;

use Api5\Controller\Request\UserAddressSubmitRequest;
/**
 * 用户，收货地址提交/更新
 *
 * @author
 *         WZ
 *        
 */
class UserAddressSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new UserAddressSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array(
            'user_id' => $this->getUserId()
        );
        $address_info = $this->getUserAddressTable()->getOne($where);
        
        $region_info = $this->getRegionInfoArray($request->address->region_id);
        $address = $this->regionInfoToString($region_info['region_info']) . ' ' . $request->address->street;
        $data = array(
            'name' => $request->address->name,
            'region_id' => $request->address->region_id,
            'region_info' => $region_info['region_info'],
            'street' => $request->address->street,
            'telephone' => $request->address->telephone,
            'address' => $address,
            'timestamp' => $this->getTime(),
        );
        if ($address_info) {
            $this->getUserAddressTable()->updateData($data, array('id' => $address_info['id']));
            $id = $address_info['id'];
        }
        else {
            $id = $this->getUserAddressTable()->insertData($data);
        }
        $response->id = $id;
        return $response;
    }
}