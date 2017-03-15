<?php
namespace Api0\Controller;

use Api0\Controller\Request\UserRequest;
use Api0\Controller\Request\CommentSubmitRequest;

/**
 * 评论与回复
 *
 * @author
 *
 */
class CommentSubmit extends CommonController
{

    function __construct()
    {
        $this->myRequest = new CommentSubmitRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest(); // 获取请求参数
        $response = $this->getAiiResponse(); // 初始化返回参数
        $this->checkLogin(); // 验证登录
        $where = array();
        $where['id'] = $request->comment->chat_id;
        $where['delete'] = 0;
        $content = $request->comment->content;
        $content = trim($content);
        if (! $content || !$where['id']) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $chat = $this->getChatTable()->getOne($where);
        if ($chat) {
            $data = array(
                'chat_id' => (int) $request->comment->chat_id,
                'user_id' => $this->getUserId(),
                'content' => $request->comment->content,
                'user_id_to' => (int) $request->comment->user_id_to,
                'timestamp' => $this->getTime()
            );
            $this->getChatTable()->updateKey($request->comment->chat_id, 1, 'stat_comment', 1);
            $id = $this->getChatCommentTable()->insertData($data);
        } else {
            return STATUS_NODATA;
        }
        $response->id = $id;
        return $response;
    }
}