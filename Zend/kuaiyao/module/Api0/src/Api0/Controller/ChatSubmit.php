<?php
namespace Api0\Controller;

use Api0\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api0\Controller\Request\CardSubmitRequest;
use Api0\Controller\Request\ChatSubmitRequest;

/**
 * 人脉圈发布
 */
class ChatSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new ChatSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $data = array();
        $data['user_id']=$this->getUserId();
        $content = $request->chat->content;
        if (!$content) {
        	return STATUS_PARAMETERS_INCOMPLETE;
        }
        $data['content']=$content;
        $images = $request->chat->images;
        $image = array();
        foreach ($images as $val)
        {
        	$image[]=$val->image->id;
        }
        if ($image) {
        	$data['images']=implode(",", $image);
        }
        
        $this->getChatTable()->insertData($data);
        
        return STATUS_SUCCESS;
    }
   
    
   
}







