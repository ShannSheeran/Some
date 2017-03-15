<?php
namespace Api3\Controller;

use Api3\Controller\Request\UserRequest;
use Api3\Controller\Request\ChatCommentSubmitRequest;

/**
 * 评论与回复
 *
 * @author
 *
 */
class ChatCommentSubmit extends CommonController
{

    function __construct()
    {
        $this->myRequest = new ChatCommentSubmitRequest();
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
        $where['id'] = $request->chatComment->chat_id;
        $content = $request->chatComment->content;
        $content = trim($content);
        if (! $content || !$where['id']) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $chat = $this->getChatTable()->getOne($where);
        if ($chat) {
            $data = array(
                'chat_id' => (int) $request->chatComment->chat_id,
                'user_id' => $this->getUserId(),
                'content' => $request->chatComment->content,
                'user_id_to' => (int) $request->chatComment->user_id_to,
                'timestamp' => $this->getTime()
            );
            $this->getChatTable()->updateKey($request->chatComment->chat_id, 1, 'stat_comment', 1);
            $id = $this->getChatCommentTable()->insertData($data);
        } else {
            return STATUS_NODATA;
        }
        $response->id = $id;
        return $response;
    }
}