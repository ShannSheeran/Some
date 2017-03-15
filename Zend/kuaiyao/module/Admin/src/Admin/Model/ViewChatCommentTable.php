<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ViewChatCommentTable extends PublicTable
{
    public function __construct(Adapter $adapter)
    {   
        $this->table = "view_chat_comment";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}
