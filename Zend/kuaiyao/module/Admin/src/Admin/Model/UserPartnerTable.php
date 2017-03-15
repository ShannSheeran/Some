<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class UserPartnerTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX."user_partner";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
