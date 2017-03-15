<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ????
*
* @author 系统生成
*
*/
class AdsPositionTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "ads_position";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getAdsPosition()
    {
        $list = $this->fetchAll();
        $ads_position_list = array();
        foreach ($list as $value)
        {
            $ads_position_list[$value->id] = $value;
        }
        return $ads_position_list;
    }
}