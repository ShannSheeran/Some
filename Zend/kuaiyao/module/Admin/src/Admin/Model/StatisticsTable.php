<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class StatisticsTable extends PublicTable
{

    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX . "statistics";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }

    /**
     * 统计按时间戳分组各项内容累加
     * @param $where
     * @param null $limit
     * @return array
     * @author chenzy
     * @update 2015-06-09
     */
    public function statistics($where,$limit=null)
    {
        $select = new Select($this->table);
        $select->where($where);
        $select->group('statistical_time');
        $select->columns(array(new Expression('SUM(click_pv) as click_pv,SUM(click_uv) as click_uv,SUM(shake_pv) as shake_pv,SUM(shake_uv) as shake_uv,statistical_time')));
        $select->limit($limit);
        $resultSet = $this->executeSelect($select);
        $result = array();
        while ($r = $resultSet->current()){
            $result[] = $r;
        }
        return $result;
    }

}
