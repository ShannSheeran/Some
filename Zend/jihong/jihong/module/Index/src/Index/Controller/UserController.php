<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
class UserController extends CommonController
{
    /* 首页
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $type = $_SESSION['user_type'];
        //第4部分--最新公告6条
        $articles = $this->getArticleTable()->fetchAll(array('delete'=>0,'article_category_id'=>1),array('timestamp desc'),6,array( 'id','title','timestamp'));
        $where = array(
            'delete'=>0,
        );
        //第3部分--最新热销（新品上市）
        if($type == 1)//经销商
        {
            $order = array('timestamp desc');
        }
        elseif($type == 2)//供应商
        {
            $order = array('sale_number desc','timestamp desc');
            $where['type'] = 2;
        }
        else 
        {
            die;
        }
        $goods = $this->getGoodsTable()->fetchAll(array('delete'=>0,'type'=>1,'status'=>3),$order,6,array('id','name','min_cash','max_cash','sale_number'));
        
        //第二部分--订单中心（我的商品，资材订单）
        
        $user_id = $_SESSION['index_user_id'];
        $where['user_id'] = $user_id;
        //我的商品
        $myGoods = $this->getGoodsTable()->fetchAll(array('delete'=>0,'user_id'=>$user_id),array('timestamp desc'),5,array('id','goods_sn','min_cash','status'));
        //订单列表
        $orders = $this->getOrderTable()->fetchAll($where,array('timestamp desc'),5,array('id','order_sn','total_cash','status'));
//         $this->dump($orders);exit;

        $time_where = new Where();
        $time_where->lessThanOrEqualTo('start_time', $this->getTime());
        $time_where->greaterThanOrEqualTo('deadline', $this->getTime());
        $time_where->equalTo('delete', DELETE_FALSE);
        $time_node = $this->getTimeNodeTable()->getOne($time_where);
        $view = new ViewModel(array(
            'time_node' => $time_node,
            'articles' => $articles,
            'goods' => $goods,
            'myGoods' => $myGoods,
            'orders' => $orders,
            'orderStatus' => $this->orderStatus(),
            'goodsStatus' => $this->goodsStatus(),
            'type'=>$type,
        ));
        $view->setTemplate('index/user/index');
        return $this->setMenu($view, 3,'index');
    }
    
    /**
     * 我的商品列表
     */
    public function myGoodsAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $user_id = $_SESSION['index_user_id'];
        $page = $this->params()->fromRoute('page',1);
        $status = $this->params()->fromRoute("status",0);
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $this->params()->fromRoute('keyword','');
        
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $this->params()->fromRoute('start_time' , '');
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $this->params()->fromRoute('end_time' , '');
        
        $where = new Where();
        $where->equalTo('delete', '0');
        $where->equalTo('user_id', $user_id);
        if($status)
        {
            $where->equalTo('status', $status);
        }
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time.' 00:00:00');
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time.' 00:00:00' > $end_time)
                {
                    $end_time = $start_time;
                }
                $where->lessThanOrEqualTo('timestamp', $end_time.' 23:59:59');
            }
        }
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        $order = array("timestamp desc");
        $goods_list  = $this->getGoodsTable()->getAll($where,null,$order,true,$page,10,$like);
        $goods_all_count = $this->getGoodsTable()->getGoodsCount(array('delete'=>0,'user_id'=>$user_id));
        $goods_wait_count = $this->getGoodsTable()->getGoodsCount(array('delete'=>0,'user_id'=>$user_id,'status'=>1));
        $goods_through_count = $this->getGoodsTable()->getGoodsCount(array('delete'=>0,'user_id'=>$user_id,'status'=>2));
        $goods_up_count = $this->getGoodsTable()->getGoodsCount(array('delete'=>0,'user_id'=>$user_id,'status'=>3));
