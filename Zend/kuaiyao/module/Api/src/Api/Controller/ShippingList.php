<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
/**
 * 商家运费列表
 */
class ShippingList extends CommonController
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $id = (int) $request->id;
        if ($id < 0)
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        $data = $this->sellerShipping2($id);
        $list = $this->addKey($data['list'], 'shipping');
        $response = $this->getAiiResponse();
        $response->total = count($list) . '';
        $response->shippings = $list;
        $response->other = $data['other'];
        return STATUS_SUCCESS;
    }
    
    /**
     * 
     * @param number $id
     * @param number $type 1 api返回，2计算运费
     * @return multitype:multitype:number string unknown  Ambigous <multitype:multitype:unknown  , unknown>
     * @version 2015-4-16 WZ
     */
    function sellerShipping2($id, $type=1)
    {
        $where = array('seller_id' => $id);
        $data = $this->getShopShippingRuleTable()->fetchAll($where);
        
        $list = array();
        $other = array(
            'status' => 2,
            'price' => '0.00',
        );
        foreach ($data as $value) {
            $region_ids = explode(',', $value['region_id']);
            foreach ($region_ids as $region_id) {
                if (! $region_id) {
                    $other['status'] = 1;
                    if ($other['price'] > $value['price']) {
                        $other['price'] = $value['price'];
                        if (2 == $type) {
                            $other[$region_id]['weight'] = $value['weight'];
                            $other[$region_id]['overPrice'] = $value['over_price'];
                        }
                    }
                }
                elseif (! isset($list[$region_id])) {
                    $list[$region_id] = array(
                        'regionId' => $region_id,
                        'price' => $value['price']
                    );
                    if (2 == $type) {
                        $list[$region_id]['weight'] = $value['weight'];
                        $list[$region_id]['overPrice'] = $value['over_price'];
                    }
                }
                elseif ($list[$region_id]['price'] > $value['price']) {
                    $list[$region_id]['price'] = $value['price'];
                    if (2 == $type) {
                        $list[$region_id]['weight'] = $value['weight'];
                        $list[$region_id]['overPrice'] = $value['over_price'];
                    }
                }
            }
        }
        return array('list' => $list, 'other' => $other);
    }
    
    
    /**
     * 旧版（弃用）
     * @param unknown $id
     * @return unknown
     * @version 2015-4-16 WZ
     */
    function sellerShipping($id)
    {
        $where = array('seller_id' => $id);
        $data = $this->getShopShippingRuleTable()->fetchAll($where);
        $region_where = new Where();
        $region_where->lessThan('deep', '3');
        $region_data = $this->getRegionTable()->getDataByIds($region_where, array('deep' => 'asc', 'id' => 'asc'));
        foreach ($region_data as $key => $value)
        {
            if (1 != $value['deep'])
            {
                $region_data[$value['parent_id']]['childs'][$value['id']] = $value;
            }
        }
        
        $list = array();
        foreach ($data as $value)
        {
            $regions = explode(',', $value['region_id']);
            foreach ($regions as $region_id)
            {
                if (! isset ($region_data[$region_id]))
                {
                    continue;
                }
                $item = $region_data[$region_id];
                if (1 == $item['deep'])
                {
                    if (! isset($list[$region_id]))
                    {
                        $list[$region_id] = array(
                            'regionId' => $region_id,
                            'childs' => array()
                        );
                    }
                    foreach ($region_data[$region_id]['childs'] as $key2 => $value2)
                    {
                        if (! isset($list[$region_id]['childs'][$value2['id']]))
                        {
                            $list[$region_id]['childs'][$value2['id']] = array(
                                'regionId' => $value2['id'],
                                'price' => $value['price']
                            );
                        }
                        elseif ($list[$region_id]['childs'][$value2['id']]['price'] > $value['price'])
                        {
                            $list[$region_id]['childs'][$value2['id']]['price'] = $value['price'];
                        }
                    }
                }
                elseif (2 == $item['deep'])
                {
                    $parent_region_id = $item['parent_id'];
                    if (! isset($list[$parent_region_id]))
                    {
                        $list[$parent_region_id] = array(
                            'regionId' => $parent_region_id,
                            'childs' => array()
                        );
                    }
                    if (! isset($list[$parent_region_id]['childs'][$region_id]))
                    {
                        $list[$parent_region_id]['childs'][$region_id] = array(
                            'regionId' => $region_id,
                            'price' => $value['price']
                        );
                    }
                    elseif ($list[$parent_region_id]['childs'][$region_id]['price'] > $value['price'])
                    {
                        $list[$parent_region_id]['childs'][$region_id]['price'] = $value['price'];
                    }
                }
            }
        }
        
        foreach ($list as $key => $value)
        {
            $list[$key]['childs'] = $this->addKey($list[$key]['childs'], 'child');
        }
        $list = $this->addKey($list, 'shipping');
        return $list;
    }
}
