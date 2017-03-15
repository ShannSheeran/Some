<?php
namespace Api5\Controller;

use Api5\Controller\Request\CardDetailsRequest;
use Zend\Form\Annotation\Object;

/**
 * 名片详情
 *
 * @author
 *
 */
class CardDetails extends CommonController
{

    function __construct()
    {
        $this->myRequest = new CardDetailsRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest(); // 获取请求参数
        $response = $this->getAiiResponse(); // 初始化返回参数
        $where = array();
        $id = (int) $request->id;
        $action = (int) $request->action;
        $action = $action ? $action : 1;
        
        if ($request->uuid && $request->major && $request->minor) {
            // 如果有设备信息
            $where['uuid'] = $request->uuid;
            $where['major'] = $request->major;
            $where['minor'] = $request->minor;
            $data = $this->getDeviceTable()->getOne($where);
           
            $pageid = array();
            if ($data['page_ids']) {
                $ids = $data['page_ids'];
                $ids = trim($ids, ',');
                $ids = explode(',', $ids);
                $one = mt_rand(0, count($ids) - 1);
                $pageid['id'] = $ids[$one]; // $data['page_ids'];
                
                $page = $this->getPageTable()->getOne(array(
                    'page_id' => $pageid['id']
                ));
                if ($page) {
                    $view = array(
                        'page_id' => $page['id'],
                        'user_id' => $this->getUserId()
                    );
                    
                    // 查找摇一摇的历史数据
                    $list_page = $this->getPageViewRecordTable()->getOne($view);
                    $this->getPageTable()->updateKey($page['id'], 1, 'count', 1);
                    if (! $list_page) {
                        $view['timestamp'] = $this->getTime();
                        $this->getPageViewRecordTable()->insertData($view);
                    } else {
                        $this->getPageViewRecordTable()->updateData(array(
                            'timestamp' => $this->getTime()
                        ), array(
                            'id' => $list_page['id']
                        ));
                    }
                    if ($page && $page['carte_id']) {
                        $carte = $this->getViewPageCarteTable()->getOne(array(
                            'c_id' => $page['carte_id']
                        ));
                        $page_id = $carte ? $carte['id'] : 0;
                        $pageList = $this->getPageTable()->getOne(array(
                            'id' => $carte['id']
                        ));
                        $user = array(
                            'id' => $pageList['user_id']
                        );
                        
                    } else {
                        return STATUS_NODATA;
                    }
                } else {
                    return STATUS_NODATA;
                }
            } else {
                return STATUS_NODATA;
            }
        } elseif ($id) {
            // 传了id
            if ($action == 1) {
                // 根据名片id查名片
                $carte = $this->getViewPageCarteTable()->getOne(array(
                    'id' => $id
                ));
                $page_id = $carte ? $carte['id'] : 0;
            } elseif ($action == 2) {
                // 根据用户id查名片
                $carte = $this->getViewUserPageTable()->getOne(array(
                    'id' => $id
                ));
                $page_id = $carte ? $carte['page_id'] : 0;
            }
        } else {
            // 什么都不传查自己
            $this->checkLogin();
            $uid = $this->getUserId();
            $carte = $this->getViewUserPageTable()->getOne(array(
                'id' => $uid
            ));
            $user_info = $this->getUserTable()->getOne(array(
                'id' => $uid
            ));
            $carte['user_name'] = $user_info['name'];
            $page_id = $carte ? $carte['page_id'] : 0;
            
            $user_id = $this->getUserId();
            
            $array = $this->getUserRelation($user_id);
            $relationship = 0;
            if (array_key_exists($user_id, $array['deep1'])) {
                $relationship = 1;
            } elseif (array_key_exists($user_id, $array['deep2'])) {
                $relationship = 2;
            }
            $vip_data = $this->getDeviceTable()->getOne(array('user_id'=>$uid));
            if($vip_data){
                $vip = 1;
            }else{
                $vip = 0;
            }
            $user = array(
                'id' => $this->getUserId(),
                'isBuy' => $user_info['is_buy'] ? (int) $user_info['is_buy'] : 2,
                'attention' => 0,
                'vip' => $vip,
                'relationship' => $relationship
            );
        }
        if (! $carte) {
            return STATUS_NODATA;
        }
        if ($id || ($request->uuid && $request->major && $request->minor)) {
            $uid = $this->getUserId();
            $user_id_2 = ($action == 2 ? $carte['id'] : $carte['user_id']);
            $relation = $this->getUserRelationTable()->getOne(array(
                'user_id_1' => $uid,
                'user_id_2' => $user_id_2
            ));
            if ($relation) {
                $is_buy = $this->getUserTable()->getOne(array(
                    'page_id' => $user_id_2
                ));
                $vip_data =  $this->getDeviceTable()->getOne(array(
                    'user_id' => $is_buy['id']
                ));
                $array = $this->getUserRelation($uid);
                $relationship = 0;
                if (array_key_exists($user_id_2, $array['deep1'])) {
                    $relationship = 1;
                } elseif (array_key_exists($user_id_2, $array['deep2'])) {
                    $relationship = 2;
                }
               
                if($vip_data){
                    $vip = 1;
                }else{
                    $vip = 0;
                }
                $user = array(
                    "id" => $user_id_2,
                    'attention' => $relation['attention'] ? (int) $relation['attention'] : 0,
                    'isBuy' => $is_buy['is_buy'] ? (int) $is_buy['is_buy'] : 2,
                    'vip'=>$vip,
                    'relationship' => $relationship
                );
            } else {
                $relationTow = $this->getUserRelationTable()->getOne(array(
                    'user_id_1' => $user_id_2,
                    'user_id_2' => $uid
                ));
                $is_buy = $this->getUserTable()->getOne(array(
                    'id' => $user_id_2
                ));
                $vip_data = $this->getDeviceTable()->getOne(array('user_id'=>$is_buy['id']));
                $array = $this->getUserRelation($uid);
                $relationship = 0;
                if (array_key_exists($is_buy['id'], $array['deep1'])) {
                    $relationship = 1;
                } elseif (array_key_exists($is_buy['id'], $array['deep2'])) {
                    $relationship = 2;
                }
                if($vip_data){
                    $vip = 1;
                }else{
                    $vip = 0;
                }
                $user = array(
                    "id" => $user_id_2,
                    'isBuy' => $is_buy['is_buy'] ? (int) $is_buy['is_buy'] : 2,
                    'vip'=>$vip,
                    'attention' => $relationTow['attention'] ? (int) $relationTow['attention'] : 0,
                    'relationship' => $relationship
                );
            }
            ;
            
            // $carte['page_id']=$carte['id'];
            // $carte['carte_name']=$carte['name'];
        }
        
        if ($carte['head_icon']) {
            $ima = $this->getImageTable()->getOne(array(
                'id' => $carte['head_icon']
            ));
        }
        
        $qq = ($carte['qq']) ? explode(',', $carte['qq']) : array();
        $email = ($carte['email']) ? explode(',', $carte['email']) : array();
        $telephone = ($carte['telephone']) ? explode(',', $carte['telephone']) : array();
        $mobile = ($carte['mobile']) ? explode(',', $carte['mobile']) : array();
        $cartenames = $this->getCarteTable()->getOne(array(
            'id' => isset($carte['c_id']) ? $carte['c_id'] : $carte['carte_id']
        )); // 111111
        
        $region_info_arr = $this->decode($cartenames['region_info']);
        if($region_info_arr[1]['region']['id']){
           $this->getCarteTable()->updateData(array('region_id' => $region_info_arr[1]['region']['id']),array(
                'id' => isset($carte['c_id']) ? $carte['c_id'] : $carte['carte_id']
            ));
        }
        
        $cartename = $this->getCarteTable()->getOne(array(
            'id' => isset($carte['c_id']) ? $carte['c_id'] : $carte['carte_id']
        )); // 111111
        
        $company_info = $this->getCompanyTable()->getOne(array(
            'id' => $cartename['company_id'],
            'delete' => 0
        ));
        
        $com_logo = $this->getImageTable()->getOne(array(
            'id' => $company_info['image']
        ));
        
        $companystatus = $cartename['company_status'] ? (int) $cartename['company_status'] : 0;
        $user_id = array(
            'id' => (int) $company_info['user_id']
        );
        
        $company['id'] = $cartename['company_id'] ? $cartename['company_id'] : 0;
        /* if ($cartename['company_id'] == 0) {
            $company['name'] = $cartename['company'];
            $company['imagePath'] = $com_logo['path'] . $com_logo['filename'];
            $company['user'] = $user_id;
            $company['address'] = array(
                'regionId' => $company_info['region_id'] ? (int) $company_info['region_id'] : 0,
                'regionInfo' => (array) json_decode($company_info['region_info']),
                'street' => $company_info['street'],
                'latitude' => $company_info['latitude'] ? (int) $company_info['latitude'] : 0,
                'longitude' => $company_info['longitude'] ? (int) $company_info['longitude'] : 0
            );
        }
         */
        /* if ($company_info['audit_status'] == 2) { */
            $company['name'] = $company_info['name'];
            $company['imagePath'] = $com_logo['path'] . $com_logo['filename'];
            $company['user'] = $user_id;
            $company['address'] = array(
                'regionId' => $company_info['region_id'] ? (int) $company_info['region_id'] : 0,
                'regionInfo' => (array) json_decode($company_info['region_info']),
                'street' => $company_info['street'],
                'latitude' => $company_info['latitude'] ? (int) $company_info['latitude'] : 0,
                'longitude' => $company_info['longitude'] ? (int) $company_info['longitude'] : 0
            );
       /*  } */

        if (! $id && ! ($request->uuid && $request->major && $request->minor)) {
            $company['name'] = $company_info['name'];
            $company['imagePath'] = $com_logo['path'] . $com_logo['filename'];
            $company['user'] = $user_id;
            $company['address'] = array(
                'regionId' => $company_info['region_id'] ? (int) $company_info['region_id'] : 0,
                'regionInfo' => (array) json_decode($company_info['region_info']),
                'street' => $company_info['street'],
                'latitude' => $company_info['latitude'] ? (int) $company_info['latitude'] : 0,
                'longitude' => $company_info['longitude'] ? (int) $company_info['longitude'] : 0
            );
        }
        
        if ($cartename['bg_image'] != 0) {
            $image_data = $this->getImageTable()->getOne(array(
                'id' => $cartename['bg_image']
            ));
            $path = $image_data['path'] . $image_data['filename'];
        } else {
            $path = "";
        }
        
        $style = array(
            'id' => $cartename['style_id'] ? (int) $cartename['style_id'] : 0,
            'image' => array(
                'id' => $cartename['bg_image'] ? (int) $cartename['bg_image'] : 0,
                'path' => $path
            )
        );
        $weixinImagePath = '';
        if ($cartename['wx_code']) {
            $weixinImagePath = $this->getImageTable()->getOne(array(
                'id' => $cartename['wx_code']
            ));
            $weixinImagePath = $weixinImagePath ? ($weixinImagePath['path'] . $weixinImagePath['filename']) : '';
        }
        
        $image = array(
            'id' => $carte['head_icon'],
            "path" => (isset($ima['path']) ? $ima['path'] : "") . (isset($ima['filename']) ? $ima['filename'] : "")
        );
        
        $weixinImage = array(
            'id' => $cartename['wx_code'],
            'path' => $weixinImagePath
        );
        
        $device = $this->getDeviceTable()->getOne(array(
            'id' => $carte['id']
        ));
        if (! $device) {
            $device_id = 0;
            $isDevice = 0;
        } else {
            if ($device['user_id']) {
                $isDevice = 1;
            } else {
                $isDevice = 2;
            }
            $device_id = (int) $device->device_id;
        }
       
        $details = array(
            "id" => $page_id,
            'cardName' => $cartename['carte_name'],
            "type" => 1,
            "name" => $cartename['name'],
            "englishName" => $carte['englist_name'],
            "mobile" => $mobile,
            "signature" => $carte['signature'],
            'image' => $image,
            'imagePath' => $image['path'],
            "qq" => $qq,
            "email" => $email,
            'wxImage' => $weixinImage,
            "job" => $cartename['position'],
            'deviceId' => $device_id,
            'companyStatus' => $companystatus,
            "user" => $user,
            'status' => $carte['status'],
            'statVisit' => $carte['count'],
            'company' => (array) $company,
            'style' => $style,
            'address' => array(
                'regionId' => (int)$cartename['region_id'],
                'regionInfo' => (array) json_decode($cartename['region_info'])
            ),
            'categoryId' => (int) $cartename['category_id'],
            'timestampUpdate' => $cartename['timestamp_update']
        );

        $response->card = $details;
        return $response;
    }
}