<?php
namespace Api5\Controller;

/**
 * 赞提交
 */
class ChatSwitch extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        if ($request->action)
        {
            $where = array();
            $where['user_id'] = $this->getUserId();
            $where['chat_id'] = $request->id;
           if ($request->open==1)
           {
                $date = $this->getChatPraiseTable()->getOne($where);
               if($date)
               {
                   return STATUS_SUCCESS;
               }
               else {
               $this->getChatTable()->updateKey($request->id, 1, 'stat_praise', 1);
               $this->getChatPraiseTable()->insertData(array('chat_id'=>$request->id,"user_id"=>$this->getUserId()));
               }
           }
           if($request->open==2)
           {
               $this->getChatTable()->updateKey($request->id, 2, 'stat_praise', 1);
               $this->getChatPraiseTable()->delete($where);
           }
        }
        return STATUS_SUCCESS;
    }
}







