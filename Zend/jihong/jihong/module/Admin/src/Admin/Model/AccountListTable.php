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
class AccountListTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "account_list";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
/*     public function getAccountList()
    {
        $list = $this->fetchAll(array('delete' => DELETE_FALSE));
        $account_list = array();
        foreach ($list as $value)
        {
            $account_list[$value->id] = $value;
        }
        return $account_list;
    } */
}