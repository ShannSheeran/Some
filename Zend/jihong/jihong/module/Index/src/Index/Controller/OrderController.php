<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
class OrderController extends CommonController
{
    public function indexAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $user_info = $this->getUserTable()->getOne(array('id' => $_SESSION['index_user_id'] , 'status' => 1 , 'register_status' => 3 , 'delete'=>DELETE_FALSE));
        $page = $this->params()->fromRoute('page' , 1);
        $keyword = $_GET['keyword'] = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : substr($this->getTime(), 0 ,10);
        $status = $this->params()->fromRoute('status' , 0);
        
        $like = array();
        if($keyword)
        {
            $like['order_sn'] = $keyword;
        }
        
        $where = new Where();
        if($user_info->type == 2)
        {
            $where->equalTo('type', 2);
        } 
        $where->equalTo('children', 0);
        $where->equalTo('user_id', $_SESSION['index_user_id']);
        $where->equalTo('delete', DELETE_FALSE);
        if(in_array($status, array(1,2,4)))
        {
            $where->equalTo('status', $status);
        }
        
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time.' 00:00:00');
            if($start_time.' 00:00:00' > $end_time)
            {
                $end_time = $start_time;
            }
        }
        if($end_time)
        {
            $where->lessThanOrEqualTo('timestamp', $end_time.' 23:59:59');
        }
        
        $order_list = $this->getViewOrderTable()->getAll($where , null , array('timestamp' => 'DESC' ,'id'=>'DESC'),true , $page , 10 , $like);
        //遍历查询该订单是否已申请售后
        if(!empty($order_list['list']))
        {
            foreach ($order_list['list'] as $k => &$v)
            {
                $where_order_goods_service = new Where();
                $where_order_goods_service->equalTo('delete', '0');
                $where_order_goods_service->equalTo('order_id', $v->id);
                $where_order_goods_service->notEqualTo('customer_service_id', '0');
                $res = $this->getOrderGoodsTable()->getOne($where_order_goods_service);
                if($res)
                {
                    $v->has_service = 1;
                }
                else 
                {
                    $v->has_service = 0;
                }
            }
        }
//         $this->dump($order_list['list']);exit;
        $order_status = $this->getAdminController()->orderStatus();
        
        $check_order = array();
        $pay_order = array();
        $deliver_order = array();
        
        $set = array(
            'children' => 0,
            'user_id' => $_SESSION['index_user_id'],
            //'status' => array(1,2,3,4,5,7),
            'delete' => DELETE_FALSE,
        );
        if($user_info->type == 2)
        {
            $set['type'] =2 ;
        }
        $total_order = $this->getOrderTable()->fetchAll($set);
       
        foreach ($total_order as $value)
        {
            if($value->status == 1)
            {
                $pay_order[] = $value;
            }
            if($value->status == 2)
            {
                $check_order[] = $value;
            }
            if($value->status == 4)
            {
                $deliver_order[] = $value;
            }
        }
        
        $view = new ViewModel(array(
            'paginator' => $order_list['paginator'],
            'condition' => array(
                'controller' => 'order',
                'action' => 'index',
                'page'   => $page,
                'status' => $status,
                'id' => 1,
                'where' => array(
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'keyword' => $keyword,
                )
            ),
            'order_list' => $order_list['list'],
            'status'=>$status,
            'order_status' => $order_status,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'keyword' => $keyword,
            'total_order' => count($total_order),
            'check_order' => count($check_order),
            'pay_order' => count($pay_order),
            'deliver_order' => count($deliver_order),
        ));
        $view->setTemplate('index/order/index');
        return $this->setMenu($view, 1 ,'orderList');
    }
    
    public function orderDetailAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $id = (int)$this->params()->fromRoute('id' , '');
        !$id && die;
        $order_info = $this->getViewOrderTable()->getOne(array('id' => $id , 'user_id' => $_SESSION['index_user_id'] , 'delete'=>DELETE_FALSE));
        !$order_info && die;
        //遍历查询该订单是否已申请售后
