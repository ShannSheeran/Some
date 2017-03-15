<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
/**
 * 省市区列表、region表
 *
 * @author WZ
 *        
 */
class RegionList extends CommonController
{

    /**
     * 返回一个数组或者Result类
     *
     * @return \Api\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
         // 全部/已开通
        $request->action = $request->action ? $request->action : 1;
        if(1 == $request->action)
        {
            $where = new Where();
            $where->notEqualTo('deep', 8);
            // 全部
            $data = $this->getRegionTable()->getDataByCache($this->getTimestampLeast(),'',$where);
        }
        
        if(STATUS_CACHE_AVAILABLE == $data)
        {
            return STATUS_CACHE_AVAILABLE;
        }
        
        $total = count($data);
        $list = $data;
        if(3 != $request->action)
        {
            foreach($data as $key => $value)
            {
                $data[$key]->parentId = $data[$key]->parent_id;
                unset($data[$key]->parent_id);
            }
        }
        else
        {
            foreach($data as $key => $value)
            {
                $data[$key]->parentIdStr = $data[$key]->parent_id;
                $data[$key]->idStr = $data[$key]->region_id;
                $data[$key]->pinyin = '';     
                unset($data[$key]->parent_id,$data[$key]->region_id,$data[$key]->id);
            }
        }
        $list = $this->addKey($list, 'region');
        $response->status = STATUS_SUCCESS;
        $response->total = $total . '';
        $response->regions = $list;
        return $response;
    }
}
