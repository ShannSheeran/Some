<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

/**
 * 分类
 */
class CategoryList extends CommonController
{
    public $my_total = 0;
    
    public $my_list = array();

    public function index()
    {
        $request = $this->getAiiRequest();
        $type = (int)$request->action;
        if (!in_array($type, array(1,2,3))){
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $order = array(
            'sort' => 'asc',
            'id' => 'asc'
        );
        if (1 == $request->action)
        {
            // 商品全部分类
            $where->equalTo('type',1);
        }
        if (2 == $request->action)
        {
            // 首页一级商品分类
            $where->equalTo('parent_id', 0);
            $where->equalTo('type',1);
        }
        if (3 == $request->action)
        {
            // 资讯分类
            $where->equalTo('parent_id', 0);
            $where->notEqualTo('id', array(1,2,3,4));
            $type = 3;
        }
        
        $param = array(
            'type' => $type
        );
        $filename = $this->makeCacheFilename($param, 1);
        $cache = $this->getCache($filename, 1);
        if (!$cache) 
        {//判断是否缓存
            if (in_array($type, array(1,2)))
            { // 商品分类
                $shop_order = array(
                    'sort' => 'asc',
                    'id' => 'asc'
                );
                $data = $this->getViewGoodsCategoryTable()->fetchAll($where,$shop_order);
                $this->setList($data, 1);
            }
            elseif(3 == $type)
            {// 资讯分类
                $shop_order = array(
                    'sort' => 'asc',
                    'id' => 'asc'
                );
                $data = $this->getArticleCategoryTable()->fetchAll($where,$shop_order);
                $this->setList1($data, 1);
            }
            
            $cache = array(
                'total' => $this->my_total . '',
                'list' => $this->my_list
            );
            $this->setCache($filename, $cache, 1);
        }
        else {
            $this->my_total = $cache['total'];
            $this->my_list = $cache['list'];
        } 
        $response = $this->getAiiResponse();
        $response->total = $this->my_total . '';
        $response->categorys = $this->my_list;
        return $response;
        
    }
    /**
     * 商品格式化列表
     * @param unknown $data
     * @version 2015-2-10 WZ
     */
    private function setList($data, $type = 0)
    {
        $list = array();
        $i=0;
        $child_list = array();
        foreach ($data as $key => $value)
        {
            $item = array(
                'id' => $value['id'],
                'name' => $value['name'],
                'imagePath' => $value['path'].$value['filename'],
            );
            if ($value['parent_id']==0)
            {//判断当前键值是否是一级分类
                $list[$i]['category']=$item;
                $i++;
            }else 
            {//非一级分类以父id作为键名保存到$child_list数组，待用
                $child_list[$value['parent_id']][]['category']=$item;
            } 
        }
        foreach ($list as $k=>$v)
        {//遍历一级分类
            if (isset($child_list[$v['category']['id']]))
            {//判断当前分类是否含有子分类    
                $list[$k]['category']['categorys'] = $child_list[$v['category']['id']];
            }
        }
        $this->my_list = $list;
        $this->my_total += count($data);
    }
    
    /**
     * 资讯分类格式化列表
     * @param unknown $data
     * @version 2015-2-10 WZ
     */
    private function setList1($data, $type = 0)
    {
        $list = array();
        foreach ($data as $key => $value)
        {
            $item = array(
                'id' => $value['id'],
                'name' => $value['name'],
            );
            $this->my_list[]['category'] = $item;
        }
        $this->my_total += count($data);
    }

}
