<?php
namespace Api\Controller;

use Api\Controller\Request\AdListRequest;
use Zend\Db\Sql\Where;

/**
 * 广告
 */
class AdList extends CommonController
{

    const Cache_Time = 10;

    public function __construct()
    {
        $this->myRequest = new AdListRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $positionId = $request->position_id ? $request->position_id:1;
        $param = array(
            'pid' => $positionId,
        );
        $filename = $this->makeCacheFilename($param, 1);
        $cache = $this->getCache($filename, 1);
        if (! $cache)
        {
            // 没有缓存
            $where = new Where();
            $where->equalTo('position_id',$positionId);
            $order_by = array(
                'timestamp_update' => 'desc'
            );
            $data = $this->getViewAdsTable()->fetchAll($where, $order_by);
            
            $list = array();
            if ($data)
            {
                foreach ($data as $value)
                {
                    $item = array(
                        'id' => $value['id'],
                        'name' => $value['position_name'],
                        'width' => $value['width'],
                        'height' => $value['height'],
                        'startTime' => $value['start_time'],
                        'endTime' => $value['end_time'],
                        'positionId' => $value['position_id'],
                        'imagePath' => $value['path'] . $value['filename']
                    );
                    $list[]['ad'] = $item;
                }
            }
            
            $total = count($data);
            $cache = array(
                'total' => $total,
                'list' => $list
            );
            $this->setCache($filename, $cache, 1);
        }
        $cache = (array) $cache;
        $total = isset($cache['total']) ? ($cache['total'] + 0) . '' : '0';
        
        $list = isset($cache['list']) ? (array) $cache['list'] : array();
        
        $response->total = $total . '';
        $response->ads = $list;
        return $response;
    }
}