//         $this->dump($goods_up_count);exit;
        $view = new ViewModel(array(
            'goods' => $goods_list['list'],
            'paginator' => $goods_list['paginator'],
            'goods_all_count' => $goods_all_count,
            'goods_wait_count' => $goods_wait_count,
            'goods_through_count' => $goods_through_count,
            'goods_up_count' => $goods_up_count,
            'goodsStatus' => $this->goodsStatus(),
            'condition' => array(
                'controller' => 'user',
                'action' => 'myGoods',
                'page'   => $page,
                'keyword' => $keyword,
                'status' => $status,
                'where' => array(),
            ),
            'keyword'=>$keyword,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
            'status' => $status,
        ));
        $view->setTemplate('index/user/my_goods');
        return $this->setMenu($view, 1 ,'goodsList');
    }
    
    /**
     * 我的商品状态（详情）
     */
    public function myGoodsDetailAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $userInfo = $this->getViewUserTable()->getOne(array('id'=>$_SESSION['index_user_id']));
        $id = $this->params()->fromRoute('id');
        $good = '';
        $images = '';
        $spec = '';
        $good_tracking = '';
        if($id)
        {
            $good = $this->getViewGoodsTable()->getOne(array('delete'=>'0','id'=>$id));
            if(empty($good))
            {
                $this->showMessage("不存该商品或商品没上架");
            }
            if($good->image)
            {
                $images_ids_arr = explode(',', $good->image);
                $images = $this->getImageTable()->getImages($images_ids_arr);
            }
            $spec = $this->getGoodsSpecificationTable()->getOne(array('delete'=>0,'goods_id'=>$id));
            $good_tracking_list = $this->getGoodsTrackingTable()->fetchAll(array('delete'=>0,'goods_id'=>$id,'user_id'=>$_SESSION['index_user_id']),null,null,array('id','status','timestamp'));
//             $this->dump($good_tracking);exit;

            $good_tracking = array();
            foreach ($good_tracking_list as $item)
            {
                $good_tracking[$item->status] = $item;
            }
        }
//         $this->dump($good);exit;
        $view = new ViewModel(array(
            'id' => $id,
            'good' => $good,
            'spec' => $spec,
            'images' => $images,
            'good_tracking' => $good_tracking,
            'user_qq' =>$userInfo['admin_qq']
        ));
        $view->setTemplate('index/user/myGoodsDetail');
        return $this->setMenu($view, 1,'goodsList');
    }
    
    /**
     * 修改密码
     */
    public function changePasswordAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $view = new ViewModel();
        $view->setTemplate('index/user/change_password');
        return $this->setMenu($view, 1,'editPassword');
    }
    
    /**
     * 修改手机号码
     */
    public function modifyPhoneAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        
        $user = $this->getUserTable()->getOne(array('id' => $_SESSION['index_user_id'] , 'status' => 1 , 'register_status' => 3 , 'delete'=>DELETE_FALSE));
        
        $view = new ViewModel(array(
            'phone' => substr_replace($user->mobile,'****',3,4),
            'fullPhone' => $user->mobile,
        ));
        $view->setTemplate('index/user/modify_phone');
        return $this->setMenu($view, 1,'editMobile');
    }
    
    /**
     * 最新公告列表
     */
    public function noticeAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $page = $this->params()->fromRoute('page' , 1);
        $notice_list = $this->getArticleTable()->getAll(array('article_category_id' => 1 , 'status'=>1 , 'delete' =>DELETE_FALSE) , null , array('id' => 'DESC') , true, $page, 10);
//         $this->dump($information_list['list']);
        foreach ($notice_list['list'] as &$value)
        {
            if ($value->image_id)
            {
                $image = $this->getImageTable()->getImages(array($value->image_id));
//                 $this->dump($image);exit;
                $value->image_path = $image[$value->image_id]['image_path'];
            }
        }
        
