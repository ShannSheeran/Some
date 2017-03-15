<?php
namespace Admin\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class PublicTable extends AbstractTableGateway
{

    /**
     * 获取所有数据
     *
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    public function fetchAll($where=null,$order=array('id'=>'DESC'),$limit=null)
    {
        $select = new Select($this->table);
        $select->where($where);
        $select->order($order);
        $select->limit($limit);
        //echo $select->getSqlString();
        $resultSet = $this->executeSelect($select);
        $result = array();
        while ($r = $resultSet->current()){
            $result[] = $r;
        }
        return $result;
    }

    /**
     * 查询多条数据
     * @param array $where 查询条件
     * @param array $columns 要查询的字段
     * @param array $order_by 排序字段
     * @param bool $need_page 是否分页  1 or ture 是 0 or false 否，默认不分页 
     * @param array $in_array in条件查询
     * @param array $like_array 关键字查询
     * @return obj
     */
    public function getData($where = null, $columns=null, $order_by = array('id'=>'DESC'), $need_page = false, $search_key = '')
    {
        $select = new Select();
        $select->from($this->table);
        if ($where) {
            $select->where($where);
        }
        
        if($columns){
            $select->columns($columns);
        }

        if ($search_key) {
            $sub_where = new Where();
            foreach($search_key as $key => $value) {
            	$sub_where_1 = new Where();
            	$sub_where_1->like($key,'%'.$value.'%');
            	$sub_where->orPredicate($sub_where_1);                
            }
            $select->where->andPredicate($sub_where);
        }
       //echo $select->getSqlString();
        if($order_by){
            $select->order($order_by);
        }
        if ($need_page) {
            $adapterOrSqlObject = $this->getSql()->getAdapter();
            $adapter = new DbSelect($select, $adapterOrSqlObject);
            return $adapter;
        } else {           
            $resultSet = $this->executeSelect($select);
            return $resultSet;
        }
    }




    public function getLimit($where = null, $order_by = array('id'=>'DESC'), $limit=3)
    {
        $select = new Select();
        $select->from($this->table);
        if ($where) {
            $select->where($where);
        }
        if($order_by){
            $select->order($order_by);
        }
        if($limit){
            $select->limit($limit);
        }

        $resultSet = $this->executeSelect($select);
        $total = $resultSet->count();
        $list = array();
        if ($total > 0) {
            while ($row = $resultSet->current()) {
                $list[] = $row;
            }
        }
        return array(
            'total' => $total,
            'list' => $list
        );

    }
    /**
     * 获取列表数据(包括分页和不分页数据)
     * @param array $columns 要查询的字段
     * @param array $where 查询条件
     * @param array $order_by 排序字段
     * @param bool $need_page 是否分页  1 or ture 是 0 or false 否，默认分页 
     * @param number $page 页码
     * @param number $limit 每页条数
     * @param array $in_array in条件查询
     * @param array $like_array 关键字查询
     * @return array
     */
    public function getAll($where=null, $columns=null, $order_by=null, $need_page=null, $page=1, $limit=0, $search_key = '')
    {
        $result = $this->getData($where, $columns, $order_by,$need_page,$search_key);
        if(!$need_page){
            $total = $result->count();
            $list = array();
            if ($total > 0) {
                while ($row = $result->current()) {
                    $list[] = $row;
                }
            }
            return array(
                'total' => $total,
                'list' => $list
            );
        }else{//分页数据
            
            $limit = $limit==0 ? PAGE_NUMBER : $limit;
            $list = $this->adapterToPager($result, $page, $limit);
            
            return array(
        		'total' => $list['total'],
        		'list' => $list['list'],
                'paginator' => $list['paginator']//PC专用，用于传入模板
            );
        }
    }

    /**
     * 获取以列ID做下标的多条记录
     * @param array $where 查询条件
     * @param array $columns 要查询的字段
     * @param array $order_by 排序字段
     * @param bool $need_page 是否分页  1 or ture 是 0 or false 否，默认不分页 
     * @param array $in_array in条件查询
     * @param array $like_array 关键字查询
     * @return array $list
     */
    public function getDataByIn($where = null,$columns=null, $order_by=null,$need_page=null,$search_key=null)
    {
        $resultSet = $this->getData($where,$columns,$order_by,$need_page,$search_key);
        $list = array();
        if ($resultSet->count() > 0) {
            foreach ($resultSet as $key => $value) {
                $list[$value['id']] = (array)$value;
            }
        }
        
        return $list;
    }



    /**
     * 查询对象转化成列表返回
     *
     * @param Adapter $adapter            
     * @param Number $page 当前分页
     * @param Number $limit 每页条数
     * @return array(total , list => object) 分页结果
     */
    public function adapterToPager($adapter, $page, $limit)
    {
        $paginator = new Paginator($adapter); // 实列化分页类
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage($limit);
        $paginator->setPageRange(8);
        $total = $paginator->getTotalItemCount() . "";
        $list = array();
        if ($total > ($page - 1) * $limit) {
            foreach ($paginator as $key => $v) {
                $list[] = $v;
            }
        }
        
        return array(
            'total' => $total,
            'list' => $list,
            'paginator' => $paginator//PC专用，用于传入模板
        );
    }

