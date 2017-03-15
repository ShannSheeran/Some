<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Admin\Model\PublicTable;
use Zend\Db\Sql\Select;
/**
* ???
*
* @author 系统生成
*
*/
class OrderTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "order";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getOrderCount($where){
    	$select = new Select();
    	$select->from($this->table)->columns(array("id"))->where($where);
    	return  $this->executeSelect($select)->count();  
    }
}