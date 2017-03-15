<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewFinancialTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = "view_financial";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
