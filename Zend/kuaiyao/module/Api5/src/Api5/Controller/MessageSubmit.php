<?php
namespace Api5\Controller;

use Api5\Controller\Request\CardListWhereRequest;
use Zend\Db\Sql\Where;
use Api5\Controller\Request\MessageSubmitRequest;

/**
 * 人脉圈发布
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
        $images = $request->message->images;
        if (! $content && ! $images) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        if ($content)
        {
            $data['content'] = $content;
        }
        if ($images)
        {
            $image = array();
            foreach ($images as $val) {
                $image[] = $val->image->id;
            }
            if ($image) {
                $data['images'] = implode(",", $image);
            }
        }
        $data['timestamp'] = $this->getTime();
        $this->getChatTable()->insertData($data);
        return STATUS_SUCCESS;
//         if (! $images && $content) {
            
//             $data['content'] = $content;
//         }
//         if (! $content && $images) {
//             $image = array();
//             foreach ($images as $val) {
//                 $image[] = $val->image->id;
//             }
//             if ($image) {
//                 $data['images'] = implode(",", $image);
//             }
//         }
//         if ($images && $content) {
//             $data['content'] = $content;
//             $image = array();
//             foreach ($images as $val) {
//                 $image[] = $val->image->id;
//             }
//             if ($image) {
//                 $data['images'] = implode(",", $image);
//             }
//         }
//         $this->getChatTable()->insertData($data);
        
        
    }
}







