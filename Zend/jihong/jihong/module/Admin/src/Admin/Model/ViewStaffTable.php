<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ???
*
* @author 系统生成
*
*/
class ViewStaffTable extends PublicTable {
public function __construct(Adapter $adapter) {
$this->table = "view_staff";
$this->adapter = $adapter;
$this->resultSetPrototype = new ResultSet();
$this->initialize();
}}