<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class CompanyTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {   
        $this->table = DB_PREFIX."company";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
