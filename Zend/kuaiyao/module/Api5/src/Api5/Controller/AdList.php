<?php
namespace Api5\Controller;

use Api5\Controller\Request\AdListRequest;
use Zend\Db\Sql\Where;

/**
 * 广告
 */
class AdList extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new AdListRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $this->checkLogin();
        
            $data = $this->getAll($this->getAdsTable(),array('delete'=>0));
            
            $list = array();
            if($data['list']){
                foreach ($data['list'] as $val) { 
                    if($val['image']){                       
                        $imagePath = $this->getImageTable()->getOne(array('id'=>$val['image']));
                        $image = $imagePath['path'] . $imagePath['filename'];
                        $width = $imagePath['width'] ?$imagePath['width'] : 0 ;
                        $height = $imagePath['height']  ? $imagePath['height']:0 ;
                    }else{
                        $image = 0;
                        $width = 0;
                        $height = 0;
                    }
                    $list[]['ad'] = array(
                        'id' => $val['id'],
                        'name'=> $val['name'],
                        'width' => (int)$width,
                        'height' => (int)$height,
                        'type' => $val['type'],
                        'target' => $val['target'],
                        'link' => $val['link'],
                        'positionId' => 0, //暂时写死为0 因为只有一个广告位
                        'imagePath' => $image,
                    );
                }
            }

        
        $response->ads = $list;
        return $response;
    }
}
