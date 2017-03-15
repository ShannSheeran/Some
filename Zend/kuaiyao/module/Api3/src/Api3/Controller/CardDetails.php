<?php
namespace Api3\Controller;

use Api3\Controller\Request\CardDetailsRequest;

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
        $id = (int)$request->id;
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
                $page = $this->getPageTable()->getOne(array('page_id'=>$pageid['id']));
                if ($page){
                $view =  array(
                    'page_id'=>$page['id'],
                    'user_id'=>$this->getUserId(),
                );
                
                //查找摇一摇的历史数据
                $list_page = $this->getPageViewRecordTable()->getOne($view);
                $this->getPageTable()->updateKey($page['id'], 1, 'count', 1);
                if(!$list_page){
                    $view['timestamp']=$this->getTime();   
                    $this->getPageViewRecordTable()->insertData($view);
                }
                else 
                {
                    $this->getPageViewRecordTable()->updateData(array('timestamp'=>$this->getTime()),array('id'=>$list_page['id']));
                }
                if ($page && $page['carte_id']) {
                    $carte = $this->getViewPageCarteTable()->getOne(array(
                        'c_id' => $page['carte_id']
                    ));
                  $page_id = $carte ? $carte['id'] : 0;
                  $pageList = $this->getPageTable()->getOne(array('id'=>$carte['id']));
                  $user=array(
                      'id'=>$pageList['user_id'],
                  );
                } else {
                    return STATUS_NODATA;
                }
                
            } else {
                return STATUS_NODATA;
            }
            }else {
                return STATUS_NODATA;
            }
        }
        elseif ($id) {
            // 传了id
            if ($action == 1) {
                // 根据名片id查名片
                $carte = $this->getViewPageCarteTable()->getOne(array(
                    'id' => $id
                ));
                
                $page_id = $carte ? $carte['id'] : 0;
            }
            elseif ($action == 2) {
                // 根据用户id查名片
                $carte = $this->getViewUserPageTable()->getOne(array(
                    'id' => $id
                ));
                $page_id = $carte ? $carte['page_id'] : 0;

            }
        }
        else {
            // 什么都不传查自己
            $this->checkLogin();
            $uid = $this->getUserId();
            $carte = $this->getViewUserPageTable()->getOne(array(
                'id' => $uid
            ));
            $user_info = $this->getUserTable()->getOne(array('id'=>$uid));
            
            $carte['user_name'] = $user_info['name'];
            
            $page_id = $carte ? $carte['page_id'] : 0;
            $user = array(
                'id' => $this->getUserId()
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
                $user = array(
                    "id" => $carte['user_id'],
                    'attention' => $relation['attention']
                );
            } else {
                $relationTow = $this->getUserRelationTable()->getOne(array(
                    'user_id_1' => $user_id_2,
                    'user_id_2' => $uid
                ));
                $user = array(
                    "id" => $user_id_2,
                    'attention' => $relationTow ? 2 : 0
                );
            };
            $isDevice=$carte['status'] == 0 ? 0 : 1;
           // $carte['page_id']=$carte['id'];
           // $carte['carte_name']=$carte['name'];
        }
   
        if ($carte['head_icon'])
        {
            $ima = $this->getImageTable()->getOne(array('id'=>$carte['head_icon']));
        }
        $qq = ($carte['qq']) ? explode(',', $carte['qq']) : array();
        $email = ($carte['email']) ? explode(',', $carte['email']) : array();
        $telephone = ($carte['telephone']) ? explode(',', $carte['telephone']) : array();
        $mobile = ($carte['mobile']) ? explode(',', $carte['mobile']) : array();
        
        $cartename = $this->getCarteTable()->getOne(array('id'=>isset($carte['c_id']) ? $carte['c_id'] : $carte['carte_id']));//111111
//         print_r($cartename);die();
        $company_info = $this->getCompanyTable()->getOne(array('id'=>$cartename['company_id'],'delete'=>0));
        $com_logo = $this->getImageTable()->getOne(array('id'=>$company_info['image']));

        $address = array(
            'regionId' => $company_info['region_id']?$company_info['region_id']:'0',
            'regionInfo' => (array)json_decode($company_info['region_info']),
            'street' => $company_info['street'],
            'latitude' => $company_info['latitude']?$company_info['latitude']:'0',
            'longitude' => $company_info['longitude']?$company_info['longitude']:'0',
        );
        
        $user_id = array('id'=>(int)$company_info['user_id']);
        $company = array(
            'id' => $company_info['id']?$company_info['id']:'0',
            'name' => $cartename['company'],
            'imagePath' => $com_logo['path'].$com_logo['filename'],
            'address' => (array)$address,
            'user' => (array)$user_id,
        );
        
        $weixinImagePath = '';
        if ($cartename['wx_code']) {
            $weixinImagePath = $this->getImageTable()->getOne(array('id'=>$cartename['wx_code']));
            $weixinImagePath = $weixinImagePath ? ($weixinImagePath['path'] . $weixinImagePath['filename']) : '';
        }

        $image = array(
            'id' => $carte['head_icon'],
            "path" =>  (isset($ima['path']) ? $ima['path'] : "") . (isset($ima['filename']) ?$ima['filename'] : ""),
            
        );
        
        $weixinImage = array(
            'id' => $cartename['wx_code'],
            'path' => $weixinImagePath, 
        );

            
        
        $details = array(
            "id" => $page_id,
            'cardName'=>$cartename['carte_name'],
            "type" => 1,
            "name" => $cartename['name'],
            "englishName" => $carte['englist_name'],
            "mobile" => $mobile,
            "signature" => $carte['signature'],
            'image' => $image,
            'imagePath'=> $image['path'],
            "qq" => $qq,
            "email" => $email,
            'wxImage' => $weixinImage,
            "job" => $carte['position'],
            'isDevice' =>isset($isDevice) ? $isDevice : 0,
            "user" => $user,
            'status' =>$carte['status'],
            'statVisit'=>$carte['count'],
            'company' => (array)$company,
            'timestampUpdate' => $cartename['timestamp_update'],
        );

        $response->card = $details;
        return $response;
    }
}