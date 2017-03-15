<?php
namespace Api\Controller;

use Api\Controller\Request\AdListRequest;
use Zend\Db\Sql\Where;

/**
 * 文章列表
 */
class ArticleList extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new AdListRequest();
        parent::__construct();
    }
    
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $type = (int)$request->id;
        
        $where = new Where();
        
        if ($type)
        {
            $where['article_category_id'] = $type;
        }else
        {
            $where->notEqualTo('article_category_id', array(2,3,4));//分类id为2，3，4的文章不在app上显示
        }
        $list = $this->getAll($this->getArticleTable(),$where,array('id','title','image_id','read_number','timestamp','description'));
        $list_array = array();
        foreach ($list['list'] as $v)
        {
            $item = array();
            $item['id'] = $v->id;
            $item['description']= $v->description;
            $item['title']= $v->title;
            $item['number']= $v->read_number;
            //获取图片信息
            $image = $this->getImageTable()->getImages(array($v->image_id));
            $item['imagePath'] = isset($image[$v->image_id]['path']) ? $image[$v->image_id]['path'].$image[$v->image_id]['filename'] : '';
            $item['timestamp'] = $v->timestamp;
            $list_array[]['article'] = $item;
        }
        $response->articles = $list_array;
        $response->total = $list['total'];
        return $response;
    }
}
