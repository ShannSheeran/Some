<?php
namespace Index\Controller;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\View\Model\ViewModel;


class NewsController extends CommonController
{
    public function indexAction()
    {
        
        $this->checkLogin('user_index');
        $this->table = $this->getNewsTable();
        $this->template = array(
            'news/index'
        );
        return $this->getList();
    }
    public function contentAction()
    {
        if (isset($_POST['id']) && $_POST['id'])
        {
            $content = $this->getArticleTable()->getOne(array('id'=>$_POST['id']));
             if($content)
             {  
                echo $content->content;
             }
        }
        die();
    }
}