    /**
     * 获取一条记录的部分数据
     */
    public function getOne($where, $part = array('*'))
    {
        $select = new Select();
        $select->from($this->table);
        $select->columns($part);
        if($where) {
        	$select->where($where);
        }
        $select->order(array(
            'id' => 'desc'
        ));
        $rowset = $this->executeSelect($select);
        $row = $rowset->current();
        if (! $row) {
            return false;
        }
        return $row;
    }

    /**
     * 插入
     */
    public function insertData($data)
    {
        $this->insert($data);
        return $this->getLastInsertValue();
    }
    
    /**
     * 更新
     */
    public function updateData($set, $where)
    {
        return $this->update($set, $where);
    }

    /**
     * 假删除
     */
    public function deleteData($id)
    {
        $this->update(array(
            'delete' => 1
        ), array(
            'id' => $id
        ));
    }

    public function deleteDatas($id)
    {
        return $this->update(array(
            'delete' => 1
        ), array(
            'id' => $id
        ));
    }
    /**
     * 返回最后一条数据
     *
     * @return boolean Ambigous ArrayObject, NULL, \ArrayObject, unknown>
     */
    public function getLastOne()
    {
        $select = new Select();
        $select->from($this->table);
        $select->order(array(
            'id' => 'desc'
        ));
        $select->limit(1);
        $rowset = $this->executeSelect($select);
        $row = $rowset->current();
        if (! $row) {
            return false;
        }
        return $row;
    }

    /**
     * 直接执行sql语句带分页的方法
     * 
     * @param string $sql
     * @param number $page
     * @param number $limit
     * @return array total,list
     */
    public function executeSql($sql, $order, $page, $limit)
    {
        // echo $sql.$order;exit;
        $stmt = $this->adapter->createStatement($sql . $order);
        $stmt->prepare();
        $result = $stmt->execute();
        
        $resultset = new ResultSet();
        $resultset->initialize($result);
        
        $total = $resultset->count(); // 获得总数
        
        $list = array();
        $key = 0;
        
        while ($item = $resultset->current()) {
            if ($key < ($page - 1) * $limit) {
                $key ++;
                continue;
            }
            if ($key >= $page * $limit) {
                break;
            }
            $list[] = $item;
            $key ++;
        }


        // $stmt = $this->adapter->createStatement($sql.$param);
        // $stmt->prepare();
        // $result = $stmt->execute();
        
        // $resultset->initialize($result);
        // $rows = array();
        // $rows = $resultset->toArray();
        
        $result = array();
        $result['total'] = $total;
        $result['list'] = $list;
        return $result;
    }

    public function executeSqlOne($sql) {
        $stmt = $this->adapter->createStatement($sql);
        $stmt->prepare();
        $result = $stmt->execute();
    }
    
    /**
     * 获取统计条目
     * @param unknown $where
     * @return Ambigous <number, NULL>
     */
    public function countData($where)
    {
        $select = new Select();
        $select->from($this->table);
        if($where) {
            $select->where($where);
        }
//         echo $select->getSqlString();
        $rowset = $this->executeSelect($select);
        return $rowset->count();
    }
    
    /**
     * 更新字段数据
     *
     * @param number $id
     * @param number $income 1增加|2减少
     * @param string $key 字段
     * @param float $value 改变量
     * @return number
     * @version 1.0.141017 WZ
     * @author WZ
     */
    public function updateKey($id, $income, $key, $value, $where = null, $other = null)
    {
        if (! $id && ! $where)
        {
            return false;
        }
        if (! $where)
        {
            $where = array('id' => $id);
        }
        $set = array();
        if ($other && is_array($other)) {
            $set = $other;
        }
        if (1 == $income && 0 < $value)
        {
            $set[$key] = new Expression("$key + $value");
            return $this->update($set, $where);
        }
        elseif (2 == $income && 0 < $value)
        {
            $set[$key] = new Expression("$key - $value");
            return $this->update($set, $where);
        }
    }

    /*
     * 更新统计数据专用
     * @param date $date
     * @param number $income 1增加|2减少
     * @param string $key 字段
     * @param float $value 改变量
     * @return number
     * @author Sheeran
     * */
    public function updateKeys($date, $income, $key, $value, $where = null, $other = null)
    {
        if (! $date && ! $where)
        {
            return false;
        }
        if (! $where)
        {
            $where = array('date' => $date);
        }
        $set = array();
        if ($other && is_array($other)) {
            $set = $other;
        }
        if (1 == $income && 0 < $value)
        {
            $set[$key] = new Expression("$key + $value");
            return $this->update($set, $where);
        }
        elseif (2 == $income && 0 < $value)
        {
            $set[$key] = new Expression("$key - $value");
            return $this->update($set, $where);
        }
    }


}
