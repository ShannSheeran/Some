<?php
namespace Api\Controller;

use Api\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api\Controller\Request\CardSubmitRequest;

/**
 * 名片提交
 */
class ChatUpdate extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        if ($request->action) {
            $where = array();
            $id = $request->id;
            $where['id'] = $id;
            $where['user_id'] = $this->getUserId();
            $this->getChatTable()->updateData(array(
                'delete' => "1"
            ), $where);
        }
        return STATUS_SUCCESS;
    }
}







