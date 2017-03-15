<?php
namespace Api\Controller;

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
        if ($request->action == 1) {
            $id = $request->id;
            if (! $id) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $page = $this->getPageTable()->getOne(array(
                'id' => $id,
                'user_id' => $Where['id']
            ));
            if (!$page){
                return STATUS_NODATA;
            }
            $this->getUserTable()->updateData(array(
                "page_id" => $id,
                'name' => $page['title']
            ), $Where);
            
            
 /* 
            $user_info = $this->getUserTable()->getOne(array(
                'user_id' => $Where['id'],
            ));
            
            $device_info = $this->getDeviceTable()->getOne(array(
                'user_id' => $
            ));
            
            $jsonData = json_encode(array(
                'device_identifier' => array(
                    'device_id' => (int) $device_info['device_id']
                ),
                'page_ids' => array(
                    (int) $page['page_id']
                ),
                'bind' => (int)1, // 0解除
                // 1关联
                'append' => 1
            )); // 0覆盖
                // 1新增
            
            $wxApi = new WxApi();
            $res = $wxApi->wxDeviceBindPage($jsonData);
            
            //print_r($_POST['carte_id']);die;
            
            if ($res['errcode'] == 0) {
                if ($device_info['carte_ids']) {
                    $carte_ids = explode(',', $device_info['carte_ids']);
                    if (! in_array($_POST['carte_id'], $carte_ids)) { // 如果没有绑定追加ID在后面
                        $carte_ids[] = $_POST['carte_id'];
                    } else {
                        if ($_POST['bind'] == 0) {
                            foreach ($carte_ids as $k => $v) {
                                if ($v == $_POST['carte_id']) {
                                    unset($carte_ids[$k]); // 解绑则删除对应的id
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $carte_ids = array(
                        $_POST['carte_id']
                    );
                }
                if ($device_info['page_ids']) {
            
                    $page_ids = explode(',', $device_info['page_ids']);
                    if (! in_array($page['page_id'], $page_ids)) { // 如果没有绑定追加ID在后面
                        $page_ids[] = $page['page_id'];
                    } else {
                        if ($_POST['bind'] == 0) {
                            foreach ($page_ids as $k => $v) {
                                if ($v == $page['page_id']) {
                                    unset($page_ids[$k]); // 解绑则删除对应的id
                                    break;
                                }
                            }
                        }
                    }
                } else {
                    $page_ids = array(
                        $page['page_id']
                    );
            
                }
            
                $this->getDeviceTable()->update(array(
                    'carte_ids' => implode(',', $carte_ids),
                    'page_ids' => implode(',', $page_ids),
                    'user_id' => $page['user_id']
                ), array(
                    'id' => $_POST['id']
                ));
                if ($_POST['bind'] == 0) {
                    $this->getCarteTable()->update(array(
                        'status' => 0
                    ), array(
                        'id' => $_POST['carte_id']
                    ));
                    $device = $this->getDeviceTable()->getOne(array('id'=>$_POST['id']));
                    if(!$device->page_id)
                    {//页面没有pageID则删除些用户绑定的设备
                        $this->getDeviceTable()->updateData(array('user_id'=>0), array('id'=>$_POST['id']));
                    }
                } else {
                    $this->getCarteTable()->update(array(
                        'status' => $device_info['device_id']
                    ), array(
                        'id' => $_POST['carte_id']
                    ));
                }
            } else {
                $this->showMessage("绑定设备失败!错误代码({$res['errcode']})");
            } 
            */
            
        }
        return STATUS_SUCCESS;
    }
}