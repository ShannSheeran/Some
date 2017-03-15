<?php
namespace Api5\Controller;

/**
 * 赞  列表
 */
class ChatPraiseList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array();
        $where['chat_id'] = $request->id;
        $chat = $this->getChatTable()->getOne(array(
            'id' => $request->id,
            'delete' => 0
        ));
        if ($request->id && $chat)
        {
            $data = $this->getChatPraiseTable()->getAll($where);

            $list = array();
            if ($data['list'])
            {
                foreach ($data['list'] as $val)
                {
                    $page = $this->getViewUserPageTable()->getOne(array(
                        'id' => $val['user_id']
                    ));
                  
                    if ($page)
                    {
                        $img = $this->getImageTable()->getOne(array(
                            'id' => $page['head_icon']
                        ));
                        $pageUser = array(
                            'id' => $page['id'],
                            'name' => $page['title'],
                            'imagePath' => $img['path'] . $img['filename']
                        );
                       
                    }else {
                        $pageUser = array(
                            'id' => '',
                            'name' => '',
                            'imagePath' => ''
                        );
                    }
                   
                    $list[]['chatPraise'] = array(
                        'id' => $val['id'],
                        'user' => $pageUser
                    );
                }
            }
            $response->total = $data['total'];
            $response->chatPraises = $list;
            return $response;
        }
        else
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
    }
}







