<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Api\Controller\User;
use Api\Controller\CommonController as Api;
use Admin\Controller\CommonController as AdminController;
use Api\Controller\CardDetails;
use Zend\View\View;
use Api\Controller\SMSCode;
use Core\System\WxApi\WxApi;
use Core\System\WxPayApi\AiiWxPay;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Validator\InArray;
use Core\System\WxApi\WxJsApi;


class UserController extends CommonController
{

    /**
     * 个人中心
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $view->setTemplate('index/user/index');
        $view = $this->UserMenuTemplate($view, 'info');
        return $this->setMenu($view);
    }

    /**
     *  PC名片详情
     * @return \Zend\View\Model\ViewModel
     * @version 2015年8月8日 WZ
     */
    
    public function userDetailsAction()
    {
        $id = (int) $this->params()->fromRoute('id');
       
        if ($id) {
            $json = array(
                'q' => array(
                    'id' => $id
                )
            );
            $_REQUEST['json'] = json_encode($json);
            
            $api = new CardDetails();
            // $api->setUserId($id);
            $api->index();
            $result = $api->response(null, true);
            $result = json_decode($result, true);
            $card = array();
            if (isset($result['q']['card']) && $result['q']['card']) {
                $card = $result['q']['card'];
            }
            $view = new ViewModel(array(
                'card' => $card
            ));
            
            
            
            $view->setTemplate('index/user/userDetails');
            return $this->setMenu($view, 2);
        }
    }
    
    /**
     * 详细的名片信息
     */
    public function pageDetailsAction()
    {
            $userId=isset($_GET['userId'])?$_GET['userId']:''; // user_id
            $id = (int) $this->params()->fromRoute('id');   //pageid  841
            $type = isset($_GET['type']) ? (int) $_GET['type'] : 0;
            $cardDate = $this->getViewPageCarteTable()->getOne(array('id'=>$id));

            //动态数
            $datas=$this->getChatTable()->getAll(array('user_id'=>$cardDate['user_id'],'delete'=>0));
            //查找一度好友

            $relation_a = $this->getUserRelationTable()->fetchAll(array('user_id_1' => $userId, 'attention' => 3));
            $user_2_id = array();
            $user_2_id_where = array();
            $user_new=array();
            foreach ($relation_a as $val){
                $user_2_id[$val['user_id_2']] = 0;
                $user_2_id_where[] = $val['user_id_2'];
                $user_new[]=$val['user_id_2'];
            }
            //查找二度好友
            if(!$user_2_id_where)
            {
                $user_2_id_where[] = 0;
            }
            $where = new where();
            $where->in('user_id_1', $user_2_id_where);
            $where->notEqualTo('user_id_2', $userId);
            $where->equalTo('attention', 3);
            $data = $this->getUserRelationTable()->fetchAll($where);
            $user_1_id = array();
            foreach ($data as $value) {

                if (array_key_exists($value['user_id_2'], $user_2_id)) {
                    $user_2_id[$value['user_id_2']] ++; // 跟好友的共同好友+1
                }
                elseif (array_key_exists($value['user_id_2'], $user_1_id)) {

                    $user_1_id[$value['user_id_2']]++; // 跟二度好友的共同好友+1
                }
                else {
                    $user_1_id[$value['user_id_2']] = 1; //第一次发现这位二度好友
                }
            }

            arsort($user_1_id); // 根据二度好友的共同好友数量排序，大到小
            $cache['deep1'] = $user_2_id;
            $cache['deep2'] = $user_1_id;

            $relationship = 0;
            if (array_key_exists($cardDate['user_id'], $cache['deep1'])) {
                $relationship = 1;
            } elseif (array_key_exists($cardDate['user_id'], $cache['deep2'])) {
                $relationship = 2;
            }

           /* if (array_key_exists($cardDate['user_id'], $cache['deep1'])) {
                $id =$cardDate['user_id'];
                $total = $cache['deep1'][$id];
            } elseif (array_key_exists($cardDate['user_id'], $cache['deep2'])) {
                $id =$cardDate['user_id'];
                $total = $cache['deep2'][$id];
            }*/
            $array=$this->getUserRelationTable()->getAll(array('user_id_1'=>$cardDate['user_id'],'attention'=>3));
            $other_id=array();
            foreach($array['list'] as  $v)
            {
                $other_id[]=$v['user_id_2'];
            }
            $total=0;
            if($user_new && $other_id){
                $new=array_intersect($user_new,$other_id);
                $total=count($new);
            }
            $p_region=$this->getRegionTable()->getOne(array('id'=>$cardDate['c_region_id']));
            $bgImg=$this->getImageTable()->getOne(array('id'=>$cardDate['bg_image']));
            $companyName=$this->getCompanyTable()->getOne(array('id'=>trim($cardDate['company_id']),'delete'=>0));
            $companyinfo='';
            if($companyName)
            {
               $newCompany=$this->getImageTable()->getOneImage($companyName['image']);
               $region=$this->getRegionTable()->getOne(array('id'=>$companyName['region_id']));
			   if($newCompany)
			   {
				    $companyinfo['logo']=$newCompany['path'].@$newCompany['filename'];
					$companyinfo['address']=$companyName['address'];
					$companyinfo['street']=$companyName['street'];
					$companyinfo['id']=$companyName['id'];
                    $companyinfo['city']=$region['name'];
                    $companyinfo['category']=$companyName['category_id'];
                    $companyinfo['scale']=$companyName['scale'];
                    $companyinfo['name']=$companyName['name'];
                    $companyinfo['web']=$companyName['home'];
                    $companyinfo['is_top']=$companyName['is_top'];
                    $companyinfo['telephone']=$companyName['telephone'];
                    $companyinfo['audit_status']=$companyName['audit_status'];
			   }
               
            }
            $carte_info = $this->getCarteTable()->getOne(array('id' => $cardDate['c_id']));
            $company = $this->getCompanyTable()->getOne(array('id' => $cardDate['company_id']));
            if($company){
                $logo = $this->getImageTable()->getOne(array('id'=>$company['image']));
                $company['imagepath'] = $logo['path'] . $logo['filename'];
            }
            /*print_r($companyName);*/
            $vcard = null;
            $wx_code_path = "";
            if ($carte_info) {
                $vcard_path = $this->getImageTable()->getOne(array('id'=>$carte_info['qr_code']));
                $vcard = array(
                    'image_id' => $carte_info['qr_code'],
                    'image_path' => $vcard_path ? $vcard_path['path'] . $vcard_path['filename'] : '',
                    'vcard' => $carte_info['vcard']
                );
                if ($carte_info['wx_code']) {
                    $wx_code_path = $this->getImageTable()->getOne(array('id'=>$carte_info['wx_code']));
                    $wx_code_path = $wx_code_path ? $wx_code_path['path'] . $wx_code_path['filename'] : "";
                }
            }
            $image = $this->getImageTable()->getOne(array('id'=>$cardDate['company_logo']));
            $head_icon = '';
            if ($cardDate['head_icon']) {
                $head_icon = $this->getImageTable()->getOne(array('id'=>$cardDate['head_icon']));
                $head_icon = $head_icon['path'] . $head_icon['filename'];
            }
            
            $show = explode(',', $cardDate['show']);
            if(!in_array('erweima', $show)){
                $cardDate['erweima'] = '';
            }
            
            $erweima_path = '';
            if ($cardDate['erweima']) {
                $erweima_path = $this->getImageTable()->getOne(array('id'=>$cardDate['erweima']));
                $erweima_path = $erweima_path['path'] . $erweima_path['filename'];
            }
            $device= $this->getDeviceTable()->getOne(array('user_id'=>$cardDate['user_id']));
            /*echo "<pre>";
            print_r($cardDate);die();*/
            $view = new ViewModel(array(
                'card' => $cardDate,
                'image' =>$image,
                'head_icon' => $head_icon,
                'erweima_path' => $erweima_path,
                'wx_code_path' => $wx_code_path,
                'vcard' => $vcard,
                'show' => $show,
                'type' => $type,
                'company' => $company,
                'newCompany'=>isset($companyinfo)?$companyinfo:'',
                'scale'=>$this->getAdminController()->scale(),
                'category'=>$this->getAdminController()->category(),
                'total'=>$datas['total'],
                'p_region'=>isset($p_region['name'])?$p_region['name']:'',
                'device'=>isset($device)?$device:'',
                'bg'=>isset($bgImg)?$bgImg:'',
                'common'=>isset($total)?$total:0,
                'degree'=>$relationship,
            ));


            if(!$type){
                if($device)
                {
                    $this->getPageTable()->updateKey($id, 1, 'count', 1);
                    $this->getAdminController()->statOporation(2,1);
                }

                $view->setTemplate('index/user/WxDetails');

            }
            else
            {
                $view->setTemplate('index/user/AppDetails');
            }
            return $this->setMenu($view, 2);
    }
    
