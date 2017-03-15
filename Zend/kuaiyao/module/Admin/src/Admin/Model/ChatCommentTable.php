<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ???????
*
* @author 系统生成
*
*/
class ChatCommentTable extends PublicTable {
public function __construct(Adapter $adapter) {
$this->table = DB_PREFIX . "chat_comment";
$this->adapter = $adapter;
$this->resultSetPrototype = new ResultSet();
$this->initialize();
}}