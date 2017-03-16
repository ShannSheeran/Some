<?php
namespace Api0\Controller;

use Api0\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api0\Controller\Common\WhereRequest;

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
            $where->equalTo('delete', 0);
            $data = $this->getAll($this->getViewMyPageTable(), $where);
            $list = array();
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                    $mobileData = $this->getUserTable()->getOne(array(
                        'id' => $val['user_id']
                    ));
                    $user = array(
                        'id' => $val['user_id'],
                        'mobile' => $mobileData['mobile']
                    );
                    
                    $mobile = ($val['mobile']) ? explode(',', $val['mobile']) : array();
                    $list[]['card'] = array(
                        'id' => $val['id'],
                        'name' => $val['title'],
                        'isDevice'=>$val['status'] ? 1 : 0,
                        'timestampUpdate' => $val['timestamp_update'],
                        'visitStat' => $val['count'],
                        'status' => $val['status'],
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
                $mobile = ($pageData['mobile']) ? explode(',', $pageData['mobile']) : array();
                $user = array(
                    'id' => $val['user_id_2'],
                    'isTop' => $val['top'],
                    'isShield' => $val['shield'],
                    'isDoNotRemind' => $val['disturb'],
                    'mobile' => $val['mobile']
                );
                $list[]['card'] = array(
                    'id' => $val['page_id'],
                    'name' => $val['title'],
                    'user' => $user,
                    'mobile' => $mobile,
                    'timestampUpdate' => $val['timestamp_update'],
                    'visiStat' => $val['count'],
                    'type' => 1,
                    'imagePath' => $val['path'] . $val['filename']
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
                if ($data) {
                    $user = array(
                        'id' => $vals['user_id_1'],
                        'mobile' => $vals['mobile'],
                        'attention' => ($vals['attention'] == 1 ? 2 : 3),
                        'content' => $vals['content']
                    );
                    foreach ($data['list'] as $val) {
                        $list[]['card'] = array(
                            'id' => $val['page_id'],
                            'name' => $val['title'],
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
                        
                        $user = array(
                            'id' => $val['id'],
                            'mobile' => $val['mobile'],
                            'attention' => $attention
                        );
                        $carteMobile = ($val['carte_mobile']) ? explode(',', $val['carte_mobile']) : array();
                        
                        $list[]['card'] = array(
                            'id' => $val['page_id'],
                            'user' => $user,
                            'name' => $val['title'],
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
                $user = array(
                    'id' => $val['id']
                );
                $list[]['card'] = array(
                    'id' => $val['page_id'],
                    'name' => $val['title'],
                    'timestamp' => $val['timestamp'],
                    'timestampUpdate' => $val['timestamp_update'],
                    'visiStat' => $val['count'],
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






