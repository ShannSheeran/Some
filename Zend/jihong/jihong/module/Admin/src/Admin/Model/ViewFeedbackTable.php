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
class ViewFeedbackTable extends PublicTable {
public function __construct(Adapter $adapter) {
$this->table = "view_feedback_list";
$this->adapter = $adapter;
$this->resultSetPrototype = new ResultSet();
$this->initialize();
}}