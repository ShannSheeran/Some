<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
/**
* ??????
*
* @author 系统生成
*
*/
class AdminCategoryTable extends PublicTable {
    public function __construct(Adapter $adapter) 
    {
        $this->table = DB_PREFIX . "admin_category";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getCategory()
    {
        $list = $this->fetchAll(array('delete' => DELETE_FALSE));
        $category_list = array();
        foreach ($list as $value)
        {
            $category_list[$value->id] = $value;
        }
        return $category_list;
    }
}