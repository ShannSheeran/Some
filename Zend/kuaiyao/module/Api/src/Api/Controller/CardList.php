<?php
namespace Api\Controller;

use Api\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api\Controller\Common\WhereRequest;

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
            5
        );
        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        $table_where = $this->getTableWhere();
        $where = new where();
        if ($table_where->search_key) {
            $sub_where = new Where();
            $sub_where->like('name', '%' . $table_where->search_key . '%')->or->like('mobile', '%' . $table_where->search_key . '%');
            $where->addPredicate($sub_where);
        }
        if (isset($action) && $action == 1) {
            $where->equalTo('user_id', $this->getUserId());
            $where->equalTo('delete',0);
            $where->equalTo('preview',0);
            $data = $this->getAll($this->getViewMyPageTable(), $where);
            
            $list = array();
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                    $mobileData = $this->getUserTable()->getOne(array(
                        'id' => $val['user_id']
                    ));
                    
                    $user = array(
                        'id' => $val['user_id'],
                        'mobile' => $mobileData['mobile'],
                        'isBuy'=> $mobileData['is_buy'],
                    );
                    
                    $page = $this->getPageTable()->getOne(array('id'=>$val['id']));
                    $cardname = $this->getCarteTable()->getOne(array('id'=>$page['carte_id']));
                    
                    $mobile = ($val['mobile']) ? explode(',', $val['mobile']) : array();
                    $list[]['card'] = array(
                        'id' => $val['id'],
                        'cardName'=>$cardname['carte_name'],
                        'name' => $val['title'],
                        'job'=>$cardname['position'],
                        'isDevice'=>$val['status'] ? 1 : 0,
                        'timestampUpdate' => $val['timestamp_update'],
                        'statVisit' => $val['count'],//9.8
                        'deviceld' => $val['status'],
                        'type' => 1,
                        'user' => $user,
                        'mobile' => $mobile,
                        'imagePath' => $val['path'] . $val['filename']
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
                $cardname = $this->getCarteTable()->getOne(array('id'=>$val['page_id']));               
                $is_buy = $this->getUserTable()->getOne(array('id'=>$val['user_id_2']));
                $user = array(
                    'id' => $val['user_id_2'],
                    'isTop' => $val['top'],
                    'isShield' => $val['shield'],
                    'isDoNotRemind' => $val['disturb'],
                    'mobile' => $val['mobile'],
                    'isBuy'=> $is_buy['is_buy']?$is_buy['is_buy']:"2",
                );
                $list[]['card'] = array(
                    'id' => $val['page_id'],
                    'name' => $val['title'],
                    'cardName'=> $cardname['carte_name'],
                    'job'=>$pageData['position'],
                    'user' => $user,
//                     'mobile' => $mobile,//多了一个
                    'timestampUpdate' => $val['timestamp_update'],
                    'visiStat' => $val['count'],
                    'type' => 1,
                    'imagePath' => $val['path'] . $val['filename'],
                );
            }
        }
        
        if (isset($action) && $action == 3) {
            $where->equalTo('user_id_2', $this->getUserId());
            $relation = $this->getAll($this->getViewUserOneRelationTable(), $where);
            
            $list = array();
            foreach ($relation['list'] as $vals) {
                $data = $this->getViewUserPageTable()->getAll(array(
                    'id' => $vals['user_id_1']
                ));
                
                $is_buy = $this->getUserTable()->getOne(array('id'=>$vals['user_id_1']));
               
                
                if ($data) {
                    $user = array(
                        'id' => $vals['user_id_1'],
                        'mobile' => $vals['mobile'],
                        'attention' => ($vals['attention'] == 1 ? 2 : 3),
                        'content' => $vals['content'],
                        'isBuy'=> $is_buy['is_buy'],
                    );
                    foreach ($data['list'] as $val) {
                        $cardname = $this->getCarteTable()->getOne(array('id'=>$val['page_id']));
                        $list[]['card'] = array(
                            'id' => $val['page_id'],
                            'name' => $val['title'],
                            'cardName'=>$cardname['carte_name'],
                            'job'=>$cardname['position'],
                            'timestampUpdate' => $val['timestamp_update'],
                            'visiStat' => $val['count'],
                            'type' => 1,
                            'imagePath' => $val['path'] . $val['filename'],
                            'user' => $user
                        );
                    }
                }
            }
        }
        
        if (isset($action) && $action == 4) {
            
            if (! $table_where->ids && ! $table_where->mobiles && ! $table_where->search_key) {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            if ($table_where->ids) {
                $where->in('id', $table_where->ids);
            }
            if ($table_where->mobiles) {
                $where->in('mobile', $table_where->mobiles);
            }
            
            $data = $this->getAll($this->getViewUserPageTable(), $where);
            
            if ($data) {
                $list = array();
                
                foreach ($data['list'] as $val) {
                    if (!$val['page_id'] == 0) {
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
                        
                        $is_buy = $this->getUserTable()->getOne(array('id'=>$val['id']));
                        
                        $user = array(
                            'id' => $val['id'],
                            'mobile' => $val['mobile'],
                            'isBuy'=>$is_buy['is_buy'],
                            'attention' => $attention,
                        );
                        $carteMobile = ($val['carte_mobile']) ? explode(',', $val['carte_mobile']) : array();
                        
                        $carte = $this->getCarteTable()->getOne(array('id'=>$val['page_id']));
                        
                        $list[]['card'] = array(
                            'id' => $val['page_id'],
                            'user' => $user,
                            'name' => $val['title'],
                            'cardName'=> $carte['carte_name'],
                            'job'=>$carte['position'],
                            'englishName' => $val['englist_name'],
                            'mobile' => $carteMobile,
                            'timestampUpdate' => $val['timestamp_update'],
                            'visiStat' => $val['count'],
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
                $is_buy = $this->getUserTable()->getOne(array('id'=>$val['id']));         
                $job = $this->getCarteTable()->getOne(array('id'=>$val['page_id']));
                
                $user = array(
                    'id' => $val['id'],
                    'isBuy'=> $is_buy['is_buy'],
                );
                $list[]['card'] = array(
                    'id' => $val['page_id'],
                    'name' => $val['name'],
                    'cardName'=>$job['carte_name'],
                    'job'=>$job['position'],
                    'timestamp' => $val['timestamp'],
                    'timestampUpdate' => $val['timestamp_update'],
                    'statVisit' => $val['count'],
                    'type' => 1,
                    'imagePath' => $val['path'] . $val['filename'],
                    'user' => $user
                );

            }
        }
       
        $response->cards = $list;
        $response->total = isset($data['total']) ? $data['total'] : 0;
        return $response;
    }
}







