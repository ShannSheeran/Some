<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewPageViewTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = "view_page_view";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
