<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewUserRelationTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = "view_user_relation";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