/*         $where_order_goods_service = new Where();
        $where_order_goods_service->equalTo('delete', '0');
        $where_order_goods_service->equalTo('order_id', $id);
        $where_order_goods_service->notEqualTo('customer_service_id', '0');
        $res = $this->getOrderGoodsTable()->getOne($where_order_goods_service);
        if($res)
        {
            $has_service = 1;
        }
         else
        {
            $has_service = 0;
        }*/
         
        $pay_img = array();
        if($order_info->pay_img)
        {
            $pay_img = $this->getImageTable()->getOne(array('id'=>$order_info->pay_img ));
        }
        else if($order_info->parent_id)
        {
            $parent_order = $this->getOrderTable()->getOne(array('id' =>$order_info->parent_id));
            if($parent_order->pay_img)
            {
                $pay_img = $this->getImageTable()->getOne(array('id'=>$parent_order->pay_img ));
            }
        }
        
        $auto_receive_timestamp  = 0 ;
        if($order_info->status == 4)
        {
            $orderTracking = $this->getOrderTrackingTable()->getOne(array('status' => 4 , 'user_id' => $_SESSION['index_user_id'] , 'order_id' => $id));
            $delay_timestamp = 0;
            $order_info->delay == 1 && $delay_timestamp = 604800;
            $auto_receive_timestamp = strtotime($orderTracking->timestamp) + 604800 - strtotime($this->getTime()) + $delay_timestamp;
        }
        
        $order_tracking_list = $this->getOrderTrackingTable()->fetchAll(array('order_id' => $order_info->id , 'user_id' => $_SESSION['index_user_id'] , 'delete' =>DELETE_FALSE) , array('status ASC'));
        $order_tracking = array();
        foreach ($order_tracking_list as $item)
        {
            $order_tracking[$item->status] = $item;
        }
        
        $order_goods_list = $this->getViewOrderGoodsTable()->fetchAll(array('order_id' =>$id , 'delete' => DELETE_FALSE ));
        $image = array();
        $order_number = 0;
        $order_cash = 0;
        if($order_goods_list)
        {
            foreach ($order_goods_list as $value)
            {
                if($value->image)
                {
                    $image_id_array = explode(',', trim($value->image , ','));
                    $image_ids[] = $image_id_array[0];
                }
                $order_number += $value->number;
                $order_cash += $value->price_cash * $value->number;
            }
            !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
        }
        
        $user_info = $this->getViewUserTable()->getOne(array('id' => $_SESSION['index_user_id']));
        $qq = $user_info->admin_qq;
        $view = new ViewModel(array(
            'auto_receive_timestamp' => $auto_receive_timestamp,
            'order_info' => $order_info,
            'order_tracking' => $order_tracking,
            'order_goods_list' => $order_goods_list,
            'image' => $image,
            'order_number' => $order_number,
            'order_cash' => $order_cash,
            'pay_img' => $pay_img,
            'qq' => $qq,
        ));
        $view->setTemplate('index/order/order_detail');
        return $this->setMenu($view, 1  ,'orderList');
    }
    
    public function saveCartIdAction()
    {
        $cart_ids = isset($_POST['cart_ids']) ? $_POST['cart_ids'] : '';
        if($cart_ids)
        {
            $_SESSION['cart_ids'] = $cart_ids;
            $result['status'] = 0;
        }
        else
        {
            $result['status'] = 1;
        }
        die(json_encode($result));
    }
    
    public function confirmOrderAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if( (!isset($_SESSION['cart_ids']) || empty($_SESSION['cart_ids']) ) &&  ( !isset($_GET['goodsId']) || empty($_GET['goodsId']) ) &&  ( !isset($_GET['order_id']) || empty($_GET['order_id']) ) )
        {
            die;
        }
        
        $contacts_list = $this->getContactsTable()->fetchAll(array('user_id' => $_SESSION['index_user_id'] , 'delete' => DELETE_FALSE));
        if($contacts_list)
        {
            $vals = array();
            foreach ($contacts_list as $key => $value)
            {
                $vals[$key] = $value->type;
            }
            array_multisort($vals, SORT_DESC, $contacts_list);
        }
       
        $cart_ids = isset($_SESSION['cart_ids']) ? $_SESSION['cart_ids'] : '';
        $goods_id = isset($_GET['goodsId']) ? (int)$_GET['goodsId'] : '';
        $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] :'';
        $action = isset($_GET['cartType']) ? $_GET['cartType'] : '';
        
        $goods_list = array();
        $image = array();
        if($cart_ids)
        {
            $cart_list = $this->getViewCartTable()->fetchAll(array('id' =>$cart_ids , 'delete' => DELETE_FALSE , 'user_id' => $_SESSION['index_user_id']));
            $image_ids = array();
            foreach ($cart_list as $value)
            {
                if($value->image)
                {
                    $image_id_array = explode(',', trim($value->image , ','));
                    $image_ids[] = $image_id_array[0];
                }
            }
            !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
            $goods_list = array();
            foreach ($cart_list as $value)
            {
                $goods_list[$value->merchant_id][] = $value;
            }
            
            $view = new ViewModel(array(
                'contacts_list' => $contacts_list,
                'goods_list' => $goods_list,
                'image' => $image,
                'cart_ids' => json_encode($_SESSION['cart_ids']),
            ));
        }
        
        if($goods_id)
        {
            $number = isset($_GET['number']) ? (int)$_GET['number'] : '';
            $specificationId = isset($_GET['specificationId']) ? (int)$_GET['specificationId'] : '';
            if( !is_numeric($number) || $number < 0 || !is_numeric($specificationId) || !$specificationId)
            {
                die;
            }
            $goods_info = $this->getViewGoodsTable()->getOne(array('id' => $goods_id , 'status' => 3 , 'delete' => DELETE_FALSE));
            $image_ids = array();
            if($goods_info->image)
            {
                $image_id_array = explode(',', trim($goods_info->image , ','));
                $image_ids[] = $image_id_array[0];
            }
            !empty($image_ids) &&  $image = $this->getImageTable()->getImages($image_ids);
            
            $specification_info = $this->getGoodsSpecificationTable()->getOne(array('id' => $specificationId , 'goods_id' => $goods_id , 'delete' => DELETE_FALSE));
            if(!$specification_info)
            {
                die;
            }
            $view = new ViewModel(array(
                'contacts_list' => $contacts_list,
                'goods_info' => $goods_info,
                'specification_info' => $specification_info,
                'image' => $image,
                'number' => $number,
                'specificationId' => $specificationId,
            ));
        }
        
        if($order_id)
        {
            $order_info = $this->getOrderTable()->getOne(array('status'=> array(6,7) , 'id' => $order_id , 'user_id' => $_SESSION['index_user_id'] ));
            if(!$order_info)
            {
                die;
            }
            $order_goods_list = $this->getViewOrderGoodsTable()->fetchAll(array('order_id' => $order_info->id , 'status' => 3));
            $order_goods_info = array();
            if($order_goods_list)
            {
                $order_goods_info = $this->getViewGoodsTable()->getOne(array('id' => $order_goods_list[0]->goods_id));
                
                $image_ids = array();
                foreach ($order_goods_list as $value)
                {
                    if($value->image)
                    {
                        $image_id_array = explode(',', trim($value->image , ','));
                        $image_ids[] = $image_id_array[0];
                    }
                }
                !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
            }
            
            $view = new ViewModel(array(
                'order_id' => $order_id,
                'contacts_list' => $contacts_list,
                'order_goods_list' => $order_goods_list,
                'order_goods_info' => $order_goods_info,
                'image' => $image,
            ));
        }
        
        
        
        $view->setTemplate('index/order/confirm_order');
        return $this->setMenu($view, 3 , $action);
    }
    
    public function paymentAction()
    {
        
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        
        $order_id = isset($_GET['orderId']) ? (int)$_GET['orderId'] : '';
        (!$order_id) && die;
        $order_info = $this->getOrderTable()->getOne(array('id' => $order_id , 'user_id' => $_SESSION['index_user_id'] , 'status' => 1 , 'delete' => DELETE_FALSE));
        !$order_info && die;
        
        $time_where = new Where();
        $time_where->lessThanOrEqualTo('start_time', $this->getTime());
        $time_where->greaterThanOrEqualTo('deadline', $this->getTime());
        $time_where->equalTo('delete', DELETE_FALSE);
        $time_node = $this->getTimeNodeTable()->getOne($time_where);
        
        
        $alipay_count = $this->getAccountListTable()->getOne(array('bank_id' => 1 , 'delete' => DELETE_FALSE));
        $bank_count = $this->getAccountListTable()->getOne(array('delete' => DELETE_FALSE) , array('*'), array('sort asc'));
        $bank_list = $this->getBankListTable()->getBankList();
        
        $action = isset($_GET['cartType']) ? $_GET['cartType'] : ''; 
        $view = new ViewModel(array(
            'alipay_count' => $alipay_count,
            'bank_count' => $bank_count,
            'bank_list' => $bank_list,
            'order_info' => $order_info,
            'time_node' => $time_node,
        ));
        $view->setTemplate('index/order/payment');
        return $this->setMenu($view, 3 , $action);
    }
    
    public function payResultAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        setcookie('contact_id' , '' , time()-1);
        setcookie('messages' , '' , time()-1);
        $_SESSION['cart_ids'] = '';
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        $res = isset($_GET['res']) ? $_GET['res'] : '';
        if( (!$id && !$res) || ($res && $res != 'failed'))
        {
            die;
        }
        
        $order_info = array();
        if($id && !$res)
        {
            $order_info = $this->getOrderTable()->getOne(array('id' => $id , 'user_id' => $_SESSION['index_user_id'] , 'delete' => DELETE_FALSE));
        }
        
        $where = array('referrer_type' => 3 , 'status' => 3 , 'delete' => DELETE_FALSE);
        if($_SESSION['user_type'] == 2)
        {
            $where['type'] = 2;
        }
        $goods_list = $this->getGoodsTable()->fetchAll( $where , array('sale_number desc') , 10);
        $image = array();
        if($goods_list)
        {
            foreach ($goods_list as $value)
            {
                if($value->image)
                {
                    $image_id_array = explode(',', trim($value->image , ','));
                    $image_ids[] = $image_id_array[0];
                }
            }
            !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
        }
        else
        {
            $where = array('status' => 3 , 'delete' => DELETE_FALSE);
            if($_SESSION['user_type'] == 2)
            {
                $where['type'] = 2;
            }
            $goods_list = $this->getGoodsTable()->fetchAll($where , array('sale_number desc') , 10);
        
            if($goods_list)
            {
                foreach ($goods_list as $value)
                {
                    if($value->image)
                    {
                        $image_id_array = explode(',', trim($value->image , ','));
                        $image_ids[] = $image_id_array[0];
                    }
                }
                !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
            }
        }
        
        $user_info = $this->getViewUserTable()->getOne(array('id' => $_SESSION['index_user_id']));
        $qq = $user_info->admin_qq;
        
        $action = isset($_GET['cartType']) ? $_GET['cartType'] : '';
        $view = new ViewModel(array(
            'order_info' =>$order_info,
            'goods_list' => $goods_list,
            'image' => $image,
            'qq' => $qq,
        ));
        $view->setTemplate('index/order/pay_result');
        return $this->setMenu($view, 3 , $action);
    }
    
    public function supplyPlanAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] != 1)
        {
            die;
        }
        
        $first_category = isset($_GET['firstCategory']) ? (int)$_GET['firstCategory'] : 0;
        $second_category = isset($_GET['secondCategory']) ? (int)$_GET['secondCategory'] : 0;
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $order_list = $this->getOrderTable()->fetchAll(array('children' => 0 , 'status' => array(3,4), 'user_id' =>$_SESSION['index_user_id'] , 'delete'=>DELETE_FALSE) , null , null , array('id'));
    
        $start_timestamp = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
        $end_timestamp = mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"));
    
        $show_start_time = date("m月d日",$start_timestamp);
        $show_end_time = date("m月d日",$end_timestamp);
    
        $week_plan_list = array();
        $week_plan = array();
        if($order_list)
        {
            $order_ids = array();
            foreach($order_list as $value)
            {
                $order_ids[] = $value['id'];
            }
    
            $like=array();
            if($keyword)
            {
                $like['name'] = $keyword;
                $like['order_sn'] = $keyword;
            }
    
            $start_time = date("Y-m-d",$start_timestamp);
            $end_time = date("Y-m-d",$end_timestamp);
            $where = new Where();
            $where->in('order_id' , $order_ids);
            $where->equalTo('delete', DELETE_FALSE);
            $where->greaterThan('number', 0);
            $where->greaterThanOrEqualTo('date_time', $start_time);
            $where->lessThanOrEqualTo('date_time', $end_time);
            if($first_category && !$second_category)
            {
                $category_list = $this->getGoodsCategoryTable()->fetchAll(array('parent_id' => $first_category , 'status' => 1 , 'delete' =>DELETE_FALSE) , null ,null ,array('id'));
                if(!empty($category_list)){
                    $category_ids = array();
                    foreach ($category_list as $value)
                    {
                        $category_ids[] = $value->id;
                    }
                    $where->in('category_id' , $category_ids);
                }
                else 
                {
                    $where->equalTo('category_id', $first_category);
                }
            }
            elseif ($second_category)
            {
                $where->equalTo('category_id' , $second_category);
            }
            $week_plan_list = $this->getViewWeekPlanTable()->getAll($where , null , array('timestamp' => 'DESC' ,'id'=>'DESC') , false, 0 , null , $like);
           
            foreach ($week_plan_list['list'] as $value)
            {
                $week_plan[$value->order_goods_id]['goods_name'] = $value->name;
                $week_plan[$value->order_goods_id]['total_supply'] = $value->total_number;
                $week_plan[$value->order_goods_id]['model'] = $value->model;
                $week_plan[$value->order_goods_id]['size'] = $value->size;
                $week_plan[$value->order_goods_id]['unit_name'] = $value->unit_name;
                $week_plan[$value->order_goods_id][date( 'N', strtotime($value->date_time))] = $value;
            }
        }
        
        $goods_category = $this->getGoodsCategoryTable()->fetchAll(array('parent_id' => 0 , 'status' => 1 , 'delete' => DELETE_FALSE));
    
        $view = new ViewModel(array(
            'week_plan' => $week_plan,
            'show_start_time' => $show_start_time,
            'show_end_time' => $show_end_time,
            'goods_category' => $goods_category,
            'first_category' => $first_category,
            'second_category' => $second_category,
            'keyword' => $keyword,
        ));
        $view->setTemplate('index/order/supply_plan');
        return $this->setMenu($view, 3 , 'supplyPlan');
    }
    
    public function supplierAccountAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] != 2)
        {
            die;
        }
        $type = $this->params()->fromRoute('type' , 2);
        $page = $this->params()->fromRoute('page' , 1);
        $type = isset($_GET['type']) ? $_GET['type'] : $type;
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : substr($this->getTime(), 0 ,10);
        if(!in_array($type, array(1,2)))
        {
            die;
        }
    
        $order_list = array();
        $where = new Where();
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time.' 00:00:00');
            if($start_time.' 00:00:00' > $end_time)
            {
                $end_time = $start_time;
            }
        }
        if($end_time)
        {
            $where->lessThanOrEqualTo('timestamp', $end_time.' 23:59:59');
        }
    
        if($type == 2)
        {
            $where->equalTo('children', 0);
            $where->equalTo('type', 2); //要改为2
            $where->equalTo('user_id', $_SESSION['index_user_id']);
            $where->equalTo('delete', DELETE_FALSE);
            $where->notEqualTo('status', 6);
            $order_list = $this->getOrderTable()->getAll($where, null , array('timestamp' => 'DESC' ,'id'=>'DESC'),true , $page , 10 );
            $order_status = $this->getAdminController()->orderStatus();
        }
    
        if($type == 1)
        {
            $where->equalTo('type', 1);
            $where->notEqualTo('status', 5);
            $where->equalTo('user_id', $_SESSION['index_user_id']);
            $where->equalTo('delete', DELETE_FALSE);
            $order_list = $this->getGoodsTable()->getAll($where, null , array('timestamp' => 'DESC' ,'id'=>'DESC'),true , $page , 10 );
            $order_status = $this->getAdminController()->goodsStatus();
        }
    
        $view = new ViewModel(array(
            'paginator' => $order_list['paginator'],
            'condition' => array(
                'controller' => 'order',
                'action' => 'supplierAccount',
                'page'   => $page,
                'type' => $type,
                'where' => array(
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                )
            ),
            'type' => $type,
            'order_list' => $order_list['list'],
            'order_status' => $order_status,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ));
        $view->setTemplate('index/order/supplier_account');
        return $this->setMenu($view, 1 ,'account');
    }
    
    public function dealerAccountAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] != 1)
        {
            die;
        }
        $page = $this->params()->fromRoute('page' , 1);
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : substr($this->getTime(), 0 ,10);
    
        $order_list = array();
        $where = new Where();
        $where->equalTo('children', 0);
        $where->equalTo('user_id', $_SESSION['index_user_id']);
        $where->equalTo('delete', DELETE_FALSE);
        $where->notEqualTo('status', 6);
        $order_status = $this->getAdminController()->orderStatus();
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time.' 00:00:00');
            if($start_time.' 00:00:00' > $end_time)
            {
                $end_time = $start_time;
            }
        }
        if($end_time)
        {
            $where->lessThanOrEqualTo('timestamp', $end_time.' 23:59:59');
        }
        $order_list = $this->getOrderTable()->getAll($where, null , array('timestamp' => 'DESC' ,'id'=>'DESC'),true , $page , 10 );
        $view = new ViewModel(array(
            'paginator' => $order_list['paginator'],
            'condition' => array(
                'controller' => 'order',
                'action' => 'dealerAccount',
                'page'   => $page,
                'where' => array(
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                )
            ),
            'order_list' => $order_list['list'],
            'order_status' => $order_status,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ));
        $view->setTemplate('index/order/dealer_account');
        return $this->setMenu($view, 1 , 'account');
    }
    
    public function accountDetailAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $type = $this->params()->fromRoute('type' , 2);
        $id = $this->params()->fromRoute('id');
        if(!$id || !in_array($type, array(1,2)))
        {
            die;
        }
    
        $order_status = array();
        $images = array();
        if($type == 1)
        {
            $info = $this->getViewGoodsTable()->getOne(array('id' => $id , 'type' => 1,'user_id'=>$_SESSION['index_user_id'] , 'delete'=>DELETE_FALSE));
            $specification_info = $this->getGoodsSpecificationTable()->getOne(array('goods_id'=>$info->id , 'delete' => DELETE_FALSE) , array('*') , array('id' => 'ASC'));
            $info->specification = $specification_info;
        }
    
        if($type ==2)
        {
            $info = $this->getOrderTable()->getOne(array('id' => $id , 'user_id' =>$_SESSION['index_user_id'] , 'status'=>array(1,2,3,4,5,7) , 'delete' =>DELETE_FALSE ));
            $order_status = $this->getAdminController()->orderStatus();
            $order_goods_list = $this->getViewOrderGoodsTable()->fetchAll( array('order_id' => $info->id , 'delete' => DELETE_FALSE));
            if($order_goods_list)
            {
                $image_ids = array();
                foreach ($order_goods_list as $value)
                {
                    if($value->image)
                    {
                        $image = explode(',', trim($value->image , ','));
                        $image_ids[] = $image[0];
                    }
                }
                $images = $this->getImageTable()->getImages($image_ids);
            }
            $info->order_goods = $order_goods_list;
        }
    
        $view = new ViewModel(array(
            'info' => $info,
            'images' => $images,
            'type' => $type,
            'order_status' => $order_status,
        ));
        $view->setTemplate('index/order/account_statement_detail');
        return $this->setMenu($view, 1 ,'account');
    }
}
