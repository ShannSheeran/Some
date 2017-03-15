<?php
namespace Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;

class ImageTable extends PublicTable
{

    public function __construct(Adapter $adapter)
    {
        $this->table = DB_PREFIX . "image";
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->initialize();
    }
    
    /**
     * 改自：2014/3/20
     *
     * @author WZ
     * @param unknown $images
     * @param int 0原图 1 4:3 ，2 5:3 3 1:1
     * @return Ambigous <multitype:, string>
     */
    public function getImages($image_ids = null)
    {
        if(!$image_ids){
            return true;
        }
        $selects = new Select();
        $selects->from($this->table);
        $selects->where->in('id', $image_ids);
        $resultSet = $this->executeSelect($selects);
        $row = array();        
        while ($r = $resultSet->current())
        {
            $row[$r["id"]]["id"] = $r["id"];
            $row[$r["id"]]["path"] = $r["path"];
            $row[$r["id"]]["filename"] = $r["filename"];
            $row[$r["id"]]["image_path"] = ROOT_PATH . UPLOAD_PATH. $r["path"].$r["filename"];
        }
        return $row;
    }
    
}