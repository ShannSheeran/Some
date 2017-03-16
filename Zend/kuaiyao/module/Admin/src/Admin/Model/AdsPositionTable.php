<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class AdsPositionTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX."ads_position";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}