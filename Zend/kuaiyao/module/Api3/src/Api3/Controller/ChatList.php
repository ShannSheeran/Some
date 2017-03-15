<?php
namespace Api3\Controller;

use Api3\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;

/**
 * 人脉圈列表
 */
class ChatList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array();
        $where['user_id_1'] = $this->getUserId();
        
        $where['attention'] = 3;
        
        $relation = $this->getUserRelationTable()->fetchAll($where); // 好友数据
        $user_list = array();
        foreach ($relation as $value) {
            $user_list[] = $value['user_id_2'];
        }
        $user_list[] = $this->getUserId();
        $chat_where = new Where();
        $chat_where->in('user_id', $user_list);
        $chat_where->equalTo('delete', "0");
        $chat = $this->getAll($this->getChatTable(), $chat_where); // 人脉圈数据
        $chat_list = array();
        $chat_ima = array();
        $array = array();

        foreach ($chat['list'] as $val) {
            
            $chat_comment_where = new Where();
            $chat_comment_where->equalTo('chat_id', $val['id'])->in('user_id', $user_list);
            $comment = $this->getViewChatCommentTable()->getAll($chat_comment_where, null, array(
                'id' => 'asc'
            ), true, 1, 10); // 评论数据
            $peaiser = $this->getViewChatPraiseTable()->getAll($chat_comment_where, null, array(
                'id' => 'asc'
            ), true, 1, 10); // 点赞数据
            $images = array();
            if ($val['images']) {
                $chat_ima = explode(',', $val['images']);
                $chat_ima_where = new Where();
                $chat_ima_where->in('id', $chat_ima);
                $ima = $this->getImageTable()->fetchAll($chat_ima_where); // 图片的shuju
                foreach ($ima as $valima) {
                    $images[]['image'] = array(
                        'id' => $valima['id'],
                        'path' => $valima['path'] . $valima['filename']
                    );
                }
            }
            $user_id_where = new Where();
            $user_id_where->in('user_id', $user_list);
            $page = $this->getViewPageCarteTable()->getOne($user_id_where); // 查询用户数据
            
            $card = array(
                'id' => $page['id'],
                'cardName'=>$page['cardName'],////111111
                'name' => $page['name'],
                'job' => $page['position'],
                'imagePath' =>$page['path'] . $page['filename'],
                'user' => array(
                    'id' => $page['user_id']
                )
            );
            $chatPraiser = array();
            foreach ($peaiser['list'] as $valpea) {
                $chatPraiser[]['chatPraiser'] = array(
                    'userId' => $valpea['user_id'],
                    'name' => $valpea['name']
                );
            }
            $chatComment = array();
            foreach ($comment['list'] as $valcom) {
                $chatComment[]['chatComment'] = array(
                    'id' => $valcom['id'],
                    'content' => $valcom['content'],
                    'user' => array(
                        'id' => $valcom['user_id'],
                        'name' => $valcom['name']
                    ),
                    'user_to' => array(
                        'id' => $valcom['user_id_to'],
                        'name' => $valcom['to_name']
                    )
                );
            }
            $chat_pea_where = new Where();
            $chat_pea_where->equalTo('chat_id', $val['id']);
            $chat_pea_where->equalTo('user_id' , $this->getUserId());
            
            $chatP = $this->getChatPraiseTable()->getOne($chat_pea_where);
            $array[]['chat'] = array(
                "id" => $val['id'],
                "type" => $val['type'],
                "content" => $val['content'],
                "images" => $images,
                "card" => $card,
                "chatPraises" => $chatPraiser,
                "chatComments" => $chatComment,
                "timestamp" => $val['timestamp'],
                "isPraise" => $chatP ? 1:0,
            );
        }
        $response->chats = $array;
        $response->total = $chat['total'];
        return $response;
    }
}







