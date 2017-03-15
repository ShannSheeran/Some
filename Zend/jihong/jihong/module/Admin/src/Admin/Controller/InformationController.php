<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class InformationController extends CommonController
{
    protected $platformId = 1;
    
    protected $FAQId = 2;
    
   // protected $company_intro = 3;
    
    protected $company_index_id = 3;
    
    //protected $support_protocol_id = 4;
    
    /**
     * 平台消息（默认方法）
     * @return \Zend\View\Model\ViewModel
     */
    public function platformInformationAction()
    {
        $this->checkLogin(); 
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->equalTo('article_category_id', $this->platformId); //1位平台消息
        $like = array();
        if($keyword)
        {
            $like['title'] = $keyword;
        }
        
        $information_list = $this->getArticleTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
        $view=new ViewModel(array(
            'paginator' => $information_list['paginator'],
            'condition' => array(
                 'action' => 'platformInformation',
                 'page'   => $page,
                 'keyword' => $keyword,
             ),
            'information_list' => $information_list['list'],
            'keyword' => $keyword,
        ));
        $view->setTemplate('admin/information/platform_information');
        return $this->setMenu($view,1);
    }
    
    /**
     * 平台消息操作
     * @return \Zend\View\Model\ViewModel
     */
    public function platformInformationOperateAction()
    {
        $this->checkLogin();
        $id = $this->params()->fromRoute('id');
        $information_info = array();
        if($id)
        {
            $information_info = $this->getArticleTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE));
        }
        $view=new ViewModel(array(
            'information_info' => $information_info,
            'id' => $id,
        ));
        $view->setTemplate('admin/information/platform_information_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加平台消息
     * @return \Zend\Http\Response
     */
    public function addPlatformInformationAction()
    {
        $this->checkLogin('platform_information_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['title']    = $this->filterWords($_POST['title']);
            $data['description']    = $this->filterWords($_POST['description']);
            $data['content']    = $_POST['content'];
            $data['app_content']    = $_POST['app_content'];
            $data['article_category_id']    = $this->platformId;
            $data['read_number'] = 0;
            $data['status'] = 1;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
        
            $res = $this->getArticleTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'platformInformation'));
            }
        }
        die;
    }
    
    /**
     * 编辑平台消息
     * @return \Zend\Http\Response
     */
    public function editPlatformInformationAction()
    {
        $this->checkLogin('platform_information_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id){
            $data = array();
            $data['title']    = $this->filterWords($_POST['title']);
            $data['description']    = $this->filterWords($_POST['description']);
            $data['content']    = $_POST['content'];
            $data['app_content']    = $_POST['app_content'];
        
            $res = $this->getArticleTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'platformInformation'));
        }
        die;
    }
    
    /**
     * 删除平台消息
     * @return \Zend\Http\Response
     */
    public function deletePlatformInformationAction()
    {
        $this->checkLogin('platform_information_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getArticleTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'platformInformation'));
            }
        }
        die;
    }
    
    /**
     * 资讯
     * @return \Zend\View\Model\ViewModel
     */
    public function informAction()
    {
        $this->checkLogin(); 
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $article_category_id     = isset($_GET['article_category_id']) ? $_GET['article_category_id'] : 0;
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->equalTo('status', 1);
        $where->notEqualTo('article_category_id', $this->platformId);
        $where->notEqualTo('article_category_id', $this->FAQId);
        if ($article_category_id)
        {
            $where->equalTo('article_category_id', $article_category_id);
        }
        $like = array();
        if($keyword)
        {
            $like['title'] = $keyword;
        }
        
        $information_list = $this->getArticleTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
        $column_list = $this->getArticleCategoryTable()->getArticleCategory();
        unset($column_list[$this->platformId]);
        unset($column_list[$this->FAQId]);
        $company_index_id = $this->company_index_id;
        
        $view=new ViewModel(array(
            'paginator' => $information_list['paginator'],
            'condition' => array(
                 'action' => 'inform',
                 'page'   => $page,
                'keyword' => $keyword,
                'where' => array(
                    'article_category_id' => $article_category_id,
                )
             ),
            'information_list' => $information_list['list'],
            'column_list' => $column_list,
            'keyword' => $keyword,
            'article_category_id' => $article_category_id,
            'company_index_id' => $company_index_id,
        ));
        $view->setTemplate('admin/information/inform');
        return $this->setMenu($view,1);
    }
    
    /**
     * 资讯操作
     * @return \Zend\View\Model\ViewModel
     */
    public function informationOperateAction()
    {
        $this->checkLogin();
        $column_list = $this->getArticleCategoryTable()->getArticleCategory();
        unset($column_list[$this->platformId]);
        unset($column_list[$this->FAQId]);
        $id = $this->params()->fromRoute('id');
        $information_info = array();
        $image = array();
        if($id)
        {
            $information_info = $this->getArticleTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE , 'status' => 1));
            if(isset($information_info->image_id))
            {
                $image = $this->getImageTable()->getOne(array('id'=>$information_info->image_id));
                $imagePath = $image['path'] . $image['filename'];
            }
        }
        $view=new ViewModel(array(
            'column_list' => $column_list,
            'information_info' => $information_info,
            'id' => $id,
            'image' => $image,
        ));
        $view->setTemplate('admin/information/information_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加资讯
     * @return \Zend\Http\Response
     */
    public function addInformationAction()
    {
        $this->checkLogin('information_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['article_category_id'] = $this->filterWords($_POST['column_id']);
            $data['title']    = $this->filterWords($_POST['title']);
            $data['content']    = $_POST['content'];
            $data['app_content']    = $_POST['app_content'];
            $data['image_id'] = $_POST['image'];
            $data['read_number'] = 0;
            $data['status'] = 1;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
        
            $res = $this->getArticleTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'inform'));
            }
        }
        die;
    }
    
    /**
     * 编辑资讯
     * @return \Zend\Http\Response
     */
    public function editInformationAction()
    {
        $this->checkLogin('information_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id){
            $data = array();
            $data['article_category_id'] = $this->filterWords($_POST['column_id']);
            $data['title']    = $this->filterWords($_POST['title']);
            $data['content']    = $_POST['content'];
            $data['app_content']    = $_POST['app_content'];
            $data['image_id'] = $_POST['image'];
            
            if($id == 1)
            {
                $data['article_category_id'] = $this->company_index_id;
            }
        
            $res = $this->getArticleTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'inform'));
        }
        die;
    }
    
    /**
     * 删除资讯
     * @return \Zend\Http\Response
     */
    public function deleteInformationAction()
    {
        $this->checkLogin('information_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getArticleTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'inform'));
            }
        }
        die;
    }
    
    /**
     * 广告管理
     * @return \Zend\View\Model\ViewModel
     */
    public function advertisementAction()
    {
        $this->checkLogin('advertise_list');
        $page = $this->params()->fromRoute('page');
        $id = $this->params()->fromRoute('id');
        
        $ads_list = $this->getViewAdsTable()->getAll(array('delete' =>DELETE_FALSE , 'position_id' =>$id) ,null, array( 'sort' => 'ASC' , 'id' => 'ASC'), true, $page, 10);
        $view=new ViewModel(array(
            'paginator' => $ads_list['paginator'],
            'condition' => array(
                'action' => 'advertisement',
                'page'   => $page,
            ),
            'ads_list' => $ads_list['list'],
            'id' => $id,
        ));
        $view->setTemplate('admin/information/advertisement');
        return $this->setMenu($view,1);
    }
    
    /**
     * 广告操作管理
     * @return \Zend\View\Model\ViewModel
     */
    public function advertisementOperateAction()
    {
        $this->checkLogin('advertise_detail');
        $ads_position_list = $this->getAdsPositionTable()->getAdsPosition();
        $id = $this->params()->fromRoute('id');
        $cid = $this->params()->fromRoute('cid');
        $advertisement_info = array();
        $image = array();
        if($id)
        {
            $advertisement_info = $this->getViewAdsTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE));
            if(isset($advertisement_info->image_id))
            {
                $image = $this->getImageTable()->getOne(array('id'=>$advertisement_info->image_id));
                $imagePath = $image['path'] . $image['filename'];
            }
        }
        $view=new ViewModel(array(
            'ads_position_list' => $ads_position_list,
            'advertisement_info' => $advertisement_info,
            'id' => $id,
            'image' => $image,
            'cid' => $cid,
        ));
        $view->setTemplate('admin/information/advertisement_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加广告
     * @return \Zend\Http\Response
     */
    public function addAdvertisementAction()
    {
        $this->checkLogin('advertise_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();

            $data['position_id'] = $this->filterWords($_POST['position_id']);
            $data['sort']    = (int)$this->filterWords($_POST['sort']);
            $data['image_id'] = $_POST['image'];
            $data['start_time']    = $_POST['start_time'];
            $data['end_time']    = $_POST['end_time'];
            $data['description']    = $_POST['description'];
            $data['admin_id']    =  $_SESSION['admin_id'];
            $data['admin_name']    =  $_SESSION['admin_name'];
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
            
            if($data['start_time'] > $data['end_time'])
            {
                echo "<script>alert('结束时间不能小于开始时间;');history.back();</script>";
                exit;
            }
            
            $res = $this->getAdsTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'advertisement' , 'id' => $data['position_id']));
            }
        }
        die;
    }
    
    /**
     * 编辑广告
     * @return \Zend\Http\Response
     */
    public function editAdvertisementAction()
    {
        $this->checkLogin('advertise_add');
        $submit = $this->params()->fromPost('submit');
        $id = $this->params()->fromPost('id');
        if($submit){
            $data = array();
        
            $data['position_id'] = $this->filterWords($_POST['position_id']);
            $data['sort']    = (int)$this->filterWords($_POST['sort']);
            $data['image_id'] = $_POST['image'];
            $data['start_time']    = $_POST['start_time'];
            $data['end_time']    = $_POST['end_time'];
            $data['description']    = $_POST['description'];
        
            if($data['start_time'] > $data['end_time'])
            {
                echo "<script>alert('结束时间不能小于开始时间;');history.back();</script>";
                exit;
            }
            
            $res = $this->getAdsTable()->updateData($data, array('id' =>$id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'advertisement'  , 'id' => $data['position_id']));
        }
        die;
    }
    
    /**
     * 删除广告
     * @return \Zend\Http\Response
     */
    public function deleteAdvertisementAction()
    {
        $this->checkLogin('advertise_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getAdsTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'advertisement'));
            }
        }
        die;
    }
    
    /**
     * 广告位
     */
    public function advertisementPositionAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
        
        $ads_position_list = $this->getAdsPositionTable()->getAll(array('delete' => DELETE_FALSE) ,null, array('id' => 'DESC'), true, $page, 10);
        $view=new ViewModel(array(
            'paginator' => $ads_position_list['paginator'],
            'condition' => array(
                'action' => 'advertisementPosition',
                'page'   => $page,
            ),
            'ads_position_list' => $ads_position_list['list'],
        ));
        $view->setTemplate('admin/information/advertisement_position');
        return $this->setMenu($view,1);
    }
    
    /**
     * 广告位操作
     * @return \Zend\View\Model\ViewModel
     */
    public function advertisementPositionOperateAction()
    {
        $this->checkLogin();
        $id = (int)$this->params()->fromRoute('id' , 0);
        $column_info = array();
        if($id)
        {
            $column_info = $this->getAdsPositionTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
        }
        $view=new ViewModel(array(
            'column_info' => $column_info,
            'id' => $id,
        ));
        $view->setTemplate('admin/information/advertisement_position_operate');
        return $this->setMenu($view,1);
    }

    /**
     * 添加广告位
     * @return \Zend\Http\Response
     */
    /* public function addAdvertisementPositionAction()
    {
        $this->checkLogin();
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['name'] = $this->filterWords($_POST['name']);
            $data['admin_id']    =  $_SESSION['admin_id'];
            $data['admin_name']    =  $_SESSION['admin_name'];
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
    
            $res = $this->getAdsPositionTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'advertisementPosition'));
            }
        }
        die;
    } */
    
   /**
     * 编辑广告位
     * @return \Zend\Http\Response
     */
      /*public function editAdvertisementPositionAction()
    {
        $this->checkLogin();
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id)
        {
            $data = array();
            $data['name'] = $this->filterWords($_POST['name']);
        
            $res = $this->getAdsPositionTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'advertisementPosition'));
        }
        die;
    } */

    /**
     * 删除广告位
     * @return \Zend\Http\Response
     */
    public function deleteAdvertisementPositionAction()
    {
        $this->checkLogin('advertise_position_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getAdsPositionTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'advertisementPosition'));
            }
        }
        die;
    }
    
    /**
     * 招聘
     * @return \Zend\View\Model\ViewModel
     */
    public function recruitAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $this->filterWords($_GET['keyword']) :  '' ;
        $status = isset( $_GET['status'] ) ? $_GET['status'] :  '' ;
        
        $where=array();
        $where['delete'] = DELETE_FALSE;
        if($status)
        {
            $where['status'] = $status;
        }
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        
        $recruit_list = $this->getJobTable()->getAll($where ,null, array( 'sort' => 'ASC' , 'id' => 'DESC'), true, $page, 10,$like);
        $education = $this->education();
        $yearsOfWorking = $this->yearsOfWorking();
        $view=new ViewModel(array(
            'paginator' => $recruit_list['paginator'],
            'condition' => array(
                'action' => 'recruit',
                'page'   => $page,
                'keyword' => $keyword,
                'status' => $status,
            ),
            'recruit_list' => $recruit_list['list'],
            'yearsOfWorking' => $yearsOfWorking,
            'education' => $education,
            'keyword' => $keyword,
            'status' => $status,
        ));
        $view->setTemplate('admin/information/recruit');
        return $this->setMenu($view,1);
    }

    /**
     * 招聘操作
     * @return \Zend\View\Model\ViewModel
     */
    public function recruitOperateAction()
    {
        $this->checkLogin();
        $id = (int)$this->params()->fromRoute('id' , 0);
        
        $education = $this->education();
        $yearsOfWorking = $this->yearsOfWorking();
        
        $recruit_info = array();
        $address_info= array();
        if($id)
        {
            $recruit_info = $this->getJobTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
            $address_info = $this->decode($recruit_info->region_info);
        }
        $view=new ViewModel(array(
            'recruit_info' => $recruit_info,
            'id' => $id,
            'yearsOfWorking' => $yearsOfWorking,
            'education' => $education,
            'address_info' => $address_info
        ));
        $view->setTemplate('admin/information/recruit_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加招聘
     */
    public function addRecruitAction()
    {
        $this->checkLogin('recruit_add');
        $submit = $this->params()->fromPost('submit');
        if($submit)
        {
            $data = array();
            $data['name'] = $this->filterWords($_POST['name']);
            $data['education'] = $this->filterWords($_POST['education']);
            $data['work_limit'] = $this->filterWords($_POST['work_limit']);
            $data['sort'] = $this->filterWords($_POST['sort']);
            $data['description'] = $_POST['description'];
            $data['region_info'] = $this->encode($_POST['county'],$_POST['city'],$_POST['province']);
            $data['region_id'] = $_POST['county'] ?  $_POST['county'] : $_POST['city'];
            $data['street'] = $this->filterWords($_POST['street']);
            $address = $this->getProvinceCityCountryName($data['region_info']);
            $data['address'] =$address.$_POST['street'];
            $data['status'] = 1;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();

            $res = $this->getJobTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'recruit'));
            }
        }
        die;
    }
    
    /**
     * 编辑招聘
     * @return \Zend\Http\Response
     */
    public function editRecruitAction()
    {
        $this->checkLogin('recruit_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id)
        {
            $data = array();
            $data['name'] = $this->filterWords($_POST['name']);
            $data['education'] = $this->filterWords($_POST['education']);
            $data['work_limit'] = $this->filterWords($_POST['work_limit']);
            $data['sort'] = $this->filterWords($_POST['sort']);
            $data['description'] = $_POST['description'];
            $data['region_info'] = $this->encode($_POST['county'],$_POST['city'],$_POST['province']);
            $data['region_id'] = $_POST['county'] ?  $_POST['county'] : $_POST['city'];
            $data['street'] = $this->filterWords($_POST['street']);
            $address = $this->getProvinceCityCountryName($data['region_info']);
            $data['address'] =$address.$_POST['street'];
        
            $res = $this->getJobTable()->updateData($data , array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'recruit'));
        }
        die;
    }
    
    public function updateRecruitStatusAction()
    {
        $this->checkLogin('recruit_delete');
        $status = $this->params()->fromRoute('status');
        $id = $this->params()->fromRoute('id');
        if($status && $id)
        {
            $res = $this->getJobTable()->updateData(array('status' => $status), array('id'=>$id));
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'recruit'));
            }
        }
        die;
    }

    public function deleteRecruitAction()
    {
        $this->checkLogin('recruit_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getJobTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'recruit'));
            }
        }
        die;
    }
    
    /**
     * 栏目
     * @return \Zend\View\Model\ViewModel
     */
    public function columnAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        /* $where->notEqualTo('id', $this->platformId);
        $where->notEqualTo('id', $this->FAQId);
        $where->notEqualTo('id', $this->company_index_id);  */
        $no_edit_column = array($this->platformId , $this->FAQId ,$this->company_index_id);
        if ($status)
        {
            $where->equalTo('status', $status);
        }
 
        $column_list = $this->getArticleCategoryTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10);
        $view=new ViewModel(array(
            'paginator' => $column_list['paginator'],
            'condition' => array(
                'action' => 'column',
                'page'   => $page,
                'status'  => $status,
            ),
            'column_list' => $column_list['list'],
            'status'  => $status,
            'no_edit_column'  => $no_edit_column,
        ));
        $view->setTemplate('admin/information/column');
        return $this->setMenu($view,1);
    }
    
    /**
     * 栏目操作
     * @return \Zend\View\Model\ViewModel
     */
    public function columnOperateAction()
    {
        $this->checkLogin();
        $id = (int)$this->params()->fromRoute('id' , 0);

        if(in_array($id, array($this->platformId , $this->FAQId , $this->company_index_id)))
        {
            echo '<script>alert("无法操作该栏目");history.back();</script>';
            die;
        }
        
        $column_info = array();
        if($id)
        {
            $column_info = $this->getArticleCategoryTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
        }
        $view=new ViewModel(array(
            'column_info' => $column_info,
            'id' => $id,
        ));
        $view->setTemplate('admin/information/column_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加栏目
     * @return \Zend\Http\Response
     */
    public function addColumnAction()
    {
        $this->checkLogin('column_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['name'] = $this->filterWords($_POST['name']);
            $data['sort']    = (int)$this->filterWords($_POST['sort']);
            $data['status'] = 1;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();

            $res = $this->getArticleCategoryTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'column'));
            }
        }
        die;
    }
    
    /**
     * 编辑栏目
     */
    public function editColumnAction()
    {
        $this->checkLogin('column_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id)
        {       
            $data = array();
            $data['name'] = $this->filterWords($_POST['name']);
            $data['sort']    = (int)$this->filterWords($_POST['sort']);

            $res = $this->getArticleCategoryTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'column'));
        }
        die;
    }
    
    /**
     * 更新栏目状态
     * @return \Zend\Http\Response
     */
    public function updateStatusAction()
    {
        $this->checkLogin('column_delete');
        $status = $this->params()->fromRoute('status');
        $id = $this->params()->fromRoute('id');
        if($status && $id)
        {
            $res = $this->getArticleCategoryTable()->updateData(array('status' => $status), array('id'=>$id));
            $this->getArticleTable()->updateData(array('status' => $status), array('article_category_id'=>$id));
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'column'));
            }
        }
        die;
    }
    
    /**
     * 删除栏目
     */
    public function deleteColumnAction()
    {
        $this->checkLogin('column_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getArticleCategoryTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'column'));
            }
        }
        die;
    }
    
    public function frequentlyAskedQuestionsAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->equalTo('article_category_id', $this->FAQId); //2为常见问题
        $like = array();
        if($keyword)
        {
            $like['title'] = $keyword;
        }
        
        $information_list = $this->getArticleTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
        $view=new ViewModel(array(
            'paginator' => $information_list['paginator'],
            'condition' => array(
                'action' => 'frequentlyAskedQuestions',
                'page'   => $page,
                'keyword' => $keyword,
            ),
            'information_list' => $information_list['list'],
            'keyword' => $keyword,
        ));
        $view->setTemplate('admin/information/frequently_asked_questions');
        return $this->setMenu($view,1);
    }
    
    public function questionOperateAction()
    {
        $this->checkLogin();
        $id = $this->params()->fromRoute('id');
        $information_info = array();
        $image = array();
        if($id)
        {
            $information_info = $this->getArticleTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE));
            if(isset($information_info->image_id))
            {
                $image = $this->getImageTable()->getOne(array('id'=>$information_info->image_id));
                $imagePath = $image['path'] . $image['filename'];
            }
        }
        $view=new ViewModel(array(
            'information_info' => $information_info,
            'id' => $id,
            'image' => $image,
        ));
        $view->setTemplate('admin/information/question_operate');
        return $this->setMenu($view,1);
    }
    
    public function addQuestionAction()
    {
        $this->checkLogin('FAQ_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['title']    = $this->filterWords($_POST['title']);
            $data['description']    = $this->filterWords($_POST['description']);
            $data['content']    = $_POST['content'];
            $data['app_content']    = $_POST['app_content'];
            $data['image_id']    = $_POST['image'];
            $data['article_category_id']    = $this->FAQId;
            $data['read_number'] = 0;
            $data['status'] = 1;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
    
            $res = $this->getArticleTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'frequentlyAskedQuestions'));
            }
        }
        die;
    }
    
    public function editQuestionAction()
    {
        $this->checkLogin('FAQ_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id){
            $data = array();
            $data['title']    = $this->filterWords($_POST['title']);
            $data['description']    = $this->filterWords($_POST['description']);
            $data['content']    = $_POST['content'];
            $data['app_content']    = $_POST['app_content'];
            $data['image_id']    = $_POST['image'];
    
            $res = $this->getArticleTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'frequentlyAskedQuestions'));
        }
        die;
    }

    public function deleteQuestionAction()
    {
        $this->checkLogin('FAQ_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getArticleTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'frequentlyAskedQuestions'));
            }
        }
        die;
    }

    public function departmentAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $where = array();
        $where['delete'] = DELETE_FALSE;
        if($status)
        {
            $where['status'] = $status;
        }
        
        $department_list = $this->getDepartmentTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10);
        $view=new ViewModel(array(
            'paginator' => $department_list['paginator'],
            'condition' => array(
                'action' => 'department',
                'page'   => $page,
                'status' => $status,
            ),
            'department_list' => $department_list['list'],
            'status' => $status,
        ));
        $view->setTemplate('admin/information/department');
        return $this->setMenu($view,1);
    }
    
    public function departmentOperateAction()
    {
        $this->checkLogin();
        $id = $this->params()->fromRoute('id');
        $department_info = array();
        if($id)
        {
            $department_info = $this->getDepartmentTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE));
        }
        $view=new ViewModel(array(
            'department_info' => $department_info,
            'id' => $id,
        ));
        $view->setTemplate('admin/information/department_operate');
        return $this->setMenu($view,1);
    }
    
    public function addDepartmentAction()
    {
        $this->checkLogin('department_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['name']    = $this->filterWords($_POST['name']);
            $data['sort']    = $this->filterWords($_POST['sort']);
            $data['status'] = 0;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
        
            $res = $this->getDepartmentTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'department'));
            }
        }
        die;
    }
    
    public function editDepartmentAction()
    {
        $this->checkLogin('department_add');
        $submit = $this->params()->fromPost('submit');
        $id = (int)$this->params()->fromPost('id');
        if($submit && $id){
            $data = array();
            $data['name']    = $this->filterWords($_POST['name']);
            $data['sort']    = $this->filterWords($_POST['sort']);
    
            $res = $this->getDepartmentTable()->updateData($data, array('id' => $id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'department'));
        }
        die;
    }
    
    public function deleteDepartmentAction()
    {
        $this->checkLogin('department_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getDepartmentTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'department'));
            }
        }
        die;
    }
    
    public function editDepartmentStatusAction()
    {
        $this->checkLogin('department_delete');
        $status = $this->params()->fromRoute('status');
        $id = $this->params()->fromRoute('id');
        if(in_array($status, array(1,2)) && $id)
        {
            $res = $this->getDepartmentTable()->updateData(array('status' => $status), array('id'=>$id));
            $this->getStaffTable()->updateData(array('status' => $status), array('department_id'=>$id));
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'department'));
            }
        }
        die;
    }
    
    public function jhTeamAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
        $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : '';
        
        $where = array();
        $where['delete'] = DELETE_FALSE;
        $where['status'] = 1;
        if($department_id)
        {
            $where['department_id'] = $department_id;
        }
        
        $staff_list = $this->getViewStaffTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10);
        $list = $this->getDepartmentTable()->fetchAll(array('delete' => DELETE_FALSE));
        $department_list = array();
        foreach ($list as $value)
        {
            $department_list[$value->id] = $value;
        }
        
        $view=new ViewModel(array(
            'paginator' => $staff_list['paginator'],
            'condition' => array(
                'action' => 'jhTeam',
                'page'   => $page,
            ),
            'staff_list' => $staff_list['list'],
            'department_list' => $department_list,
            'department_id' => $department_id,
        ));
        $view->setTemplate('admin/information/jh_team');
        return $this->setMenu($view,1);
    }
    
    public function jhTeamOperateAction()
    {
        $this->checkLogin();
        $id = $this->params()->fromRoute('id');
        $team_info = array();
        $image = array();
        $department_list = $this->getDepartmentTable()->fetchAll(array('delete' => DELETE_FALSE));
        if($id)
        {
            $team_info = $this->getStaffTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE));
            if(isset($team_info->image_id))
            {
                $image = $this->getImageTable()->getOne(array('id'=>$team_info->image_id));
                $imagePath = $image['path'] . $image['filename'];
            }
        }
        $view=new ViewModel(array(
            'team_info' => $team_info,
            'department_list' => $department_list,
            'id' => $id,
            'image' => $image,
        ));
        $view->setTemplate('admin/information/jh_team_operate');
        return $this->setMenu($view,1);
    }
    
    public function AddJhTeamAction()
    {
        $this->checkLogin('team_memeber_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['name']    = $this->filterWords($_POST['name']);
            $data['department_id'] = $_POST['department_id'];
            $data['position'] = $this->filterWords($_POST['position']);
            $data['description'] = $this->filterWords($_POST['description']);
            $data['sort'] = $this->filterWords($_POST['sort']);
            $data['image_id'] = $_POST['image'];
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
        
            $res = $this->getStaffTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'jhTeam'));
            }
        }
        die;
    }
    
    public function editJhTeamAction()
    {
        $this->checkLogin('team_memeber_add');
        $submit = $this->params()->fromPost('submit');
        $id = $this->params()->fromPost('id');
        if($submit){
            $data = array();
            $data['name']    = $this->filterWords($_POST['name']);
            $data['department_id'] = $_POST['department_id'];
            $data['position'] = $this->filterWords($_POST['position']);
            $data['description'] = $this->filterWords($_POST['description']);
            $data['sort'] = $this->filterWords($_POST['sort']);
            $data['image_id'] = $_POST['image'];
            
            $res = $this->getStaffTable()->updateData($data, array('id' =>$id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'jhTeam'));
        }
        die;
    }

    public function deleteStaffAction()
    {
        $this->checkLogin('team_memeber_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getStaffTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'jhTeam'));
            }
        }
        die;
    }

    public function CoProducerAction()
    {
        $this->checkLogin();
        $page = $this->params()->fromRoute('page');
    
        $production_list = $this->getProductionTable()->getAll(array('delete' => DELETE_FALSE) ,null, array( 'sort'=> 'ASC' , 'id' => 'DESC'), true, $page, 10);
    
        $view=new ViewModel(array(
            'paginator' => $production_list['paginator'],
            'condition' => array(
                'action' => 'CoProducer',
                'page'   => $page,
            ),
            'production_list' => $production_list['list'],
        ));
        $view->setTemplate('admin/information/co_producer');
        return $this->setMenu($view,1);
    }
    
    public function producerOperateAction()
    {
        $this->checkLogin();
        $id = $this->params()->fromRoute('id');
        $producer_info = array();
        $image = array();
        if($id)
        {
            $producer_info = $this->getProductionTable()->getOne(array('id' => $id , 'delete'=>DELETE_FALSE));
            if(isset($producer_info->image))
            {
                $image = $this->getImageTable()->getOne(array('id'=>$producer_info->image));
                $imagePath = $image['path'] . $image['filename'];
            }
        }
        $view=new ViewModel(array(
            'producer_info' => $producer_info,
            'id' => $id,
            'image' => $image,
        ));
        $view->setTemplate('admin/information/producer_operate');
        return $this->setMenu($view,1);
    }
    
    public function addProducerAction()
    {
        $this->checkLogin('producer_add');
        $submit = $this->params()->fromPost('submit');
        if($submit){
            $data = array();
            $data['name']    = $this->filterWords($_POST['name']);
            $data['sort'] = $this->filterWords($_POST['sort']);
            $data['image'] = $_POST['image'];
            $data['content'] = $_POST['content'];
            $data['description'] = $this->filterWords($_POST['description']);
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
        
            $res = $this->getProductionTable()->insertData($data);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'CoProducer'));
            }
        }
        die;
    }
    
    public function editProducerAction()
    {
        $this->checkLogin('producer_add');
        $submit = $this->params()->fromPost('submit');
        $id = $this->params()->fromPost('id');
        if($submit){
            $data = array();
            $data['name']    = $this->filterWords($_POST['name']);
            $data['sort'] = $this->filterWords($_POST['sort']);
            $data['description'] = $this->filterWords($_POST['description']);
            $data['image'] = $_POST['image'];
            $data['content'] = $_POST['content'];
        
            $res = $this->getProductionTable()->updateData($data, array('id' =>$id));
            return $this->redirect()->toRoute('admin-information',array('action'=>'CoProducer'));
        }
        die;
    }
    
    public function deleteProducerAction()
    {
        $this->checkLogin('producer_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $res = $this->getProductionTable()->deleteData($id);
            if($res){
                return $this->redirect()->toRoute('admin-information',array('action'=>'CoProducer'));
            }
        }
        die;
    }
}
