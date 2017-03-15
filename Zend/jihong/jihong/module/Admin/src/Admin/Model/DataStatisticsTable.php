<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\ResultSet;
/**
* ?????
*
* @author 系统生成
*
*/
class DataStatisticsTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "data_statistics";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}