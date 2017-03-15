<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
/**
* ????
*
* @author 系统生成
*
*/
class WeekPlanTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "week_plan";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getSupplyNumberCount($where){
        $select = new Select();
        $select->from($this->table)->columns(array("id"))->where($where);
        return  $this->executeSelect($select)->count();
    }
}