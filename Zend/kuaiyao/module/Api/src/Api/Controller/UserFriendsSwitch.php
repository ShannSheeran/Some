<?php
namespace Api\Controller;

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
            4
        );
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
        if ($request->action == 1 && ! ($Where['user_id_1'] == $Where['user_id_2'])) {
            $id = $request->id; // 传过来的ID
            if ($data) {
                if ($request->open == 2) {
                    $issent = $this->getUserRelationTable()->getOne(array('user_id_1'=>$id,'user_id_2'=>$this->getUserId()));
                    if ($issent)
                    {
                         $this->getUserRelationTable()->delete(array(
                        'user_id_2' => $id,
                        'user_id_1' => $this->getUserId()
                    ));
                    $this->getUserRelationTable()->delete(array(
                        'user_id_2' => $this->getUserId(),
                        'user_id_1' => $id
                    ));
                    }
                    
                }
            } else {
                if ($request->open == 1) {
                    
                    $issent = $this->getUserRelationTable()->getOne(array('user_id_1'=>$id,'user_id_2'=>$this->getUserId()));
                    
                    if ($issent)
                    {
                        $this->getUserRelationTable()->insertData(array(
                            "user_id_1" => $Where['user_id_1'],
                            "user_id_2" => $id,
                            'attention'=>3,
                            "timestamp" => $this->getTime(),
                            "content" => $request->content
                        ));
                        $this->getUserRelationTable()->updateData(array('attention'=>3), array('id'=>$issent['id']));
                    }
                    else 
                    {
                        $this->getUserRelationTable()->insertData(array(
                            "user_id_1" => $Where['user_id_1'],
                            "user_id_2" => $id,
                            'attention'=>1,
                            "timestamp" => $this->getTime(),
                            "content" => $request->content
                        ));
                    }
                }
            }
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
        /*
         *
         * if
         * ($request->action==2)
         * {
         * $top
         * =
         * $request->open;
         * if
         * ($top==1)
         * {
         * $this->getUserRelationTable()->updateData(array('top'=>$top),array('id'=>
         * $data['id']));
         * }
         * elseif
         * ($top==2)
         * {
         * $this->getUserRelationTable()->updateData(array('top'=>$top),array('id'=>
         * $data['id']));
         * }
         *
         * }
         * if
         * ($request->action==3)
         * {
         * $shield
         * =
         * $request->open;
         * if
         * ($shield==1){
         * $this->getUserRelationTable()->updateData(array('shield'=>$shield),
         * array('id'
         * =>$data['id']));
         * }
         * elseif
         * ($shield==2)
         * {
         * $this->getUserRelationTable()->updateData(array('shield'=>$shield),
         * array('id'
         * =>$data['id']));
         * }
         * }
         * if
         * ($request->action==4)
         * {
         * $disturb
         * =
         * $request->open;
         * if
         * ($disturb==1){
         * $this->getUserRelationTable()->updateData(array('disturb'=>$disturb),
         * array('id'=>$data['id']));
         * }
         * elseif
         * ($disturb==2)
         * {
         * $this->getUserRelationTable()->updateData(array('disturb'=>$disturb),
         * array('id'=>$data['id']));
         * }
         * }
         */
        return STATUS_SUCCESS;
    }
}







