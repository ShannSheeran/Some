<?php
namespace Api5\Controller;

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
        if(!$request->action){
            return STATUS_PARAMETERS_INCOMPLETE;
        }
      /*   if($request->action != 1){
            return STATUS_INCORRECT_FORMAT;
        } */
        $array = array(
            1,
            2
        );
        if(!in_array($request->action, $array)){
            return STATUS_INCORRECT_FORMAT;
        }
        if ($request->action == 1) 
        {
            $list= $indexController->styleImageList();
            foreach ($list as $k=>$v)
            {
                $item['item']['id'] = $k+1;
                $item['item']['name'] = $v['name']; 
                $where = array(
                    'id' => $v['image']
                );
              $image_path = $this -> getImageTable()->fetchAll($where);
                $item['item']['image']['id'] = $image_path['0']->id;
                $item['item']['image']['path'] = $image_path['0']->path.$image_path['0']->filename;
                $items[] = $item;
                
                
            }
          }
          if ($request->action == 2)
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
        $response->total = count($items);
        $response->items = $items;
        return $response;
    }
}
