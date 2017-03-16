<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class UserApplicationTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX."user_application";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}