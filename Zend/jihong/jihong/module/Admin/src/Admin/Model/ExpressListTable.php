<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ??????
*
* @author 系统生成
*
*/
class ExpressListTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "express_list";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}