    /**
     * 详细名片里面的内容
     * 
     * @version 2015-8-13 WZ
     */
    public function pageContentAction() {
        $id = (int) $this->params()->fromRoute('id');
         $cid = (int) $this->params()->fromRoute('alert');

        if ($id && $cid) {
            $info = $this->getViewPageCarteTable()->getOne(array('id' => $id));
            if ($info) {
                if ($cid == 1) {
                    // 公司或项目描述
                    $content = $info['description'];
                    $title = "公司介绍";
                }
                elseif ($cid == 2) {
                    // 经营项目
                    $content = $info['project'];
                    $title = "经营项目";
                }
            }
        }
//         if(isset($content)) {
            $view = new ViewModel(array(
                'content' => $content,
                'title' => $title
            ));
            $view->setTemplate('index/user/pageContent');
            return $this->setMenu($view, 2);
//         }
//         else {
//             $this->showNext();
//         }
    }
 /**
     * 注册和登陆处理
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function resgiterCheckAction()
    {
        /**
         * 注册获取验证码
         */
        if (isset($_POST['register_code']) && $_POST['register_code'])
        {
            $mobile=$_POST['register_code'];
            if ($mobile)
            {
                $json =array(
                    'q'=>array(
                        'a'=>1,
                        'type'=>1,
                        'mobile'=>$mobile
                    )
                );
            }
        }
        /**
         * 登陆获取验证码
         */
        if (isset($_POST['login_code']) && $_POST['login_code'])
        {
            $mobile=$_POST['login_code'];
            if ($mobile)
            {
                
                $json =array(
                    'q'=>array(
                        'a'=>1,
                        'type'=>2,
                        'mobile'=>$mobile
                    )
                );
            }
        }
        /*
         * 注册登陆
         */
        if (isset($_POST['submit_register']) && $_POST['submit_register'])
        {
            $mobile = $_POST['submit_register'];
            $code = $_POST['code'];
            if ($mobile)
            {
                $this->getAdminController()->statOporation(1,1);
                $json =array(
                    'q'=>array(
                        'a'=>2,
                        'type'=>1,
                        'mobile'=>$mobile,
                        'where'=>array(
                            'code'=>$code
                        ),
                    )
                );
            }
        }
        /*
         * 直接登陆
         */
        if (isset($_POST['submit_login']) && $_POST['submit_login'])
        {
            $mobile=$_POST['submit_login'];
            $code = $_POST['code'];
            if ($mobile)
            {
                $json =array(
                    'q'=>array(
                        'a'=>2,
                        'type'=>2,
                        'mobile'=>$mobile,
                        'where'=>array(
                            'code'=>$code
                        ),
                    )
                );
            }
        }
        
        if (isset($_POST['phone']) && $_POST['phone'])
        {
            $mobile=$_POST['phone'];
            if ($mobile)
            {
                
                $json =array(
                    'q'=>array(
                        'a'=>1,
                        'type'=>4,
                        'mobile'=>$mobile
                    )
                );
            }
        }
        
        $_REQUEST['json'] = json_encode($json);
        $api = new SMSCode();
        $api->index();
        $result = $api->response(null, true);
        echo $result;
        
        $result = json_decode($result, true);

        if ($result['q']['s'] == 0 && ((isset($_POST['submit_login']) && $_POST['submit_login']) || (isset($_POST['submit_register']) && $_POST['submit_register']))) {
            $user_info = $this->getUserTable()->getOne(array('id'=>$result['q']['id']));
           // $_SESSION['index_user_id'] = $result['q']['id'];
            setcookie('index_user_id',$result['q']['id'],time()+3600*24*30,ROOT_PATH);
            $user_data = $this->getUserTable()->getOne(array('id'=>$result['q']['id']));
            setcookie('name',$user_data['name'],time()+3600*24*30,ROOT_PATH);
            setcookie('mobile',$user_data['mobile'],time()+3600*24*30,ROOT_PATH);
            setcookie('code',md5($result['q']['id'].$user_data['mobile'].$user_info['status']),time()+3600*24*30,ROOT_PATH);
           // $_SESSION['name'] = $user_data['name'];
           // $_SESSION['mobile'] = $user_data['mobile'];
        }

        exit();
    }
    
    /**
     * 登出
     * 
     * @version 2015年8月11日 WZ
     */
    public function logoutAction()
    {
        $this->clearCookie();
        $this->redirect()->toRoute('index', array(
            'controller' => 'index',
            'action' => 'index',
        ));
        
    }
    
    /**
     * 用户资料提交页面
     * @return \Zend\View\Model\ViewModel
     * @version 2015年8月10日 WZ
     */
    public function DetailsAction()
    {
        
        $this->login();
        $date = $this->getViewUserPageTable()->getOne(array('mobile'=>$_COOKIE['mobile']));
        $image = $this->getImageTable()->getOne(array('id'=>$date['wx_code']));
        $view = new ViewModel(array(
            'data'=>$date,
            'image'=>$image
        ));
        $view->setTemplate('index/user/login');
        return $this->setMenu($view, 1);
    }
    
   public function buyDetailsAction()
   {

       $check_box = $this->login();

       if(!$check_box)
       {
            $this->redirect()->toRoute('index/index');             
       }
       else 
       {
            $pay_type = isset($_POST['pay_type']) ? (int) $_POST['pay_type'] : 0;

            if ($pay_type == 3){
                $return =  $this->getApiController()->getYlPayInfo($_POST['order_sn'],$_POST['amount'],1);
                echo json_encode(array('content' => $return));
                die();              
            }else if ($pay_type == 1) {
                
            }else if ($pay_type == 2) {

            }
             
            $view = new ViewModel();
            $view->setTemplate('index/user/buy');
            return $this->setMenu($view, 1);
       }        

   }
    /**
     * 用户提交操作
     * 
     * @version 2015年8月10日 WZ
     */
    public function DetailsSubAction()
    {
        $check_box = $this->login();
        if (isset($_POST['submit']) && $_POST['submit'])
        {
           /*  $province_id = $_POST['province_id'];
            $city_id = $_POST['city_id'];
            $county = $_POST['county'] ? $_POST['county'] : $city_id;
            $region_info = $this->encode($county, $city_id, $province_id);
            $region_info_arr = array();
            $street = $address = trim($_POST['street']);
            if ($region_info) {
                $region_info_arr = $this->decode($region_info);
                $address = $this->getProvinceCityCountryName($region_info) . $street;
            }
             */
           
            $_POST['name'] = $_COOKIE['name'] ? $_COOKIE['name']  :  $_POST['name']; 

            
            if (!$_POST['name'])
            {
                $this->showMessage("用户名不能为空");
                
            }
            if (!$_POST['position'])
            {
                $this->showMessage("职位不能为空");
            }
            else 
            {
                if(isset($_FILES) && $this->check_file_type($_FILES['head_icon']['tmp_name'])){
                
                    $file = $this->getApiController()->uploadImageForController("head_icon");
                    $image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
                }else{
                    $image_id = $_POST['image'];
                }
             /*    if(isset($_FILES) && $this->check_file_type($_FILES['wx_code']['tmp_name'])){
                
                    $file = $this->getApiController()->uploadImageForController("wx_code");
                    $wx_code_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
                }else{
                    $wx_code_id = $_POST['wx_code'];
                } */
                $wx_code_id = $_POST['wx_code'];
                $data =array(
                    'title' => $_POST['name'],
                    );
                $list =array(
                    'name'=>$_POST['name'],
                    'signature'=>$_POST['signature'],
                    'telephone'=>$_POST['telephone'],
                    'qq' =>$_POST['qq'],
                    'email'=>$_POST['email'],
                    'position'=>$_POST['position'],
                    'mobile'=>$_COOKIE['mobile'],
                  //  'weixin_number'=>$_POST['weixin_number'],
                    'head_icon'=>$image_id,
                    'wx_code'=>$wx_code_id,
                    'weibo_link'=>$_POST['weibo_link'],
                  /*   'address' => $address,
                    'region_id' => $county,
                    'region_info' => $region_info,
                    'street' => $street,
                    'longitude' => trim($_POST['longitude']),
                    'latitude' => trim($_POST['latitude']), */
                );
                $userData = $this->getViewUserPageTable()->getOne(array('mobile'=>$_COOKIE['mobile']));
                if ($userData)
                {
                    if ($userData['page_id'])
                    {
                        $this->getPageTable()->updateData($data, array('id'=>$userData['page_id']));
                        if (!$userData['name'])
                        {
                            setcookie('name',$_POST['name']);
                            $this->getUserTable()->updateData(array('name'=>$_POST['name']),array('mobile'=>$_COOKIE['mobile']));
                            
                        }
                    }else 
                    {
                        $pageID = $this->getPageTable()->insertData($data);
                         setcookie('name',$_POST['name']);
                        $this->getUserTable()->updateData(array('page_id'=>$pageID,'name'=>$_POST['name']), array('id'=>$userData['id']));
                    }
                    if ($userData['carte_id'])
                    {
                        $this->getCarteTable()->updateData($list, array('id'=>$userData['carte_id']));
                       $url = $this->plugin('url')->fromRoute('index', array(
                            'controller' => 'user',
                            'action' => 'DetailsTwo',
                        ));
                        $this->showNext($url);
                       // $this->back("提交成功",array('c'=>'user','a'=>'DetailsTwo'));
                    }else
                    {
                        $carteID = $this->getCarteTable()->insertData($list);
                        $userDat = $this->getViewUserPageTable()->getOne(array('mobile'=>$_COOKIE['mobile']));
                        $this->getPageTable()->updateData(array('carte_id'=>$carteID), array('id'=>$userDat['page_id']));
                        $url = $this->plugin('url')->fromRoute('index', array(
                            'controller' => 'user',
                            'action' => 'DetailsTwo',
                        ));
                        $this->showNext($url);
                        //$this->back("提交成功",array('c'=>'user','a'=>'DetailsTwo'));
                       
                    }
                    
                }
            }
            
        }
    }
    /**
     * 第二个提交页面
     * @return \Zend\View\Model\ViewModel
     * @version 2015年8月10日 WZ
     */
    public function DetailsTwoAction()
    {
      
        $this->login();
        $data = $this->getViewUserPageTable()->getOne(array('mobile'=>$_COOKIE['mobile']));
        $imageData = $this->getImageTable()->getOne(array('id'=>$data['company_logo']));
        $region = $data->region_info ? $data->region_info : '';
        if($region)
        {
            $region = json_decode($region,true);
        }

        $view = new ViewModel(array(
            'data' => $data,
            'imageData' => $imageData,
            'region'=> $region
        ));
        $view->setTemplate('index/user/login2');
        return $this->setMenu($view, 1);
    }
    
    public function DetailsTwosubAction()
    {   
        if (isset($_POST['submit']) && $_POST['submit']) {
            $province_id = $_POST['province_id'];
            $city_id = $_POST['city_id'];
            $county = $_POST['county'] ? $_POST['county'] : $city_id;
            $region_info = $this->encode($province_id,$city_id,$county);
            $region_info_arr = array();
            $street = $address = trim($_POST['street']);
            if ($region_info) 
            {
                $region_info_arr = $this->decode($region_info);
                $address = $this->getProvinceCityCountryName($region_info) . $street;
            }

            if (isset($_FILES) && $this->check_file_type($_FILES['Filedata']['tmp_name'])) {
                $file = $this->getApiController()->uploadImageForController("Filedata");
                $image_id = isset($file["ids"][0]) ? $file["ids"][0] : 0;
            } else {
                $image_id = $_POST['company_logo'];
            }
            
            //2015.10.10 提交网址补丁 HY
            if(strstr($_POST['web_address'],'https://')){
                $web_address = $_POST['web_address'];
            }else{
                $web_address = 'https://'.$_POST['web_address'];
            }
            
            $list = array(
                'company' => $_POST['company_name'],
                'en_company' => $_POST['en_company'],
                'industry' => $_POST['industry'],
                'street' => $_POST['street'],
                'web_address' => $_POST['web_address'],
                'description' => $_POST['company'],
                'project' => '',
                'company_logo' => $image_id,
                'address' => $address,
                'region_id' => $county,
                'region_info' => $region_info,
                'street' => $street,
                'longitude' => trim($_POST['longitude']),
                'latitude' => trim($_POST['latitude']),
            );
            $data = $this->getViewUserPageTable()->getOne(array(
                'mobile' => $_COOKIE['mobile']
            ));
            
            if ($data['carte_id']){
            $carte = $this->getCarteTable()->updateData($list, array(
                'id' => $data['carte_id']
            ));
            }else 
            {
                
                $carte = $this->getCarteTable()->insertData($list);
               if ($data['page_id'])
               {
                   $this->getPageTable()->updateData(array('carte_id'=>$carte),array('id'=>$data['page_id']));
               }
               else 
               {
                   $pageId = $this->getPageTable()->insertData(array('carte_id'=>$carte));
               }
                $this->getUserTable()->updateData(array('page_id'=>$pageId),array('id'=>$data['id']));
            }
            if ($carte>=0) {
                $url = $this->plugin('url')->fromRoute('index', array(
                    'controller' => 'user',
                    'action' => 'imageData',
                ));
                $this->showNext($url);
                $this->redirect()->toRoute('index', array(
                    'controller' => 'user',
                    'action' => 'DetailsTwo'
                ));
            }
        }
    }
    public function imageDataAction()
    {
        $this->login();
       // $userData = $this->getViewUserPageTable()->getOne(array('mobile'=>$_COOKIE['mobile']));
       // $image = $this->getImageTable()->getOne(array('id'=>$userData['wx_image']));
        $view = new ViewModel(array(
            ));
        $view->setTemplate('index/user/image');
        return $this->setMenu($view, 1);
    }

    
    
    /**
     * 订单查询
     *
     * @version 2015-8-20 HY
     */
    public function orderAction() {
        $this->login();
        $user_id = $_COOKIE['index_user_id'];
        $list = array();
        $this->action = "order";
        $this->table =  $this->getOrderTable();
        $this->Where = array('user_id'=>$user_id);
        $this->template = array('user/order','order');//视图
        $this->delete = false;
        return  $this->getList();
    }
    
    /**
     * 推荐码/提现 用户个人信息
     *
     * @version 2015-8-25 HY
     */
    public function withdrawAction(){
        $this->login();
        $banklist = $this->bankList();
        
        $page = $this->params("page",1);
        $is_page = isset($_GET['is_page'])?$_GET['is_page']:'';
        $user_id = $_COOKIE['index_user_id']; 

        $user_where = array('id'=>$user_id);
        $user =  $this->getUserTable()->getOne($user_where);//一维数组
        
        $where = new Where();
      
        $where->lessThan('recommend_stat',$user['recommend_stat']);
        $num = $this->getUserTable()->countData($where);//超过人数
        //$number_code=$this->getInvitationCodeTable()->countData(array('user_id'=>$user_id,'status'=>1));
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        $financial_info = $this->getFinancialTable()->getAll($where,null,null,true,$page,8);//用户个人提现信息
        
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        $order=array(
            'status'=>'ASC'
        );
        $K_code = $this->getInvitationCodeTable()->fetchAll($where,$order);//用户个人提现信息   
        
        $view = new ViewModel(array(
            'paginator' => $financial_info['paginator'],
            'condition' => array(
                'action' => 'withdraw',
                'page' => $page,
                'where' => array('is_page'=>1)
            ),
            'user' => $user,
            'num' => $num,
           //'$number_code'
            'is_page' => $is_page,
            "page" => $page,
            'financial_info'=>$financial_info['list'],
            'K_code'=>$K_code,
            'banklist'=>$banklist
        ));
        $view->setTemplate('index/user/withdraw');
        return $this->setMenu($view, 1); 
    }
    
    /**
     * 推荐码信息
     *
     * @version 2015-8-25 HY
     */
    public function inviationCodeAction(){
        $this->login();
        $user_id = $_COOKIE['index_user_id'];
        
        $where = new Where();
        $where->equalTo('user_id',$user_id);
        $inviation_info = $this->getInvitationCodeTable()->fetchAll($where);
        //print_r($inviation_info);//K码即邀请码信息
    } 
    
    /**
     * 申请提现
     *
     * @version 2015-8-27 HY
     */
    public function withdrawapplyAction(){
        $this->login();
        $user_id = $_COOKIE['index_user_id'];
        
        $user_where = array('id'=>$user_id);
        $user =  $this->getUserTable()->getOne($user_where);//一位数组
        
        $bankList = $this->bankList();
        $view = new ViewModel(array(
            'user'=>$user,
            'bankList'=>$bankList
        ));
        $view->setTemplate('index/user/withdrawapply');
        return $this->setMenu($view, 1);
    }
    
    /**
     * 提现添加
     *
     * @version 2015-8-31 HY
     */
    public function withdrawinsertAction(){
        $this->login();
        $user_id = $_COOKIE['index_user_id'];
        /*
         * 注册登陆
         */
        if (isset($_POST['codes']) && $_POST['codes'])
        {
            $mobile = $_COOKIE["mobile"];
            $code = $_POST['codes'];
            if ($mobile)//有手机号
            {
                $json =array(
                    'q'=>array(
                        'a'=>2,
                        'type'=>4,
                        'mobile'=>$mobile,
                        'where'=>array(
                            'code'=>$code
                        ),
                    )
                );
            }
        }
        else
        {
           echo 3;
           die();
        }
        $_REQUEST['json'] = json_encode($json);

        $api = new SMSCode();
        $api->index();
        $result = $api->response(null, true);
        $result = json_decode($result,true);

       if($result['q']['s'] !=0)
       {
           echo 4;
           die();
       }

        $user_where = array('id'=>$user_id);
        $user =  $this->getUserTable()->getOne($user_where);
        
        $transfer_no = date("YmdHis",time()).mt_rand(10,99);
        $timestamp = date("YmdHis",time());
        
        if(isset($_POST['money'])){  
            if($_POST['money']>$user['money'] || $_POST['money']<0){
                echo 0;
                die;
            }
            
            $withdraw = array(
                'amount'=>trim($_POST['money']),
                'card_owner'=>trim($_POST['card_owner']),
                'card_number'=>trim($_POST['card_number']),
                'payment_type'=>4,
                'status'=>3,
                'timestamp'=>$timestamp,
                'transfer_no'=>$transfer_no,
                'income'=>2,
                'type'=>3,
                'bank'=>$_POST['bank'],
                'user_id'=>$user_id,
            );
            if($this->getFinancialTable()->insert($withdraw)){
                
                $rest_money = $user['money']-$_POST['money'];
                $set = array('money'=>$rest_money);
                $where = array('id'=>$user_id);
                if($this->getUserTable()->update($set,$where)){
                    echo 1;
                    die;      
                }else{
                    echo 0;
                    die;
                }
            }else{
                echo 0;
                die;
            }
            
        }
      
    }
    /**
     * 提现申请成功
     *
     * @version 2015-9-1 HY
     */
    public function withdrawsuccessAction(){
        
        $this->login();
         
    }
    
    /**
     * 生成微信预付单并返回支付信息
     * @param number $id 订单ID
     * @param number $amount 支付金额
     * @version  2015-07-14
     */
    public function getWxPayInfoAction()
    {   
        $order_sn  = $_POST['order_sn'];
        if ($order_sn) 
        {
            $order_info = $this->getOrderTable()->getOne(array('order_sn'=>$order_sn));
            if($order_info && $order_info->total && $order_info->status == 1)
            {
            	$value = array(
            	    'total_fee' => $order_info->total,
            	    'body' => '购买快摇名片随身设备消费:' . $order_info->total . '元',
            	    'out_trade_no' => $order_sn
            	);
            	$wxpay = new AiiWxPay();
            	$wx_data = $wxpay->setValue($value,1)->getNative();
            	
            	if($wx_data['result_code'] == 'SUCCESS')
            	{

            	    echo $this->generateCode($wx_data['code_url']);
            	    die();
            	}
            }
         
        } else {
           echo '';
        }
        die('');
    }
    
    
    /**
     * 获取微信扫码支付订单状态
     *
     * @version
     *          2015年8月7日
     * @author
     *         liujun
     */
    public function getWxOrderStatusAction()
    {
        $out_trade_no = isset($_POST['out_trade_no']) ? $_POST['out_trade_no'] : '';
        if ($out_trade_no) {
            $wxApi = new AiiWxPay();
            $wxData = $wxApi->setValue(array(
                'out_trade_no' => $out_trade_no
            ), 2)->orderQuery();
            
            if (isset($wxData['trade_state']) && $wxData['trade_state'] == 'SUCCESS') { // 支付成功
   
              $order_info = $this->getOrderTable()->getOne(array('order_sn'=>$out_trade_no));
              for($i=1;$i<=8;$i++)
              {
                  $code = $this->getApiController()->makeCode(6, 6);//生成6位随机字母加数字为推荐码
                  $set = array('code'=>$code,'user_id'=>$order_info['user_id'],'timestamp'=>$this->getTime());
                  $this->getInvitationCodeTable()->insert($set);
              }
              if($order_info['code_id']){
                  $statusUpdata = $this->getInvitationCodeTable()->updateData(array('recommended_user_id'=>$order_info['user_id'],'status'=>1), array('id'=>$order_info['code_id']));//k码状态更新
                  
                  $code_id = $this->getInvitationCodeTable()->getOne(array('id'=>$order_info['code_id']));
                  $codeOwner_info = $this->getUserTable()->getOne(array('id'=>$code_id['user_id']));
                  
                  $money = $codeOwner_info['money'] + 500;
                  $recommend_bonus = $codeOwner_info['recommend_bonus'] + 500;
                  $recommend_stat = $codeOwner_info['recommend_stat'] + 1;
                  
                  $recommendedAwards = $this->getUserTable()->updateData(array('money' => $money,'recommend_bonus' => $recommend_bonus,'recommend_stat' => $recommend_stat,'is_buy' => 1), array('id' => $code_id['user_id']));
                  $order_status = $this->getOrderTable()->updateData(array('status'=>2),array('order_sn'=>$out_trade_no));
                  if($order_status && $recommendedAwards && $statusUpdata){
                      echo 1;
                      die();
                  }else{
                      echo 0;
                      die();
                  }
              }else{
                  $order_status = $this->getOrderTable()->updateData(array('status'=>2),array('order_sn'=>$out_trade_no));
                      if($order_status){
                          echo 1;
                          die();
                      }else{
                          echo 0;
                          die();
                      }
              }   
               
            } else { // 失败
                echo 0;
                die();
            }
        }
        echo 0;
        die();
    }
    
    /**
     *
     * 前端生成临时二维码
     *
     * @param $str 要生成的字符串
     * @version
     *          2015年6月30日
     * @author
     *         liujun
     */
    public function generateCode($str)
    {
        if (! $str) {
            die();
        }
        $url = "http://api.wwei.cn/wwei.html?data=" . urlencode($str) . "&version=1.0&apikey=20141110217674";
        $json_data = file_get_contents($url);

        $data = json_decode($json_data);

        if (isset($data->status) && $data->status == 1) {
            $img_url = $data->data->qr_filepath;
        } else {
            $img_url = '';
        }
        return ($img_url);
    }
    
    /** 9.28
     * 新增用户/编辑用户
     */
    public function addAction()
    {
        $data_array = array(
            'head_icon',
            'name',
            'signature',
            'mobile',
            'telephone',
            'qq',
            'email',
            'weixin_number',
            'weibo',
            'company_logo',
            'company_name',
            'en_company',
            'industry',
            'street',
            'web_address',
            'description',
            'company_album',
            'project',
            'project_album',
            'tianmao_shop_url',
            'jingdong_shop_url',
            'taobao_shop_url',
            'wx_code',
            'position'
    
        );
    
        $info = '';
        $images= array();
    
        if (isset($_POST['is_post']) && $_POST['is_post'])
        {//提交保存数据
            $verification_item = array(
                'name',
                'user_id'
            );
    
            foreach ($verification_item as $val)
            {
                if(!isset($_POST[$val]) || !$_POST[$val])
                {
                    return $this->showMessage("提交数据不完整");
                }
            }
    
            if(isset($_POST['wxjs_toke']) && $_POST['wxjs_toke'])
            {
                if(isset($_POST['head_icon']) &&  $_POST['head_icon'])
                {
                    if(!is_numeric($_POST['head_icon']))
                    {
                        $head_image = $this->generatePictures($_POST['wxjs_toke'], $_POST['head_icon']);
                        $_POST['head_icon'] = isset($head_image['id']) ? $head_image['id'] : 0;
                    }
                }
    
                if(isset($_POST['wx_code']) &&  $_POST['wx_code'])
                {
                    if(!is_numeric( $_POST['wx_code']))
                    {
                        $wx_image = $this->generatePictures($_POST['wxjs_toke'], $_POST['wx_code']);
    
                        if(isset($wx_image['id']) && $wx_image['id'])
                        {
                            $wx_image_path = HTTP . ROOT_PATH.UPLOAD_PATH.$wx_image['path'];
                            $url = "http://api.wwei.cn/dewwei.html?data=" . $wx_image_path . "&apikey=20141110217674";//读取二维码内容
                            $json_data = file_get_contents($url);
                            $data = json_decode($json_data);
    
                            if (isset($data->status) && $data->status == 1)
                            {
                                $text = $data->data->raw_text;
                                $imgInfo = $this->generationQRcode($text);
                                $_POST['wx_code']  = $imgInfo['id'];
                            }
                        }
                    }
                }
            }
            $user = $this->getUserTable()->getOne(array('id'=>$_POST['user_id']));
            $_POST['mobile'] = $user->mobile;
            $carte_data = array(
                'name' => $_POST['name'],
                'position' => $_POST['position'],
                'mobile' => $_POST['mobile'],
                'telephone' => $_POST['telephone'],
                'qq' => $_POST['qq'],
                'email' => $_POST['email'],
                'head_icon' => $_POST['head_icon'],
                'wx_code' => $_POST['wx_code'],
                'company' => $_POST['company'],
                'web_address' => $_POST['web_address']
            );
    
            $page_data = array(
                'title' => $_POST['name'],
                'user_id' => $_POST['user_id']
            );
    
            if($user->page_id)
            {
                $page = $this->getPageTable()->getOne(array('id'=>$user->page_id));
    
                if($page->carte_id)
                {
                    $carte_id = $page->carte_id;
                    $this->getCarteTable()->update($carte_data,array('id'=>$page->carte_id));
                }
                else
                {
                    $carte_data['timestamp'] = $this->getTime();
                    $carte_id = $this->getCarteTable()->insertData($carte_data);
                }
                $page_data['carte_id'] = $carte_id;
                $page_data['timestamp'] = $this->getTime();
                $this->getPageTable()->updateData($page_data,array('id'=>$user->page_id));
            }
            else
            {
                $carte_data['timestamp'] = $this->getTime();
                $carte_id = $this->getCarteTable()->insertData($carte_data);
                $page_data['timestamp'] = $this->getTime();
                $page_data['carte_id'] = $carte_id;
                $page_id = $this->getPageTable()->insertData($page_data);
                $this->getUserTable()->update(array('page_id'=>$page_id),array('id'=>$_POST['user_id']));
    
            }
             
            $data = array();
            foreach ($data_array as $k)
            {
                $data[$k] = isset($_POST[$k]) && $_POST[$k] ? $this->HtmlFilter($_POST[$k]) : '';
            }
    
            $this->getUserApplicationTable()->insertData($data);
    
    
            return $this->redirect ()->toRoute ( 'admin-user', array (
                'action' => 'success'
            ) );
    
        }
        else
        {
            $WxJsApi =  new WxJsApi();
            $openid = $WxJsApi->GetOpenid();    
            $_SESSION['openid'] = $openid;
    
            if($_SESSION['openid'])
            {
                $user_partner = $this->getUserPartnerTable()->getOne(array('open_id'=>$_SESSION['openid']));
    
                $info = $this->getViewUserPageTable()->getOne(array('id'=>$user_partner->user_id));
    
                $image_id = array();
                if(isset($info->wx_code) && $info->wx_code)
                {
                    $image_id[] = $info->wx_code;
                }
                if(isset($info->head_icon) && $info->head_icon)
                {
                    $image_id[] = $info->head_icon;
                }
    
                if($image_id)
                {
                    $images = $this->getImageTable()->getImages($image_id);
                }
            }
        }
    
        $template = 'index/user/add';
        $view = new ViewModel(array('info'=>$info,'images'=>$images));
        $view->setTemplate($template);
        return $view;
    }
    
    /**
     *
     * 手机页面切换主名片
     *
     * @version
     *          2015年11月2日
     * @author
     *         HY
     */
    
    public function checkcompanyAction()
    {   //验证
        $id = $_POST['id'] ? $_POST['id'] : '0';//用户id
        $mid = $_POST['mcard'] ? $_POST['mcard'] : '0';
        $cid = $_POST['wcard'] ? $_POST['wcard'] : '0';
        $card = $this->getViewPageCarteTable()->getOne(array('id'=>$cid,'user_id'=>$id));
        $mcard = $this->getViewPageCarteTable()->getOne(array('id'=>$mid,'user_id'=>$id));
        if($mid == $cid){
            echo 1;
            die();
        }
        else if($mid !== $cid && $card){
            echo 2;
            die;
        }else{
            echo 3;
            die;
        }

        die;

        

    }
    
    /**
     *
     * 手机页面切换主名片
     *
     * @version
     *          2015年11月2日
     * @author
     *         HY
     */
    
    public function setcardAction(){
        $id = $_POST['id'];
       
        if (! $id) {
            echo 2;
            die();
        }
        
        $carde = $this->getPageTable()->getOne(array('id'=>$id));
        
        if(!$carde){
            echo 2;
            die();
        }
        
        $page = $this->getPageTable()->getOne(array(
            'id' => $id,
            'user_id' => $carde['user_id']
        ));
        
        if (!$page){
            echo 2;
            die();
        }
        
        $this->getUserTable()->updateData(array(
            "page_id" => $id,
        ), array(
            "id" => $carde['user_id']
        ));
        echo 1;
        die();
    }
    /*
     * 名片详情 v1.5;
     *
     *
     * */
    public function cardDetailsAction()
    {
        $userId=isset($_GET['userId'])?$_GET['userId']:''; // user_id
        $id = (int) $this->params()->fromRoute('id');   //pageid  841
        if(!$id){
            $id = $_GET['id'];
        }
        /*echo $id;die;*/
        $type = isset($_GET['type']) ? $_GET['type'] : 0;
        /*$userId=150;*/
        /* $cardDate['user_id']=139;*/
        $cardDate = $this->getViewPageCarteTable()->getOne(array('id'=>$id));
        //动态数
        $datas=$this->getChatTable()->getAll(array('user_id'=>$cardDate['user_id'],'delete'=>0));
        //查找一度好友

        $relation_a = $this->getUserRelationTable()->fetchAll(array('user_id_1' => $userId, 'attention' => 3));
        $user_2_id = array();
        $user_2_id_where = array();
        $user_new=array();
        foreach ($relation_a as $val){
            $user_2_id[$val['user_id_2']] = 0;
            $user_2_id_where[] = $val['user_id_2'];
            $user_new[]=$val['user_id_2'];
        }
        //查找二度好友
        if(!$user_2_id_where)
        {
            $user_2_id_where[] = 0;
        }
        $where = new where();
        $where->in('user_id_1', $user_2_id_where);
        $where->notEqualTo('user_id_2', $userId);
        $where->equalTo('attention', 3);
        $data = $this->getUserRelationTable()->fetchAll($where);
        $user_1_id = array();
        foreach ($data as $value) {

            if (array_key_exists($value['user_id_2'], $user_2_id)) {
                $user_2_id[$value['user_id_2']] ++; // 跟好友的共同好友+1
            }
            elseif (array_key_exists($value['user_id_2'], $user_1_id)) {

                $user_1_id[$value['user_id_2']]++; // 跟二度好友的共同好友+1
            }
            else {
                $user_1_id[$value['user_id_2']] = 1; //第一次发现这位二度好友
            }
        }

        arsort($user_1_id); // 根据二度好友的共同好友数量排序，大到小
        $cache['deep1'] = $user_2_id;
        $cache['deep2'] = $user_1_id;

        $relationship = 0;
        if (array_key_exists($cardDate['user_id'], $cache['deep1'])) {
            $relationship = 1;
        } elseif (array_key_exists($cardDate['user_id'], $cache['deep2'])) {
            $relationship = 2;
        }

        /* if (array_key_exists($cardDate['user_id'], $cache['deep1'])) {
             $id =$cardDate['user_id'];
             $total = $cache['deep1'][$id];
         } elseif (array_key_exists($cardDate['user_id'], $cache['deep2'])) {
             $id =$cardDate['user_id'];
             $total = $cache['deep2'][$id];
         }*/
        $array=$this->getUserRelationTable()->getAll(array('user_id_1'=>$cardDate['user_id'],'attention'=>3));
        $other_id=array();
        foreach($array['list'] as  $v)
        {
            $other_id[]=$v['user_id_2'];
        }
        $total=0;
        if($user_new && $other_id){
            $new=array_intersect($user_new,$other_id);
            $total=count($new);
        }
        $p_region=$this->getRegionTable()->getOne(array('id'=>$cardDate['c_region_id']));
        $bgImg=$this->getImageTable()->getOne(array('id'=>$cardDate['bg_image']));
        $companyName=$this->getCompanyTable()->getOne(array('id'=>trim($cardDate['company_id']),'delete'=>0));
        $companyinfo='';
        if($companyName)
        {
            $newCompany=$this->getImageTable()->getOneImage($companyName['image']);
            $region=$this->getRegionTable()->getOne(array('id'=>$companyName['region_id']));
            if($newCompany)
            {
                $companyinfo['logo']=$newCompany['path'].@$newCompany['filename'];
                $companyinfo['address']=$companyName['address'];
                $companyinfo['street']=$companyName['street'];
                $companyinfo['id']=$companyName['id'];
                $companyinfo['city']=$region['name'];
                $companyinfo['category']=$companyName['category_id'];
                $companyinfo['scale']=$companyName['scale'];
                $companyinfo['name']=$companyName['name'];
                $companyinfo['web']=$companyName['home'];
                $companyinfo['is_top']=$companyName['is_top'];
                $companyinfo['telephone']=$companyName['telephone'];
                $companyinfo['audit_status']=$companyName['audit_status'];
                $companyinfo['latitude']=$companyName['latitude'];
                $companyinfo['longitude']=$companyName['longitude'];
                $companyinfo['audit_status']=$companyName['audit_status'];

            }

        }
        $carte_info = $this->getCarteTable()->getOne(array('id' => $cardDate['c_id']));
        $company = $this->getCompanyTable()->getOne(array('id' => $cardDate['company_id']));
        if($company){
            $logo = $this->getImageTable()->getOne(array('id'=>$company['image']));
            $company['imagepath'] = $logo['path'] . $logo['filename'];
        }
        /*print_r($companyName);*/
        $vcard = null;
        $wx_code_path = "";
        if ($carte_info) {
            $vcard_path = $this->getImageTable()->getOne(array('id'=>$carte_info['qr_code']));
            $vcard = array(
                'image_id' => $carte_info['qr_code'],
                'image_path' => $vcard_path ? $vcard_path['path'] . $vcard_path['filename'] : '',
                'vcard' => $carte_info['vcard']
            );
            if ($carte_info['wx_code']) {
                $wx_code_path = $this->getImageTable()->getOne(array('id'=>$carte_info['wx_code']));
                $wx_code_path = $wx_code_path ? $wx_code_path['path'] . $wx_code_path['filename'] : "";
            }
        }
        $image = $this->getImageTable()->getOne(array('id'=>$cardDate['company_logo']));
        $head_icon = '';
        if ($cardDate['head_icon']) {
            $head_icon = $this->getImageTable()->getOne(array('id'=>$cardDate['head_icon']));
            $head_icon = $head_icon['path'] . $head_icon['filename'];
        }

        $show = explode(',', $cardDate['show']);
        if(!in_array('erweima', $show)){
            $cardDate['erweima'] = '';
        }

        $erweima_path = '';
        if ($cardDate['erweima']) {
            $erweima_path = $this->getImageTable()->getOne(array('id'=>$cardDate['erweima']));
            $erweima_path = $erweima_path['path'] . $erweima_path['filename'];
        }
        $device= $this->getDeviceTable()->getOne(array('user_id'=>$cardDate['user_id']));
        /*echo "<pre>";
        print_r($cardDate);die();*/
        $view = new ViewModel(array(
            'card' => $cardDate,
            'image' =>$image,
            'head_icon' => $head_icon,
            'erweima_path' => $erweima_path,
            'wx_code_path' => $wx_code_path,
            'vcard' => $vcard,
            'show' => $show,
            'type' => $type,
            'company' => $company,
            'newCompany'=>isset($companyinfo)?$companyinfo:'',
            'scale'=>$this->getAdminController()->scale(),
            'category'=>$this->getAdminController()->category(),
            'total'=>$datas['total'],
            'p_region'=>isset($p_region['name'])?$p_region['name']:'',
            'device'=>isset($device)?$device:'',
            'bg'=>isset($bgImg)?$bgImg:'',
            'common'=>isset($total)?$total:0,
            'degree'=>$relationship,
            'id' =>$id,
        ));
        

        if(!$type){
            if($device)
            {
                $this->getPageTable()->updateKey($id, 1, 'count', 1);
            }

            $view->setTemplate('index/user/WxDetails');

        }
        else
        {
            $view->setTemplate('index/user/AppDetails2');
        }
        return $this->setMenu($view, 2);
    }




    /*
     * 用户动态
     *
     *
     * */
    public function relationCircleAction(){
        $id = $this->params()->fromRoute('id');
        //$id=26;
        $chat = $this->getChatTable()->getOne(array('id' => $id));
        $user = $this->getUserTable()->getOne(array('id' => $chat['user_id']));
        $info = $this->getViewPageCarteTable()->getOne(array('id' => $user['page_id']));
        $head_icon = '';
        if($info){
            $head = $this->getImageTable()->getOne(array('id' => $info['head_icon']));
            $head_icon = $head['path'].$head['filename'];
        }
        $img = array();
        if( $chat['images'] ){
            $ids = explode(',',$chat['images']);
            $where = new where();
            $where -> in('id',$ids);
            $images = $this->getImageTable()->getAll($where);
            foreach($images['list'] as $v){
                $img[] =$v['path'].$v['filename'];
            }
        }
        $viw=new ViewModel(array(
            'content' => isset($chat['content']) ? $chat['content'] : '',
            'images' => $img,
            'head_icon' => $head_icon,
            'presonal' => $info
        ));
        $viw->setTemplate('index/user/relationCircle');
        return $viw;
    }


    /*
    * 购买支付页面
    *
    * */

    public function orderSubmitAction()
    {
        $this->login();
        $user_info = $this->getUserTable()->getOne(array('mobile' => $_COOKIE['mobile']));
        $infos = $this->getUserAddressTable()->getOne(array('user_id'=>$user_info['id']));
        $main_card = $this->getViewPageCarteTable()->getOne(array('id' =>$user_info['page_id']));
        if (! $infos) {
            //第一次获取地址信息，自动名片信息加到地址信息上
            $user_info = $this->getUserTable()->getOne(array('mobile' => $_COOKIE['mobile']));
            $page_info = null;
            if ($user_info) {
                $page_info = $this->getViewUserPageTable()->getOne(array('page_id' => $user_info['page_id']));
            }

            $info = array(
                'name' => ($page_info && $page_info['name']) ? $page_info['name'] : '',
                'region_id' => ($page_info && $page_info['region_id']) ? $page_info['region_id'] : 0,
                'region_info' => ($page_info && $page_info['region_info']) ? $page_info['region_info'] : '',
                'street' => ($page_info && $page_info['street']) ? $page_info['street'] : '',
                'telephone' => ($page_info && $page_info['mobile']) ? $page_info['mobile'] : '',
                'address' => ($page_info && $page_info['address']) ? $page_info['address'] : '',
                'user_id' => $user_info['id'],
                'timestamp' => $this->getTime(),
            );

            $info['id'] = $this->getUserAddressTable()->insertData($info);
        }else{
            $this->getUserAddressTable()->updateData(array('name' =>$main_card['name']),array('user_id' => $user_info['id']));
        }

        //订单提交
        if($_POST) {
            $codeInfo = $this->getInvitationCodeTable()->getAll(array('status'=>0));
            $user = $this->getUserTable()->getOne(array('mobile' => $_COOKIE['mobile']));
            $address_info1 = $this->getUserAddressTable()->getOne(array('user_id' => $user['id']));
            $region = $this->getAllRegionInfo($_POST['county']);
            $region_info = $this->encode($region['province_id'], $region['city_id'], $region['county_id']);
            $address = $region['province'] . $region['city'] . $region['county'];
            $regionId = $_POST['region_id'];
            $payment_type = $_POST['payment_type'];
            $order_num = (string)$this->makeSN();
            $price = 198;
            $amount = 30;
            $number = $_POST['buy_num'];
            $code = $_POST['code'];
            $code_2 = array();
            foreach($codeInfo['list'] as $v){
                $code_2[] = $v['code'];
            }
            $code_num=0;
            if($code[0]){
                $code_num = count($code);
                foreach($code as $val){
                    if(!in_array(trim($val),$code_2)){
                        $this->showTu('推荐码不正确');
                        beak;
                    }
                }
            }

            if (count($code) != count(array_unique($code))) {
                $this->showTu('请勿输入重复验证码');
            }
            $total = $price * $number-$code_num*$amount;
            $where = new where();
            $where->in('code', $code);
            $code_info = $this->getInvitationCodeTable()->getAll($where);
            $codeIdArr = array();
            if ($code_info['list'] && is_array($code_info['list'])) {
                foreach ($code_info['list'] as $v) {
                    $codeIdArr[] = $v['id'];
                }

            }
            $codeString = '';
            if ($codeIdArr) {
                $codeString = implode(',', $codeIdArr);
            }
            //发票
            $invoice = $_POST['invoice_status'];
            if ($invoice == 0) {
                $invoice = 1;
                $invoice_type = '';
                $invoice_name = '';
            } else {
                $invoice = $_POST['invoice_status'];
                $invoice_type = $_POST['invoice_type'];
                $invoice_name = $_POST['input_name'];
            }
            //地址处理
            $address_data = array(
                'name' => $_POST['name'],
                'telephone' => $_POST['mobile'],
                'region_id' => $_POST['county'],
                'region_info' => $region_info,
                'street' => $_POST['street'],
                'address' => $address,
            );

            /*if ($address_info1 && $address_data) {
                $address_data = array('timestamp_update' => $this->getTime());
                $return = $this->getUserAddressTable()->updateData($address_data, array('user_id' => $address_info1['user_id']));
            } else {
                $address_data = array('timestamp' => $this->getTime());
                $regionId = $this->getUserAddressTable()->insertData($address_data);
            }*/

            $order_data = array(
                'order_sn' => $order_num,
                'code_id' => $codeString,
                'number' => $number,
                'price' => $price,
                'total' => $total,
                'status' => 1,
                'payment' => $payment_type,
                'invoice_status' => $invoice,
                'invoice_type' => $invoice_type,
                'invoice_name' => $invoice_name,
                'address_id' => $regionId,
                'address_name' => $_POST['name'],
                'address_street' => $_POST['street'],
                'address_telephone' => $_POST['mobile'],
                'address_region_info' => $region_info,
                'user_id' => $user['id'],
                'timestamp' => $this->getTime(),
            );
            echo "<pre>";print_r($order_data);die;
            $return = $this->getOrderTable()->insertData($order_data);
            if($return){
                return $this->redirect()->toRoute('index',array('controller' => 'user', 'action' => 'orderDetails' ,'id'=>$return));
            }
        }


        $code_arr = $this->getInvitationCodeTable()->getAll(array('status' => 0));
        $array = array();
        foreach($code_arr['list'] as $v){
            $array[] = $v['code'];
        }
        $viw=new ViewModel(array(
            'region_info' => $infos,
            'code_array'  => $array,
        ));
        $viw->setTemplate('index/user/payment');
        return $this->setMenu($viw,1);
    }

    /*
    * 订单详情
    * */
    public function orderDetailsAction(){
        $this->login();
        $id = $this->params()->fromRoute('id');
        $order_info = $this->getOrderTable()->getOne(array('id' => $id));
        $address = $this->getProvinceCityCountryName($order_info['address_region_info']);
        $code = array();
        if($order_info['code_id']){
            $code_arr = implode(',',$order_info['code_id']);
            if($code_arr){
                $where = new where();
                $where->in('id',$code_arr);
                $code_info = $this->getInvitationCodeTable()->getAll($where);
                foreach($code_info['list'] as $v){
                    $code[] = $v['code'];
                }
            }

        }
        $view = new ViewModel(array(
            'order' => $order_info,
            'address' => $address,
            'code' => $code
        ));
        $view->setTemplate('index/user/orderDetails');
        return $this->setMenu($view,1);
    }

    public function PaySuccessAction(){
        $this->login();
        $view = new ViewModel();
        $view->setTemplate('index/user/PaySuccess');
        return $this->setMenu($view,1);
    }


    /*
     * 推荐码验证
     * */
    public function checkCodeAction(){
        $code = trim($_POST['code']);
        $codes = $this->getInvitationCodeTable()->getAll(array('status' => 0));
        $code_array = array();
        foreach($codes['list'] as $v){
            $code_array[] = $v['code'];
        }
        if(in_array($code,$code_array))
        {
            echo 1;
            exit;
        }
        else
        {
            echo 2;
            exit;
        }

    }

    /*
     * 微信扫描二维码支付
     * */
    public function codePayAction(){
        $code = $_GET['code'];
        $view = new ViewModel(array(
            'code'=>$code
        ));
        $view->setTemplate('index/user/codepay');
        return $this->setMenu($view,1);
    }

    /*
     * 支付宝，微信扫码，银联支付
     *
     * */
    public function payOffAction(){
        $type = $_POST['type'];//支付方式
        $id = $_POST['order_sn'];
        $this->getOrderTable()->updateData(array('payment'=>$type),array('id'=>$id));
        $order_info = $this->getOrderTable()->getOne(array('id'=>$id));//订单信息
        $order_info['total'] = 0.01;
        $total_fee = 0.01;
        $subject = '购买商品消费'.$order_info['total'].'元';
        $body = '购买商品';
        if($type == 1){
            //支付宝支付
            include_once APP_PATH . '/vendor/Core/System/alipayapi.php';
            $alipay = new \alipayapi();
            $alipay->total_fee = $order_info['total']; // 付款金额
            $alipay->out_trade_no = $order_info['order_sn']; // 订单号
            $alipay->subject = $subject;
            $alipay->body = $body;
            $content = $alipay->PostAlipay();
            $result = array(
                'type' => 1,
                'content' => $content
            );
            echo json_encode($result);
            die;
        }
        else if($type == 2)
        {
            $value = array(
                'total_fee' => $order_info['total'],
                'body' => '购买快摇名片随身设备消费:' . $order_info['total'] . '元',
                'out_trade_no' => $order_info['order_sn']
            );
            $wxpay = new AiiWxPay();
            $wx_data = $wxpay->setValue($value,1)->getNative();
            if($wx_data['return_code'] == 'SUCCESS')
            {
                $codeStr = $this->generateCode($wx_data['code_url']);
                echo $result = json_encode(array(
                    'code' => $codeStr,
                    'type' => 2,
                    'order_sn'=>$order_info['order_sn']
                ));
                die();
            }else{
                $this->showTu('支付失败');
            }
        }
        else if($type == 3)
        {
            $return =  $this->getApiController()->getYlPayInfo($order_info['order_sn'],$order_info['total'],1);
            echo json_encode(array(
                'content' => $return,
                'type' => 3
            ));
            die();
        }
    }

    public function getWxStatusAction(){
        //D:\xampp\htdocs\svn_php\kuaiyao\vendor\Core\System\WxPayApi\AiiWxPay.php
        $out_trade_no = $_POST['out_trade_no'];
        $wxApi = new AiiWxPay();
        $wxData = $wxApi->setValue(array('out_trade_no' => $out_trade_no), 2)->orderQuery();
        if (isset($wxData['trade_state']) && $wxData['trade_state'] == 'SUCCESS'){
            $result = array('status'=>1);
            print_r(json_encode($result));
            die;
        }else{
            $result = array('status'=>0);
            print_r(json_encode($result));
            die;
        }
    }

}