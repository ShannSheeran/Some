<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\City;
use Core\System\UploadfileApi;
use Core\System\WxApi\WxApi;





class ActivityController extends CommonController
{

    /**
     * 鍚嶇墖鍒楄〃
     *
     * @return multitype:
     * @version 2014-12-29   
     */
   /* public function index1Action()
    {
	    $this->checkLogin();
		$cache=new City();
        $this->table = $this->getViewActivityTable();
        $this->seach = array(
            'mobile',
            'id',
            'name'
        );
        $this->template = array(
            'activity/index',
            'user'
        );
		 $this->delete=true;

		if($_POST)
		{
			 //推荐
			 $is_top=isset($_POST['recommend'])?$_POST['recommend']:''; 
			 //行业分类
			 $category=isset($_POST['category'])?$_POST['category']:''; 
			 //规模
			 $scale=isset($_POST['scale'])?$_POST['scale']:''; 
			//城市
			 $c_name=isset($_POST['city'])?$_POST['city']:'';
			 $where_array = array();
		  if($is_top!=0)
		   { 
			   $where_array['is_top'] = $is_top;   
		   }
		if($c_name!='所有城市')
		  {
			  $where_array['r_name'] = $c_name;   

		   }
		 
		  if($category !=0)
		  {
			   $where_array['category_id'] = $category;   
			 
		   }
		   if($scale !=0)
		   {
			    $where_array['scale'] = $scale;   
		   }

		$this->where = $where_array;
       }
	/* 	$this->order=array('sort'=>'DESC'); */
     /* $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '活动'
            ),
            array(
                'url' => '#',
                'title' => '活动列表'
            )
        );
			
        return $this->getList();
    }
=======

        return $this->getList();*/
    /*}*/

	public function indexAction()
	{
		$this->checkLogin();
		$c_id = $this->params()->fromRoute('other');
		$cache=new City();
		$city_name= $cache->w_cache();
		if(count($city_name)>1)
		{
			unset($city_name[0]);
			$cityName=new where();
			$cityName->in('name',$city_name);
			$city_id=$this->getRegionTable()->getAll($cityName);
			$city_info='';
			foreach($city_id['list'] as $k=>$v)
			{
				$city_info[$v['id']]=$v['name'];
			}
		}


		$where = array();
		if($_POST)
		{
			//推荐
			$is_top=isset($_POST['recommend'])?$_POST['recommend']:'';
			//行业分类
			$category=isset($_POST['category'])?$_POST['category']:'';
			//规模
			$scale=isset($_POST['scale'])?$_POST['scale']:'';
			//城市
			$c_id=isset($_POST['city'])?$_POST['city']:'';


			if($is_top!=0)
			{
				$where['is_top'] = $is_top;
			}
			if($c_id != 0)
			{
				$where['region_id'] = $c_id;

			}

			if($category !=0)
			{
				$where['category_id'] = $category;

			}
			if($scale !=0)
			{
				$where['scale'] = $scale;
			}

		}

		$this->breadcrumb = array(
			array(
				'url' => '#',
				'title' => '活动'
			),
			array(
				'url' => '#',
				'title' => '活动列表'
			)
		);
		$where['delete']=0;
		if($c_id){
			$where['company_id']=$c_id;
		}
		$list=$this->getViewActivityTable()->fetchAll($where,array('sort'=>'DESC'));
		/*echo "<pre>";
		print_r($list)1;*/
		$view = new ViewModel(array(
			'list'=>$list,
			'scale'=>$this->scale(),
			'category'=>$this->category(),
			'is_top'=> isset($is_top) ? $is_top : '',
			'cate'=> isset($category) ? $category : '',
			'c_scale'=>isset($scale) ? $scale : '',
			'city'=>$city_info,
			'city_name'=>isset($c_id) ? $c_id : '',

		));
		$view->setTemplate('admin/activity/index');
		return $this->setMenu($view, 'user');
	}

	
    //缂栬緫娲诲姩
    public function editAction(){
		$this->checkLogin();
        $id=$this->params()->fromRoute('id');
		$info=$this->getViewActivityTable()->getOne(array('id'=>$id));
		$ids=explode(',',$info['images']);
        $images = $this->getImageTable()->getImages($ids);
        $view=new ViewModel(array(
            'data'=>$info,
            'images'=>$images,
        ));
        if(!empty($_POST)){
           $ac_info['name']=$_POST['acName'];
           $ac_info['sort']=$_POST['sort'];
           $ac_info['content']=$_POST['content'];
		   $img=$_POST['image_ids'];
           $ids=implode(',',$img);
		   $ac_info['images']=$ids;
		   $ac_info['timestamp_update']=$this->getTime();
           $id=$_POST['id'];
			$back=$this->getActivityTable()->updateData($ac_info,array('id'=>$id));
		   if($back){
			   $this->redirect()->toRoute('admin-activity',array('action'=>'index'));
		   }else{
			   $this->showMessage('修改失败');
		   }
           
        }
         $view->setTemplate('admin/activity/edit');
        return $this->setMenu($view, 1);
        
    }
    
