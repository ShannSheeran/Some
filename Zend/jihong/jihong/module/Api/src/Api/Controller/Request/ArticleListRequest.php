<?php
namespace Api\Controller\Request;

use Api\Controller\Common\Request;
/**
 * 文章定义接收类的属性
 *
 * @author WZ
 *        
 */
class ArticleListRequest extends Request
{

    /**
     * 文章分类id
     *
     * @var String
     */
    public $id;
    
    function __construct()
    {
        parent::__construct();
        $key = array('id' => 'id');
        $this->setOptions('key', $key);
    }
}