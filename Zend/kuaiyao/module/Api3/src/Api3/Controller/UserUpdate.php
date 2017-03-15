<?php
namespace Api3\Controller;

use Core\System\WxApi\WxApi;
/**
 * 用户，更新个人信息
 *
 * @author
 *         WZ
 *        
 */
class UserUpdate extends User
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $Where = array();
        $Where['id'] = $this->getUserId();
        
        if ($request->action == 1) 
        {
            $id = $request->id;//新的
            if (! $id)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $page = $this->getPageTable()->getOne(array(
                'id' => $id,
                'user_id' => $Where['id']
            ));
            if (!$page)
            {
               return STATUS_NODATA;
            }
            $card = $this->getCarteTable()->getOne(array('id'=>$page['carte_id']));
            
            $user_info = $this->getUserTable()->getOne(array('id'=>$this->getUserId()));
            if($user_info->page_id)
            {
                $page_info = $this->getPageTable()->getOne(array('id'=>$user_info->page_id));
                if($page_info->carte_id)
                {
                    $carte_info = $this->getCarteTable()->getOne(array('id'=>$page_info->carte_id));
                    $device_id = $carte_info->status;
                    $device_info = $this->getDeviceTable()->getOne(array('device_id'=>$device_id));
                }
                else
                {
                    return STATUS_UNKNOWN;    
                }
                
            }
            else
             {
                return STATUS_UNKNOWN;    
            }
            if(!$device_id)
            {
                $card = $this->getCarteTable()->getOne(array('id'=>$page['carte_id']));  
                $this->getUserTable()->updateData(array(
                    "page_id" => $id,
                    'name' => $card['name']
                ), $Where);
            }
            else 
            {
                $jsonData = json_encode(array(
                    'device_identifier' => array(
                        'device_id' => (int) $device_id //设备id
                    ),
                    'page_ids' => array(
                        (int) $page['page_id']
                    ),
                    'bind' => (int)1, // 0解除
                    // 1关联
                    'append' => 0
                )); // 0覆盖
                    // 1新增
                
                $wxApi = new WxApi();
                $res = $wxApi->wxDeviceBindPage($jsonData);
                if ($res['errcode'] == 0)
                {
                    $this->getUserTable()->updateData(array(
                        "page_id" => $id,
                        'name' => $card['name'],
                    ), $Where);
                    if(isset($carte_info->id) && $carte_info->id)
                    {
                        $this->getCarteTable()->updateData(array('status'=>0), array('id'=>$carte_info->id));
                    }
                    $this->getPageTable()->updateData(array('page_id'=>$page['page_id']),array('id'=>$id));
                    $this->getDeviceTable()->updateData(array('page_ids'=>$page['page_id'],'carte_ids'=>$page['carte_id']), array('id'=>$device_info['id']));
                    $this->getCarteTable()->updateData(array('status'=>$device_id), array('id'=>$card['id']));
                     
                
                }
                else
                {
                    return STATUS_UNKNOWN;
                }
            }

           
            
        }
        return STATUS_SUCCESS;
    }
}