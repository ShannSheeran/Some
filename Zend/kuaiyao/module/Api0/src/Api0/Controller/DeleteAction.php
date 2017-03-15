<?php
namespace Api0\Controller;

use Api0\Controller\Request\AdListRequest;
use Zend\Validator\InArray;

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
            3
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
        return STATUS_SUCCESS;
    }
}
