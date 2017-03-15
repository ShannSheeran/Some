<?php
namespace Api3\Controller;

use Index\Controller\IndexController as index;

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
        $indexController =new index();
        if ($request->action == 1) 
        {
            $list= $indexController->bankList();
            $items = array();
            foreach ($list as $k=>$v)
            {
                $item['item']['id'] = $k;
                $item['item']['name'] = $v; 
                $items[] = $item;
            }
          }
       
        $response->total = 0;
        $response->items = $items;
        return $response;
    }
}
