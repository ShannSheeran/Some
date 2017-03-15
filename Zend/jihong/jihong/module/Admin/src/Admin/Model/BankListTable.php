<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ???
*
* @author 系统生成
*
*/
class BankListTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "bank_list";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getBankList()
    {
        $list = $this->fetchAll();
        $bank_list = array();
        foreach ($list as $value)
        {
            $bank_list[$value->id] = $value;
        }
        return $bank_list;
    }
}