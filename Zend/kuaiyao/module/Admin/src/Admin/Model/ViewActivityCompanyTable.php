<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewActivityCompanyTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = "view_activity_company";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
