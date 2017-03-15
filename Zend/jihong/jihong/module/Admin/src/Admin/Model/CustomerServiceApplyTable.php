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
class CustomerServiceApplyTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "customer_service_apply";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getServiceApplyCount($where){
        $select = new Select();
        $select->from($this->table)->columns(array("id"))->where($where);
        return  $this->executeSelect($select)->count();
    }
}