<?php
namespace Api5\Controller;

use Api5\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api5\Controller\Request\CardSubmitRequest;

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







