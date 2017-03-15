<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\AiiPush\AiiPush;
use Core\System\AiiPush\AiiMyFile;

class UserController extends CommonController
{
    /* !CodeTemplates.overridecomment.nonjd!
     * 会员列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->checkLogin('member_list');
        $page = $this->params()->fromRoute('page');
        $type  = $this->params()->fromRoute('type') ? trim($this->params()->fromRoute('type')) : '';
        $type = isset($_GET['type']) ? trim($_GET['type']) : $type;

        $level = $this->params()->fromRoute('level') ? trim($this->params()->fromRoute('level')) : '';
        $level = isset($_GET['level']) ? trim($_GET['level']) : $level;
        
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $keyword;
        
        $param_array = "";
        if(!empty($type) && !empty($level)){
            $param_array = array('delete' => DELETE_FALSE,'type'=>$type,'user_level'=>$level,'register_status' => 3 );
        }elseif(!empty($type) && empty($level)){
            $param_array = array('delete' => DELETE_FALSE,'type'=>$type ,'register_status' => 3);
        }elseif(empty($type) && !empty($level)){
            $param_array = array('delete' => DELETE_FALSE,'user_level'=>$level  ,'register_status' => 3);
        }else{
            $param_array = array('delete' => DELETE_FALSE  ,'register_status' => 3);
        }

        $user_list = $this->getUserTable()->getAll($param_array ,null, array('id' => 'DESC'), true, $page, 10,array("company_name"=>$keyword));
        $user_info_list = $user_list['list'];
        if(!empty($user_info_list)){
            $user_info_final = "";
            foreach ($user_info_list as $k => $user){
                if(!empty($user['region_info'])){
                    $region_info = $this->getProvinceCityCountryName($user['region_info']);
                    $user['region_info']=$region_info;
                    $user_info_final[$k] = $user;
                }
            }
        }
        $view=new ViewModel(array(
            'users' => $user_info_final,
            'paginator' => $user_list['paginator'],
            'user_level' => $this->userLevel(),
            'enterpris_type' => $this->enterprisType(),
            'condition' => array(
                 'action' => $this->action,
                 'type'    => $type,
                 'level' => $level,
                 'page'   => $page,
                 'keyword' => $keyword,
                 'where' => array(),
             ),
            'type' => $type,
            'keyword' => $keyword,
            'level' => $level,
        ));
        $view->setTemplate('admin/user/index');
        return $this->setMenu($view,1);
    }
    
    /**
     * 展示会员详情
     * @return \Zend\View\Model\ViewModel
     */
    public function detailAction()
    {
        $this->checkLogin('member_detail');
        $id = $this->params()->fromRoute('id');
        $user_info = $this->getViewUserTable()->getOne(array('id'=>$id));
        $user_label = $this->getUserLabelTable()->fetchAll(array('delete'=>0));
        $admin_list = $this->getAdminTable()->fetchAll(array('delete'=>0));
        $address = $this->decode($user_info->region_info);
//         $this->dump($address);exit;
        $view=new ViewModel(array(
            'user_info'=>$user_info,
            'admin_list' => $admin_list,
            'user_label' => $user_label,
            'enterpris_type' => $this->enterprisType(),
            'user_level' => $this->userLevel(),
            'address' => $address
        ));
        $view->setTemplate('admin/user/detail');
        return $this->setMenu($view,1);
    }

    /**
     * 增加会员和编辑会员操作
     * @return \Zend\Http\Response
     */
    public function addUserAction()
    {
        $this->checkLogin('member_edit');
        $data = array();
        if (isset($_POST['submit'])) {
            foreach ($_POST as $k => $v) {
                $data[$k] = $v;
            }
        }
        $id = $data['id'];
        $data['region_id'] = $data['county'] ? $data['county'] : $data['city'];
        $data['region_info'] = $this->encode($data['county'], $data['city'], $data['province']);
        //$password = $data['password'];
        //$data['password'] = md5($password);
        if(isset($data['password'])){ unset($data['password']); }
        if(isset($data['name'])){ unset($data['name']); }
        unset($data['county'], $data['city'], $data['province'], $data['submit']);
        if (! $id) 
        {
            $this->getUserTable()->insertData($data);
        } 
        else
        {
            unset($data['id']);
            $this->getUserTable()->updateData($data, array(
                'id' => $id
            ));
        }
        
         return $this->redirect()->toRoute('admin-user',array('action'=>'index'));
    }
    
