<?php
namespace Api0\Controller;

use Api0\Controller\Request\TopActionRequest;
use Zend\Db\Sql\Where;

/**
 * 分类
 */
class CategoryList extends ForTypes
{
    public $my_total = 0;
    
    public $my_list = array();

    public function __construct()
    {
        $this->myRequest = new TopActionRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $type = (int)$request->category;
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $order = array(
            'sort' => 'asc',
            'id' => 'asc'
        );
        
        if (2 == $request->action)
        {
            // 推荐分类，暂时只有商城有
            $where->equalTo('hot', 1);
            $type = 2;
        }
        
        $param = array(
            'type' => $type
        );
        $filename = $this->makeCacheFilename($param, 1);
        $cache = $this->getCache($filename, 1);
        if (! $cache) {
            if (0 == $type || 2 == $type)
            {
                // 商城
                $shop_order = array(
                    'parent_id' => 'asc',
                    'sort' => 'asc',
                    'id' => 'asc'
                );
                $data = $this->getShopCategoryTable()->fetchAll($where, $shop_order);
                $this->setList($data, 2);
            }
            
            if (0 == $type || in_array($type,array(21,22,23,231,24,241,242,243,244,245,251,252,253,254,255,256,257)))
            {
                // 生活信息
                if ($type)
                {
                    $where->equalTo('type', $type);
                }
                $data = $this->getInfoCategoryTable()->fetchAll($where, $order);
            
                $format_list_1 = array();
                foreach ($data as $value)
                {
                    $format_list_1[$value['type']][$value['id']] = $value['name'];
                }
            
                $type_array = array(
                    0 => array(21,22,23,231,24,244,245,251,252,253,255),
                    1 => array(241 => array('unit' => '厅')),
                    2 => array(242 => '',243 => array('unit' => '层'),254 => ''),
                    //                 3 => array(25)
                );
            
                foreach ($format_list_1 as $key => $value)
                {
                    // 普通，直接输出
                    if (in_array($key, $type_array[0]))
                    {
                        $this->setList4($value, $key);
                    }
            
                    // 从1到N，N以上
                    if (array_key_exists($key, $type_array[1]))
                    {
                        $this->setList2($value, $key, $type_array[1][$key]);
                    }
            
                    // 根据范围输出 A以下，A - B，...，N-1 - N，N以上
                    if (array_key_exists($key, $type_array[2]))
                    {
                        $this->setList3($value, $key, $type_array[2][$key]);
                    }
                }
            }
            
            if (0 == $type || 25 == $type)
            {
                // 职位分类特殊处理
                $this->setList5();
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
        
        if (1 == $type)
        {
            // 论坛
            $region_id = (int) $request->table->where->region_id;
            if ($region_id) {
                $region_list = $this->getRegionList($region_id);
                $sub_where = new Where();
                $sub_where->like('region_list', $region_list . '%')->or->equalTo('region_list', '');
                $where->andPredicate($sub_where);
            }
            $data = $this->getForumSectionTable()->fetchAll($where, $order);
            $this->setList($data, 1);
        }
        
        $response = $this->getAiiResponse();
        $response->total = $this->my_total . '';
        $response->categorys = $this->my_list;
        return $response;
    }
    
    /**
     * 格式化列表
     * @param unknown $data
     * @version 2015-2-10 WZ
     */
    private function setList($data, $type = 0)
    {
        $list = array();
        foreach ($data as $key => $value)
        {
            $item = array(
                'id' => $value['id'],
                'type' => isset($value['type']) ? $value['type'] : $type . '',
                'name' => $value['name'],
                'imagePath' => isset($value['image']) ? $this->getImagePath($value['image']) : '',
            );
            if (isset($value['parent_id']))
            {
                $item['parentId'] = $value['parent_id'];
            }
            if (isset($value['hot']))
            {
                $item['isHot'] = $value['hot'];
            }
            if (isset($value['total']))
            {
                $item['total'] = $value['total'];
            }
            if (isset($value['today_count']))
            {
                $item['statToday'] = $value['today_count'];
            }
            if (isset($value['hot_post_title']))
            {
                $item['hotTitle'] = $value['hot_post_title'];
            }
            $this->my_list[]['category'] = $item;
        }
        $this->my_total += count($data);
    }
    
    /**
     * 格式化列表4
     * @param unknown $data
     * @version 2015-2-10 WZ
     */
    private function setList4($data, $type = 0)
    {
        $list = array();
        foreach ($data as $key => $value)
        {
            $item = array(
                'id' => $key . '',
                'type' => $type . '',
                'name' => $value . '',
            );
            $this->my_list[]['category'] = $item;
        }
        $this->my_total += count($data);
    }
    
    /**
     * 
     * @param array $data
     * @param number $type
     * @param unknown $param array('step' => 1,'unit' => '','from' => '')
     * @version 2015-2-11 WZ
     */
    private function setList2($data, $type = 0, $param = array())
    {
        if ($data)
        {
            $max = max($data);
            $list = array();
            $step = isset ($param['step']) ? $param['step'] : 1;
            $unit = isset ($param['unit']) ? $param['unit'] : '';
            for($i = isset($param['from']) ? $param['from'] : 1 ; $i <= $max ; $i = $i + $step)
            {
                $name = $i . $unit;
                $item = array(
                    'id' => $i . '',
                    'type' => $type . '',
                    'name' => $name . '',
                    'min' => $i . '',
                    'max' => $i . '',
                );
                $this->my_list[]['category'] = $item;
                $this->my_total++;
            }
            
            $name = $max . $unit . '以上';
            $item = array(
                'id' => $i . '',
                'type' => $type . '',
                'name' => $name . '',
                'min' => $max . '',
                'max' => '+',
            );
            $this->my_list[]['category'] = $item;
            $this->my_total++;
        }
    }
    
    /**
     * 格式化数据方法3
     * 
     * @param array $data
     * @param number $type
     * @param array $param array('unit' => '');
     * @version 2015-2-11 WZ
     */
    private function setList3($data, $type = 0, $param = array())
    {
        $i = 1;
        if (243 == $type)
        {
            $item = array(
                'id' => $i++ . '',
                'type' => $type . '',
                'name' => '地下',
                'min' => '-',
                'max' => '-1',
                'imagePath' => ''
            );
            $this->my_list[]['category'] = $item;
            $item = array(
                'id' => $i++ . '',
                'type' => $type . '',
                'name' => '1层',
                'min' => '1',
                'max' => '1',
                'imagePath' => ''
            );
            $this->my_list[]['category'] = $item;
            $this->my_total += 2;
        }
        
        $data = max ($data);
        if ($data)
        {
            $data = explode(',', $data);
//             sort($data,SORT_NUMERIC);
            $count = count($data) - 1;
            $list = array();
            $unit = isset ($param['unit']) ? $param['unit'] : '';
            
            $name = $data[0] . $unit . '以下';
            $item = array(
                'id' => $i++ . '',
                'type' => $type . '',
                'name' => $name . '',
                'min' => '-',
                'max' => $data[0],
            );
            $this->my_list[]['category'] = $item;
            
            foreach($data as $key => $value) 
            {
                if (0 == $key)
                {
                    continue;
                }
                $name = $data[$key - 1] . '-' . $data[$key] . $unit;
                $item = array(
                    'id' => $i++ . '',
                    'type' => $type . '',
                    'name' => $name . '',
                    'min' => $data[$key - 1] . '',
                    'max' => $data[$key] . '',
                );
                $this->my_list[]['category'] = $item;
            }
            
            $name = $data[$count] . $unit . '以上';
            $item = array(
                'id' => $i++ . '',
                'type' => $type . '',
                'name' => $name . '',
                'min' => $data[$count] . '',
                'max' => '+',
            );
            $this->my_list[]['category'] = $item;
            
            $this->my_total += $count + 2;
        }
    }
    
    /**
     * 格式化列表5
     * @param unknown $data
     * @version 2015-2-10 WZ
     */
    private function setList5()
    {
        $where = array('delete' => DELETE_FALSE);
        $order = array('sort' => 'asc', 'id' => 'asc');
        $data = $this->getInfoJobTable()->fetchAll($where, $order);
        $list = array();
        foreach ($data as $value)
        {
            if ($value['category_id'] == 0)
            {
                $item = array(
                    'id' => $value['id'],
                    'type' => '25',
                    'name' => $value['name'],
                    'groups' => array(),
                );
                $list[$value['id']] = $item;
            }
            else 
            {
                $item = array(
                    'id' => $value['id'],
                    'name' => $value['name']
                );
                $list[$value['category_id']]['groups'][]['group'] = $item;
            }
        }
        
        foreach ($list as $value)
        {
            $this->my_list[]['category'] = $value;
        }
        $this->my_total += count($data);
    }
}
