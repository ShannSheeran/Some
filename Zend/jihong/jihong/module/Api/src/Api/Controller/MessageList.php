<?php
namespace Api\Controller;

/**
 * 留言列表
 */
class MessageList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $a = $request->action ? (int)$request->action : 1;
        $where = array();
        $where['user_id'] = $this->getUserId();
        $where['type'] = $a;
        $list = $this->getAll($this->getLeaveMessageTable(),$where);
        $list_array = array();
        foreach ($list['list'] as $v)
        {
            $item = array();
            $item['id'] = $v->id;
            $item['content']= $v->content;
            $item['userId']= $v->user_id;
            $item['adminId']= $v->admin_id;
            $list_array[]['message'] = $item;
        }
        $response->messages = $list_array;
        $response->total = $list['total'];
        return $response;
    }
}
