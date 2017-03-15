<?php
namespace Api5\Controller;

use Api5\Controller\Request\UserBinRequest;
use Api0\Controller\Common\Request;
use Core\System\WxApi\WxApi;

/**
 * 设备绑定
 */
class UserBindDevice extends User
{

    public function __construct()
    {
        $this->myRequest = new UserBinRequest();
        parent::__construct();
    }

    /**
     *
     * @return string
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        if (! $request->action) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $where = array(
            'uuid' => $request->device->uuid ? $request->device->uuid : "",
            'major' => $request->device->major ? $request->device->major : 0,
            'minor' => $request->device->minor ? $request->device->minor : 0
        );
        
        $device_data = $this->getDeviceTable()->getOne($where);
        
        if ($request->action == 1) {
            if (! $device_data) {
                $response->id = 0;
                return STATUS_NO_DEVICETOKEN;
            } else {
                
                if ($device_data->user_id == 0 && $device_data->page_ids == 0) {
                    $response->id = $device_data->id;
                } else {
                    $response->id = $device_data->id;
                    return STATUS_DEVICE_BOUND;
                }
            }
        }
        
        if ($request->action == 2) {
            if (! $device_data) {
                return STATUS_NO_DEVICETOKEN;
            }
            $Where = array();
            $Where['id'] = $this->getUserId();
            $ids = $this->getUserTable()->getOne(array('id'=>$this->getUserId()));
            $page_id = $this->getPageTable()->getOne(array('id'=>$ids['page_id']));
            $card = $this->getCarteTable()->getOne(array('id'=>$page_id['carte_id']));
            $jsonData = json_encode(array(
                'device_identifier' => array(
                    'device_id' => (int) $device_data['device_id'] //设备id
                ),
                'page_ids' => array(
                    (int) $page_id['page_id']
                ),
                'bind' => (int)1, // 0解除
                // 1关联
                'append' => 1
            )); // 0覆盖
                // 1新增
            
            $wxApi = new WxApi();
            $res = $wxApi->wxDeviceBindPage($jsonData);
            if ($res['errcode'] == 0)
            {
//                 $this->getUserTable()->updateData(array(
//                     "page_id" => $page_id['id'],
//                     'name' => $card['name'],
//                 ), $Where);
                if(isset($card->id) && $card->id)
                {
                    $this->getCarteTable()->updateData(array('status'=>0), array('id'=>$page_id['id']));
                }
//                 $this->getPageTable()->updateData(array('page_id'=>$page_id['page_id']),array('id'=>$page_id['id']));
                $this->getDeviceTable()->updateData(array('user_id' => $this->getUserId(), 'page_ids'=>$page_id['page_id'],'carte_ids'=>$page_id['carte_id']), array('id'=>$device_data['id']));
                $this->getCarteTable()->updateData(array('status'=>$device_data['device_id']), array('id'=>$card['id']));
            }
            else
            {
                return STATUS_UNKNOWN;
            }
//             if ($device_data->user_id == 0 && $device_data->page_ids == 0) {
//                 $data['user_id'] = $this->getUserId();
//                 $where = array(
//                     'id' => $device_data->id
//                 );
//                 $this->getDeviceTable()->update($data, $where);
//             } else {
//                 return STATUS_DEVICE_BOUND;
//             }
        }
        return STATUS_SUCCESS;
    }
}
