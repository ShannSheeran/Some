<?php
namespace Api\Controller;

/**
 * 删除对象
 */
class DeleteAction extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $action = $request->action;
        $id = $request->id;
        if (empty($id) && in_array($action, array(1,2))) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        
        $array = array(
            1,
            2,
            3,
            4
        );
        if (! in_array($action, $array)) {
            return STATUS_UNKNOWN;
        }
        if ($action == 1) {
            $this->getChatTable()->updateData(array(
                'delete' => 1
            ), array(
                'id' => $id,
                'user_id' => $this->getUserId()
            ));
        }
        if ($action == 2) {
            $this->getChatCommentTable()->updateData(array(
                'delete' => 1
            ), array(
                'id' => $id,
                'user_id' => $this->getUserId()
            ));
        }
        if ($action==3)
        {
            $user_id = $this->getUserId();
            $this->getPageViewRecordTable()->delete(array('user_id'=>$user_id));
        }
        if ($action==4)//1111111
        {
            $user_id = $this->getUserId();
            
            $page_info = $this->getPageTable()->getOne(array('id'=>$id,'user_id'=>$user_id));
            
            if($page_info)
            {
                $this->getPageTable()->updateData(array(
                    'delete' => 1
                ), array(
                    'id' => $id,
                    'user_id'=> $this->getUserId()
                ));
                $this->getCarteTable()->updateData(array(
                    'delete' => 1
                ), array(
                    'id' => $page_info['carte_id'],
                ));
            }else{
                return STATUS_UNKNOWN;
            }
            
        }
        return STATUS_SUCCESS;
    }
}