    //澧炲姞娲诲姩
    public function addAction(){
       $this->checkLogin();
      
           //鍙栧嚭鎵�湁鍏徃
		  if($_POST){
				$data1=$this->getCompanyTable()->getAll();
				$con=array();
				foreach($data1['list'] as $val){
				   $con[]=$val['name'];
				}
				$data['name']=$_POST['acName'];
				$data['content']=$_POST['content'];
				$data['sort']=$_POST['sort'];
				$ids=$_POST['image_ids'];
			  	if($ids && is_array($ids))
				{
					$data['images']=implode(',',$ids);
				}
				$data['timestamp']=$this->getTime();
				$data['timestamp_update'] = $this->getTime();
				$company=$_POST['company'];
				if(in_array($company,$con)){
					$one=$this->getCompanyTable()->getOne(array('name'=>$company));
					if($one){
						$data['company_id']=$one['id'];
						$back=$this->getActivityTable()->insertData($data);
						if($back){
							 $this->redirect()->toRoute('admin-activity',array('action'=>'index'));
						}else{
							$this->showMessage('添加失败');
						}
				   
					}
				}else{
					$this->showMessage('公司不存在，不能添加活动');
				}
		  }
         $view=new ViewModel(array());
         $view->setTemplate('admin/activity/add');
          return $this->setMenu($view, 1);
    }
    

	
	public function companyAction(){
		$this->checkLogin();
		$data=$this->getCompanyTable()->getAll();
        $con=array();
        foreach($data['list'] as $val){
          $con[]=$val['name'];
        }
        $company=isset($_POST['company'])?$_POST['company']:'';
        if(!in_array($company,$con)){
            echo 0;
			die();
        }else{
            echo 1;
			die();
        }
	}
    
    public function hotAction()
    {
		$this->checkLogin();
        if($_POST && isset($_POST['add'])){
            $this->getRegionTable()->updateData(array('sort'=>1), array('id'=>$_POST['city_id']));
        }  
              
        $where = new Where();
        $where->notEqualTo('sort', 0);
        $city = $this->getRegionTable()->fetchAll($where);
		$id=$this->params()->fromRoute('id');
		if($id!==''){
			$a=$this->getRegionTable()->updateData(array('sort'=>0), array('id'=>$id));
			if($a){
				$this->redirect()->toRoute('admin-activity',array('action'=>'hot'));
			}
			
		}
		
		$this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '城市'
            ),
            array(
                'url' => '#',
                'title' => '热门城市'
            )
        );
        $view=new ViewModel(array(
		  'city'=>$city,
        ));
        $view->setTemplate('admin/activity/hot');
        return $this->setMenu($view, 1);

    }
        
    public function hotCityAction(){
		$sort=isset($_POST['sort'])?$_POST['sort']:'';
		$city_id=isset($_POST['cid'])?$_POST['cid']:'';
		if($sort && $sort!==0 && $city_id!==''){
			if(is_numeric($sort)){
				$back=$this->getRegionTable()->updateData(array('sort'=>$sort),array('id'=>(int)$city_id));
				if($back){
					$this->redirect()->toRoute('admin-activity',array('action'=>'hot'));
					die();
				}
				
			}
		}
	}

    public function delactivityAction(){
		$this->checkLogin();
		$id=$this->params()->fromRoute('id');
        if($id){
            $back=$this->getActivityTable()->deleteData($id);
			$this->redirect()->toRoute('admin-activity',array('action'=>'index'));
        }
		
    }

}