<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

/**
 * 短信验证码（注册、绑定手机、找回密码）
 *
 * @author 系统生成
 *        
 *        
 */
class PaperTable extends PublicTable
{

    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX . "paper";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
}