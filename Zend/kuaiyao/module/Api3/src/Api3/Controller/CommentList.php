<?php
namespace Api3\Controller;

/**
 * 评论列表
 */
class CommentList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $chat = $this->getChatTable()->getOne(array(
            'id' => $request->id,
            'delete' => 0
        ));
        $where = array();
        $where['chat_id'] = $request->id;
        if ($chat && $request->id) {
            $data = $this->getChatCommentTable()->getAll($where);
            $list =array();
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                    $page = $this->getViewUserPageTable()->getOne(array(
                        'id' => $val['user_id']
                    ));
                    if ($page) {
                        $img = $this->getImageTable()->getOne(array(
                            'id' => $page['head_icon']
                        ));
                        $user = array(
                            'id' => $page['id'],
                            'name' => $page['name'],
                            'imagePath' => $img['path'] . $img['filename']
                        );
                        if ($val['user_id_to']) {
                            $pageTo = $this->getViewUserPageTable()->getOne(array(
                                'id' => $val['user_id_to']
                            ));
                            $imgTo = $this->getImageTable()->getOne(array(
                                'id' => $pageTo['head_icon']
                            ));
                            $userTo = array(
                                'id' => $pageTo['id'],
                                'name' => $pageTo['name'],
                                'imagePath' => $imgTo['path'] . $imgTo['filename']
                            );
                        } else {
                            $userTo = array(
                                'id' => '',
                                'name' => '',
                                'imagePath' => ''
                            );
                        }
                    } else {
                        $user = array(
                            'id' => '',
                            'name' => '',
                            'imagePath' => ''
                        );
                        $userTo = array(
                            'id' => '',
                            'name' => '',
                            'imagePath' => ''
                        );
                    }
                    $list[]['comment']=array(
                        'id' => $val['id'],
                        'content'=>$val['content'],
                        'user'=>$user,
                        'userTo'=>$userTo
                    );
                }
                $response->total = $data['total'];
                $response->comments= $list;
                return $response;
            }
        } else {
            return STATUS_NODATA;
        }
    }
}







