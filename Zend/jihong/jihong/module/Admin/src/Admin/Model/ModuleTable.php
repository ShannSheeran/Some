<?php
namespace Admin\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Admin\Model\PublicTable;
/**
* ???
*
* @author 系统生成
*
*/
class ModuleTable extends PublicTable {
    public function __construct(Adapter $adapter) {
        $this->table = DB_PREFIX . "module";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    /**
     * 权限数组输出
     * @param string $priv_str 当前用户权限字符串
     * @author liujun
     * @return  integer
     */
    public function fetchAll($priv_str=FALSE)
    {
        /* 获取权限的分组数据 */
        $select = $this->select(array('parent_id'=>0));
        while ($rows = $select->current())
        {
            $priv_arr[$rows->id] = $rows;
        }
        $in = array_keys($priv_arr);
    
        /* 按权限组查询底级的权限名称 */
         
        $selects = new \Zend\Db\Sql\Select();
        $selects->from($this->table);
        $selects->where->in('parent_id', $in );
        $row= $this->executeSelect($selects);
    
        while ($priv = $row->current())
        {
            $priv_arr[$priv->parent_id]["priv"][$priv->action_code] = $priv;
        }
         
        // 将同一组的权限使用 "," 连接起来，供JS全选
        foreach ($priv_arr AS $action_id => $action_group)
        {
            $priv_arr[$action_id]['priv_list'] = join(',', @array_keys($action_group->priv));
    
            foreach ($action_group->priv AS $key => $val)
            {
                $priv_arr[$action_id]['priv'][$key]['cando'] = (strpos($priv_str, $val->action_code) !== false || $priv_str == 'all') ? 1 : 0;
            }
        }
         
        return $priv_arr;
    }
}