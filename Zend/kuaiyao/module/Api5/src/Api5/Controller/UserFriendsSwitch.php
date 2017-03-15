<?php
namespace Api5\Controller;

/**
 * 用户，更新个人信息
 *
 * @author
 *         WZ
 *        
 */
class UserFriendsSwitch extends User
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $request->open = $request->open ? $request->open : 1;
        $Where = array();
        $Where['user_id_1'] = $this->getUserId(); // 用户id
        $Where['user_id_2'] = $request->id;
        if (! $Where['user_id_2']) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $open_array = array(
            1,
            2
        );
        if (! in_array($request->open, $open_array)) {
            return STATUS_UNKNOWN;
        }
        $action_array = array(
            1,
            2,
            3,
            4,
            5
        )
        ;
        if (! in_array($request->action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        $user = $this->getUserTable()->getOne(array(
            'id' => $Where['user_id_2'],
            "delete" => 0
        ));
        if (! $user) {
            return STATUS_NODATA;
        }
        $data = $this->getUserRelationTable()->getOne($Where);
      
        if ($request->action == 1 && !($Where['user_id_1'] == $Where['user_id_2'])) {
            $id = $request->id; // 传过来的ID
            /* if ($data) { */
                if ($request->open == 2) {
                    $issent = $this->getUserRelationTable()->getOne(array(
                        'user_id_1' => $id,
                        'user_id_2' => $this->getUserId()
                    ));
                    if ($issent) {
                        $this->getUserRelationTable()->delete(array(
                            'user_id_2' => $id,
                            'user_id_1' => $this->getUserId()
                        ));
                        $this->getUserRelationTable()->delete(array(
                            'user_id_2' => $this->getUserId(),
                            'user_id_1' => $id
                        ));
                    }
                    $this->clearUserRelationshipCache($this->getUserId(), $id);
                }
            /* } else { */
                if ($request->open == 1) {
                    $issent = $this->getUserRelationTable()->getOne(array(
                        'user_id_1' => $id,
                        'user_id_2' => $this->getUserId()
                    ));
                    if ($issent) {
                        $this->getUserRelationTable()->updateData(array(
                            'attention' => 3
                        ), array(
                            'id' => $issent['id']
                        ));
                        $issent2 = $this->getUserRelationTable()->getOne(array(
                            'user_id_1' => $this->getUserId(),
                            'user_id_2' => $id
                        ));
                         if (! $issent2) {
                            // 兼容1.5之前的版本，如果不需要兼容可以删除掉
                            $this->getUserRelationTable()->insertData(array(
                                "user_id_1" => $Where['user_id_1'],
                                "user_id_2" => $id,
                                'attention' => 3,
                                "timestamp" => $this->getTime(),
                                "content" => $request->content
                            ));
                        } else {
                            $this->getUserRelationTable()->updateData(array(
                                'attention' => 3
                            ), array(
                                'id' => $issent2['id']
                            ));
                        } 
                        $this->clearUserRelationshipCache($this->getUserId(), $id);
                    }else {
                        $this->getUserRelationTable()->insertData(array(
                            "user_id_1" => $Where['user_id_1'],
                            "user_id_2" => $id,
                            'attention' => 1,
                            "timestamp" => $this->getTime(),
                            "content" => $request->content
                        ));
                        
                        $this->getUserRelationTable()->insertData(array(
                            "user_id_1" => $id,
                            "user_id_2" => $Where['user_id_1'],
                            'attention' => 2,
                            "timestamp" => $this->getTime(),
                            "content" => $request->content
                        ));
                    }
                }
           /*  } */
        }
        $key = array(
            2 => 'top',
            3 => 'shield',
            4 => 'disturb'
        );
        if (isset($key[$request->action])) {
            $this_key = $key[$request->action];
            $open = $request->open == 1 ? 1 : 2;
            if ($data[$this_key] != $open) {
                $this->getUserRelationTable()->updateData(array(
                    $this_key => $open
                ), array(
                    'id' => $data['id']
                ));
            }
        }
        if ($request->action == 5 && !($Where['user_id_1'] == $Where['user_id_2'])) {
            
            $id = $request->id;
            
           
            $user_id_1_issent = $this->getUserRelationTable()->getOne(array(
                'user_id_1' => $id,  
                'user_id_2' => $this->getUserId()  
            ));
            $user_id_2_issent = $this->getUserRelationTable()->getOne(array(
                'user_id_1' => $this->getUserId(),
                'user_id_2' => $id
            ));
            //好友拒绝  对方还是可以添加
            if ($user_id_2_issent['attention'] == 1  && $user_id_2_issent['is_show'] == 1) {
                $this->getUserRelationTable()->delete(array('id' => $user_id_2_issent['id']));
                $this->getUserRelationTable()->delete(array('id' => $user_id_1_issent['id']));
            }
            
            if ($user_id_2_issent['attention'] == 2  && $user_id_2_issent['is_show'] == 1) {
                $this->getUserRelationTable()->delete(array('id' => $user_id_2_issent['id']));
                $this->getUserRelationTable()->delete(array('id' => $user_id_1_issent['id']));
            }    
            //好友接受之后，删除记录操作
            if($user_id_1_issent && $user_id_2_issent){ 
                $this->getUserRelationTable()->updateData(array('is_show' => '2'),array('id' => $user_id_2_issent['id']));               
            }else{              
                $this->getUserRelationTable()->updateData(array('is_show' => '2'), array('id' => $user_id_1_issent['id']));
            }
            $this->clearUserRelationshipCache($this->getUserId(), $id);
            return STATUS_SUCCESS;
        }
    }

    /**
     * 清除好友关系缓存
     *
     * @param unknown $user_id_1            
     * @param unknown $user_id_2            
     * @version
     *          2015-12-31
     *          WZ
     */
    function clearUserRelationshipCache($user_id_1, $user_id_2)
    {
        $user_ids = array(
            $user_id_1,
            $user_id_2
        );
        $user_list1 = $this->getUserRelationTable()->fetchAll(array(
            'user_id_1' => $user_id_1,
            'attention' => 3
        ));
        if ($user_list1) {
            foreach ($user_list1 as $value) {
                $user_ids[] = $value['user_id_2'];
            }
        }
        
        $user_list2 = $this->getUserRelationTable()->fetchAll(array(
            'user_id_1' => $user_id_2,
            'attention' => 3
        ));
        if ($user_list2) {
            foreach ($user_list2 as $value) {
                $user_ids[] = $value['user_id_2'];
            }
        }
        
        $user_ids = array_unique($user_ids);
        foreach ($user_ids as $user_id) {
            $filename = 'relationship/' . $user_id;
            $this->setCache($filename, array(), 1);
        }
    }
}







