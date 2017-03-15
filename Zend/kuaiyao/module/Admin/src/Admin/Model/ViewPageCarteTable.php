<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewPageCarteTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {   
        $this->table = "view_page_carte";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
