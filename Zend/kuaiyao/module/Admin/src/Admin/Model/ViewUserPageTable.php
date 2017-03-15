<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewUserPageTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = "view_user_page";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