//         $this->dump($information_list['list']);exit;
        $view = new ViewModel(array(
            'paginator' => $notice_list['paginator'],
            'condition' => array(
                'controller' => 'index',
                'action' => 'dynamic',
                'page'   => $page,
            ),
            'notice' => $notice_list['list'],
        ));
        $view->setTemplate('index/user/notice');
        return $this->setMenu($view,3,'index');
    }
    
    /**
     * 最新公告详情
     */
    public function noticeDetailAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $id = (int)$this->params()->fromRoute('id');
        $notice = $this->getArticleTable()->getOne(array('id' => $id , 'status'=>1 , 'delete' =>DELETE_FALSE));
        $view = new ViewModel(array(
            'notice' => $notice,
        ));
        $view->setTemplate('index/user/noticeDetail');
        return $this->setMenu($view, 3,'index');
    }
    
    /**
     * 会员信息
     */
    public function userAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $data = array();
        if(!empty($_POST))
        {
            foreach ($_POST as $k=>$v)
            {
                $data[$k] = $v;
            }
//             $this->dump($data);exit;
            $id = $data['id'];
            $data['region_id'] = $data['county'] ? $data['county'] : $data['city'];
            $data['region_info'] = $this->encode($data['county'], $data['city'], $data['province']);
            unset($data['county'], $data['city'], $data['province'], $data['id']);
//             $this->dump($data);exit;
            if ($id)
            {
                $res = $this->getUserTable()->updateData($data, array('id' => $id));
                $this->showMessage("更新成功");exit;
                return $this->redirect()->toRoute('index',array('controller'=>'user','action'=>'user'));
            }
            die;
        }
        $user = $this->getUserTable()->getOne(array('delete'=>'0','status' => 1,'id'=>$_SESSION['index_user_id']));
        $address = $this->decode($user->region_info);
//         $this->dump($address);exit;
        $view = new ViewModel(array(
            'user'=>$user,
            'address' => $address
        ));
        $view->setTemplate('index/user/user');
        return $this->setMenu($view, 1 , 'userInfo');
    }
    
    public function cartAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $action= isset($_GET['cartType']) ? $_GET['cartType'] : '';
        setcookie('contact_id' , '' , time()-1);
        setcookie('messages' , '' , time()-1);
        $_SESSION['cart_ids'] = '';
        $view = new ViewModel();
        $view->setTemplate('index/user/cart');
        return $this->setMenu($view, 3 , $action);
    }
    
    public function deleteCartAction()
    {
        if(!isset($_SESSION['index_user_id']))
        {
            $result['status'] = 1;
            die(json_encode($result));
        }
        
        $ids = isset($_POST['ids']) ? $_POST['ids'] : '';
        $res = $this->getCartTable()->delete(array('id' => $ids , 'user_id' => $_SESSION['index_user_id']));
        if($res)
        {
            $result['status'] = 0;
        }
        else 
        {
            $result['status'] = 1;
        }
        die(json_encode($result));
    }
    
    public function contactDetailAction()
    {
        $id = isset($_POST['contact_id']) ? $_POST['contact_id'] : '';
        $contact_detail = $this->getContactsTable()->getOne(array('id' => $id ,'user_id' => $_SESSION['index_user_id'] , 'delete' => DELETE_FALSE));
        $address_info = $this->decode($contact_detail->region_info);
        $contact_detail->address_info = $address_info;
        $respose = array();
        if($contact_detail)
        {
            $respose['status'] = 0;
            $respose['detail'] = $contact_detail;
        }
        else 
        {
            $respose['status'] = 1;
        }
        die(json_encode($respose));
    }
    
    public function changeContactTypeAction()
    {
        $id = isset($_POST['contact_id']) ? $_POST['contact_id'] : '';
        $default_contact = $this->getContactsTable()->getOne(array('type' => 1 ,'user_id' => $_SESSION['index_user_id'] , 'delete' => DELETE_FALSE));
        $update = true;
        if($default_contact)
        {
            $edit = $this->getContactsTable()->updateData(array('type' => 0 ), array('id' => $default_contact->id));
            !$edit && $update = false;
        }
        
        $res = array();
        $update && $res = $this->getContactsTable()->updateData(array('type' => 1), array('id' => $id));
        if($res)
        {
            $result['status'] = 0;
        }
        else 
        {
            $result['status'] = 1;
        }
        die(json_encode($result));
    }
}
