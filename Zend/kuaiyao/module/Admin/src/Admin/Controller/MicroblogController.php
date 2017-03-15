<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class MicroblogController extends CommonController
{

    /**
     * 微博列表
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->checkLogin('schoolMicroblogLook');
        $id = (int) $this->params()->fromRoute('id');
        $page = $this->params('page', 1);
        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
        $where = array(
            'delete' => 0
        );
        $data = $this->getChatTable()->getAll($where, null, array(
            'id desc'
        ), true, $page, PAGE_NUMBER, array(
            'id' => $keyword,
            'content' => $keyword
        ));
        
        if ($data['total'])
        {
            $user_id_array = array();
            foreach ($data['list'] as $v)
            {
                $user_id_array[] = $v->user_id;
            }
            
            $where = new Where();
            $where->in('id', $user_id_array);
            $user = $this->getUserTable()->getDataByIn($where, array(
                'id',
                'mobile'
            ));
        }
        
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '人脉圈'
            ),
            array(
                'url' => '',
                'title' => '信息列表'
            )
        );
        
        $date = array(
            'paginator' => $data['paginator'],
            'list' => $data['list'],
            'user' => $user,
            'condition' => array(
                'action' => 'index',
                'cid' => 0,
                'page' => $page,
                'where' => $where
            ), // 提交的where参数用get参数传递
            'where' => $where,
            'cid' => 0,
            'page' => $page,
            'keyword' => $keyword
        );
        if ($keyword)
        {
            $date['condition']['keyword'] = $keyword;
        }
        $view = new ViewModel($date);
        $view->setTemplate('admin/microblog/index');
        return $this->setMenu($view, 'microblgo');
    }

    /**
     * 微博详情
     *
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-23 liujun
     */
    public function detailsAction()
    {
        $this->checkLogin('schoolMicroblogLook');
        $id = (int) $this->params()->fromRoute('id');
        
        $info = $this->getChatTable()->getOne(array(
            'id' => $id
        ));
      
        $images = array();
        if ($info->images)
        {
            
            $image_id = explode(',', $info->images);
            $images = $this->getImageTable()->getAdminImages($image_id); // 微博图片
        }
        $user_info = '';
        if($info)
        {
             $user = $this->getUserTable()->getOne(array('id'=>$info->user_id));
        }
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '人脉圈'
            ),
            array(
                'url' => $this->plugin('url')->fromRoute('admin-microblog', array(
                    'action' => 'index'
                )),
                'title' => '信息列表'
            ),
            array(
                'url' => '',
                'title' => '信息详细'
            )
        );
        $view = new ViewModel(array(
            'info' => $info,
            'images' => $images,
            'user' => $user
        ));
        $view->setTemplate('admin/microblog/details');
        return $this->setMenu($view, 'weiBo');
    }

    /**
     * 评论列表
     *
     * @return multitype:
     * @version 2014-12-23 liujun
     */
    public function commentAction()
    {
        $this->checkLogin('schoolMicroblogLook');
        $this->table = $this->getViewChatCommentTable();
        $this->delete = false;
        $this->template = array(
            'microblog/comment',
            'weiboReport'
        );
        $this->seach = array(
            'id',
            'content'
        );
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '人脉圈'
            ),
            array(
                'url' => '',
                'title' => '评论列表'
            )
        );
        return $this->getList();
    }

    /**
     * 删除微博
     *
     * @version 2014-12-22 liujun
     */
    public function deleteAction()
    {
        $this->checkLogin('schoolMicroblogDelete');
        $this->table = $this->getChatTable();
        $this->deleteDate();
    }

    /**
     * 删除评论
     *
     * @version 2014-12-23 liujun
     */
    public function commentDeleteAction()
    {
        $this->checkLogin('schoolMicroblogDelete');
        $this->table = $this->getWbCommentTable();
        $this->deleteDate();
    }
}