<?php
namespace Api\Controller;

use Api\Controller\Request\MessageSubmitRequest;

/**
 *  提交留言
 */
class MessageSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new MessageSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $data = array();
        $data['user_id'] = $this->getUserId();   
        $content = $request->message->content;
        $type = $request->message->type;
        if (! $content || ! $type) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if ($content)
        {
            $data['content'] = $content;
        }
        $data['type'] = $type;
        $data['timestamp'] = $this->getTime();
        $this->getLeaveMessageTable()->insertData($data);
   
        return STATUS_SUCCESS;   
        
    }
}







