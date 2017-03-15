<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class ConfigController extends CommonController
{
    /**
     * admin list
     */
    public function adminListAction()
    {
        $this->checkLogin('admin_list'); 
        $page       = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $status     = isset($_GET['status']) ? $_GET['status'] : 0;
        $type       = isset($_GET['type']) ? $_GET['type'] : 0;
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->notEqualTo('id', 1);
        if ($status)
        {
            $where->equalTo('status', $status);
        }
        if($type)
        {
            $where->equalTo('admin_category_id', $type);
        }
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }

        $admin_list = $this->getAdminTable()
                                     ->getAll($where ,null, array('id' => 'DESC'), true, $page, 10 ,$like);
        
        $category_list = $this->getAdminCategoryTable()->getCategory();
        $view=new ViewModel(array(
            'paginator' => $admin_list['paginator'],
            'condition' => array(
                 'action' => 'adminList',
                 'page'   => $page,
                 'keyword' => $keyword,
                 'type' => $type,
                 'status' => $status,
             ),
            'admin_list' => $admin_list['list'],
            'category_list' => $category_list,
            'keyword' => $keyword,
            'type' => $type,
            'status' => $status,
        ));
        $view->setTemplate('admin/config/admin_list');
        return $this->setMenu($view,1);
    }
    
    /**
     * admin detail
     * @return \Zend\View\Model\ViewModel
     */
    public function adminOperateAction()
    {
        $this->checkLogin('admin_detail');
        $id  = (int)$this->params('id');
        
        $admin_info = array();
        if($id)
        {
            $admin_info = $this->getAdminTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
        }

        $category_list = $this->getAdminCategoryTable()->getCategory();
        
        $view=new ViewModel(array(
            'category_list' => $category_list,
            'admin_info' => $admin_info,
            'id' => $id,
        ));
        $view->setTemplate('admin/config/admin_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * add admin
     */
    public function addAdminAction()
    {
        $this->checkLogin('admin_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['admin_category_id'] = addslashes($_POST['admin_category_id']);
            $data['real_name'] = addslashes($_POST['real_name']);
            $data['mobile'] = addslashes($_POST['mobile']);
            $data['qq'] = addslashes($_POST['qq']);
            $data['name'] = addslashes($_POST['name']);
            $data['password'] = md5($_POST['password']);
            $data['status'] = 1;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
            
            if(!$data['name'] || !$data['password'] || !$data['qq'])
            {
                echo '<script>alert("管理员信息不完整");history.back()</script>';
                exit;
            }
            
            $exists_admin = $this->getAdminTable()->getOne(array('name' => $data['name']));
            if($exists_admin)
            {
                echo '<script>alert("已存在该管理员");history.back()</script>';
                exit;
            }
            
            $res = $this->getAdminTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-config',array('action'=>'adminList'));
            }
        }
        die;
    }
    
    /**
     * edit admin
     * @return \Zend\Http\Response
     */
    public function editAdminAction()
    {
        $this->checkLogin('admin_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id)
        {
            if($id == 1){
                echo "<script>alert('该超级管理员无法编辑;');history.back();</script>";
                exit;
            }
            
            $data = array();
            $data['admin_category_id'] = addslashes($_POST['admin_category_id']);
            $data['real_name'] = addslashes($_POST['real_name']);
            $data['mobile'] = addslashes($_POST['mobile']);
            $data['qq'] = addslashes($_POST['qq']);
            $data['name'] = addslashes($_POST['name']);
            if($_POST['password'])
            {
                $data['password'] = md5($_POST['password']);
            }
            
            if(!$data['name'] || !$data['qq'])
            {
                echo '<script>alert("管理员信息不完整");history.back()</script>';
                exit;
            }
            
            $res = $this->getAdminTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-config',array('action'=>'adminList'));
        }
        die;
    }
    
    /**
     * update administator's status
     * @return \Zend\Http\Response
     */
    public function updateStatusAction()
    {
        $this->checkLogin('admin_delete');
        $status = $this->params()->fromRoute('status');
        $id = $this->params()->fromRoute('id');
        if($status && $id)
        {
            if($id == 1){
                echo "<script>alert('超级管理员无法编辑;');history.back();</script>";
                exit;
            }
            $res = $this->getAdminTable()->updateData(array('status' => $status), array('id'=>$id));
            if($res){
                return $this->redirect()->toRoute('admin-config',array('action'=>'adminList'));
            }            
        }
        die;
    }
    
    /**
     * delete admin
     * @return \Zend\Http\Response
     */
    public function deleteAdminAction()
    {
        $this->checkLogin('admin_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            if($id == 1){
                echo "<script>alert('超级管理员无法删除;');history.back();</script>";
                exit;
            }
            $res = $this->getAdminTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-config',array('action'=>'adminList'));
            }
        }
        die;
    }
    
    /**
     * category list
     * @return \Zend\View\Model\ViewModel
     */
    public function adminCategoryAction()
    {
        $this->checkLogin('admin_category_list');
        $page = $this->params()->fromRoute('page');

        $admin_category_list = $this->getAdminCategoryTable()->getAll(array('delete' => DELETE_FALSE) ,null, array('id' => 'DESC'), true, $page, 10);
        $view=new ViewModel(array(
            'paginator' => $admin_category_list['paginator'],
            'condition' => array(
                'action' => 'adminCategory',
                'page'   => $page,
            ),
            'admin_category_list' => $admin_category_list['list'],
        ));
        $view->setTemplate('admin/config/admin_category');
        return $this->setMenu($view,1);
    }
    
    /**
     * category detail 
     * @return \Zend\View\Model\ViewModel
     */
    public function categoryOperateAction()
    {
        $this->checkLogin('admin_category_detail');
        $id  = (int)$this->params('id');
        
        $admin_type_info = array();
        if($id)
        {
            if($id == 1){
                echo "<script>alert('超级管理员拥有所有权限，无法编辑;');history.back();</script>";
                exit;
            }
            $admin_type_info = $this->getAdminCategoryTable()->getOne(array('id' => $id,'delete' => 0));	   
            $action_list = $this->getModuleTable()->fetchAll($admin_type_info['action_list']);
        }
        else 
        {
            $action_list = $this->getModuleTable()->fetchAll();
        }
        
        $view=new ViewModel(array(
            'admin_type_info' => $admin_type_info,
            'action_list' => $action_list,
            'id' => $id,
        ));
        $view->setTemplate('admin/config/category_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * add category
     */
    public function addCategoryAction()
    {
        $this->checkLogin('admin_category_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['name'] = addslashes($_POST['adminType']);
            $data['action_list'] = implode(',', $_POST['action_code']);
            $data['timestamp'] = $this->getTime();
            $data['delete'] = DELETE_FALSE;
            
            if(!$data['name'])
            {
                echo "<script>alert('请输入管理员类型姓名;');history.back();</script>";
                exit;
            }
            
            $exists_category = $this->getAdminCategoryTable()->getOne(array('name' => $data['name']));
            if($exists_category)
            {
                echo "<script>alert('已存在该管理员类型;');history.back();</script>";
                exit;
            }
            
            $data = $this->getAdminCategoryTable()->insertData($data);
            if($data){
                return $this->redirect()->toRoute('admin-config',array('action'=>'adminCategory'));
            }
        }
        die;
    }
    
    /**
     * edit category
     * @return \Zend\Http\Response
     */
    public function editCategoryAction()
    {
        $this->checkLogin('admin_category_add');
        $id = (int)$this->params()->fromPost('id');
        $submit = $this->params()->fromPost('submit');
        if($submit && $id){
            if($id == 1){
                echo "<script>alert('超级管理员拥有全部权限，无法编辑;');history.back();</script>";
                exit;
            }
            
            $data = array();
            $data['name'] = addslashes($_POST['adminType']);
            $data['action_list'] = implode(',', $_POST['action_code']);
        
            $data = $this->getAdminCategoryTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-config',array('action'=>'adminCategory'));
        }
        die;
    }
    
    /**
     * delete category
     * @return \Zend\Http\Response
     */
    public function deleteCategoryAction()
    {
        $this->checkLogin('admin_category_delete');
        $id = (int)$this->params()->fromRoute('id');
        if($id)
        {
            if($id == 1){
                echo "<script>alert('超级管理员无法删除;');history.back();</script>";
                exit;
            }
            $res = $this->getAdminCategoryTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-config',array('action'=>'adminCategory'));
            }
        }
        die;
    }
    
    /**
     * account list
     * @return \Zend\View\Model\ViewModel
     */
    public function accountAction()
    {
        $this->checkLogin('account_list');
        $page = $this->params()->fromRoute('page');

        $account_list = $this->getAccountListTable()->getAll(array('delete' => DELETE_FALSE) ,null, array('sort' => 'ASC' ,'id' => 'DESC'), true, $page, 10);
        $bank_list = $this->getBankListTable()->getBankList();       
        $view=new ViewModel(array(
            'paginator' => $account_list['paginator'],
            'condition' => array(
                'action' => 'account',
                'page'   => $page,
            ),
            'account_list' => $account_list['list'],
            'bank_list'      => $bank_list,
        ));
        $view->setTemplate('admin/config/account');
        return $this->setMenu($view,1);
    }
    
    /**
     * account detail
     * @return \Zend\View\Model\ViewModel
     */
    public function accountOperateAction()
    {
        $this->checkLogin('account_detail');
        
        $id = (int)$this->params()->fromRoute('id' , 0);
        $account_info = array();
        if($id)
        {
            $account_info = $this->getAccountListTable()->getOne(array('id' => $id , 'delete'=> DELETE_FALSE));
        }

        $bank_list = $this->getBankListTable()->fetchAll();
        
        $view=new ViewModel(array(
            'bank_list' => $bank_list,
            'account_info' => $account_info,
            'id' => $id,
        ));
        $view->setTemplate('admin/config/account_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * add account
     * @return \Zend\Http\Response
     */
    public function addAccountAction()
    {
        $this->checkLogin('account_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $bankId   = (int)$_POST['bankId'][0];
            $number = addslashes($_POST['number']);
            
            if(!$bankId)
            {
                echo "<script>alert('请选择银行;');history.back();</script>";
                exit ;
            }
            
            if( strlen($number) > 19 || strlen($number) < 16)
            {
                echo "<script>alert('帐号号码位数应在16到19位;');history.back();</script>";
                exit ;
            }
            
            $account_exists = $this->getAccountListTable()->getOne(array('bank_id' => $bankId , 'delete' => DELETE_FALSE));
            if($account_exists)
            {
                echo "<script>alert('收款账号不能出现相同的银行');history.back();</script>";
                exit ;
            }
            
            $data = array();
            $data['bank_id'] = $bankId;
            $data['number'] = $number;
            $data['name'] = $this->filterWords($_POST['name']);
            $data['sort'] = (int)$_POST['sort'];
            $data['branch'] = $this->filterWords($_POST['branch']);
            $data['timestamp'] = $this->getTime();
            $data['delete'] = DELETE_FALSE;
            
            $data = $this->getAccountListTable()->insertData($data);
            if($data){
                return $this->redirect()->toRoute('admin-config',array('action'=>'account'));
            }
        }
        die;
    }
    
    /**
     * edit account
     * @return \Zend\Http\Response
     */
    public function editAccountAction()
    {
        $this->checkLogin('account_add');
        $id = (int)$this->params()->fromPost('id');
        $submit = $this->params()->fromPost('submit');
        if($submit && $id){        
            $bankId   = (int)$_POST['bankId'][0];
            $number = addslashes($_POST['number']);
            
            if(!$bankId)
            {
                echo "<script>alert('请选择开户行;');history.back();</script>";
                exit ;
            }
            
            if( strlen($number) > 19 || strlen($number) < 16)
            {
                echo "<script>alert('帐号号码位数应在16到19位;');history.back();</script>";
                exit ;
            }
            
            $account_info = $this->getAccountListTable()->getOne(array('id' => $id));
            if($account_info->bank_id != $bankId )
            {
                $account_exists = $this->getAccountListTable()->getOne(array('bank_id' => $bankId , 'delete' => DELETE_FALSE));
                if($account_exists)
                {
                    echo "<script>alert('收款账号不能出现相同的银行');history.back();</script>";
                    exit ;
                }
            }
            
            $data = array();
            $data['bank_id'] = $bankId;
            $data['number'] = $number;
            $data['branch'] = $this->filterWords($_POST['branch']);
            $data['name'] = $this->filterWords($_POST['name']);
            $data['sort'] = (int)$_POST['sort'];
        
            $res = $this->getAccountListTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-config',array('action'=>'account'));
        }
        die;
    }
    
    /**
     * delete account
     * @return \Zend\Http\Response
     */    
    public function deleteAccountAction()
    {
        $this->checkLogin('account_delete');
        $id = (int)$this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getAccountListTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-config',array('action'=>'account'));
            }
        }
        die;
    }
    /**
     * timeNode
     * @return \Zend\Http\Response
     */
    public function timeNodeAction()
    {
        $this->checkLogin('time_node');
        $page = (int)$this->params()->fromRoute('page');  
        $where['delete'] = DELETE_FALSE;
        $list = $this->getTimeNodeTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10);
       $view=new ViewModel(array(
            'paginator' => $list['paginator'],
            'condition' => array(
                 'action' => 'adminList',
                 'page'   => $page,
             ),
            'list' => $list['list'],
        ));
        $view->setTemplate('admin/config/time_node');
        return $this->setMenu($view,1);
    }
    
    public function addTimeNodeAction()
    {
        $this->checkLogin('time_node_add');
        $id = (int)$this->params()->fromPost('id');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data['start_time'] = $_POST['start_time'];
            $data['deadline'] = $_POST['end_time'];
            $data['node'] = isset($_POST['time_node']) ? $_POST['time_node'] : '16:30';
            
            if (!$data['start_time'] || !$data['deadline'])
            {
                $this->showMessage('没有添加时间段');
            }

            $start_where = new Where();
            $start_where->lessThanOrEqualTo('start_time', $data['start_time']);
            $start_where->greaterThanOrEqualTo('deadline', $data['start_time']);
            $start_where->equalTo('delete', DELETE_FALSE);
            
            $end_where = new Where();
            $end_where->lessThanOrEqualTo('start_time', $data['deadline']);
            $end_where->greaterThanOrEqualTo('deadline', $data['deadline']);
            $end_where->equalTo('delete', DELETE_FALSE);
            
            if($id)
            {
                $start_where->notEqualTo('id', $id);
                $end_where->notEqualTo('id', $id);
            }
            
            $end_where->orPredicate($start_where);
            
            $exists = $this->getTimeNodeTable()->getOne($end_where);
            if($exists)
            {
                $this->showMessage('时间段不能有交集');
            }
            
            if($id)
            {
                $this->getTimeNodeTable()->updateData($data , array('id' => $id));
            }
            else 
            {
                $data['delete'] = DELETE_FALSE;
                $data['timestamp'] = $this->getTime();
                
                $this->getTimeNodeTable()->insert($data);
            }
            
            return $this->redirect()->toRoute('admin-config',array('action'=>'timeNode'));
        }
    }
    
    public function timeNodeOperateAction()
    {
        $this->checkLogin('time_node_detail');
        $id = (int)$this->params()->fromRoute('id');
        $info = $this->getTimeNodeTable()->getOne(array('id'=>$id));
        $view=new ViewModel(array(
            'info' => $info,
            'id' => $id
        ));
        $view->setTemplate('admin/config/time_node_operate');
        return $this->setMenu($view,1);
    }
    
    public function delTimeNodeAction()
    {
        $this->checkLogin('time_node_delete');
        $id = (int)$this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getTimeNodeTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-config',array('action'=>'timeNode'));
            }
        }
        die;
    }
    
    public function adminPasswordAction()
    {
        $this->checkLogin();
        if($_POST)
        {
            if($_POST['password'] != $_POST['comfirm_password'] )
            {
                echo "<script>alert('密码输入不一致;');history.back();</script>";
                exit ;
            }
            
            $admin_info = $this->getAdminTable()->getOne(array('id' => $_SESSION['admin_id'] , 'status' => 1 , 'delete' => DELETE_FALSE));
            if($admin_info['password'] != md5($_POST['old_password']))
            {
                echo "<script>alert('旧密码不正确;');history.back();</script>";
                exit ;
            }
            
            $res = $this->getAdminTable()->updateData(array('password' => md5($_POST['password'])), array('id' => $_SESSION['admin_id']));
            if($res){
                return $this->redirect()->toRoute('admin-index',array('action'=>'index'));
            }
        }
        $view=new ViewModel(array());
        $view->setTemplate('admin/config/admin_password');
        return $this->setMenu($view,1);
    }
    
    public function settingAction()
    {
        $setting_list = $this->getSetupTable()->fetchAll();
        
        $view=new ViewModel(array(
            'setting_list' => $setting_list,
        ));
        $view->setTemplate('admin/config/setting');
        return $this->setMenu($view,1);
    }
    
    /**
     * ajax, check existence of the category name
     */
    public function checkAction()
    {
        $where[addslashes($_POST['type'])] = addslashes($_POST['name']);
        $check = $this->getAdminCategoryTable()->getOne($where);
        if($check){
            echo 1;exit;
        }
        echo 0;exit;
    }
    
    /**
     * ajax,character's permission list
     */
    public function getPermissionListAction()
    {
        $id = addslashes((int)$_POST['id']);
        if($id == 1)
        {
            $action_list = $this->getModuleTable()->fetchAll('all');
        }
        else 
        {
            $where = array();
            $where['id'] = $id;
            $where['delete'] = DELETE_FALSE;
            $admin_type_info = $this->getAdminCategoryTable()->getOne($where);
            $action_list = $this->getModuleTable()->fetchAll($admin_type_info['action_list']);
        }
        echo json_encode($action_list);
        die;
    }
}
