<?php
namespace Api5\Controller;

use Zend\Db\Sql\Where;
use Api5\Controller\Request\ActivityDetailsRequest;

/**
 * 查询个人信息
 */
class ActivityDetails extends CommonController
{
    function __construct()
    {
        $this->myRequest = new ActivityDetailsRequest();
        parent::__construct();
    }
    
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $activity_id = $request->id;
        
        $activity_info = $this->getViewActivityCompanyTable()->getOne(array('id'=>$activity_id));
        $imagePath = '';
        if ($activity_info['images']) {
            $images = explode(',', $activity_info['images']);
            $images = $images[0];
            $imagePath = $this->getImageTable()->getOne(array('id'=>$images));
            if ($imagePath) {
                $imagePath = $imagePath['path'] . $imagePath['filename'];
            }
        }
        
        $item = array(
               'id' => $activity_info['id'],
             'name' => $activity_info['name'],
        'imagePath' => $imagePath,
          'content' => strip_tags($activity_info['content']),
          'company' => array(
               'id' => $activity_info['com_id'],
             'name' => $activity_info['company_name'],
          ),
        'timestamp' => $activity_info['timestamp'],
        );
        
        $response->activity = $item;
        return $response;
    }
}
