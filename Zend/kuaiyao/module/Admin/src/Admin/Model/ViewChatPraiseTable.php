<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewChatPraiseTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {   
        $this->table = "view_chat_praise";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
