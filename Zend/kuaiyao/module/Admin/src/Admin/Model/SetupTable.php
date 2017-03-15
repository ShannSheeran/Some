<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class SetupTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX . "setup";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
