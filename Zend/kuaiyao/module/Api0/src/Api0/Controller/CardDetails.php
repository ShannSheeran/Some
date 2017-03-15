<?php
namespace Api0\Controller;

use Api0\Controller\Request\CardDetailsRequest;

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
            }
            elseif ($action == 2) {
                // 根据用户id查名片
                $carte = $this->getViewUserPageTable()->getOne(array(
                    'id' => $id
                ));
            }
        }
        else {
            // 什么都不传查自己
            $this->checkLogin();
            $uid = $this->getUserId();
            $carte = $this->getViewUserPageTable()->getOne(array(
                'id' => $uid
            ));
            //$carte['carte_name']=$carte['name'];
            // $carte = $this->getCarteTable()->getOne(array('id'=>$list['page_id']));
            
            $user = (object)array();
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
            $carte['page_id']=$carte['id'];
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
        $details = array(
            "id" => $carte['page_id'],
            "type" => 1,
            "name" => $carte['title'],
            "english"=>isset($carte['englist_name']) ? $carte['englist_name'] :"",
            "englishName" => $carte['englist_name'],
            "profile" =>$carte['profile'],
            "description" =>$carte['description'],
            "mobile" => $mobile,
            "imagePath" =>  (isset($ima['path']) ? $ima['path'] : "") . (isset($ima['filename']) ?$ima['filename'] : ""),
            'imageId'=>$carte['head_icon'],
            "signature" => $carte['signature'],
            "qq" => $qq,
            "weiboLink"=>$carte['weibo_link'],
            "email" => $email,
            "telephone" => $telephone,
            "wechat" => $carte['weixin_number'],
            "companyName" => $carte['company'],
            "job" => $carte['position'],
            "show"=>$carte['show'],
            'isDevice' =>isset($isDevice) ? $isDevice : 0,
            'status' =>$carte['status'],
            "address" => array(
                "street" => $carte['address']
            ),
            
            "user" => $user
        );
        
        $response->card = $details;
        return $response;
    }
}