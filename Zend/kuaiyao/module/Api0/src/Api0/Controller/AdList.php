<?php
namespace Api0\Controller;

use Api0\Controller\Request\AdListRequest;
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
        
        $school_id = 0;
        $region_id = 0;
        $region_array = array();
        if (LOGIN_STATUS_LOGIN == $this->getUserStatus())
        {
            $user_id = $this->getUserId();
            $user = $this->getUserTable()->getOne(array(
                'id' => $user_id
            ));
            if ($user)
            {
                // $school_id = $user->school_id;
                $region_id = $user->city_region_id;
                if ($user->city_region_info)
                {
                    $region_info = $this->decode($user->city_region_info);
                    if ($region_info && is_array($region_info)) {
                        foreach ($region_info as $value)
                        {
                            $region_array[] = $value->region->id;
                        }
                    }
                }
                elseif ($user->city_region_id)
                {
                    $region_info = $this->getRegionInfoArray($user->region_id);
                    if ($region_info['province'])
                        $region_array[] = $region_info['province'];
                    if ($region_info['city'])
                        $region_array[] = $region_info['city'];
                    if ($region_info['county'])
                        $region_array[] = $region_info['county'];
                }
            }
        }
        
        $param = array(
            'pid' => $request->position_id,
            'rid' => $region_id
        );
        $filename = $this->makeCacheFilename($param, 1);
        $cache = $this->getCache($filename, 1);
        if (! $cache)
        {
            // 没有缓存
            $where = new Where();
            $where->equalTo('status', 1); // 还要不要加时间验证？
            if ($request->position_id)
            {
                $where->equalTo('ads_position_id', $request->position_id);
            }
            $sub_where = new Where();
            $sub_where2 = new Where();
            $sub_where2->equalTo('limit', 0);
            $sub_where->andPredicate($sub_where2);
            if ($school_id)
            {
                $sub_where2 = new Where();
                $sub_where2->equalTo('limit', 1);
                $sub_where2->equalTo('foreign_id', $school_id);
                $sub_where->orPredicate($sub_where2);
            }
            if ($region_array)
            {
                $sub_where2 = new Where();
                $sub_where2->equalTo('limit', 2);
                $sub_where2->in('foreign_id', $region_array);
                $sub_where->orPredicate($sub_where2);
            }
            $where->andPredicate($sub_where);
            $order_by = array(
                'am_timestamp_update' => 'desc'
            );
            $data = $this->getViewAdsTable()->fetchAll($where, $order_by);
            
            $list = array();
            if ($data)
            {
                foreach ($data as $value)
                {
                    $item = array(
                        'id' => $value['ads_material_id'],
                        'name' => $value['material_name'],
                        'width' => $value['position_width'],
                        'height' => $value['position_height'],
                        'link' => $value['url'],
                        'startTime' => $value['start_time'],
                        'endTime' => $value['end_time'],
                        'positionId' => $value['ads_position_id'],
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
