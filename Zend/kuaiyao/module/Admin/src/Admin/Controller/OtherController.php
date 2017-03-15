<?php
namespace Admin\Controller;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\Iterator as paginatorIterator;

class OtherController extends CommonController
{

    /**
     * 留言列表
     * 
     * @version 2014-12-18 liujun
     */
    public function messageAction()
    {
        $this->checkLogin('otherMessageLook');
		$cid = $this->params("cid") ? $this->params("cid") : 1;
        $page = $this->params("page") ? $this->params("page") : 1;
        $list  = $this->getLeaveMessageTable()->getMessage($cid );
        $paginator = new Paginator(new paginatorIterator($list)); // 实列化分页类
        $paginator->setCurrentPageNumber($page)
        ->setItemCountPerPage(PAGE_NUMBER)
        ->setPageRange(5);
         $user_name = $this->getUserTable()->getDataByIn(null,array('id_name','id','nickname'));
         $view = new ViewModel(array(
            'condition' => array(
                "cid"=>$cid,
                'where' => null,
                'action' => 'message',
            	'page' =>$page
            ),
            'paginator' => $paginator,
            'page' => $page,
			'cid'=>$cid ,
            'user_name' => $user_name,
             
        ));
         $this->breadcrumb = array (
         		array (
         				'url' => '#',
         				'title' => '其它'
         		),
         		array (
         				'url' => '',
         				'title' => '留言列表'
         		),
         
         );
        $view->setTemplate('newAdmin/other/message');
        return $this->setMenu($view, 'message');
		
    }
    
    /**
     * 留言详情
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|\Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function messageDetailsAction()
    {
        $this->checkLogin('otherMessageLook');
        $id = $this->params('id');
        if($id){           
        	$list = $this->getLeaveMessageTable()->getAll(array('user_id'=>$id),null,array("id asc"),false);
        	$user_name = $this->getUserTable()->getDataByIn(array('id'=>$id),array('id','id_name','mobile'));
        }
        if(isset($_POST['submit']) && $_POST['submit']){
        	$this->checkLogin('otherMessageEdit');
            $content = $_POST['content'] ? $_POST['content'] : '';
            $date = array(
                'content' => $content,
                'user_id' => $_POST['id'],
                'admin_id' => $_SESSION['admin_id'],
                'timestamp' => $this->getTime()
            );
            
            $this->getLeaveMessageTable()->insert($date);
            return $this->redirect()->toRoute('new-admin-other',array('action'=>'messageDetails','id'=>$_POST['id']));
        }

        $view = new ViewModel(array(
        		'list' => $list['list'],
        		'user_name' => $user_name,
                'id' => $id
        ));
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '其它'
        		),
        		array (
        				'url' => $this->plugin ( 'url' )->fromRoute ( 'new-admin-other', array (
        						'action' => 'message'
        				) ),
        				'title' => '留言列表'
        		),
        		array (
        				'url' => '',
        				'title' => '留言详细'
        		),
        
        );
        $view->setTemplate('newAdmin/other/messageDetails');
        return $this->setMenu($view, 'message');
        
    }
    
    /**
     * 吐槽
     * @return multitype:
     * @version 2014-12-18 liujun
     */
    public function tucaoAction()
    {
        $this->checkLogin('otherTucaoLook');
        $this->table = $this->getWishTable();
        $this->action = 'tucao';
        $this->template = array('other/tucao','tucao');
        $this->other = array('user_name'=>$this->getUserTable()->getDataByIn(null,array('id','nickname','id_name')));
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '其它'
        		),
        		array (
        				'url' => '',
        				'title' => '吐槽列表'
        		),
        
        );
        return $this->getList();
    }
   
    
    
}