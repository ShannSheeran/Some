<?php
namespace Api0\Controller;

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
     * @return \Api0\Controller\BaseResult
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $region_id = (int) $request->table->where->region_id;
        // 1全部/2已开通
        $request->action = $request->action ? $request->action : 1;
        if(1 == $request->action)
        {
            // 全部
            $where = null;
            $filename = 'all';
            if ($region_id) {
                $where = array(
                    'parent_id' => $region_id
                );
                $filename = $region_id;
            }
            $filename = $this->makeCacheFilename($filename, 1);
            $data = $this->getCache($filename, 1);
            if (! $data) {
                $data = $this->getAll($this->getRegionTable(), $where);
                
                $list = array();
                foreach($data['list'] as $key => $value)
                {
                    $list []['region'] = array(
                        'id' => $value['id'],
                        'parentId' => $value['parent_id'],
                        'name' => $value['name'],
                        'pinyin' => $value['pinyin']
                    );
                }
                $this->setCache($filename, array('total' => $data['total'], 'list' => $list), 1);
                $data['list'] = $list;
            }
        }
        if(STATUS_CACHE_AVAILABLE == $data)
        {
            return STATUS_CACHE_AVAILABLE;
        }
        
        $response->status = STATUS_SUCCESS;
        $response->total = $data['total'] . '';
        $response->regions = $data['list'];
        return $response;
    }
}
