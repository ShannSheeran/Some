<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ???????????????????
*
* @author 系统生成
*
*/
class SmsCodeTable extends PublicTable {
public function __construct(Adapter $adapter) {
$this->table = DB_PREFIX . "sms_code";
$this->adapter = $adapter;
$this->resultSetPrototype = new ResultSet();
$this->initialize();
}}