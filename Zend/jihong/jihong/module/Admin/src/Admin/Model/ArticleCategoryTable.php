<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Where;
/**
* ?????
*
* @author 系统生成
*
*/
class ArticleCategoryTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "article_category";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    public function getArticleCategory()
    {
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $list = $this->fetchAll($where);
        $article_category_list = array();
        foreach ($list as $value)
        {
            $article_category_list[$value->id] = $value;
        }
        return $article_category_list;
    }
}