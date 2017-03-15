<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
//use Core\System\UploadfileApi;
use Core\System\WxApi\WxApi;
use Core\System\City;
use Core\System\imageCache;

class CompanyController extends CommonController
{

    /**
     * 公司名片列表
     *
     * @return multitype:
     * @version 2014-12-29   liujun
     */
    public function indexAction()
    {
        $this->checkLogin('user_index');
//         $carte_head_icon_ids = array();
//         $carte_head_icon = array();
        
        $page = $this->params()->fromRoute('page');
        $cid = $this->params()->fromRoute('cid');
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $like = null;
        
        $this->seach = array(
            'company',//公司名
            'id'          //公司id
        );
        
        if ((isset($_POST['submit']) && $_POST['keyword'] != '') || $keyword) {
            $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
            if ($keyword && is_array($this->seach)) {
                foreach ($this->seach as $v) {
                    $like[$v] = $keyword;
                }
            }
        }

        //筛选
        $cache=new City();
        $city_name= $cache->w_cache();
        $city_info1 = array();
        if(count($city_name)>0)
        {
            $cityName=new where();
            $cityName->in('name',$city_name);
            $city_id=$this->getRegionTable()->getAll($cityName);
            foreach($city_id['list'] as $k=>$v)
            {
                $city_info1[$v['id']]=$v['name'];
            }
        }
        if($_POST && $_POST['choose'])
        {
            //推荐
            $is_top=isset($_POST['recommend'])?$_POST['recommend']:'';
            //行业分类
            $category=isset($_POST['category'])?$_POST['category']:'';
            //规模
            $scale=isset($_POST['scale'])?$_POST['scale']:'';
            //城市
            $c_id=isset($_POST['city'])?$_POST['city']:'';

            $where = array();
            if($is_top!=0)
            {
                $where['is_top'] = $is_top;
            }
            if($c_id!=0)
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

        $where['delete']=0;
        $where['audit_status']=2;


        //$company_info = $this->getCompanyTable()->getAll($where, null, array('id' => 'DESC'), true, $page, PAGE_NUMBER, $like);
        $company_info = $this->getCompanyTable()->getAll($where);
        foreach($company_info['list'] as $v)
        {
            $activity = $this->getActivityTable()->getAll(array('company_id' => $v['id'],'delete'=>0));
            $v['a_total']= $activity['total'];
            $region=$this->getRegionTable()->getOne(array('id'=>$v['region_id']));
            $city_info= json_decode($v['region_info'],TRUE);
            if(isset($city_info[1]) && $city_info[1]){
                $v['city']=$city_info[1]['region']['name'];
            }

        }
       /* echo "<pre>";
        print_r($company_info);die;*/


        /*echo "<pre>";
        print_r($company_info);die;*/
//         $username = array();
//         foreach($list['list'] as $val){
//             if($val['user_id'])
//             {
//                 $user_id_array[] =  $val['user_id'];
        
//             }
//         }
//         if($user_id_array)
//         {
//             $where =new Where();
//             $where->in('id',$user_id_array);
//             $username = array();
//             $user  = $this->getUserTable()->fetchAll($where);
//             foreach ($user as $v)
//             {
//                 $username[$v->id]= $v->name;
//             }
//         }
        //$logo = $this->getImageTable()->getAll(array('id'=$company_info['']));
        $image_arr = array();
        foreach($company_info['list'] as $val){
            if($val['image'])
            {
                $image_arr[] =  $val['image']; 
            }
        }
        //print_r();
        if($image_arr)
        {
            $where =new Where();
            $where->in('id',$image_arr);
            $image_arr = array();
            $image = $this->getImageTable()->fetchAll($where);
            foreach ($image as $v)
            {
                $image_arr[$v->id]= $v->path . $v->filename;
            }
        }

        //print_r($image_arr);die;
        $audit_where=new where();
        $audit_where->equalTo('delete',0);
        $audit_where->in('audit_status',array('0'=>0,'1'=>1,'2'=>3));
        $audit=$this->getCompanyTable()->getAll($audit_where);
        $this->breadcrumb = array(
            array(
                'url' => '',
                'title' => '公司名片'
            ),
            array(
                'url' => '',
                'title' => '公司名片列表'
            )
        );

        $view = new ViewModel(array(
            //'paginator' => $carte_info['paginator'],
//             'condition' => array(
//                 'action' => $this->action,
//                 'cid' => $cid,
//                 'page' => $page,
//                 'keyword' => $keyword,
//                 'where' => $where
//             ),
            'company' => $company_info,
            'category' => $this->category(),
            'scale' => $this->scale(),
            'image' => $image_arr,
//             'page' => $page,
//             'keyword' => $keyword,
//             'where' => $where
            'is_top'=> isset($is_top) ? $is_top : '',
            'cate'=> isset($category) ? $category : '',
            'c_scale'=>isset($scale) ? $scale : '',
            'city'=>$cache->w_cache(),
            'c_id'=>isset($c_id) ? $c_id : '',
            'city_info'=>isset($city_info1) ? $city_info1 : '',
            'audit'=> $audit['total'],

        ));
        $view->setTemplate('admin/company/index');
        return $this->setMenu($view, 'user');
    }

    /**
     * 删除公司名片
     */ 
    public function delcompanyAction()
    {   
        $this->checkLogin('user_index');
        $id=$this->params()->fromRoute('id');
        if($id){
            $data = $this->getCompanyTable()->updateData(array('delete'=>1), array('id'=>$id));
            if($data){
                $this->redirect()->toRoute('admin-company', array(
                    'action' => 'index'
                ));
            }else{
                $this->showMessage('删除失败！');
            }
        }else{
            $this->showMessage('请求参数错误！');
        }
    }
    
    /**
     * 公司名片详细页
     */
    public function companydetailsAction()
    {
        $this->checkLogin('user_index');
        
        $id = $this->params()->fromRoute('id');
        if($id){//修改页面
            $company_info = $this->getCompanyTable()->getOne(array('delete'=>0,'id'=>$id));

            if($company_info['stat_audit']<0){
                $this->getCompanyTable()->updateData(array('stat_audit' =>0),array('id'=>$id));
            }

            $staff_pass=$this->getCarteTable()->getAll(array('company_id'=>$company_info['id'],'company_status'=>3,'delete'=>0));
            $all=$this->getCompanyTable()->getAll();
            $carte=array();
            $user_info = $this->getUserTable()->getOne(array('id'=>$company_info['user_id']));
            if($company_info['user_id'])
            {
                $carte=$this->getViewPageCarteTable()->getOne(array('user_id'=>$company_info['user_id']));
            }
            //取主名片
            if($carte)
            {
                $header=$this->getImageTable()->getOne(array('id'=>$carte['head_icon']));
                $header_path=$header['path'].$header['filename'];
            }


            //提供
            $tag_relation=$this->getTagsRelationsTable()->getAll(array('foreign_id'=>$id,'type'=>1));

            if($tag_relation['total']!==0){
                $tag=array();
                foreach($tag_relation['list'] as $v)
                {
                    $tag[]=$v['tag_id'];
                }
                $where_array=new where();
                $where_array->in('id',$tag);
                $provide=$this->getTagsTable()->getAll($where_array);
            }
            //需要

            $tag_relation_1=$this->getTagsRelationsTable()->getAll(array('foreign_id'=>$id,'type'=>2));
            if($tag_relation_1['total']!==0 ){
                $tag_1=array();
                foreach($tag_relation_1['list'] as $v)
                {
                    $tag_1[]=$v['tag_id'];
                }
                $where_array1=new where();
                $where_array1->in('id',$tag_1);
                $need=$this->getTagsTable()->getAll($where_array1);
            }

            $all_company=array();
            foreach($all['list'] as $v)
            {
                $all_company[]=$v['name'];
            }
            if($company_info){
                $son_company=$this->getCompanyTable()->getAll(array('parent_id'=>$company_info['id'],'delete'=>0));
                foreach($son_company['list'] as $k=>$v)
                {
                    $son_region=$this->getRegionTable()->getOne(array('id'=>$v['region_id']));
                    $son_logo  =$this->getImageTable()->getOne(array('id'=>$v['image']));
                    $v['logo']=$son_logo['path'].$son_logo['filename'];
                    $v['region']=$son_region['name'];
                }
                $image = $this->getImageTable()->getOne(array('id'=>$company_info['image']));
                $imagePath = $image['path'] . $image['filename'];
                $license=$this->getImageTable()->getOne(array('id'=>$company_info['license_image']));
                $coporation=$this->getImageTable()->getOne(array('id'=>$company_info['ID_image']));

                $builder = $this->getViewPageCarteTable()->getOne(array('user_id'=>$company_info['user_id']));
                $builderPath = $builder['path'] . $builder['filename'];
                $builder['userpath'] = $builderPath;
                
                $filiale = $this->getCompanyTable()->fetchAll(array(
                    'parent_id' => $company_info['id'],
                    'delete' => 0
                ));
                
            }else{
                $this->showMessage('没有相关数据！');
            }
            $region = json_decode($company_info['region_info']);
            $province = $this->getRegionTable()->fetchAll(array('parent_id'=>1));

            $this->breadcrumb = array(

                array(
                    'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'index')),
                    'title' => '名片列表'
                ),
                 array(
                     'url' => '',
                     'title' => '名片详情'
                 ),
            );

            $view = new ViewModel(array(
                'logo' =>      $imagePath ? $imagePath : '',
                'company_info' => $company_info,
                'filiale' =>  $filiale,
                'scale' =>    $this->scale(),
                'category' => $this->category(),
                'province' => $province,
                'region' =>   $region,
                'builder' =>  $builder,
                'license'=>   $license,
                'coporation'=>$coporation,
                'image'=>     $image,
                'son_company'=>$son_company,
                'all'=>       $all_company,
                'card'=>      isset($carte)?$carte:'',
                'total'=>     isset($total['total'])?$total['total']:'',
                'head'=>      isset($header_path)?$header_path:'',
                'provide'=>   isset($provide)?$provide:'',
                'need'=>      isset($need)?$need:'',
                'staff_total'=>isset($company_info['stat_audit'])?$company_info['stat_audit']:'',
                'pass_total'=>isset($staff_pass['total'])?$staff_pass['total']:'',
                'user_info' => isset($user_info) ? $user_info :''
            ));
            $view->setTemplate('admin/company/companyDetails');
            return $this->setMenu($view, 'company');            
            
        }else{//增加页面
            $scale = $this->scale();
            $province = $this->getRegionTable()->fetchAll(array('parent_id'=>1));           
            $view = new ViewModel(array(
                'province' => $province,
                'scale' => $scale,
                'category' => $this->category(),
            ));
            $view->setTemplate('admin/company/addCompany');
            return $this->setMenu($view, 'company');      
        }       
    }
    
    /**
     * 修改公司名片
     */
    public function modifyAction()
    {
        $this->checkLogin();
		$county_id = isset($_POST['county'])?$_POST['county']:''; 
        $city_id = isset($_POST['city_id'])?$_POST['city_id']:'';        
        $province_id = isset($_POST['province_id'])?$_POST['province_id']:'';      
        
        $region = $this->getApiController()->getRegionInfoArray($county_id);
        
        $json_address = json_decode($region['region_info']);

        $_address = "";
        for($i=0;$i<count($json_address);$i++){
            $_address .= $json_address["$i"]->region->name.' ';
        }
       
        $regions = $region['region_info'];
        
        $data = array(
            'name' => $_POST['name'],
            'is_top' => $_POST['is_top'],
            'category_id' => $_POST['category'],
            'telephone' => $_POST['telephone'] ? $_POST['telephone']:'',
            'project' => '',
            'audit_status' => 1,
            'description' => $_POST['description'],
            'scale' => $_POST['scale'],
            'longitude' => $_POST['longitude'],
            'latitude' => $_POST['latitude'],
            'street' =>$_POST['street'] ,
            'address' => $_address,
            'region_id' => $city_id,
            'region_info' => $regions,
            'provide'=>isset($_POST['provide_content'])?$_POST['provide_content']:'',
            'needs'       => isset($_POST['needs_content'])?$_POST['needs_content']:'',
        );
        $provide_id=isset($_POST['provide_id'])?$_POST['provide_id']:'';
        $need_id=isset($_POST['need_id'])?$_POST['need_id']:'';
        $pr=isset($_POST['provide'])?$_POST['provide']:'';
        $ne=isset($_POST['needs'])?$_POST['needs']:'';
        $need_1=array_filter($pr);
        $provide_1=array_filter($ne);



       //logo{"image":"1469","timestampUpdate":"2016-01-07 21:40:01"}
        if(isset($_FILES) && $this->check_file_type($_FILES['up_logo']['tmp_name'])){
            $file = $this->getApiController()->uploadImageForController("up_logo");
            $data['image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
        }else{
            $data['image'] = isset($_POST['up_logo'])?$_POST['up_logo']:'';
        }
        //营业执照
        if(isset($_FILES) && $this->check_file_type($_FILES['up_license']['tmp_name'])){
            $file = $this->getApiController()->uploadImageForController("up_license");
            $data['license_image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
        }else{
            $data['license_image'] = isset($_POST['up_license'])?$_POST['up_license']:'';
        }
        //法人身份证
        if(isset($_FILES) && $this->check_file_type($_FILES['up_coporation']['tmp_name'])){
            $file = $this->getApiController()->uploadImageForController("up_coporation");
            $data['ID_image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
        }else{
            $data['ID_image'] = isset($_POST['up_coporation'])?$_POST['up_coporation']:'';
        }
       /* echo "<pre>";
        print_r($data);die;*/

        if(isset($_POST['id']) && $_POST['id']){//修改
            $data['timestamp_update'] = $this->getTime();
			//缓存城市
            $json_info = json_decode($data['region_info'],true);
            if($json_info && isset($json_info[1]) && $json_info[1]){
                $c_name = $json_info[1]['region']['name'];
            }else{
                $c_name = '广州市';
            }
			$cache=new City();
			$cache->w_cache($c_name);
            if($provide_id)
            {
                $where=new where();
                $where->in('tag_id',$provide_id);
                $this->getTagsRelationsTable()->delete($where);
            }

            if($need_id)
            {
                $where2=new where();
                $where2->in('tag_id',$need_id);
                $this->getTagsRelationsTable()->delete($where2);
            }

            if($provide_1 && isset($provide_1[0]) && $provide_1[0]!=='')
            {
                $array_id=array();
                foreach($provide_1 as $v)
                {
                    $back=$this->getTagsTable()->insertData(array('name'=>$v,'timestamps'=>$this->getTime()));
                    $array_id[]=$back;
                }
                foreach($array_id as $v){
                    $back=$this->getTagsRelationsTable()->insertData(array('type'=>1,'tag_id'=>$v,'foreign_id'=>$_POST['id']));
                }
            }

            if($need_1 && isset($need_1[0]) && $need_1[0]!=='')
            {
                $array_id_2=array();
                foreach($need_1 as $v)
                {
                    $back=$this->getTagsTable()->insertData(array('name'=>$v,'timestamps'=>$this->getTime()));
                    $array_id_2[]=$back;
                }
                foreach($array_id_2 as $v){
                    $back=$this->getTagsRelationsTable()->insertData(array('type'=>2,'tag_id'=>$v,'foreign_id'=>$_POST['id']));
                }
            }
            //更新
            $data['audit_status']=$_POST['audit_status'];
            $updata = $this->getCompanyTable()->updateData($data, array('id'=>$_POST['id']));

            if($updata){
                $this->redirect()->toRoute('admin-company', array(
                    'action' => 'index'
                ));
            }else{
                $this->showMessage('更新失败！');
            }
            
        }else if(!isset($_POST['id'])){
			if(isset($_POST['submit']) && $_POST['submit']){
				
			
				$data['timestamp'] = $this->getTime();
				//缓存城市
				$c_name=$this->getRegionTable()->getOne(array('id'=>$data['region_id']));
				$cache=new City();
				$cache->w_cache($c_name['name']);
				
				//插入
				$insert = $this->getCompanyTable()->insertData($data);

                if($provide_1 && $provide_1[1] && $provide_1[1]!=='' )
                {
                    $array_id=array();
                    foreach($provide_1 as $v)
                    {
                        $back=$this->getTagsTable()->insertData(array('name'=>$v,'timestamps'=>$this->getTime()));
                        $array_id[]=$back;
                    }
                    foreach($array_id as $v){
                        $back=$this->getTagsRelationsTable()->insertData(array('type'=>1,'tag_id'=>$v,'foreign_id'=>$insert));
                    }
                }

                if($need_1 && $need_1[1] && $need_1[1]!=='')
                {
                    $array_id_2=array();
                    foreach($need_1 as $v)
                    {
                        $back=$this->getTagsTable()->insertData(array('name'=>$v,'timestamps'=>$this->getTime()));
                        $array_id_2[]=$back;
                    }
                    foreach($array_id_2 as $v){
                        $back=$this->getTagsRelationsTable()->insertData(array('type'=>2,'tag_id'=>$v,'foreign_id'=>$insert));

                    }
                }


                //增加统计数据
				if($insert){
						$this->redirect()->toRoute('admin-company', array(
							'action' => 'companydetails','id'=>$insert
						));
					}else{
						$this->showMessage('新增失败！');
					}
			}
		}	
        
    }
    
    public function checkNameAction()
    {
        $this->checkLogin();
        
        if($_POST['company'] && !isset($_POST['id'])){//增加
            $company = $_POST['company'];
            if(!$company){
                echo 2;
                die();
            }
            $company_info = $this->getCompanyTable()->getOne(array('delete'=>0,'name'=>$company));
            if($company_info){
                echo 0;//重名
                die();
            }else{     
                echo 1;
                die();
            }
        }
        
        if($_POST['company'] && isset($_POST['id']) && (int)$_POST['id']){//修改
            $company = $_POST['company'];
            if(!$company){
                echo 2;
                die();
            }           
            $where = new where();
            $where->equalTo('name', $_POST['company']);
            $where->notEqualTo('id', (int)$_POST['id']);
            $where->equalTo('delete',0);
            $company_info = $this->getCompanyTable()->getOne($where);
            if($company_info){
                echo 0;
                die();
            }else{
                echo 1;
                die();
            }       
        }       
         
        echo 0;
        die();
    }

    /*
     * 公司名片审核查看
     * */
    public function indexTwoAction()
    {
        $this->checkLogin('user_index');
        $page = $this->params()->fromRoute('page');
        $cid = $this->params()->fromRoute('cid');


        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $like = null;

        $this->seach = array(
            'company',//公司名
            'id'          //公司id
        );

        if ((isset($_POST['submit']) && $_POST['keyword'] != '') || $keyword) {
            $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
            if ($keyword && is_array($this->seach)) {
                foreach ($this->seach as $v) {
                    $like[$v] = $keyword;
                }
            }
        }

        //筛选
        $cache=new City();
        $city_name= $cache->w_cache();
        $city_info1=array();
        if(count($city_name)>0)
        {
            $cityName=new where();
            $cityName->in('name',$city_name);
            $city_id=$this->getRegionTable()->getAll($cityName);
            foreach($city_id['list'] as $k=>$v)
            {
                $city_info1[$v['id']]=$v['name'];
            }
        }

        //print_r($city_info);die;
        if($_POST && $_POST['choose'])
        {
            //推荐
            $is_top=isset($_POST['recommend'])?$_POST['recommend']:'';
            //行业分类
            $category=isset($_POST['category'])?$_POST['category']:'';
            //规模
            $scale=isset($_POST['scale'])?$_POST['scale']:'';
            //城市
            $c_id=isset($_POST['city'])?$_POST['city']:'';

            $where = array();
            if($is_top!=0)
            {
                $where['is_top'] = $is_top;
            }
            if($c_id!=0)
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
        $where1=new where();
        $where1->equalTo('delete',0);
        $where1->in('audit_status',array('0'=>1,'1'=>0,'2'=>3));
        $company_info = $this->getCompanyTable()->getAll($where1);

        foreach($company_info['list'] as $v)
        {
            $region=$this->getRegionTable()->getOne(array('id'=>$v['region_id']));
            $city_info= json_decode($v['region_info'],TRUE);
            if(isset($city_info[1]) && $city_info[1]){
                $v['city']=$city_info[1]['region']['name'];
            }
        }

        $image_arr = array();
        foreach($company_info['list'] as $val){
            if($val['image'])
            {
                $image_arr[] =  $val['image'];
            }
        }
        //print_r();
        if($image_arr)
        {
            $where =new Where();
            $where->in('id',$image_arr);
            $image_arr = array();
            $image = $this->getImageTable()->fetchAll($where);
            foreach ($image as $v)
            {
                $image_arr[$v->id]= $v->path . $v->filename;
            }
        }
        $this->breadcrumb = array(
            array(
                'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'index')),
                'title' => '公司名片'
            ),
            array(
                'url' => '',
                'title' => '公司名片审核'
            )
        );
        //print_r($image_arr);die;

        $view = new ViewModel(array(

            'company' => $company_info,
            'category' => $this->category(),
            'scale' => $this->scale(),
            'image' => $image_arr,
            'is_top'=> isset($is_top) ? $is_top : '',
            'cate'=> isset($category) ? $category : '',
            'c_scale'=>isset($scale) ? $scale : '',
            'city'=>$cache->w_cache(),
            'c_id'=>isset($c_id) ? $c_id : '',
            'city_info'=>$city_info1,

        ));
        $view->setTemplate('admin/company/indextwo');
        return $this->setMenu($view, 'user');
    }


    /*
     * 员工
     * */
     public function staffAction()
     {
         
        
         $id=$this->params()->fromRoute('id');
       
         $company=$this->getCompanyTable()->getOne(array('id'=>$id,'delete'=>0));
         $where = new where();
         $where->equalTo('company_id',trim($company['id']));
         $where->equalTo('delete',0);
         $where->notEqualTo('company_status',3);
         $info = $this->getCarteTable()->getAll($where);
         foreach($info['list'] as $k=>$v )
         {
             $head_img=$this->getImageTable()->getOne(array('id'=>$v['head_icon']));
             if($head_img)
             {
                 $v['logo']=$head_img['path'].$head_img['filename'];
             }

         }

         $this->breadcrumb = array(
             array(
                 'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'index')),
                 'title' => '公司名片'
             ),
             array(
                 'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'companydetails','id'=>$id)),
                 'title' => '名片详情'
             ),
             array(
                 'url' => '',
                 'title' => '员工申请列表'
             )
         );

         $view = new ViewModel(array(
            'info'=>$info,
             'id'=>$id,
             'company_id'=>$company['id']
         ));
         $view->setTemplate('admin/company/staff');
         return $this->setMenu($view,1);
     }



    public function passAction()
    {
        $id=$this->params()->fromRoute('id');
        $cid=$this->params()->fromRoute('cid');
        $top=$this->params()->fromRoute('page');
        $other=$this->params()->fromRoute('other');
        $user = $this->getUserTable()->getOne(array('id'=>$top));
        if($user){
            $page = $this->getPageTable()->getOne(array('id' => $user['page_id']));
            $card = $this->getCarteTable()->getOne(array('id' =>$page['carte_id'],'delete'=>0));
            $head_imga=$this->getImageTable()->getOne(array('id'=>$card['head_icon']));
            if($card){
                $main = array(
                    'id'=>$card['id'],
                    'name' =>$card['name'],
                    'company'=>$card['company'],
                    'position'=>$card['position'],
                    'mobile'=>$card['mobile'],
                    'head_icon'=>$head_imga['path'].$head_imga['filename'],
                    'timestamp'=>$card['timestamp']
                );
            }
        }
        $company=$this->getCompanyTable()->getOne(array('id'=>$id,'delete'=>0));
        if($cid && $other)
        {
            $sp_company = $this->getCompanyTable()->getOne(array('id'=>$other,'delete'=>0));
            if($sp_company['stat_stuff']>0){
                $num = $sp_company['stat_stuff']-1;
                $this->getCompanyTable()->updateData(array('stat_stuff'=>$num),array('id'=>$sp_company['id']));
            }
            $back=$this->getCarteTable()->updateData(array('company_status'=>1,'company'=>'','position'=>'','company_id'=>''),array('id'=>$cid));



            if($back)
            {
                echo "<script type='text/javascript'>history.back();</script>";
            }
        }


        $where = new where();
        $where->equalTo('delete',0);
        $where->equalTo('company_id',$company['id']);
        $where->equalTo('company_status',3);
        $info=$this->getCarteTable()->getAll($where);
        
        foreach($info['list'] as $k=>$v )
        {
            $head_img=$this->getImageTable()->getOne(array('id'=>$v['head_icon']));
            if($head_img)
            {
                $v['logo']=$head_img['path'].$head_img['filename'];
            }

        }

        $this->breadcrumb = array(
            array(
                'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'index')),
                'title' => '公司名片'
            ),
            array(
                'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'audit','id'=>$id)),
                'title' => '名片详情'
            ),
            array(
                'url' => '',
                'title' => '员工列表'
            )
        );
        $view = new ViewModel(array(
            'info'=>$info,
            'total'=>isset($infos['total'])?$infos['total']:'',
            'id'=>$id,
            'page' => $page,
            'card' =>isset($main) ? $main : '',
        ));
        $view->setTemplate('admin/company/pass');
        return $this->setMenu($view,1);
    }
    /*
     * 删除子公司
     * */

    public function deleteSonCompanyAction()
    {
        $cid=isset($_GET['cid']) ? $_GET['cid'] : '';
        $id=$this->params()->fromRoute('id');
        if($cid && $id)
        {
            $return=$this->getCompanyTable()->update(array('parent_id' => 0), array('id' => $cid));

            if($return>0)
            {
                $this->showMessage('子公司删除成功');
                echo "<script type='text/javascript'>window.location.href=history.go(-1);</script>";
            }
        }else
        {
            $this->showMessage('子公司删除失败');
            echo "<script type='text/javascript'>window.location.href=history.go(-1);</script>";
        }
        exit;
    }


    /*
     * 增加子公司
     *
     * */

    public function addSonCompanyAction()
    {
		$name=$_POST['name'];
        $id=$_POST['id'];
        $where=new where();
        $where->notEqualTo('parent_id',$id);
        $where->equalTo('delete',0);
        $all=$this->getCompanyTable()->getAll($where);
        $company=array();
        foreach($all['list'] as $v)
        {
            $company[]=$v['name'];
        }
        $result=array();
        if(in_array($name,$company))
        {
            $new=$this->getCompanyTable()->getOne(array('name'=>$name));
            $back=$this->getCompanyTable()->update(array('parent_id'=>$id),array('id'=>$new['id']));
            if($back>0){
               echo 1;
            }

        }
        else
        {
           echo 2;
        }
        exit;
    }


    /*
     * 公司审核
     *
     * */
    public function auditAction()
    {
        $this->breadcrumb = array(
            array(
                'url' => $this->plugin('url')->fromRoute('admin-company',array('action'=>'indexTwo')),
                'title' => '名片审核'
            ),
            array(
                'url' => '',
                'title' => '名片详情'
            )
        );
        if($_POST){
            $city_id = isset($_POST['city_id'])?$_POST['city_id']:'';
            $province_id = isset($_POST['province_id'])?$_POST['province_id']:'';
            $county_id = isset($_POST['county'])?$_POST['county']:'';
            $region = $this->getApiController()->getRegionInfoArray($county_id);
            $regions = $region['region_info'];
            $json_address = json_decode($region['region_info']);

            $_address = "";
            for($i=0;$i<count($json_address);$i++){
                $_address .= $json_address["$i"]->region->name.' ';
            }

            $data = array(
                'name' => $_POST['name'],
                'is_top' => $_POST['is_top'],
                'category_id' => $_POST['category'],
                'telephone' => $_POST['telephone'] ? $_POST['telephone']:'',
                'project' => '',
                'audit_status'=>3,
                'description' => $_POST['description'],
                'scale' => $_POST['scale'],
                'longitude' => $_POST['longitude'],
                'latitude' => $_POST['latitude'],
                'street' => $_POST['street'],
                'address' => $_address,
                'region_id' => $city_id,
                'region_info' => $regions,
                'provide'=>isset($_POST['provide_content'])?$_POST['provide_content']:'',
                'needs'       => isset($_POST['needs_content'])?$_POST['needs_content']:'',
            );
            $pr=isset($_POST['provide'])?$_POST['provide']:'';
            $provide_id=isset($_POST['provide_id'])?$_POST['provide_id']:'';
            $ne=isset($_POST['needs'])?$_POST['needs']:'';
            $need_id=isset($_POST['needs_id'])?$_POST['needs_id']:'';
            $need_1=array_filter($pr);
            $provide_1=array_filter($ne);
            //logo
            if(isset($_FILES) && $this->check_file_type($_FILES['up_logo']['tmp_name'])){
                $file = $this->getApiController()->uploadImageForController("up_logo");
                $data['image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
            }else{
                $data['image'] = isset($_POST['up_logo'])?$_POST['up_logo']:'';
            }
            //营业执照
            if(isset($_FILES) && $this->check_file_type($_FILES['up_license']['tmp_name'])){
                $file = $this->getApiController()->uploadImageForController("up_license");
                $data['license_image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
            }else{
                $data['license_image'] = isset($_POST['up_license'])?$_POST['up_license']:'';
            }
            //法人身份证
            if(isset($_FILES) && $this->check_file_type($_FILES['up_coporation']['tmp_name'])){
                $file = $this->getApiController()->uploadImageForController("up_coporation");
                $data['ID_image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
            }else{
                $data['ID_image'] = isset($_POST['up_coporation'])?$_POST['up_coporation']:'';
            }
            $data['timestamp_update'] = $this->getTime();
            //缓存城市
            $json_info = json_decode($data['region_info'],true);
            if($json_info && isset($json_info[1]) && $json_info[1]){
                $c_name = $json_info[1]['region']['name'];
            }else{
                $c_name = '广州市';
            }
            $cache=new City();
            $cache->w_cache($c_name);

            if($provide_id)
            {
                $where=new where();
                $where->in('tag_id',$provide_id);
                $this->getTagsRelationsTable()->delete($where);
            }

            if($need_id)
            {
                $where2=new where();
                $where2->in('tag_id',$need_id);
                $this->getTagsRelationsTable()->delete($where2);
            }

            if($provide_1)
            {
                $array_id=array();
                foreach($provide_1 as $v)
                {
                    $back=$this->getTagsTable()->insertData(array('name'=>$v,'timestamps'=>$this->getTime()));
                    $array_id[]=$back;
                }
                foreach($array_id as $v){
                    $back=$this->getTagsRelationsTable()->insertData(array('type'=>1,'tag_id'=>$v,'foreign_id'=>$_POST['id']));
                }
            }


            if($need_1)
            {
                $array_id_2=array();
                foreach($need_1 as $v)
                {
                    $back=$this->getTagsTable()->insertData(array('name'=>$v,'timestamps'=>$this->getTime()));
                    $array_id_2[]=$back;
                }

                foreach($array_id_2 as $v){
                    $a[]=$back=$this->getTagsRelationsTable()->insertData(array('type'=>2,'tag_id'=>$v,'foreign_id'=>$_POST['id']));
                }

            }

            //更新
            $updata = $this->getCompanyTable()->updateData($data, array('id'=>$_POST['id']));

            if($updata){
                $this->redirect()->toRoute('admin-company', array(
                    'action' => 'indexTwo'
                ));
            }else{
                $this->showMessage('更新失败！');
            }
        }

        $id = $this->params()->fromRoute('id');
        if($id){//修改页面
            $company_info = $this->getCompanyTable()->getOne(array('delete'=>0,'id'=>$id));
            $staff=$this->getCarteTable()->getAll(array('company'=>$company_info['name'],'company_status'=>2));
          /* echo "<pre>";
            print_r($company_info);die;*/
            $user_info = $this->getUserTable()->getOne(array('id'=>$company_info['user_id']));
            $all=$this->getCompanyTable()->getAll(array('delete'=>0));
            $all_company=array();
            $carte=array();
            if($company_info['user_id'])
            {
                $carte=$this->getViewPageCarteTable()->getOne(array('user_id'=>$company_info['user_id']));
                if($carte)
                {
                    $header=$this->getImageTable()->getOne(array('id'=>$carte['head_icon']));
                    $header_path=$header['path'].$header['filename'];
                }
            }


            //提供
            $provide_array=array();
            $tag_relation=$this->getTagsRelationsTable()->getAll(array('foreign_id'=>$id,'type'=>1));
            if($tag_relation)
            {
                $tag=array();
                foreach($tag_relation['list'] as $v)
                {
                    $tag[]=$v['tag_id'];
                }
                if($tag && is_array($tag))
                {
                    $where_array=new where();
                    $where_array->in('id',$tag);
                    $provide=$this->getTagsTable()->getAll($where_array);
                    if($provide['total']!==0)
                    {
                        foreach($provide['list'] as $v)
                        {
                            $provide_array[$v['id']]=$v['name'];
                        }
                    }

                }

            }


            //需要
            $need_array=array();
            $tag_relation_1=$this->getTagsRelationsTable()->getAll(array('foreign_id'=>$id,'type'=>2));
            if($tag_relation_1 && $tag_relation_1['total']!==0)
            {
                $tag_1=array();
                foreach($tag_relation_1['list'] as $v)
                {
                    $tag_1[]=$v['tag_id'];
                }
                if($tag_1 && is_array($tag_1))
                {
                    $where_array1=new where();
                    $where_array1->in('id',$tag_1);
                    $need=$this->getTagsTable()->getAll($where_array1);
                    foreach($need['list'] as $k=>$v)
                    {
                        $need_array[$v['id']]=$v['name'];
                    }

                }

            }
            foreach($all['list'] as $v)
            {
                $all_company[]=$v['name'];
            }
            if($company_info){
                $son_company=$this->getCompanyTable()->getAll(array('parent_id'=>$company_info['id'],'delete'=>0));
                foreach($son_company['list'] as $k=>$v)
                {
                    $son_region=$this->getRegionTable()->getOne(array('id'=>$v['region_id']));
                    $son_logo  =$this->getImageTable()->getOne(array('id'=>$v['image']));
                    $v['logo']=$son_logo['path'].$son_logo['filename'];
                    $v['region']=$son_region['name'];
                }
                $image = $this->getImageTable()->getOne(array('id'=>$company_info['image']));
                $imagePath = $image['path'] . $image['filename'];
                $license=$this->getImageTable()->getOne(array('id'=>$company_info['license_image']));
                $coporation=$this->getImageTable()->getOne(array('id'=>$company_info['ID_image']));

                $builder = $this->getViewPageCarteTable()->getOne(array('user_id'=>$company_info['user_id']));
                $builderPath = $builder['path'] . $builder['filename'];
                $builder['userpath'] = $builderPath;

                $filiale = $this->getCompanyTable()->fetchAll(array(
                    'parent_id' => $company_info['id'],
                    'delete' => 0
                ));

            }else{
                $this->showMessage('没有相关数据！');
            }
        }
            $region = json_decode($company_info['region_info']);
            $province = $this->getRegionTable()->fetchAll(array('parent_id'=>1));

        $view = new ViewModel(array(
            'logo' =>      $imagePath ? $imagePath : '',
            'company_info' => $company_info,
            'filiale' =>  $filiale,
            'scale' =>    $this->scale(),
            'category' => $this->category(),
            'province' => $province,
            'region' =>   $region,
            'builder' =>  $builder,
            'license'=>   $license,
            'coporation'=>$coporation,
            'image'=>     $image,
            'son_company'=>$son_company,
            'all'=>       $all_company,
            'card'=>      isset($carte)?$carte:'',
            'pass_total'=>     isset($company_info['stat_staff'])?$company_info['stat_staff']:'0',
            'head'=>      isset($header_path)?$header_path:'',
            'need'=>      isset($need_array)?$need_array:'',
            'provide'=>   isset($provide_array)?$provide_array:'',
            'user_info' => isset($user_info) ? $user_info :''
        ));
        $view->setTemplate('admin/company/audit');
        return $this->setMenu($view,1);
    }

    /*
     * 更改公司审核状态
     *
     * */
    public function checkAuditAction()
    {
        $status=$_POST['status'];
        $id=$_POST['id'];
        if($status && $id)
        {
            if($status==2)
            {
                $back=$this->getCompanyTable()->update(array('audit_status'=>$status),array('id'=>$id));
                if($back)
                {
                    $this->statOporation(3,1,$id);
                    echo 1;
                }
            }
            else if($status==3)
            {
                $back=$this->getCompanyTable()->update(array('audit_status'=>$status),array('id'=>$id));
                if($back)
                {
                    echo 1;
                }
            }
        }else{
            echo 2;
        }
        exit;
    }

    /*
     * 员工审核状态
     *
     * */
    public function updateStatuAction()
    {
        $status=$_POST['status'];
        $id=$_POST['id'];
        $cId=$_POST['companyId'];
        if($status && $id)
        {
            $back=$this->getCarteTable()->update(array('company_status'=>$status),array('id'=>$id));
            $stuff_Num = $this->getCompanyTable()->getOne(array('id'=>$cId));
            //stat_audit  申请数量
            if($stuff_Num['stat_audit'] > 0){
                $this->getCompanyTable()->updateKey($cId,2,'stat_audit',1);
            }else{
                $stuff_Num = $this->getCompanyTable()->updateData(array('stat_audit' => 0), array('id'=>$cId));
            }
           
            if($status==3)
            {
                // stat_stuff 员工数量
                $this->getCompanyTable()->updateKey($cId,1,'stat_stuff',1);
            }

            if($back)
            {
                echo 1;
            }else{
                echo 2;
            }
        }else{
            echo 2;
        }
        exit;
    }

    /*
     * 添加,编辑公司管理人
     *
     * */
    public function addManngeAction()
    {
        $mobile = $_POST['mobile'];
        $id = $_POST['id'];
        $info = $this->getUserTable()->getOne(array('mobile' => $mobile));
        if($info)
        {
            $this->getCompanyTable()->updateData(array('user_id'=>$info['id']),array('id'=>$id));
            echo 1;
        }else{
            echo 2;
        }
        exit;
    }


    public function changeAction()
    {

        $mobile1 = $_POST['mobile1'];
        $cId = $_POST['cId'];
        $info = $this->getUserTable()->getOne(array('mobile' => $mobile1));
        if($info)
        {
            $this->getCompanyTable()->updateData(array('user_id'=>$info['id']),array('id'=>$cId));
            echo 1;
        }else{
            echo 2;
        }
        exit;
    }
    /*
     *
     * 公司访问量
     *
     * */
    public function visitAction()
    {
        $id=$this->params()->fromRoute('id');
        if($id)
        {
            $_SESSION['companyId']=$id;
        }
        $year=isset($_POST['YYYY'])?$_POST['YYYY']:date('Y',time());
        $month=isset($_POST['MM'])?$_POST['MM']:'01';
        $year_array=array();
        for($i=1;$i<7;$i++)
        {
            $year_array[]=2014+$i;
        }

        $month_array=array();
        for($j=1;$j<=12;$j++)
        {
            if($j<10){
                $month_array[]='0'.$j;
            }else{
                $month_array[]=$j;
            }
        }


        $date=$year.$month;

        $time=$this->getTheMonths($date);
        $where=new where();
        $where->equalTo('type',5);
        $where->equalTo('foreign_id', $_SESSION['companyId']);
        $where->greaterThanOrEqualTo('date',$time['first']);
        $where->lessThanOrEqualTo('date',$time['last']);
        $data=$this->getStatisticsDayTable()->getAll($where);
        $t=array();
        $d=array();

        foreach($data['list'] as $v)
        {
            $d[]=intval($v['value']);
            $t[]=$v['date'];
        }
        $view=new ViewModel(array(
            'data'=>json_encode($d),
            'date'=>json_encode($t),
            'info'=>$data,
            'total'=>$data['total'],
            'month'=>$month_array,
            'year'=>$year_array,
            'se_month'=>$month,
            'se_year'=>$year,
        ));
        $view->setTemplate('admin/company/visit');
        return $this->setMenu($view,1);
    }

    /*
     * 驳回已审核公司
     *
     * */
    public function rejectAction()
    {
        $status = $_POST['status'];
        $id = $_POST['id'];
        if($status && $id)
        {
            $back = $this->getCompanyTable()->updateData(array('audit_status'=>3),array('id'=>$id));
            if($back)
            {
                echo 1;
                exit;
            }else{
                echo 2;
                exit;
            }

        }else{
            echo 2;
            exit;
        }
    }


    /**
     * 修复公司统计信息，慎用
     */
    public function repairAction()
    {
        $sql = "update ky_company as co set stat_audit = (select count(*) from ky_carte as ca where co.id = ca.company_id and ca.company_status = 2);";
        $this->getCompanyTable()->executeSqlOne($sql);
        exit;
    }
}