    /**
     * 停用和启用操作
     */
    public function stopOrStartUserAction(){
        $this->checkLogin('member_status_edit');
        $param = $_POST;
        if($param['op'] == 'stop'){
            $res = $this->getUserTable()->updateData(array('status'=>'2'), array('id'=>$param['id']));
            if($res){
                die(json_encode(array('code'=>'1')));
            }else{
                die(json_encode(array('code'=>'0')));
            }
        }elseif($param['op'] == 'start'){
            $res = $this->getUserTable()->updateData(array('status'=>'1'), array('id'=>$param['id']));
            if($res){
                die(json_encode(array('code'=>'1')));
            }else{
                die(json_encode(array('code'=>'0')));
            }
        }
    }
    
    /**
     * 会员类型列表
     * @return multitype:
     */
    public function categoryAction()
    {
        $this->checkLogin('member_category_list');
        $this->table = $this->getUserLabelTable();
        $this->action = 'category';
        $this->template = array(0=>'user/category');
        $this->order = array('id desc');
        return $this->getList();
    }
    
    /**
     * 会员类型编辑 和添加
     * @return \Zend\View\Model\ViewModel
     */
    public function categoryOperateAction()
    {
        $this->checkLogin('member_category_detail');
        $id = $this->params()->fromRoute('id');
        if($id){
            $userLabel = $this->getUserLabelTable()->getOne(array('id'=>$id));
        }
        $view=new ViewModel(array(
            'userLabel'=>$userLabel,
        ));
        $view->setTemplate('admin/user/category_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加和更新会员类型操作
     * @return \Zend\Http\Response
     */
    public function addCategoryAction(){
        $this->checkLogin('member_category_add');
        $data = array();
        if(isset($_POST['submit'])){
            foreach ($_POST as $k=>$v){
                $data[$k] = $v;
            }
        }
        $id = $data['id'];
        unset($data['submit']);
        if(!$id){//插入
            $data['timestamp'] = date("Y-m-d H:i:s");
            $this->getUserLabelTable()->insertData($data);
        }else{//更新
            unset($data['id']);
            $data['timestamp_update'] = date("Y-m-d H:i:s");
            $this->getUserLabelTable()->updateData($data,array('id'=>$id));
        }
        return $this->redirect()->toRoute('admin-user',array('action'=>'category'));
    }
    
    /**
     * 删除会员类型
     * @return \Zend\Http\Response
     */
    public function delCategoryAction(){
        $this->checkLogin('member_category_delete');
        $id = $this->params()->fromRoute('id');
        if($id){
            $this->getUserLabelTable()->deleteData($id);
            return $this->redirect()->toRoute('admin-user',array('action'=>'category'));
        }else{
            return $this->showMessage("参数错误！");
        }
    }
    
    /**
     * 会员申请列表
     * @return \Zend\View\Model\ViewModel
     */
    public function applicationListAction()
    {
        $this->checkLogin('member_application_list');
        $page = $this->params()->fromRoute('page');
        
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword']:$this->params()->fromRoute('keyword','');
        $type = isset($_GET['type']) ? $_GET['type']:$this->params()->fromRoute('type',0);
        $status = isset($_GET['status']) ? $_GET['status']:$this->params()->fromRoute('status',0);
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($start_time && $start_time > $end_time)
            {
                $end_time = $start_time;
            }
            $where->lessThanOrEqualTo('timestamp', $end_time);
        }
        if($type)
        {
            $where->equalTo('type', $type);
        }
        if($status)
        {
            $where->equalTo('register_status', $status);
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
        }
        
        $enterpris_type = $this->enterprisType();
        $user_audit_status = $this->userAuditStatus();
        $user_list = $this->getViewUserTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
        $view=new ViewModel(array(
            'paginator' => $user_list['paginator'],
            'condition' => array(
                'action' => 'applicationList',
                'page'   => $page,
                'keyword'   => $keyword,
                'type'   => $type,
                'status'   => $status,
                'where' => array(
                    'start_time'   => $start_time,
                    'end_time'   => $end_time,
                ),
            ),
            'user_list' => $user_list['list'],
            'enterpris_type' => $enterpris_type,
            'user_audit_status' => $user_audit_status,
            'keyword'   => $keyword,
            'type'   => $type,
            'status'   => $status,
            'start_time'   => $start_time,
            'end_time'   => $end_time,
        ));
        $view->setTemplate('admin/user/application_list');
        return $this->setMenu($view,1);
    }
    
    /**
     * 申请资料
     * @return \Zend\View\Model\ViewModel
     */
    public function applicationInformationAction()
    {
        $this->checkLogin('member_application_detail');
        $id = $this->params()->fromRoute('id' ,0);
        if($id)
        {
            $enterpris_type = $this->enterprisType();
            $user_info = $this->getUserTable()->getOne(array('id' => $id, 'delete' => DELETE_FALSE));
            $address_info = $this->decode($user_info->region_info);
            $admin_list = $this->getAdminTable()->fetchAll(array('status'=>1 , 'delete'=>DELETE_FALSE));
            $user_label = $this->getUserLabelTable()->fetchAll(array('delete' => DELETE_FALSE));
        }
        else 
        {
            die;    
        }
        
        $view=new ViewModel(array(
            'id' => $id,
            'user_info' => $user_info,
            'enterpris_type' => $enterpris_type,
            'address_info' => $address_info,
            'admin_list' => $admin_list,
            'user_label' => $user_label,
        ));
        $view->setTemplate('admin/user/application_information');
        return $this->setMenu($view,1);
    }
    
    /**
     * ajax 请求管理员的联系方式
     */
    public function getAdminContactsAction()
    {
        $id = $this->params()->fromPost('id' , 0);
        $admin_info = $this->getAdminTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE , 'status' => 1));
        echo json_encode($admin_info);
        die;
    }
    
    /**
     * 处理会员申请资料
     */
    public function dealUserApplicationAction()
    {
        $this->checkLogin('member_application_review');
        $id = $this->params()->fromPost('id');
        $pass = $this->params()->fromPost('pass');
        $save = $this->params()->fromPost('save');
        $nopass = $this->params()->fromPost('nopass');
        
        $data = array();
        $data['company_name'] = trim($_POST['company_name']);
        $data['type'] = $_POST['type'];
        $data['contacts_name'] = trim($_POST['contacts_name']);
        $data['mobile']= $mobile =  trim($_POST['mobile']);
        $data['fax']= trim($_POST['fax']);
        $data['qq']= trim($_POST['qq']);
        $data['email']= trim($_POST['email']);
        $data['region_info'] = $this->encode('',$_POST['city'],$_POST['province']);
        $data['region_id'] = $_POST['city'] ?  $_POST['city'] : $_POST['province'];
        $data['street'] = $this->filterWords($_POST['street']);
        $address = $this->getProvinceCityCountryName($data['region_info']);
        $data['address'] =$address.$_POST['street'];
        $data['description'] =$_POST['description'];
        
        if($pass)
        {
            $data['name'] = trim($_POST['name']);
            $_POST['password'] = trim($_POST['password']);
            $data['password'] = md5($_POST['password']);
            $data['label_id'] = $_POST['label_id'];
            $data['admin_id'] = $_POST['admin_id'];
            $data['register_status'] = 3;
            $data['auditor'] = $_SESSION['admin_name'];
            $data['audit_time'] = $this->getTime();
            
            if(!preg_match('/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/', $data['mobile']) )
            {
                echo "<script>alert('手机号码不正确');history.back();</script>";
                exit;
            }
            
            if(!preg_match('/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/', $data['email']) )
            {
                echo "<script>alert('邮箱不正确');history.back();</script>";
                exit;
            }
            
            if(!$_POST['name'] || !$_POST['password'])
            {
                echo "<script>alert('没有给该会员账号和密码');history.back();</script>";
                exit;
            }
            
            $exists_name = $this->getUserTable()->getOne(array('name' => $data['name'] ));
            if($exists_name)
            {
                echo "<script>alert('账号已存在');history.back();</script>";
                exit;
            }
            
            if(preg_match('/[\x{4e00}-\x{9fa5}]+/u', $data['name'] ) )
            {
                echo "<script>alert('会员账号不能有中文');history.back();</script>";
                exit;
            }
            
            if( strlen($_POST['password']) < 6 || strlen($_POST['password']) > 20)
            {
                echo "<script>alert('密码长度不对');history.back();</script>";
                exit;
            }
            
            if($_POST['password'] != $_POST['confirm_password'])
            {
                echo "<script>alert('输入的密码不一致');history.back();</script>";
                exit;
            }
            
            if(!$_POST['admin_id'])
            {
                echo "<script>alert('没有为该会员绑定业务员');history.back();</script>";
                exit;
            }
            
            $res = $this->getUserTable()->updateData($data, array('id' => $id));
            
            //$content = '你的审核通过';
            //$this->smsPush($content, $mobile);
            return $this->redirect()->toRoute('admin-user',array('action'=>'applicationInformation' , 'id' => $id));
        }
        
        if($save)
        {
            $data['label_id'] = $_POST['label_id'];
            $data['admin_id'] = $_POST['admin_id'];
            $data['register_status'] = 2;
            
            $res = $this->getUserTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-user',array('action'=>'applicationInformation' , 'id' => $id));
        }
        
        if($nopass)
        {
            if(!$_POST['refuse_content'])
            {
                echo "<script>alert('请填写审核不通过理由');history.back();</script>";
                exit;
            }
            $data = array();
            $data['register_status'] = 4;
            $data['auditor'] = $_SESSION['admin_name'];
            $data['refuse_reason'] = $_POST['refuse_content'];
            $data['audit_time'] = $this->getTime();
            $res = $this->getUserTable()->updateData($data, array('id' => $id));
            
            //$content = '你的审核不通过'; 
            //$this->smsPush($content, $mobile);
            
            return $this->redirect()->toRoute('admin-user',array('action'=>'applicationInformation' , 'id' => $id));
        }
    }
    
    /**
     * 短信通知
     *
     * @author WZ
     * @param unknown $content
     * @param array $mobile
     * @return multitype:boolean
     */
    public function smsPush($content, array $mobile)
    {
        $push = new AiiPush();
        $return = array();
        foreach ($mobile as $m)
        {
            if (SMSCODE_SWITCH)
            {
                if ($m)
                {
                    $result = array('success' => array(1));
                    $result = $push->pushSingleDevice($m, 8, $content);
                    $return[] = $result['success'] ? true : false;
                }
                else
                {
                    $return[] = false;
                }
            }
            else
            {
                $return[] = true;
            }
            if (PUSH_LOG_SWITCH)
            { // 开启了推送与短信的日志记录
                if (isset($result))
                {
                    if ($result)
                    {
                        $temp = '短信，短信发送成功， mobile：' . $m . '，content：' . $content;
                    }
                    else
                    {
                        $temp = '短信，短信发送失败不能进行验证， mobile：' . $m . '，content：' . $content;
                    }
                }
                else
                {
                    $temp = '短信，没有开启短信发送，mobile：' . $m . '，content：' . $content;
                }
                if(! isset($file))
                {
                    $file = new AiiMyFile();
                }
                $file->setFileToPublicLog();
                $file->putAtStart($temp);
            }
        }
        return $return;
    }
}
