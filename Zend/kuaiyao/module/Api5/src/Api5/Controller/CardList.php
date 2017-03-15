<?php
namespace Api5\Controller;

use Api5\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api5\Controller\Common\WhereRequest;
use Zend\Validator\InArray;

/**
 * 名片列表
 */
class CardList extends CommonController
{

    public function __construct()
    {
        $this->myWhereRequest = new CardListWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $action = $request->action;
        $action_array = array(
            1,
            2,
            3,
            4,
            5,
            6,
            7,
            8
        );
        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        $table_where = $this->getTableWhere();
        $where = new where();
        if ($table_where->search_key) {
            $sub_where = new Where();
            $sub_where->like('carte_name', '%' . $table_where->search_key . '%')->or->like('mobile', '%' . $table_where->search_key . '%');
            $where->addPredicate($sub_where);
        }
        if (isset($action) && $action == 1) {
            $where->equalTo('user_id', $this->getUserId());
            $where->equalTo('delete', 0);
            $where->equalTo('preview', 0);
            $data = $this->getAll($this->getViewMyPageTable(), $where);
            $list = array();
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                    $mobileData = $this->getUserTable()->getOne(array(
                        'id' => $val['user_id']
                    ));
                    $vip_data = $this->getDeviceTable()->getOne(array(
                        'user_id' => $mobileData['id']
                    ));
                    $deviceId = $this->getDeviceTable()->getOne(array(
                        'user_id' => $val['user_id']
                    ));
                    $array = $this->getUserRelation($this->getUserId());
                    $relationship = 0;
                    if (array_key_exists($val['user_id'], $array['deep1'])) {
                        $relationship = 1;
                    } elseif (array_key_exists($val['user_id'], $array['deep2'])) {
                        $relationship = 2;
                    }
                    if($vip_data){
                        $vip = 1;
                    }else{
                        $vip = 0;
                    }
                    $user = array(
                        'id' => $val['user_id'],
                        'mobile' => $mobileData['mobile'],
                        'vip'=>$vip,
                        'relationship' => $relationship,
                        'isBuy' => $mobileData['is_buy']
                    );
                    $page = $this->getPageTable()->getOne(array(
                        'id' => $val['id']
                    ));
                    $cardname = $this->getCarteTable()->getOne(array(
                        'id' => $page['carte_id']
                    ));
                    $company = $this->getCompanyTable()->getOne(array(
                        'id' => $val['company_id'],
                        'delete' => 0
                    ));
                   /*  if ($company['audit_status'] == 2) { */
                        $company_info['name'] = $company ? $company['name'] : "";
                    /* } */
                    $company_info['id'] = $company['id'] ? $company['id'] : '0';
                    $path = $this->getImageTable()->getOne(array(
                        'id' => $cardname['wx_code']
                    ));
                    $weixinImage = array(
                        'id' => $cardname['wx_code'],
                        'Path' => $path['path'] . $path['filename']
                    );
                    $mobile = ($val['mobile']) ? explode(',', $val['mobile']) : array();
                    $list[]['card'] = array(
                        'id' => $val['id'],
                        'cardName' => $cardname['carte_name'],
                        'name' => $val['name'],
                        'job' => $cardname['position'],
                        'timestampUpdate' => $val['timestamp_update'],
                        'company' => $company_info,
                        'statVisit' => $val['count'], // 9.8
                        'deviceId' => (int) $deviceId['device_id'],
                        'type' => 1,
                        'user' => $user,
                        'mobile' => $mobile,
                        'companyStatus' => $cardname['company_status'],
                        'imagePath' => $val['path'] . $val['filename'],
                        'wxImage' => $weixinImage
                    );
                }
            }
        }
        if (isset($action) && $action == 2) {
            $where->equalTo('user_id_1', $this->getUserId());
            $where->equalTo('attention', "3");
            
            $data = $this->getAll($this->getViewUserRelationTable(), $where);
            $list = array();
            foreach ($data['list'] as $val) {
                $pageData = $this->getViewPageCarteTable()->getOne(array(
                    'id' => $val['page_id']
                ));
                $cardname = $this->getCarteTable()->getOne(array(
                    'id' => $val['page_id']
                ));
                
                $is_buy = $this->getUserTable()->getOne(array(
                    'id' => $val['user_id_2']
                ));
                $vip_data = $this->getDeviceTable()->getOne(array(
                    'user_id' => $is_buy['id']
                ));
                $deviceId = $this->getDeviceTable()->getOne(array(
                    'user_id' => $is_buy['id']
                ));
                $path = $this->getImageTable()->getOne(array(
                    'id' => $cardname['wx_code']
                ));
                $company = $this->getCompanyTable()->getOne(array(
                    'id' => $pageData['companys_id'],
                    'delete' => 0
                ));
                $array = $this->getUserRelation($this->getUserId());
                $relationship = 0;
                if (array_key_exists($is_buy['id'], $array['deep1'])) {
                    $relationship = 1;
                } elseif (array_key_exists($is_buy['id'], $array['deep2'])) {
                    $relationship = 2;
                }
                
                $company_info['id'] = (int) $cardname['company_id'];
                if ($cardname['company_id'] == 0) {
                    $company_info['name'] = $cardname['company'] ?  $cardname['company'] :"";
                }
                
                if ($company['audit_status'] == 2) {
                    $company_info['name'] = $company['name'] ? $company['name'] : "";
                }
                if($vip_data){
                    $vip = 1;
                }else{
                    $vip = 0;
                }
                
                $weixinImage = array(
                    'id' => $cardname['wx_code'],
                    'Path' => $path['path'] . $path['filename']
                );
                $user = array(
                    'id' => $val['user_id_2'],
                    'isTop' => $val['top'],
                    'isShield' => $val['shield'],
                    'isDoNotRemind' => $val['disturb'],
                    'vip'=>$vip,
                    'relationship' => $relationship,
                    'mobile' => $val['mobile'],
                    'isBuy' => $is_buy['is_buy'] ? $is_buy['is_buy'] : "2"
                );
                $list[]['card'] = array(
                    'id' => $val['page_id'],
                    'name' => $pageData['name'],
                    'cardName' => $cardname['carte_name'],
                    'job' => $pageData['position'],
                    'statVisit' => $pageData['count'], // 9.8
                    'deviceId' => (int) $deviceId['device_id'],
                    'user' => $user,
                    'timestampUpdate' => $val['timestamp_update'],
                    'company' => $company_info,
                    'visiStat' => $val['count'],
                    'type' => 1,
                    'imagePath' => $val['path'] . $val['filename'],
                    'companyStatus' => $cardname['company_status'],
                    'wxImage' => $weixinImage
                );
            }
        }
        
        if (isset($action) && $action == 3) {
            $user_id = $this->getUserId();
            $where->equalTo('user_id_1', $this->getUserId());
            $where->notEqualTo('is_show', 2);
            $relation = $this->getAll($this->getViewUserOneRelationTable(), $where);
            $list = array();
            foreach ($relation['list'] as $vals) {
                $data = $this->getViewUserPageTable()->getAll(array(
                    'id' => $vals['user_id_2']
                ));
                foreach ($data['list'] as $v) {
                    $carte = $this->getCarteTable()->getOne(array(
                        'id' => $v['carte_id']
                    ));
                }
                $device = $this->getDeviceTable()->getOne(array(
                    'carte_ids' => $carte['id']
                ));
                
                $company = $this->getCompanyTable()->getOne(array(
                    'id' => $carte['company_id'],
                    'delete' => 0
                ));
                
                $is_buy = $this->getUserTable()->getOne(array(
                    'id' => $vals['user_id_2']
                ));
                $vip_data = $this->getDeviceTable()->getOne(array(
                    'user_id' => $is_buy['id']
                ));
                $path = $this->getImageTable()->getOne(array(
                    'id' => $carte['wx_code']
                ));
                $array = $this->getUserRelation($this->getUserId());
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
                if ($data) {
                    $user = array(
                        'id' => $vals['user_id_2'],
                        'mobile' => $is_buy['mobile'],
                        'attention' => $vals['attention'],
                        'content' => $vals['content'],
                        'vip'=>$vip,
                        'relationship' => $relationship,
                        'isBuy' => $is_buy['is_buy']
                    );
                    
                    $company_info['id'] = (int) $carte['company_id'];
                   /*  if ($carte['company_id'] == 0) {
                        $company_info['name'] = $carte['company'] ? $carte['company'] : "";
                    } */
                    
                    /* if ($company['audit_status'] == 2) { */
                    $company_info['name'] = $company['name'] ? $company['name'] : "";
                   /*  } */
                    
                    $weixinImage = array(
                        'id' => $carte['wx_code'],
                        'Path' => $path['path'] . $path['filename']
                    );
                
                    foreach ($data['list'] as $val) {
                        $cardname = $this->getCarteTable()->getOne(array(
                            'id' => $val['carte_id']
                        ));
                        $list[]['card'] = array(
                            'id' => $val['page_id'],
                            'name' => $val['name'] ? $val['name'] : $val['title'],
                            'cardName' => $val['carte_name'],
                            'job' => $cardname['position'],
                            'timestampUpdate' => $val['timestamp_update'],
                            'wxImage' => $weixinImage,
                            'companyStatus' => $carte['company_status'],
                            'company' => $company_info,
                            'statVisit' => $val['count'],
                            'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                            'type' => 1,
                            'imagePath' => $val['path'] . $val['filename'],
                            'user' => $user
                        );
                    }
                }
            }
        }
        if (isset($action) && $action == 4) {
           
            if (! $table_where->ids && ! $table_where->mobiles && ! $table_where->search_key && ! $table_where->type) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            if ($table_where->ids) {
                $where->in('id', $table_where->ids);
            }
            if ($table_where->mobiles) {
                $where->in('mobile', $table_where->mobiles);
            }
            if ($table_where->type) {
                $cate_user_id['id'] = $this->getUserId();
                $page_id = $this->getUserTable()->getOne($cate_user_id);
                $carte_data = $this->getViewPageCarteTable()->getOne(array('id'=>$page_id['page_id']));
                if ($table_where->type == 1) {
                        // 查找同城
                        if($carte_data->c_region_id == 0){
                            $carte_datas = $this->getCompanyTable()->getOne(array('id'=>$carte_data->company_id));
                            /* $region=$this->getRegionTable()->getOne(array('id'=>$carte_datas->region_id)); */
                            $city_info= json_decode($carte_datas->region_info,TRUE);
                            if(isset($city_info[1]) && $city_info[1]){
                                $v['id']=$city_info[1]['region']['id'];
                            }
                            $carte_data->c_region_id = $v['id'];
                            
                        } 
                        $region_a = new where();
                        $region_a->equalTo('c_region_id', $carte_data->c_region_id);
                        $region_a->equalTo('delete', 0);
                        $region_a->notEqualTo('user_id', 0);
                        $region_number = $this->getViewPageCarteTable()->fetchAll($region_a);
                        $array_fiy_1 = $this->quchu($region_number,$carte_data['c_id']);
                        $num_1 = array_unique($array_fiy_1);
                       
                        if(!$num_1){
                            $num_1 = array(
                                '0' => 0,
                            );
                        }
                        $where->in('carte_id', $num_1);
                } elseif ($table_where->type == 2) {
                       // 查找同行
                       if($carte_data->c_category_id == 0){
                           $carte_datas = $this->getCompanyTable()->getOne(array('id'=>$carte_data->company_id));
                           $carte_data->c_category_id = $carte_datas['category_id'];
                       }
                        $category_a = new where();
                        $category_a->equalTo('c_category_id', $carte_data->c_category_id);
                        $category_a->equalTo('delete', 0);
                        $category_a->notEqualTo('user_id', 0);
                        $category_number = $this->getViewPageCarteTable()->fetchAll($category_a);
                        $array_fiy_2 = $this->quchu($category_number,$carte_data['c_id']);
                        $num_2 = array_unique($array_fiy_2);
                        if(!$num_2){
                            $num_2 = array(
                                '0' => 0,
                            );
                        }
                        $where->in('carte_id', $num_2);                 
                } elseif ($table_where->type == 3) {
                        // 查找同事
                        $num_3 = '';
                        if($carte_data['card_status'] == 3){
                            $company_a = new where();
                            $company_a->equalTo('company_id', $carte_data->company_id);
                            $company_a->equalTo('card_status', 3);
                            $company_a->equalTo('delete', 0);
                            $company_a->notEqualTo('user_id', 0);
                            $company_number = $this->getViewPageCarteTable()->fetchAll($company_a);
                            $array_fiy_3 = $this->quchu($company_number,$carte_data['c_id']);
                            $num_3 = array_unique($array_fiy_3);
                        }

                        if(!$num_3){
                            $num_3 = array(
                                '0' => 0,
                            );
                        }
                        $where->in('carte_id', $num_3);
                } else {
                    return STATUS_INCORRECT_FORMAT;
                }
            }
            $like = null;
            /* $like=array('name'=>$table_where->search_key); */
            
            // 关键字搜索可能是这样写
             $data = $this->getAll($this->getViewUserPageTable(), $where,$like);
            
            /* $data = $this->getViewUserPageTable()->fetchAll($where); */
            if ($data) {
                $list = array();
                foreach ($data['list'] as $val) {
                    if (! $val['page_id'] == 0) {
                        $listData = $this->getUserRelationTable()->getOne(array(
                            'user_id_1' => $this->getUserId(),
                            'user_id_2' => $val['id']
                        ));
                        if (! $listData) {
                            $lists = $this->getUserRelationTable()->getOne(array(
                                'user_id_2' => $this->getUserId(),
                                'user_id_1' => $val['id']
                            ));
                            $attention = isset($lists['attention']) ? 2 : 0;
                        } else {
                            $attention = isset($listData['attention']) ? $listData['attention'] : 0;
                        }
                        
                        $is_buy = $this->getUserTable()->getOne(array(
                            'id' => $val['id']
                        ));
                        $vip_data = $this->getDeviceTable()->getOne(array(
                            'user_id' => $is_buy['id']
                        ));
                        $array = $this->getUserRelation($this->getUserId());
                        $relationship = 0;
                        if (array_key_exists($is_buy['id'], $array['deep1'])) {
                            $relationship = 1;
                        } elseif (array_key_exists($is_buy['id'], $array['deep2'])) {
                            $relationship = 2;
                        }
                        $carteMobile = ($val['carte_mobile']) ? explode(',', $val['carte_mobile']) : array();
                        $carte = $this->getCarteTable()->getOne(array(
                            'id' => $val['page_id']
                        ));
                        $device = $this->getDeviceTable()->getOne(array(
                            'carte_ids' => $carte['id']
                        ));
                        $path = $this->getImageTable()->getOne(array(
                            'id' => $carte['wx_code']
                        ));
                        $company = $this->getCompanyTable()->getOne(array(
                            'user_id' => $val['id'],
                            'delete' => 0
                        ));
                        $weixinImage = array(
                            'id' => $carte['wx_code'],
                            'Path' => $path['path'] . $path['filename']
                        );
                        
                       
                        
                      /*   if ($carte['company_id'] == 0) {
                            $company_info['name'] = $val['company'] ? $val['company'] : "";
                        }
                         */
                      /*   if ($company['audit_status'] == 2) { */
                        $category_number = $this->getViewPageCarteTable()->getOne(array('c_id' =>$val['carte_id']));
                        $company_info['id'] = (int) $category_number['company_id'];
                        $company_info['name'] = $category_number['company'] ? $category_number['company'] : "";
                       /*  } */
                        if($vip_data){
                            $vip = 1;
                        }else{
                            $vip = 0;
                        }
                        $user = array(
                            'id' => $val['id'],
                            'mobile' => $val['mobile'],
                            'isBuy' => $is_buy['is_buy'],
                            'vip'=>$vip,
                            'relationship' => $relationship,
                            'attention' => $attention
                        );
                        $list[]['card'] = array(
                            'id' => $val['page_id'],
                            'user' => $user,
                            'name' => $val['name'] ? $val['carte_name'] : $val['title'],
                            'cardName' => $carte['carte_name'],
                            'job' => $val['position'],
                            'englishName' => $val['englist_name'],
                            'mobile' => $carteMobile,
                            'timestampUpdate' => $val['timestamp_update'],
                            'statVisit' => $val['count'],
                            'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                            'wxImage' => $weixinImage,
                            'companyStatus' => $carte['company_status'],
                            'company' => $company_info,
                            'type' => 1,
                            'imagePath' => $val['path'] . $val['filename']
                        );
                    }
                }
            }
        }
        
        if (isset($action) && $action == 5) {
            $where->equalTo('user_id', $this->getUserId());
            $data = $this->getAll($this->getViewPageViewTable(), $where);
            $list = array();
            
            foreach ($data['list'] as $val) {
                $is_buy = $this->getUserTable()->getOne(array(
                    'id' => $val['id']
                ));
                $carte = $this->getCarteTable()->getOne(array(
                    'id' => $val['carte_id']
                ));
                $device = $this->getDeviceTable()->getOne(array(
                    'carte_ids' => $carte['id']
                ));
                $vip_data = $this->getDeviceTable()->getOne(array(
                    'user_id' => $is_buy['id']
                ));
                if($vip_data){
                    $vip = 1;
                }else{
                    $vip = 0;
                }
                $path = $this->getImageTable()->getOne(array(
                    'id' => $carte['wx_code']
                ));
                $company = $this->getCompanyTable()->getOne(array(
                    'user_id' => $val['user_id'],
                    'delete' => 0
                ));
                
                // $wximage = $this->getImageTable()->getOne(array('id'=>$val['']));
                $weixinImage = array(
                    'id' => $carte['wx_code'],
                    'Path' => $path['path'] . $path['filename']
                );
                
                $company_info['id'] = (int) $carte['company_id'];
                /* if ($carte['company_id'] == 0) {
                    $company_info['name'] = $carte['company'] ? $carte['company'] :  "";
                } */
                
                /* if ($company['audit_status'] == 2) { */
                $company_info['name'] = $company['name'] ? $company['name'] : "";
                /* } */
                
                $array = $this->getUserRelation($this->getUserId());
                $relationship = 0;
                if (array_key_exists($is_buy['id'], $array['deep1'])) {
                    $relationship = 1;
                } elseif (array_key_exists($is_buy['id'], $array['deep2'])) {
                    $relationship = 2;
                }
                $user = array(
                    'id' => $val['id'],
                    'isBuy' => $is_buy['is_buy'],
                    'mobile' => $is_buy['mobile'],
                    'vip'=>$vip,
                    'relationship' => $relationship
                );
                
                $list[]['card'] = array(
                    'id' => $val['page_id'],
                    'name' => $carte['name'],
                    'companyName' => $carte['company'],
                    'cardName' => $carte['carte_name'],
                    'job' => $carte['position'],
                    'timestamp' => $val['timestamp'],
                    'timestampUpdate' => $val['timestamp_update'],
                    'statVisit' => $val['count'],
                    'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                    'type' => 1,
                    'wxImage' => $weixinImage,
                    'companyStatus' => $carte['company_status'],
                    'company' => $company_info,
                    'imagePath' => $val['path'] . $val['filename'],
                    'user' => $user
                );
            }
        }
        
        if (isset($action) && $action == 6) {
            $user_id = $this->getUserId();
            $array = $this->getUserRelation($user_id);
            $data = array(
                'total' => count($array['deep2'])
            );
            $query_table = $this->getTable();
            $page = $query_table->page;
            $limit = $query_table->limit;
            $user_ids = array();
            $step = 0;
            foreach ($array['deep2'] as $key => $value) {
                if ($step >= ($page - 1) * $limit && $step < $page * $limit) {
                    $user_ids[] = $key;
                }
                $step ++;
            }
            $list = array();
            foreach ($user_ids as $v) {
                $pageData = $this->getViewUserPageTable()->getOne(array(
                    'id' => $v
                ));
                $carte = $this->getCarteTable()->getOne(array(
                    'id' => $pageData['carte_id']
                ));
                $vip_data = $this->getDeviceTable()->getOne(array(
                    'user_id' => $pageData['carte_id']
                ));
                $device = $this->getDeviceTable()->getOne(array(
                    'carte_ids' => $carte['id']
                ));
                $company = $this->getCompanyTable()->getOne(array(
                    'id' => $carte['company_id'],
                    'delete' => 0
                ));
                $path = $this->getImageTable()->getOne(array(
                    'id' => $carte['wx_code']
                ));
                $relationship = 0;
                if (array_key_exists($pageData['id'], $array['deep1'])) {
                    $relationship = 1;
                    $mutualFriend = $array['deep1'][$pageData['id']];
                } elseif (array_key_exists($pageData['id'], $array['deep2'])) {
                    $relationship = 2;
                    $mutualFriend = $array['deep2'][$pageData['id']];
                }
                $company_info['id'] = (int) $carte['company_id'];
               /*  if ($carte['company_id'] == 0) {
                    $company_info['name'] = $carte['company'] ? $carte['company'] : "";
                } */
                
               /*  if ($company['audit_status'] == 2) { */
                $company_info['name'] = $carte['company'] ? $carte['company'] : "";
              /*   } */
                
                $weixinImage = array(
                    'id' => $carte['wx_code'],
                    'Path' => $path['path'] . $path['filename']
                );
                $company = array(
                    'id' => $company['id'],
                    'name' => $company['name']
                );
                $mobileData = $this->getUserTable()->getOne(array(
                    'id' => $pageData['id']
                ));
                $vip_data = $this->getDeviceTable()->getOne(array(
                    'user_id' => $mobileData['id']
                ));
                if($vip_data){
                    $vip = 1;
                }else{
                    $vip = 0;
                }
                $user = array(
                    'id' => $mobileData['id'],
                    'mobile' => $pageData['mobile'],
                    'isBuy' => $mobileData['is_buy'],
                    'vip' => $vip,
                    'relationship' => $relationship,
                    'mutualFriend' => $mutualFriend
                );
                $list[]['card'] = array(
                    "id" => $pageData['page_id'],
                    'cardName' => $carte['carte_name'],
                    'type' => 1,
                    'name' => $carte['name'],
                    'job' => $carte['position'],
                    'timestamp' => $pageData['timestamp'],
                    'timestampUpdate' => $pageData['timestamp_update'],
                    'statVisit' => $pageData['count'],
                    'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                    'imagePath' => $pageData['path'] . $pageData['filename'],
                    'wxImage' => $weixinImage,
                    'companyStatus' => $carte['company_status'],
                    'company' => $company_info,
                    'user' => $user
                );
            }
        }
        
        if (isset($action) && $action == 7) {
            $type = $request->table->where->type;
            $company_id = $request->table->where->companyId;
            $array_type = array(
                1,
                2
            );
            if (! in_array($type, $array_type)) {
                return STATUS_UNKNOWN;
            }
            
            if (! $company_id) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            if ($type == 1) {
                $where->equalTo('company_id', $company_id);
                $where->equalTo('company_status', 3);
                $where->equalTo('delete', 0);
                $data = $this->getAll($this->getCarteTable(), $where);
                /*$data = $this->getCarteTable()->fetchAll($where);*/
                $list = array();
                if ($data['list']) {
                    foreach ($data['list'] as $val) {
                        
                        $page_id = $this->getPageTable()->getOne(array('carte_id'=>$val['id']));
                        $image_Path = $this->getImageTable()->getOne(array('id'=>$val['head_icon']));
                        $mobileData = $this->getUserTable()->getOne(array(
                            'id' => $page_id['user_id']
                        ));
                        
                        $array = $this->getUserRelation($this->getUserId());
                        $relationship = 0;
                        if (array_key_exists($mobileData['id'], $array['deep1'])) {
                            $relationship = 1;
                        } elseif (array_key_exists($mobileData['id'], $array['deep2'])) {
                            $relationship = 2;
                        }
                        $vip_data = $this->getDeviceTable()->getOne(array(
                            'user_id' => $page_id['user_id']
                        ));
                        if($vip_data){
                            $vip = 1;
                        }else{
                            $vip = 0;
                        }
                        $user = array(
                            'id' => (int)$page_id['user_id'],
                            'mobile' => $mobileData['mobile'],
                            'isBuy' => (int)$mobileData['is_buy'],
                            'vip' => (int)$vip,
                            'relationship' => (int)$relationship
                        );
                      /*   $page = $this->getPageTable()->getOne(array(
                            'id' => $val['id']
                        )); 
                        $cardname = $this->getCarteTable()->getOne(array(
                            'id' => $page_id['carte_id']
                        ));*/
                        $company = $this->getCompanyTable()->getOne(array(
                            'id' => $val['company_id'],
                            'delete' => 0
                        ));
                        $device = $this->getDeviceTable()->getOne(array(
                            'carte_ids' => $val['id']
                        ));
                       /*  if ($company['audit_status'] == 2) { */
                            $company_info['name'] = $company ? $company['name'] : $val['company'];
                       /*  } */
                        $company_info['id'] = $company['id'] ? (int)$company['id'] : '0';
                        $path = $this->getImagePath($val['wx_code']);
                        
                       /*  $weixinImage = array(
                            'id' => $val['wx_code'],
                            'Path' => $path
                        ); */
                        /* var_dump($path);exit; */
                        $mobile = ($val['mobile']) ? explode(',', $val['mobile']) : array();
                        $list[]['card'] = array(
                            'id' => (int)$page_id['id'],
                            'cardName' => $val['carte_name'],
                            'name' => $val['name'],
                            'job' => $val['position'],
                            'isDevice' => $val['status'] ? (int)1 : (int)0,
                            'timestampUpdate' => $val['timestamp_update'],
                            'company' => $company_info,
                            'statVisit' => (int)$page_id['count'], // 9.8
                            'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                            'type' => 1,
                            'user' => $user,
                            'mobile' => $mobile,
                            'imagePath' => $image_Path['path'] . $image_Path['filename'],
                            'wxImage' => $path,
                            'companyStatus' => (int)$val['company_status']
                        );
                    }
                }
            } else {
                $where->equalTo('company_id', $company_id);
                $where->equalTo('delete', 0);
                /*
                 * $where->equalTo('company_status',
                 * 2);
                 */
                $data = $this->getAll($this->getCarteTable(), $where);
                /*$data = $this->getCarteTable()->fetchAll($where);*/
                $list = array();
                if ($data['list']) {
                    foreach ($data['list'] as $val) {
                        $page_id = $this->getPageTable()->getOne(array('carte_id'=>$val['id']));
                        $image_Path = $this->getImageTable()->getOne(array('id'=>$val['head_icon']));
                        $mobileData = $this->getUserTable()->getOne(array(
                            'id' => $page_id['user_id']
                        ));
                        $array = $this->getUserRelation($this->getUserId());
                        $relationship = 0;
                        if (array_key_exists($mobileData['id'], $array['deep1'])) {
                            $relationship = 1;
                        } elseif (array_key_exists($mobileData['id'], $array['deep2'])) {
                            $relationship = 2;
                        }
                        $vip_data = $this->getDeviceTable()->getOne(array(
                            'user_id' => $page_id['user_id']
                        ));
                        if($vip_data){
                            $vip = 1;
                        }else{
                            $vip = 0;
                        }
                        $user = array(
                            'id' => (int)$page_id['user_id'],
                            'mobile' => $mobileData['mobile'],
                            'isBuy' => (int)$mobileData['is_buy'],
                            'vip' => (int)$vip,
                            'relationship' => (int)$relationship
                        );
                       /*  $page = $this->getPageTable()->getOne(array(
                            'id' => $val['id']
                        )); 
                        $cardname = $this->getCarteTable()->getOne(array(
                            'id' => $page['carte_id']
                        ));*/
                        $company = $this->getCompanyTable()->getOne(array(
                            'id' => $val['company_id'],
                            'delete' => 0
                        ));
                        $device = $this->getDeviceTable()->getOne(array(
                            'carte_ids' => $val['id']
                        ));
                        
                       /*  if ($company['audit_status'] == 2) { */
                            $company_info['name'] = $company ? $company['name'] : $val['company'];
                      /*   } */
                        $company_info['id'] = $company['id'] ? (int)$company['id'] : 0;
                        
                        $path = $this->getImagePath($val['wx_code']);
                        
                       /*  $weixinImage = array(
                            'id' => $val['wx_code'],
                            'Path' => $path
                        ); */
                        
                        $mobile = ($val['mobile']) ? explode(',', $val['mobile']) : array();
                        $list[]['card'] = array(
                            'id' => (int)$page_id['id'],
                            'cardName' => $val['carte_name'],
                            'name' => $val['name'],
                            'job' => $val['position'],
                            'isDevice' => $val['status'] ? 1 : 0,
                            'timestampUpdate' => $val['timestamp_update'],
                            'company' => $company_info,
                            'statVisit' => (int)$page_id['count'], // 9.8
                            'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                            'type' => (int)1,
                            'user' => $user,
                            'mobile' => $mobile,
                            'imagePath' => $image_Path['path'] . $image_Path['filename'],
                            'wxImage' => $path,
                            'companyStatus' => (int)$val['company_status']
                        );
                    }
                }
            }
        }
        if (isset($action) && $action == 8) {
            if (! $request->table->where->userId) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            // 取出自身的ID数据
            $userid_1 = $this->getUserId();
            $user_id_1 = $this->getUserRelationTable()->fetchAll(array(
                'user_id_1' => $userid_1,
                'attention' => 3
            ));
            $user_id_1_2 = array();
            foreach ($user_id_1 as $val) {
                $user_id_1_2[] = $val['user_id_2'];
            }
            
            // 取出用户ID数据
            $userid_2 = $request->table->where->userId;
            $user_id_2 = $this->getUserRelationTable()->fetchAll(array(
                'user_id_1' => $userid_2,
                'attention' => 3
            ));
            $user_id_2_2 = array();
            foreach ($user_id_2 as $val) {
                $user_id_2_2[] = $val['user_id_2'];
            }
            $data = array_intersect($user_id_1_2, $user_id_2_2);
            $list = array();
            foreach ($data as $v) {
                $pageData = $this->getViewUserPageTable()->getOne(array(
                    'id' => $v
                ));
                $carte = $this->getCarteTable()->getOne(array(
                    'id' => $pageData['carte_id']
                ));
                $device = $this->getDeviceTable()->getOne(array(
                    'carte_ids' => $carte['id']
                ));
                $company = $this->getCompanyTable()->getOne(array(
                    'id' => $carte['company_id'],
                    'delete' => 0
                ));
                $path = $this->getImageTable()->getOne(array(
                    'id' => $carte['wx_code']
                ));
                $array = $this->getUserRelation($this->getUserId());
                
                $relationship = 0;
                if (array_key_exists($v, $array['deep1'])) {
                    $relationship = 1;
                } elseif (array_key_exists($v, $array['deep2'])) {
                    $relationship = 2;
                }
                
                $company_info['id'] = (int) $carte['company_id'];
               /*  if ($carte['company_id'] == 0) {
                    $company_info['name'] = $carte['company'] ?$carte['company'] : "";
                } */
                
               /*  if ($company['audit_status'] == 2) { */
                $company_info['name'] = $company['name'] ? $company['name'] : "";
               /*  } */
                
                $weixinImage = array(
                    'id' => $carte['wx_code'],
                    'Path' => $path['path'] . $path['filename']
                );
                
                $company = array(
                    'id' => $company['id'],
                    'name' => $company['name']
                );
                
                $mobileData = $this->getUserTable()->getOne(array(
                    'id' => $pageData['id']
                ));
                $vip_data = $this->getDeviceTable()->getOne(array(
                    'user_id' => $mobileData['id']
                ));
                if($vip_data){
                    $vip = 1;
                }else{
                    $vip = 0;
                }
                $user = array(
                    'id' => $mobileData['id'],
                    'mobile' => $pageData['mobile'],
                    'isBuy' => $mobileData['is_buy'],
                    'vip' => $vip,
                    'relationship' => $relationship
                );
                $list[]['card'] = array(
                    "id" => $pageData['page_id'],
                    'cardName' => $carte['carte_name'],
                    'type' => 1,
                    'name' => $carte['name'],
                    'job' => $carte['position'],
                    'timestamp' => $pageData['timestamp'],
                    'timestampUpdate' => $pageData['timestamp_update'],
                    'statVisit' => $pageData['count'],
                    'deviceId' => (int) $device['device_id'] ? (int) $device['device_id'] : 0,
                    'imagePath' => $pageData['path'] . $pageData['filename'],
                    'wxImage' => $weixinImage,
                    'companyStatus' => $carte['company_status'],
                    'company' => $company_info,
                    'user' => $user
                );
            }
        }
        $response->cards = $list;
        $response->total = isset($data['total']) ? $data['total'] : 0;
        return $response;
    }
    public function quchu($number,$page_id)
    {
        $array = array();
        foreach ($number as $val) {
                $array[] = (int) $val['c_id'];
        }
        $array_me[] = $page_id;
        return  $array_fiy = array_diff($array,$array_me);

    }
}






