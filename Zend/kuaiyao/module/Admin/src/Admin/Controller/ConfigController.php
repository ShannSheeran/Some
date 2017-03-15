<?php
namespace Admin\Controller;

use Core\System\imageCache;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;


class ConfigController extends CommonController
{

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @version 2014-12-18 liujun
     */
    public function indexAction()
    {
        $this->checkLogin('config_index');
        $resultSet = $this->getAdminTable()->fetchAll(array(
            'delete' => 0
        ));
       
        $adminArray = array();
        foreach ($resultSet as $r)
        {
            
            $type_name = array(1=>'超级管理员',2=>'普通管理员',3=>'运营管理员');
            $r['type_name'] = isset($type_name[$r['super']]) ? $type_name[$r['super']] : '普通管理员';
            
            $adminArray[] = $r;
        }

        $view = new ViewModel(array(
            'admin' => $adminArray
        ));
        
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => '',
        				'title' => '管理员列表'
        		),
        
        );
        $view->setTemplate('admin/config/index');
        return $this->setMenu($view, 'config');
    }
   
    
    /**
     * 系统设置
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function settingAction()
    {
        $this->checkLogin('config_add_role');
        
        if (isset($_POST['submit']))
        {
            $id = $_POST['id'];
            if ($_SESSION['super'] != 1)
            {
                echo "<script>alert('编辑失败，只有超级管理员才有此权限！');history.back(-1);</script>";
                exit();
            }
            $value = $_POST['value'];
            
            $date = array(
                'value' => $value
            );
            
            if ($id)
            {
                
                $this->getSetupTable()->update($date, array(
                    'id' => $id
                ));
             
            }
        }
        $info = $this->getSetupTable()->fetchAll();
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => '',
        				'title' => '系统配置'
        		),
        
        );
        $view = new ViewModel(array(
            'info' => $info
        ));
        $view->setTemplate('admin/config/setting');
        return $this->setMenu($view, 'setting');
    }
   
    /**
     * 积分项设置
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function pointAction()
    {
        $this->checkLogin('config_add_role');
    
        if (isset($_POST['submit']))
        {
            $id = $_POST['id'];
            if ($_SESSION['super'] != 1)
            {
                echo "<script>alert('编辑失败，只有超级管理员才有此权限！');history.back(-1);</script>";
                exit();
            }
            $value = $_POST['value'];
    
            $date = array(
                'value' => $value
            );
    
            if ($id)
            {
    
                $this->getPointItemTable()->update($date, array(
                    'id' => $id
                ));
                 
            }
        }
        $info = $this->getPointItemTable()->fetchAll();
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => '',
        				'title' => '积分项设置'
        		),
        
        );
        $view = new ViewModel(array(
            'info' => $info,
            'pointType' => $this->getPointType()
        ));
        $view->setTemplate('admin/config/point');
        return $this->setMenu($view, 'point');
    }
     
    /**
     * 添加管理员
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function addAdminAction()
    {
        $this->checkLogin('config_adminedit');
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        					'url' => $this->plugin ( 'url' )->fromRoute ( 'admin-config', array (
        						'action' => 'index'
        				) ),
        				'title' => '管理员列表'
        		),
        		array (
        				'url' => '',
        				'title' => '添加管理员'
        		),
        
        );
        $view = new ViewModel();
        $view->setTemplate('admin/config/add_admin');
        return $this->setMenu($view, 'config');
        
        
    }

    /**
     * 管理数据提交
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function actAddAdminAction()
    {
        $this->checkLogin('config_adminedit');
        if (isset($_POST['submit']) && $_POST['submit'])
        {
            
            if ($_SESSION['super'] != 1)
            {
                echo "<script>alert('对不起，添加管理员权限只有超级管理员才拥有！');history.back(-1);</script>";
                die();
            }
            
            $password = $_POST['password'];
            $password1 = $_POST['password1'];
            $name = $_POST['admin_name'];
            $super = $_POST['super'];
            if ($password != $password1)
            {
                echo "<script>alert('对不起，两次输入密码不一至！');history.back(-1);</script>";
                die();
            }
            else
            {
                
                
                
                $this->getAdminTable()->insertData(array(
                    'name' => $name,
                    'password' => md5($password),
                    'timestamp' => date('Y-m-d H:i:s'),
                    'status' => 1,
                    'super'=>$super
                ));
                return $this->redirect()->toRoute('admin-config', array(
                    'action' => 'index'
                ));
            }
        }
    }
    
    /**
     * ajax验证
     * 
     * @version 2014-12-18 liujun
     */
    public function checkAction()
    {
        $where[addslashes($_POST['type'])] = addslashes($_POST['name']);
        $check = $this->getAdminTable()->getOne($where);
        if ($check)
        {
            echo 1;
            exit();
        }
        echo 0;
        exit();
    }
    
    /**
     * 管理员详情
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function admindetailsAction()
    {
        $this->checkLogin('config_index');
        $id = (int) $this->params('id');
        $admin = $this->getAdminTable()->getOne(array('id'=>$id));
        $view = new ViewModel(array(
            'admin' => $admin
        ));
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => $this->plugin ( 'url' )->fromRoute ( 'admin-config', array (
        						'action' => 'index'
        				) ),
        				'title' => '管理员列表'
        		),
        		array (
        				'url' => '',
        				'title' => '管理员详细'
        		),
        
        );
        
        $view->setTemplate('admin/config/admin_details');
        return $this->setMenu($view, 'config');
    }
    
    /**
     * 管理员编辑
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function admineditAction()
    {

        $this->checkLogin('config_adminedit');
        if (isset($_POST['submit']) && $_POST['submit'])
        {
            if ($_SESSION['super'] != 1) 
            {
                echo "<script>alert('对不起，编辑管理员权限只有超级管理员才拥有！');history.back(-1);</script>";
                die();
            }
            $id = (int) $this->params()->fromRoute('id');
            $data = array();

            if ($_POST['password'] != '')
            {
                $password = md5($_POST['password']);
                $data = array(
                    'password' => $password
                );
                $this->getAdminTable()->updateData($data, array('id'=>$id));
            }
            return $this->redirect()->toRoute('admin-config', array(
                'action' => 'index'
            ));
        }
        else
        {
            return $this->redirect()->toRoute('admin-config', array(
                'action' => 'index'
            ));
        }
    }

    /**
     * 管理员删除
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function deletAction()
    {
        $this->checkLogin('config_delete');
        $id = $this->params('id');
        if ($_SESSION['super'] != 1)
        {
            echo "<script>alert('删除失败，只有超级管理员才有此权限！');history.back(-1);</script>";
            exit();
        }
        $info = $this->getAdminTable()->getOne(array('id'=>$id));
        if ($info['super'] == 1)
        {
            echo "<script>alert('删除失败，超级管理员不允许删除！');history.back(-1);</script>";
            exit();
        }
        else
        {
            $deldata = array(
                'delete' => '1'
            );
            $this->getAdminTable()->update($deldata, array(
                'id' => $id
            ));
            return $this->redirect()->toRoute('admin-config');
        }
    }

    /**
     * 停用管理员状态
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function stopAction()
    {
        $this->checkLogin('config_adminedit');
        $id = $this->params('id');
        if ($_SESSION['super'] != 1)
        {
            echo "<script>alert('停用失败，只有超级管理员才有此权限！');history.back(-1);</script>";
            exit();
        }
        
        $info = $this->getAdminTable()->getOne(array('id'=>$id));
        if ($info['super'] != 1)
        {
            $deldata = array(
                'status' => '2'
            );
            $this->getAdminTable()->update($deldata, array(
                'id' => $id
            ));
        }
        else
        {
            echo "<script>alert('超级管理员不可停用！');history.back(-1);</script>";
            exit();
        }
        return $this->redirect()->toRoute('admin-config');
    }

    /**
     * 启用管理员
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function startAction()
    {
        $this->checkLogin('config_adminedit');
        if ($_SESSION['super'] != 1)
        {
            echo "<script>alert('启用失败，只有超级管理员才有此权限！');history.back(-1);</script>";
            exit();
        }
        $id = $this->params('id');
        $deldata = array(
            'status' => '1'
        );
        $this->getAdminTable()->update($deldata, array(
            'id' => $id
        ));
        return $this->redirect()->toRoute('admin-config');
    }

    
    /**
     * 敏感字添加编辑
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function sensitiveWordsAction()
    {
         
        if(isset($_POST['submit']))
        {
             
            $this->getApiController()->writtenSensitiveWords($_POST['content']);
        }
        $info = $this->getApiController()->getSensitiveWords();
        if($info){
            $info = implode($info, '|');
        }
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => '',
        				'title' => '敏感词管理'
        		),
        
        );
        $view = new ViewModel(array(
            'info'=>$info ? $info : '' ));
        $view->setTemplate('admin/config/sensitiveWords');
        return $this->setMenu($view,'sensitiveWords');
    }

    /**
     * 问题列表
     * @return multitype:
     * @version 2014-12-18 liujun
     */
    public function questionAction()
    {
        $this->table = $this->getQuestionTable();
        $this->seach = 'title';
        $this->template = array('config/question','question');
        $this->action = 'question';
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => '',
        				'title' => '问题列表'
        		),
        
        );
        return  $this->getList();
    
    }
    
    /**
     * 问题详情
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function questionDetailsAction()
    {
        $this->checkLogin('question_index');
        $id = (int) $this->params()->fromRoute('id');
        $info = '';
        if ($id) {
            $info = $this->getquestionTable()->getOne(array(
                'id' => $id
            ));
        }
    
        $view = new ViewModel(array(
            'info' => $info
        )
        );
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '站点信息'
        		),
        		array (
        				'url' => $this->plugin ( 'url' )->fromRoute ( 'admin-config', array (
        						'action' => 'question'
        				) ),
        				'title' => '问题列表'
        		),
        		array (
        				'url' => '',
        				'title' => '问题详细'
        		),
        
        );
        $view->setTemplate('admin/config/questionDetails');
        return $this->setMenu($view, 'question');
    }
    
    /**
     * 添加编辑问题
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function addQuestionAction()
    {
        $this->checkLogin('question_add');
        $id = $_POST['id'];
        if (isset($_POST['submit'])) {
            $title = $_POST['title'];
            $option_a = $_POST['option_a'];
            $option_b = $_POST['option_b'];
            $option_c = $_POST['option_c'];
            $option_d = $_POST['option_d'];
            $answer = $_POST['answer'];
    
            $date = array(
                'title' => $title,
                'option_a' => $option_a,
                'option_b' => $option_b,
                'option_c' => $option_c,
                'option_d' => $option_d,
                'answer' => $answer,
            );
    
            if (! $id) {
                $date['timestamp'] = date("Y-m-d H:i:s");
                $id = $this->getquestionTable()->insertData($date);
            } else {
                $this->getquestionTable()->update($date, array(
                    'id' => $id
                ));
            }
        }
        return $this->redirect()->toRoute('admin-config',array('action'=>'question'));
    }
    
    /**
     * 删除一个问题
     * 
     * @version 2014-12-18 liujun
     */
    public function questionDeleteAction()
    {
        $this->Table = $this->getQuestionTable();
        $this->deleteDate();
    }

    /*
     * 个性化背景图片
     * */
    public function personalAction()
    {

        $img=new imageCache();
        if($_POST)
        {
            $ids=$_POST['image_ids'];
            $total=(int)count($ids);
            if($total>=10)
            {
                echo "<script type='text/javascript'>alert('请上传1——9张图片');history.back();</script>";
            }
            $data = $_POST['image_ids'];
            $filename =  'img/img_id';
            $img->setCache($filename,$data,1);
           /* $img->ImgCache($data);*/
        }
        /*$Ids=$img->ImgCache();
        $where=new where();
        $images_array_path=array();
        if($Ids)
        {
            $where->in('id',$Ids);
            $images=$this->getImageTable()->getAll($where);
            foreach($images['list'] as $k=>$v)
            {
                $images_array_path[]=$v['path'].$v['filename'];
            }
        }*/

        $view = new ViewModel(array(
            /*'images'=>$images_array_path*/
        ));
        $view->setTemplate('admin/config/personal');
        return $this->setMenu($view,1);
    }


    public function deleteAdminAction()
    {
        $id=$_POST['id'];
        $back=$this->getAdminTable()->deleteDatas($id);
        if($back)
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
        exit;

    }

}