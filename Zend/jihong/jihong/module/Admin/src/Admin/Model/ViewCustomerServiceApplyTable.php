<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
 * ????
 *
 * @author 系统生成
 *
 */
class ViewCustomerServiceApplyTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = "view_customer_service_apply";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }}