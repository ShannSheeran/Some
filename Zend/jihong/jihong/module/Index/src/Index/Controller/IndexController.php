<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Api\Controller\SMSCode;
use Api\Controller\UserRegister;
use Api\Controller\GoodsDetails;
use Api\Controller\CartSubmit;
use Api\Controller\CartDetails;
use Api\Controller\DeleteAction;
use Api\Controller\MessageSubmit;
use Api\Controller\AddressSubmit;
use Api\Controller\OrderSubmit;
use Api\Controller\GoodsList;
use Api\Controller\OrderList;
use Api\Controller\UserUpdatePassword;
use Api\Controller\UserBindMobile;
use Api\Controller\StatusUpdate;
use Api\Controller\UserDetails;
use Zend\Mail\Message;



class IndexController extends CommonController
{
    //固定广告位ID
    private $pc_ads_position = 2;
    
    private $pc_company_ads_position = 3;
    
    private $pc_dream_ads_position = 4;
    
    private $pc_trade_ads_position = 5;
    
    private $pc_wealthy_ads_position = 6;
    
    private $pc_industry_ads_position = 7;
    
    private $pc_contract_ads_position = 8;
    
    private $pc_english_ads_position = 9;
    
    //固定分类ID
    private $platform_information_id = 1;
    
    private $FAQ_id = 2;
    
    private $company_index_id = 3;
    
    //固定文章ID
    private $company_intro = 1; //企业介绍文章ID
    
    private $company_dream = 2;
    
    private $trade_platform = 3;
    
    private $wealth_tree = 4;
    
    /**
     * 首页
     */
    public function indexAction()
    {
        $ads_where = new Where();
        $ads_where->equalTo('position_id', $this->pc_ads_position);
        $ads_where->equalTo('delete', DELETE_FALSE);
        $ads_where->lessThanOrEqualTo('start_time', $this->getTime());
        $ads_where->greaterThanOrEqualTo('end_time', $this->getTime());
        $ads_list = $this->getViewAdsTable()->fetchAll($ads_where);
        
        $hotsale_goods_list = $this->getGoodsTable()->fetchAll(array('status' => 3 ,'salse_type'=> 0 ,'referrer_type' =>3));
        foreach ($hotsale_goods_list as $value)
        {
            if ($value->image)
            {
                $image_id = explode(',', trim($value->image,","));
                $image_ids[] = $image_id[0];
            }
        }
        $images = $this->getImageTable()->getImages($image_ids, 1);
        
        $platform_informaion_list = $this->getArticleTable()->fetchAll(array('article_category_id' => $this->platform_information_id , 'status'=>1 , 'delete' =>DELETE_FALSE) , $order = array('id desc') , 2);
        
        $company_intro = $this->getArticleTable()->getOne(array('id' => $this->company_intro ,  'status'=>1 , 'delete' =>DELETE_FALSE ));
        $company_intro_image = array();
        if($company_intro->image_id)
        {
            $company_intro_image = $this->getImageTable()->getOne(array('id' => $company_intro->image_id));
        }
        
        $user_info = array();
        $goods_count = 0;
        $equipment_count = 0;
        $market_feekback_count = 0;
        $hot_sale_count = 0;
        $order_count = 0;
        $pay_order_count = 0;
        $weekly_plan_supply_number = 0;
        $new_goods_count = 0;
        if(isset($_SESSION['index_user_id']) && !empty($_SESSION['index_user_id']))
        {
            $user_id = $_SESSION['index_user_id'];
            $user_info = $this->getUserTable()->getOne(array('id' => $user_id , 'register_status' => 3 , 'status' => 1 , 'delete' => DELETE_FALSE));
            
            if(isset($user_info->type) && $user_info->type == 2)
            {
                $goods_count = $this->getGoodsTable()->getGoodsCount(array('user_id' => $user_id , 'status' => array(1,2,3,4,6) , 'delete' => DELETE_FALSE));
                
                $equipment_count = $this->getOrderTable()->getOrderCount(array('user_id' => $user_id , 'type' =>2 , 'parent_id' => 0 , 'status' => array(1,2,3,4,5,7) , 'delete' => DELETE_FALSE));
                
                $market_feekback_count = $this->getFeedbackTable()->getFeekbackCount(array('user_id' => $user_id , 'delete' => DELETE_FALSE));
                
                $hot_sale_count = $this->getGoodsTable()->getGoodsCount(array('status' => 3 , 'referrer_type' => 3 , 'delete' => DELETE_FALSE));
            }
           elseif (isset($user_info->type) && $user_info->type == 1)
           {
               $order_count = $this->getOrderTable()->getOrderCount(array('user_id' => $user_id , 'parent_id' => 0 , 'status' => array(1,2,3,4,5,7) , 'delete' => DELETE_FALSE));
               
               $pay_order_count = $this->getOrderTable()->getOrderCount(array('user_id' => $user_id , 'parent_id' => 0 , 'status' => 1 , 'delete' => DELETE_FALSE));
               
               $start_timestamp = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
               $end_timestamp = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
               $start_time = date("Y-m-d",$start_timestamp);
               $end_time = date("Y-m-d",$end_timestamp);
               $delivery_waiting_order_count = $this->getOrderTable()->fetchAll(array('user_id' => $user_id , 'status' => array(3,4) , 'delete' => DELETE_FALSE) , array('id desc') , '' , array('id'));
               $delivery_waiting_order_ids = array();
               if($delivery_waiting_order_count)
               {
                   foreach ($delivery_waiting_order_count as $item)
                   {
                       $delivery_waiting_order_ids[] = $item->id;
                   }
                   $supply_goods_list = $this->getOrderGoodsTable()->fetchAll(array('order_id' =>$delivery_waiting_order_ids , 'delete' => DELETE_FALSE) , array('id desc') , '' , array('id'));
                   $supply_goods_ids = array();
                   foreach ($supply_goods_list as $item)
                   {
                       $supply_goods_ids[] = $item->id;
                   }
                   
                   $where = new Where();
                   $where->in('order_goods_id', $supply_goods_ids);
                   $where->equalTo('delete', DELETE_FALSE);
                   $where->greaterThanOrEqualTo('date_time', $start_time);
                   $where->lessThanOrEqualTo('date_time', $end_time);
                   $weekly_plan_supply_list = $this->getWeekPlanTable()->fetchAll($where , array('id desc') , '' , array('number') );
                   foreach ($weekly_plan_supply_list as $item)
                   {
                       $weekly_plan_supply_number += $item->number;
                   }
               }
               
               $new_goods_count = $this->getGoodsTable()->getGoodsCount(array('user_id' => $user_id , 'status' => 3 , 'referrer_type' => 1 , 'delete' => DELETE_FALSE));
           }
        }
        
        $setting_list = $this->getSetupTable()->fetchAll(array('id' => array(1,2,3,4)));
        $setting = array();
        foreach ($setting_list as $value)
        {
            $setting[$value->id] =$value;
        }
        
        $view = new ViewModel(array(
            'goods_count' => $goods_count,
            'equipment_count' => $equipment_count,
            'market_feekback_count' => $market_feekback_count,
            'hot_sale_count' => $hot_sale_count,
            'order_count' => $order_count,
            'pay_order_count' => $pay_order_count,
            'weekly_plan_supply_number' => $weekly_plan_supply_number,
            'new_goods_count' => $new_goods_count,
            'ads_list' => $ads_list,
            'user_info' => $user_info,
            'hotsale_goods_list' => $hotsale_goods_list,
            'images' => $images,
            'platform_informaion_list' => $platform_informaion_list,
            'company_intro_image' => $company_intro_image,
            'company_intro' => $company_intro,
            'setting' => $setting,
        ));
        $view->setTemplate('index/index/index');
        return $this->setMenu($view, 2);
    }
        
    public function companyIntroAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_company_ads_position));
        $company_intro = $this->getArticleTable()->getOne(array('id' => $this->company_intro));
        $view = new ViewModel(array(
            'company_intro' => $company_intro,
            'ads_info' => $ads_info,
        ));
        $view->setTemplate('index/index/company_intro');
        return $this->setMenu($view, 2 , 'companyIntro');
    }
    
    public function dynamicAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_company_ads_position));
        $page = $this->params()->fromRoute('page' , 1);
        $information_list = $this->getArticleTable()->getAll(array('article_category_id' => $this->platform_information_id , 'status'=>1 , 'delete' =>DELETE_FALSE) , null , array('id' => 'DESC') , true, $page, 10);
        $image_ids = array();
        foreach ($information_list['list'] as $value)
        {
            if ($value->image_id)
            {
                $image_id = explode(',', trim($value->image_id,","));
                $image_ids[] = $image_id[0];
            }
        }
        $images = $this->getImageTable()->getImages($image_ids, 1);
        $view = new ViewModel(array(
            'paginator' => $information_list['paginator'],
            'condition' => array(
                'controller' => 'index',
                'action' => 'dynamic',
                'page'   => $page,
            ),
            'information_list' => $information_list['list'],
            'images' => $images,
            'ads_info' => $ads_info,
        ));
        $view->setTemplate('index/index/dynamic');
        return $this->setMenu($view, 2 ,'companyIntro');
    }
    
    public function teamsAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_company_ads_position));
        $department_list = $this->getDepartmentTable()->fetchAll(array('status' => 1 , 'delete' => DELETE_FALSE));
        $view = new ViewModel(array(
            'department_list' => $department_list,
            'action' => 'teams',
            'ads_info' => $ads_info,
        ));
        $view->setTemplate('index/index/teams');
        return $this->setMenu($view, 2  ,'companyIntro');
    }
    
    public function getStaffAction()
    {
        $department_id = isset($_POST['department_id']) ? $_POST['department_id'] : '';
        if($department_id)
        {
            $staff_list = $this->getViewStaffTable()->fetchAll(array('department_id' => $department_id , 'status' => 1 , 'delete' => DELETE_FALSE));
            
            echo json_encode($staff_list);
        }
        die;
    }
    
    public function recruitAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_company_ads_position));
        $recruit_list = $this->getJobTable()->fetchAll(array('status' => 1 , 'delete' => DELETE_FALSE));
        $education = $this->getAdminController()->education();
        $years_of_working = $this->getAdminController()->yearsOfWorking();
        $view = new ViewModel(array(
            'recruit_list' => $recruit_list,
            'years_of_working' => $years_of_working,
            'education' => $education,
            'action' => 'recruit',
            'ads_info' => $ads_info,
        ));
        $view->setTemplate('index/index/recruit');
        return $this->setMenu($view, 2 ,'companyIntro');
    }
    
    public function questionsAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_company_ads_position));
        $page = $this->params()->fromRoute('page' , 1);
        $questions_list = $this->getArticleTable()->getAll(array('article_category_id' => $this->FAQ_id , 'status'=>1 , 'delete' =>DELETE_FALSE) , null , array('id' => 'DESC') , true, $page, 10);
        $image_ids = array();
        foreach ($questions_list['list'] as $value)
        {
            if ($value->image_id)
            {
                $image_id = explode(',', trim($value->image_id,","));
                $image_ids[] = $image_id[0];
            }
        }
        $images = $this->getImageTable()->getImages($image_ids, 1);
        
        $view = new ViewModel(array(
            'paginator' => $questions_list['paginator'],
            'condition' => array(
                'controller' => 'index',
                'action' => 'questions',
                'page'   => $page,
            ),
            'questions_list' => $questions_list['list'],
            'images' => $images,
            'action' => 'questions',
            'ads_info' => $ads_info,
        ));
        $view->setTemplate('index/index/questions');
        return $this->setMenu($view, 2 ,'companyIntro');
    }
    
    public function articleDetailAction()
    {
        
        $id = (int)$this->params()->fromRoute('id');
        $cid = (int)$this->params()->fromRoute('cid' , $this->company_index_id);
        $information = array();
        if($id && $cid != 2)
        {
            $information = $this->getArticleTable()->getOne(array('id' => $id , 'status'=>1 , 'delete' =>DELETE_FALSE));
        }
        else if($id && $cid == 2)
        {
            $information = $this->getProductionTable()->getOne(array('id' => $id , 'delete' =>DELETE_FALSE));
        }
        
        if($cid == $this->company_index_id)
        {
            $id == $this->company_dream && $action = 'dream';
            $id == $this->trade_platform && $action = 'trade';
            $id == $this->wealth_tree && $action = 'tree';
            
            $special_page = 0;
            if($id == $this->trade_platform)
            {
                $special_page = 1;
                $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_trade_ads_position));
            }
            if($id == $this->company_dream)
            {
                $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_dream_ads_position));
            }
            if($id == $this->wealth_tree)
            {
                $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_wealthy_ads_position));
            }
            
            $view = new ViewModel(array(
                'special_page' =>$special_page,
                'information' => $information,
                'ads_info' => $ads_info,
            ));
            $view->setTemplate('index/index/article_detail');
        }
        else if($cid == 1)
        {
            $action = "companyIntro";
            $view = new ViewModel(array(
                'information' => $information,
            ));
            $view->setTemplate('index/index/dynamic_detail');
        }
        else if($cid == 2)
        {
            $action = "industry";
            $view = new ViewModel(array(
                'information' => $information,
            ));
            $view->setTemplate('index/index/producer_detail');
        }
        
        return $this->setMenu($view, 2 , $action);
    }
    
    public function indexEnglishAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_english_ads_position));
        $setting_list = $this->getSetupTable()->fetchAll(array('id' => array(2,3,4,7,8)));
        $setting = array();
        foreach ($setting_list as $value)
        {
            $setting[$value->id] =$value;
        }
        
        $view = new ViewModel(array(
            'ads_info' => $ads_info,
            'setting' => $setting,
        ));
        $view->setTemplate('index/index/index_English');
        return $view;
    }

    public function sendMailAction()
    {
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $subject = isset($_POST['subject']) ? $_POST['subject'] : '';
        $body = isset($_POST['body']) ? $_POST['body'] : '';
        
        if(!$email)
        {
            $result['status'] = 1;
            $result['description'] = 'failed';
            echo json_encode($result);
            die;
        }
        
        try {
            $message = new Message();
            $message->addTo('673380975@qq.com')
                            ->addFrom(SEND_MAIL_ADDR)
                            ->setSubject($subject)
                            ->setBody($body.'sender : '.$email);
            
            $transport = new SmtpTransport();
            $options = new SmtpOptions(array(
                'host' => SEND_MAIL_HOST,
                'port' => SEND_MAIL_PORT,
                'connection_class' => 'plain',
                'connection_config' => array(
                    'username' => SEND_MAIL_ADDR,
                    'password' => SEND_MAIL_PASS,
                    'ssl'      => 'tls',
                )
            ));
            $transport->setOptions($options);
            $transport->send($message);
        } catch (\Exception $e) {
            $result['status'] = 1;
            $result['description'] = 'failed';
            echo json_encode($result);
            die;
        }
        
        $result['status'] = 0;
        $result['description'] = 'success';
        echo json_encode($result);
        die;
    }

/*     public function industryInfoAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_industry_ads_position));
        $view = new ViewModel(array(
            'ads_info' => $ads_info,
        ));
        $view->setTemplate('index/index/industry_info');
        return $this->setMenu($view , 2 , 'industry' );
    } */
    
    public function supplyListAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_industry_ads_position));
        
        $page = (int)$this->params()->fromRoute('page',1);
        
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : "";
        $keyword = isset($_GET['keyword'])&&$_GET['keyword'] ? trim($_GET['keyword']) : $keyword;
        
        $big_category = $this->params()->fromRoute('bc') ? $this->params()->fromRoute('bc') : "0";
        $big_category = isset($_GET['bc'])&&$_GET['bc'] ? $_GET['bc'] : $big_category;
        $small_category = $this->params()->fromRoute('sc') ? $this->params()->fromRoute('sc') : "0";
        $small_category = isset($_GET['sc'])&&$_GET['sc'] ? $_GET['sc'] : $small_category;
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        
        $small_category_info = '';
        $big_category_info = '';
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        
        $small_category_info = '';
        $big_category_info = '';
        
        if($small_category)
        {
            $small_category_info = $this->getGoodsCategoryTable()->getOne(array('id'=>$small_category,'delete'=>DELETE_FALSE , 'status'=>1),array('name','parent_id'));
            $big_category_info = $this->getGoodsCategoryTable()->getOne(array('id'=>$small_category_info->parent_id , 'delete'=>DELETE_FALSE , 'status'=>1),array('name'));
        
            $where = new Where();
            $where->equalTo('delete', DELETE_FALSE);
            $where->equalTo('status', '3');
            $where->equalTo('type', 1);
            $where->equalTo('category_id', $small_category);
        }
        elseif(!$small_category && $big_category)
        {
            $big_category_info = $this->getGoodsCategoryTable()->getOne(array('id'=>$big_category,'delete'=>DELETE_FALSE,'status'=>1),array('name'));
        
            $where = new Where();
            $where->equalTo('delete', DELETE_FALSE);
            $where->equalTo('status', '3');
            $where->equalTo('type', 1);
            $where->equalTo('category_id', $big_category);
        
            $small_category_arr = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>DELETE_FALSE,'status'=>1,'type'=>1,'parent_id'=>$big_category),null,null,array('id'));
            if(!empty($small_category_arr))
            {
                foreach ($small_category_arr as $k=>$v)
                {
                    $where_sub = new Where();
                    $where_sub->equalTo('delete', DELETE_FALSE);
                    $where_sub->equalTo('status', '3');
                    $where_sub->equalTo('type', 1);
                    $where_sub->equalTo('category_id', $v->id);
                    $where->orPredicate($where_sub);
                }
            }
        }
        else
        {
            $where = new Where();
            $where->equalTo('delete', DELETE_FALSE);
            $where->equalTo('status', '3');
            $where->equalTo('type', 1);
        }
        //商品列表
        $page_size = 12;
        $goods_list = $this->getGoodsTable()->getAll($where,null,null,true,$page,$page_size,$like);
        $goods = array();
        if(!empty($goods_list['list']))
        {
            foreach ($goods_list['list'] as $k=>$v)
            {
                if($v->image)
                {
                    $image_arr = explode(',', $v->image);
                    $image_info = $this->getImageTable()->getOne(array('id'=>$image_arr[0]));
                    $v->image_path = ROOT_PATH . UPLOAD_PATH.'thumb/190X190X3/'.$image_info->path.$image_info->filename;
                }
                $goods[$k] = $v;
            }
        }
        
        $goods_new = $this->getGoodsTable()->fetchAll(array('delete'=>DELETE_FALSE,'status'=>'3','type'=>1),array('timestamp desc'),3);
        $goods_new_list = array();
        if(!empty($goods_new))
        {
            foreach ($goods_new as $k=>$v)
            {
                if($v->image)
                {
                    $image_arr = explode(',', $v->image);
                    $image_info = $this->getImageTable()->getOne(array('id'=>$image_arr[0]));
                    $v->image_path = ROOT_PATH . UPLOAD_PATH.'thumb/183X183X3/'.$image_info->path.$image_info->filename;
                }
                $goods_new_list[$k] = $v;
            }
        }
        $firstCategory = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>DELETE_FALSE,'parent_id'=>'0','status'=>1,'type'=>1));
        $where_cate = new Where();
        $where_cate->equalTo('delete', DELETE_FALSE);
        $where_cate->equalTo('status', 1);
        if($big_category)
        {
            $where_cate->EqualTo('parent_id', $big_category);
        }
        else
        {
            $where_cate->notEqualTo('parent_id', '0');
        }
        
        $secondCategory = '';
        $bc = $this->params()->fromRoute('bc');
        $sc = $this->params()->fromRoute('sc');
        if(($bc !== "0") || $small_category)
        {
            $secondCategory = $this->getGoodsCategoryTable()->fetchAll($where_cate);
        }
        
        $view = new ViewModel(array(
            'paginator' => $goods_list['paginator'],
            'condition' => array(
                'controller' => 'index',
                'action' => 'supplyList',
                'page'   => $page,
                'keyword' => $keyword,
                'bc' => $big_category,
                'sc' => $small_category,
                'where' => array(
                    'search'=>$keyword
                )
            ),
            'goods_new' => $goods_new_list,
            'goods_list' => $goods,
            'firstCategory' => $firstCategory,
            'secondCategory' => $secondCategory,
            'big_category' => $big_category,
            'small_category' => $small_category,
            'big_category_info' => $big_category_info,
            'small_category_info' => $small_category_info,
            'page' => $page,
            'bc' => $big_category,
            'sc' => $small_category,
            'search'=>$keyword,
            'page_count' => ceil($goods_list['total']/$page_size),
            'ads_info' => $ads_info,
        ));
        
        $view->setTemplate('index/index/supply_list');
        return $this->setMenu($view, 2 , 'industry');
    }

    public function supplyDetailAction()
    {
        $id = $this->params()->fromRoute('id');
        $goods_info = array();
        $images = array();
        $small_category = array();
        $big_category = array();
        if($id)
        {
            $goods_info = $this->getViewGoodsTable()->getOne(array('id' => $id));
            if($goods_info->image)
            {
                $image_ids = explode(',' , trim($goods_info->image , ','));
                $images = $this->getImageTable()->fetchAll(array('id' => $image_ids));
            }
            if($goods_info->category_id)
            {
                $small_category = $this->getGoodsCategoryTable()->getOne(array('id' => $goods_info->category_id));
                $big_category = $this->getGoodsCategoryTable()->getOne(array('id' => $small_category->parent_id));
            }
        }
        $view = new ViewModel(array(
            'goods_info' => $goods_info,
            'images' => $images,
            'small_category' => $small_category,
            'big_category' => $big_category,
        ));
        $view->setTemplate('index/index/supply_detail');
        return $this->setMenu($view , 2 , 'industry');
    }

    public function producerListAction()
    {
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_industry_ads_position));
        $page = $this->params()->fromRoute('page' , 1);
        $producer_list = $this->getProductionTable()->getAll(array('delete' => DELETE_FALSE) , null , array('sort ASC') ,true,$page,4);
        foreach ($producer_list['list'] as $key => $value)
        {
            $image_info = array();
            if($value->image)
            {
                $image_info = $this->getImageTable()->getOne(array('id' => $value->image));
            }
            $producer_list['list'][$key]['image_info'] = $image_info;
        }
         $view = new ViewModel(array(
            'paginator' => $producer_list['paginator'],
            'condition' => array(
                'controller' => 'index',
                'action' => 'producerList',
                'page'   => $page,
            ),
            'producer_list' => $producer_list['list'],
             'ads_info' => $ads_info,
        ));
         $view->setTemplate('index/index/producer_list');
        return $this->setMenu($view , 2 , 'industry');
    }
    
    public function contractAction()
    {
        $place = isset($_GET['place']) ? $_GET['place'] : 1;
        !in_array($place, array(1,2,3)) && $place = 1;
        
        switch ($place)
        {
            case 1:
                $setting_list = $this->getSetupTable()->fetchAll(array('id' => array(1,2,3,4)));
                break;
            case 2:
                $setting_list = $this->getSetupTable()->fetchAll(array('id' => array(9,13,15,20)));
                break;
            case 3:
                $setting_list = $this->getSetupTable()->fetchAll(array('id' => array(10,14,16,21)));
                break;
        }
        
        $setting = array();
        foreach ($setting_list as $value)
        {
            $setting[$value->sort] =$value;
        }
        
        $ads_info = $this->getViewAdsTable()->getOne(array('position_id' => $this->pc_contract_ads_position));
        $view = new ViewModel(array(
            'ads_info' => $ads_info,
            'place' => $place,
            'setting' => $setting,
        ));
        $view->setTemplate('index/index/contract');
        return $this->setMenu($view , 2 , 'contract');
    }
    
    /**
     * APP 文章详情
     * @return \Zend\View\Model\ViewModel
     */
    public function detailAction()
    {
        $type = isset($_GET['type']) ? (int)$_GET['type'] : '';  //1：文章 ； 2：广告
        $id = isset($_GET['id']) ? (int)$_GET['id'] : '';

        if(!in_array($type, array(1,2)) || !is_numeric($id) || $id<0)
        {
            die;
        }
        
        if($type == 1)
        {
            $info = $this->getArticleTable()->getOne(array('id' => $id , 'status' => 1 , 'delete'=>DELETE_FALSE));
            
            $this->getArticleTable()->updateData(array('read_number' => ($info->read_number + 1)), array('id' => $info->id));
            
            $category_name = $this->getArticleCategoryTable()->getOne(array('id' => $info->article_category_id));
            $info->detail = $info->app_content;
            $info->category_name = $category_name->name;
        }
        
        if($type == 2)
        {
            $info = $this->getAdsTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
            $info->detail = $info->description;
            $info->category_name = '';
        }
        
        $view = new ViewModel(array(
            'info' => $info,
            'type' => $type,
        ));
        return $view->setTemplate('index/index/app_article_detail');
    }
    
    public function getJsonAction()
    {
        $this->login();
        header('Access-Control-Allow-Origin: *');
        if(!isset($_REQUEST['json']))
        {
            $_REQUEST['json'] = isset($_POST['request']) ? json_encode($_POST['request']) : '';
            $className = isset($_POST['request']['n']) ? $_POST['request']['n'] : '';
        }
        else
        {
            $className = isset($_REQUEST['n']) ? $_REQUEST['n'] : '';
        }
    
        $_SESSION['user_ip'] = isset($_POST['user_ip']) ? $_POST['user_ip'] : $this->getClientIp();
        $user_id = 0;
        if ($_REQUEST['json'] && $className)
        {
            $json_array = json_decode($_REQUEST['json'],true);
            if($className == "SMSCode" &&  (isset($json_array['q']['type']) && $json_array['q']['type'] == 1 && $json_array['q']['a'] == 1) ){//非注册开启验证码
                $_SESSION['is_captcha'] = 1;//设置开启验证码
            }
            if(isset($_SESSION['index_user_id']))
            {
                $user_id = $_SESSION['index_user_id'];
            }
    
            switch ($className)
            {
                case 'SMSCode':
                    $obj = new SMSCode();
                    break;
                case 'UserRegister':
                    $obj = new UserRegister();
                    break;
                case 'GoodsDetails':
                    $obj = new GoodsDetails();
                    break;
                case 'CartSubmit':
                    $obj = new CartSubmit();
                    break;
                case 'CartDetails':
                    $obj = new CartDetails();
                    break;
                case 'DeleteAction':
                    $obj = new DeleteAction();
                    break;
                case 'MessageSubmit':
                    $obj = new MessageSubmit();
                    break;
                case 'AddressSubmit':
                    $obj = new AddressSubmit();
                    break;
                case 'OrderSubmit':
                    $obj = new OrderSubmit();
                    break;
                case 'GoodsList':
                    $obj = new GoodsList();
                    break;
                case 'OrderList':
                    $obj = new OrderList();
                    break;
                case 'UserUpdatePassword':
                    $obj = new UserUpdatePassword();
                    break;
                case 'UserBindMobile':
                    $obj = new UserBindMobile();
                    break;
                case 'StatusUpdate':
                    $obj = new StatusUpdate();
                    break;
                case 'UserDetails':
                    $obj = new UserDetails();
                    break;

            }
            $serviceLocator = $this->getServiceLocator();
            $obj->setServiceLocator($serviceLocator);
            if($user_id)
            {
                $obj->setUserId($user_id);
            }
            $response = $obj->index();
            if ($response)
            {
                $obj->setResponse($response);
            }
            $obj->response();
    
            var_dump($response);
        }
        exit();
    }
}
