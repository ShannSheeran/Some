<?php
namespace Api0\Controller;

use Api0\Controller\Request\ReferenceItemListWhereRequest;

/**
 * 参照项列表
 */
class ReferenceItemList extends CommonController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $request_where = $this->getTableWhere();
        $response = $this->getAiiResponse();
        $param = array();
        if ($request_where->region_id)
        {
            $param['parent_id'] = $request_where->region_id;
        }
        $filename = $this->makeCacheFilename($param, $type = 1);
        $cache = $this->getCache($filename, $type);
        if (! $cache)
        {
            if ($request->action == 1) {
                $list = array();
            }
            $total = $data['total'];
            
            $this->setCache($filename, array(
                'total' => $total,
                'list' => $list
            ), 1);
        }
        else
        {
            $total = $cache['total'];
            $list = $cache['list'];
        }
        $response->total = $total . '';
        $response->items = $list;
        return $response;
    }
}
