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
     * 获取一张图片 Api
     * 
     * @param number $id
     * @return multitype:string |multitype:string unknown 
     * @version 2015-1-21 WZ
     */
    public function getOneImage($id)
    {
        $item = array(
            'path' => '',
            'height' => '0',
            'width' => '0'
        );
        if (0 == $id)
        {
            return $item;
        }
        $image = $this->getOne(array('id' => $id));
        if ($image)
        {
            $item['path'] = $image['path'] . $image['filename'];
            $item['height'] = $image['height'];
            $item['width'] = $image['width'];
        }
        return $item;
    }

    /**
     * 改自：2014/12/09
     * 
     * @author liujun
     * @param array $image_ids 图片数组ID           
     * @return arry  以图片ID为下标的数组
     */
    public function getImages($image_ids)
    {
        $selects = new Select();
        $selects->from($this->table);
        $selects->where->in('id', $image_ids);
        $resultSet = $this->executeSelect($selects);
        $row = array();
        while ($r = $resultSet->current()) {
            $row[$r["id"]]["id"] = $r["id"];
            $row[$r["id"]]["path"] = $r["path"].$r['filename'];
            $row[$r["id"]]['width'] = $r['width'];
            $row[$r["id"]]['height'] = $r['height'];       
        }
        return $row;
    }
    
    
    /**
     * 改自：2014/12/15
     *
     * @author liujun
     * @param array $image_ids 图片数组ID
     * @return arry  以图片ID为下标的数组
     */
    public function getAdminImages($image_ids)
    {
        $selects = new Select();
        $selects->from($this->table);
        $selects->where->in('id', $image_ids);
        $resultSet = $this->executeSelect($selects);
        $row = array();
        while ($r = $resultSet->current()) {
            $row[$r["id"]]["id"] = $r["id"];
            $row[$r["id"]]["path"] = ROOT_PATH.UPLOAD_PATH.$r["path"].$r['filename'];
            $row[$r["id"]]['width'] = $r['width'];
            $row[$r["id"]]['height'] = $r['height'];
        }
        return $row;
    }
    
}
