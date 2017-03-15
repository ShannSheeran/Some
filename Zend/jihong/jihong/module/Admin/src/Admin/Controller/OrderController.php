<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class OrderController extends CommonController
{
    /**
     * 订单列表
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->checkLogin('order_index'); 
        $type = $this->params()->fromRoute('type' , '1');
        $page = $this->params()->fromRoute('page');
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword']:$this->params()->fromRoute('keyword','');
        $status = isset($_GET['status']) ? $_GET['status']:$this->params()->fromRoute('status',0);
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->equalTo('children', 0);
        $where->equalTo('type', $type);
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
        if($status)
        {
            $where->equalTo('status', $status);
        }
        
        $like = array();
        if($keyword)
        {
            $like['user_name'] = $keyword;
            $like['order_sn'] = $keyword;
        }
        
        $order_status = $this->orderStatus();
        $order_list = $this->getViewOrderTable()->getAll($where,null, array('id' => 'DESC'), true, $page, 10 , $like);
        
        $view=new ViewModel(array(
            'paginator' => $order_list['paginator'],
            'condition' => array(
                 'action' => 'index',
                 'page'   => $page,
                 'end_time' => $end_time,
                 'start_time' => $start_time,
                 'keyword' => $keyword,
                 'status' => $status,
                'where' => array(
                    'end_time' => $end_time,
                    'start_time' => $start_time,
                )
             ),
            'order_list' => $order_list['list'],
            'type' => $type,
            'order_status' => $order_status,
            'end_time' => $end_time,
            'start_time' => $start_time,
            'keyword' => $keyword,
            'status' => $status,
        ));
        $view->setTemplate('admin/order/index');
        return $this->setMenu($view,1);
    }
    
    /**
     * 订单详情
     * @return \Zend\View\Model\ViewModel
     */
    public function detailAction()
    {
        $this->checkLogin('order_detail');
        $type  = $this->params()->fromRoute('type' , 1);
        $id  = $this->params()->fromRoute('id');
        if($id)
        {
            $express_list = $this->getExpressListTable()->fetchAll();
            $pay_type = $this->payType();
            $order_status = $this->orderStatus();
            
            $order_info = $this->getViewOrderTable()->getOne(array('id' => $id , 'type' => $type , 'delete' => DELETE_FALSE));
            $order_goods_list = $this->getViewOrderGoodsTable()->fetchAll(array('order_id' => $id ,'delete'=>DELETE_FALSE));
            
            $parent_order = array();
            $siblings_order = array();
            if($order_info->parent_id >0)
            {
                $parent_order   = $this->getViewOrderTable()->getOne(array('id' => $order_info->parent_id));
                $siblings_order = $this->getViewOrderTable()->fetchAll(array('parent_id' => $order_info->parent_id , 'type' => $type , 'delete' => DELETE_FALSE));
            }
            else 
            {
                $parent_order  =$order_info;
                $siblings_order[] =$order_info;
            }
            $image_info = $this->getImageTable()->getOne(array('id' =>$parent_order->pay_img ));
        }
        else 
        {
            echo "<script>history.back();</script>";
            exit;
        }
        
        $view=new ViewModel(array(
            'id' =>$id,
            'type' => $type,
            'order_info' => $order_info,
            'pay_type' => $pay_type,
            'order_status' => $order_status,
            'order_goods_list' => $order_goods_list,
            'express_list' => $express_list,
            'parent_order' => $parent_order,
            'siblings_order' => $siblings_order,
            'image_info' => $image_info,
        ));
        $view->setTemplate('admin/order/detail');
        return $this->setMenu($view,1);
    }
    
    /**
     * ajax修改数量
     */
    public function editNumberAction()
    {
        $order_review = $this->checkLogin('order_review');
        if(!$order_review)
        {
            echo json_encode(array('status' => 2));
            die;
        }
        
        $id = $this->params()->fromPost('id');
        $number = (int)$this->params()->fromPost('number');
        
        $order_goods_info = $this->getOrderGoodsTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
        $total_cash = $order_goods_info->price_cash * $number;
        $different_number = $number - $order_goods_info->number;
        $different_cash = $total_cash - $order_goods_info->cash;
        $res = $this->getOrderGoodsTable()->updateData(array('number' => $number , 'supply_number' => $number , 'cash' => $total_cash), array('id' => $id));
        
        $order_info = $this->getOrderTable()->getOne(array('id' => $order_goods_info->order_id ));
        $order_number = $order_info->total_number + $different_number;
        $order_cash = $order_info->total_cash + $different_cash;
        $this->getOrderTable()->updateData(
            array(
                'total_number' =>$order_number , 
                'total_cash' => $order_cash ,
                'total_supply_number' => $order_number,
         ), array(
             'id' => $order_info->id
         ));
        
        $order_info = $this->getOrderTable()->getOne(array('id' => $order_goods_info->order_id ));
        if($different_cash > 0)
        {
            $this->getGoodsTable()->updateKey($order_goods_info->goods_id, 2 , 'number' , $different_number);
            $this->getGoodsTable()->updateKey($order_goods_info->goods_id, 1 , 'sale_number' , $different_number);
            $this->getGoodsSpecificationTable()->updateKey($order_goods_info->specification_id, 2 , 'number' , $different_number);
            $this->getGoodsSpecificationTable()->updateKey($order_goods_info->specification_id, 1 , 'sale_number' , $different_number);
            
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(5, 2, $order_goods_info->order_id , true , true , $different_cash);
        }
        else
        {
            $this->getGoodsTable()->updateKey($order_goods_info->goods_id, 1 , 'number' , $different_number);
            $this->getGoodsTable()->updateKey($order_goods_info->goods_id, 2 , 'sale_number' , $different_number);
            $this->getGoodsSpecificationTable()->updateKey($order_goods_info->specification_id, 1 , 'number' , $different_number);
            $this->getGoodsSpecificationTable()->updateKey($order_goods_info->specification_id, 2 , 'sale_number' , $different_number);
            
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(6, 2, $order_goods_info->order_id, true , true , $different_cash);
        }
        
        if($res)
        {
            echo json_encode(array('status' => 1));
        }
        else
        {
            echo json_encode(array('status' => 2));
        }
        die;
    }
    
    /**
     * ajax修改单价
     */
    public function editPricecashAction()
    {
        $order_review = $this->checkLogin('order_review');
        if(!$order_review)
        {
            echo json_encode(array('status' => 2));
            die;
        }
        
        $id = $this->params()->fromPost('id');
        $pricecash = $this->params()->fromPost('pricecash');
        
        $order_goods_info = $this->getOrderGoodsTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
        $total_cash = $order_goods_info->number * $pricecash;
        $different_cash = $total_cash - $order_goods_info->cash;
        
        $res = $this->getOrderGoodsTable()->updateData(array('price_cash' => $pricecash , 'cash' => $total_cash), array('id' => $id));
        
        $order_info = $this->getOrderTable()->getOne(array('id' => $order_goods_info->order_id ));
        $order_cash = $order_info->total_cash + $different_cash;
        $this->getOrderTable()->updateData(
            array(
                'total_cash' => $order_cash ,
            ), array(
                'id' => $order_info->id
            ));
        
        $order_info = $this->getOrderTable()->getOne(array('id' => $order_goods_info->order_id ));
        if($different_cash > 0)
        {
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(5, 2, $order_goods_info->order_id, true , true , $different_cash);
        }
        else
        {
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(6, 2, $order_goods_info->order_id, true , true , $different_cash);
        }
        
        if($res)
        {
            echo json_encode(array('status' => 1));
        }
        else
        {
            echo json_encode(array('status' => 2));
        }
        die;
    }
    
    /**
     * 处理订单
     */
    public function dealOrderAction()
    {
        $this->checkLogin('order_review');
        
        $pass = $this->params()->fromPost('pass');
        $nopass = $this->params()->fromPost('nopass');
        $cancel = $this->params()->fromPost('cancel');
        $delivery = $this->params()->fromPost('delivery');
        $id = $this->params()->fromPost('id');
        $type = $this->params()->fromPost('type');

        if($cancel)
        {
            $data['reason'] = $this->params()->fromPost('reason' ,'');
            $data['status']  = 6;
            $this->getOrderTable()->updateData($data, array('id' => $id));
            
            $order_info = $this->getOrderTable()->getOne(array('id' =>$id ));
            
            //获得订单商品
            $order_good = $this->getOrderGoodsTable()->fetchAll(array('order_id'=>$id));
            foreach ($order_good as $v)
            {//加库存
                $this->getGoodsTable()->updateKey($v->goods_id, 1 , 'number' , $v->number);
                $this->getGoodsTable()->updateKey($v->goods_id, 2 , 'sale_number' , $v->number);
                $this->getGoodsSpecificationTable()->updateKey($v->specification_id, 1 , 'number' , $v->number);
                $this->getGoodsSpecificationTable()->updateKey($v->specification_id, 2 , 'sale_number' , $v->number);
            }
            
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(6, 2, $id , true);
        }
        
        if($nopass)
        {
            $reason = $this->params()->fromPost('reason');
            if(!$reason)
            {
                echo "<script>alert('请填写审核不通过理由');history.back();</script>";
                exit;
            }
            
            $data = array();
            $data['reason'] = $reason;
            $data['status'] = 7;
            $this->getOrderTable()->updateData($data, array('id' => $id));
            
            $order_info = $this->getOrderTable()->getOne(array('id' =>$id ));
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(6, 2, $id , true);
            
            //获得订单商品
            $order_good = $this->getOrderGoodsTable()->fetchAll(array('order_id'=>$id));
            foreach ($order_good as $v)
            {//加库存
                $this->getGoodsTable()->updateKey($v->goods_id, 1 , 'number' , $v->number);
                $this->getGoodsTable()->updateKey($v->goods_id, 2 , 'sale_number' , $v->number);
                $this->getGoodsSpecificationTable()->updateKey($v->specification_id, 1 , 'number' , $v->number);
                $this->getGoodsSpecificationTable()->updateKey($v->specification_id, 2 , 'sale_number' , $v->number);
            }
        }
        
        if($pass)
        {
            $data['reason'] = $this->params()->fromPost('reason' ,'');
            $data['status']  = 3;
            $this->getOrderTable()->updateData($data, array('id' => $id));
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(5, 2, $id , true , false);
            
/*            $user_info = $this->getUserTable()->getOne(array('id' => $order_info->user_id ));
            $this->getAdminFinancialController()->makeOrderFinancialRecordAction(5, 2, $order_info , true);
 */            
        }
        
        if($delivery)
        {
            $express_id   = $this->params()->fromPost('express_id');
            $shipping_sn = $this->params()->fromPost('shipping_sn');
            if(!$express_id || !$shipping_sn)
            {
                echo "<script>alert('物流信息没有填写完整');history.back();</script>";
                exit;
            }
            
            $data['reason'] = $this->params()->fromPost('reason' ,'');
            $data['express_id'] = $express_id;
            $data['shipping_sn'] = $shipping_sn;
            $data['shipments'] = $this->getTime();
            $data['status']  = 4;
            
            $this->getOrderTable()->updateData($data, array('id' => $id));
            
            
        }
        
        $order_info = $this->getOrderTable()->getOne(array('id' => $id));
        $value['status'] = $data['status'];
        $value['user_id'] = $order_info['user_id'];
        $value['order_id'] = $id;
        $value['timestamp'] = $this->getTime();
        $this->getOrderTrackingTable()->indateKey($value,1,$data['status'],$order_info['order_sn']);
        return $this->redirect()->toRoute('admin-order',array('action'=>'detail' , 'id' => $id ,'type'=>$type));
    }
    
    public function weeklyPlanAction()
    {
        $this->checkLogin('order_weeklyplan');
        $page = $this->params()->fromRoute('page');
        $keyword = $_GET['keyword'] ? $_GET['keyword'] : $this->params()->fromRoute('keyword','');
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->equalTo('children', 0);
        $where->greaterThan('total_supply_number', 0);
        $where->equalTo('status', 3);
        $where->equalTo('user_type', 1);
        $like = array();
        if($keyword)
        {
            $like['order_sn'] = $keyword;
            $like['user_name'] = $keyword;
        }
        $order_list = $this->getViewOrderTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10 , $like);
        $view=new ViewModel(array(
            'paginator' => $order_list['paginator'],
            'keyword' => $keyword,
            'condition' => array(
                 'page' => $page,
                 'action' => 'weeklyPlan',
                 'keyword' => $keyword,
             ),
            'order_list' => $order_list['list'],
        ));
        $view->setTemplate('admin/order/weekly_plan');
        return $this->setMenu($view,1);
    }
    
    public function planDetailAction()
    {
        $this->checkLogin('order_weeklyplan_detail');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $order_info = $this->getViewOrderTable()->getOne(array('id' => $id));
            $order_goods_list = $this->getViewOrderGoodsTable()->fetchAll(array('order_id'=>$id , 'delete' => DELETE_FALSE));
            $date = date('Y-m');
            $week = $this->getMonthweeks($date.'-1');
            
            $datatime = $this->params()->fromRoute('datetime' ,'');
            $datatime = isset($_GET['datetime']) ? $_GET['datetime'] : $datatime;
            if($datatime)
            {
                $where_time = explode('~', $datatime);
                $date = substr($where_time[0], 0 , 7);
                $week = $this->getMonthweeks($date.'-1');
            }
            else
            {
                $where_time = explode('~', $week[0]);
            }
            $start_time = $where_time[0];
            $end_time = $where_time[1];
            
            if($start_time <= date('Y-m-d') && $end_time >= date('Y-m-d'))
            {
                $disabled_day = date('w');
            }
            elseif($start_time > date('Y-m-d'))
            {
                $disabled_day = 0;
            }
            elseif ($end_time < date('Y-m-d'))
            {
                $disabled_day = 7;
            }
            
            foreach ($order_goods_list as $key => $value)
            {
                $where = new Where();
                $where->equalTo('order_goods_id', $value->id);
                $where->equalTo('delete', DELETE_FALSE);
                $where->greaterThanOrEqualTo('date_time', $start_time);
                $where->lessThanOrEqualTo('date_time', $end_time);
                $weekly_plan = $this->getWeekPlanTable()->fetchAll($where);
                
                foreach ($weekly_plan as $value)
                {
                    $week_date = date('D' , strtotime($value->date_time));
                    $order_goods_list[$key][$week_date] = $value;
                }
            }
        }
        $view=new ViewModel(array(
            'id' => $id,
            'date' => $date,
            'week' => $week,
            'order_info'    => $order_info,
            'order_goods_list' => $order_goods_list,
            'disabled_day' => $disabled_day,
            'datatime' => $datatime,
        ));
        $view->setTemplate('admin/order/weekly_plan_detail');
        return $this->setMenu($view,1);
    }
    
    public function saveWeeklyPlanAction()
    {
        $this->checkLogin('order_weeklyplan_edit');
        $week_duration = $_POST['week'];
        $id = $_POST['id'];
        $save = isset($_POST['save']) ? $_POST['save'] : '';
        $save_and_next = isset($_POST['saveAndNext']) ? $_POST['saveAndNext'] : '';
        $week = explode('~', $week_duration);
        $number = count($_POST['order_goods_id']);
        
        for ($i = 0 ; $i < $number ; $i++)
        {
            $disabled_day = $_POST['disabled_day'];
            for($j = 0 ; $j < 7 ; $j++ ,$disabled_day++ )
            {
                $data = array();
                $data['order_goods_id'] = $_POST['order_goods_id'][$i];
                $data['number'] = isset($_POST['number'.$i][$j]) ? (int) $_POST['number'.$i][$j] : 0;
                if( $_POST['plan_id'.$i][$j] )
                {
                        $this->getWeekPlanTable()->updateData($data, array('id' =>$_POST['plan_id'.$i][$j] , 'order_goods_id' => $data['order_goods_id'] ));
                }
                else 
                {
                    if($data['number'])
                    {
                        $timestamp = strtotime($week[0]) + $disabled_day * 86400;
                        $data['date_time'] = date('Y-m-d' , $timestamp);
                        $data['delete'] = DELETE_FALSE;
                        $data['timestamp'] = $this->getTime();
                        $this->getWeekPlanTable()->insertData($data);
                    }
                }
            }
        }
        
        if($save_and_next)
        {
            $next_week_start_timestamp = strtotime($week[0]) + 7 * 86400;
            $next_week_end_timestamp = strtotime($week[1]) + 7 * 86400;
            $next_week_start = date('Y-m-d' , $next_week_start_timestamp);
            $next_week_end = date('Y-m-d' , $next_week_end_timestamp);
            $week_duration = $next_week_start.'~'.$next_week_end;
        }

        return $this->redirect()->toRoute('admin-order',array('action'=>'planDetail' ,  'id' => $id ,'datetime'=>$week_duration));
        die;
    }
    
    public function selectWeekAction()
    {
        $date = isset($_POST['date']) ? $_POST['date'] : '';
        $week = $this->getMonthweeks($date);
        echo json_encode($week);
        die;
    }
    
    public function getMonthweeks($date)
    {
        $ret = array();
        $start_timestamp = strtotime($date);
        $month_days  = date('t', $start_timestamp);
        $month = date('m', $start_timestamp);
        $month_start_date = date('Y-m-d', $start_timestamp);
        $month_end_date = date('Y-m-' . $month_days, $start_timestamp);
        $end_timestamp = strtotime($month_end_date);
                
        $week_number = 5;
        date_default_timezone_set('PRC');
        $t = strtotime('+1 monday ' . $month_start_date);
        for ($n = 1; $n < $week_number; $n ++) 
        {
            $b = strtotime("+$n week -1 week", $t);
            $check_month = date('m' , strtotime("-1 day", $b));
            if($check_month < $month)
            {
                continue;
            }
            $week_start_date = date("Y-m-d", strtotime("-1 day", $b));
            $week_end_date = date("Y-m-d", strtotime("5 day", $b));
            $ret[] = $week_start_date . '~' . $week_end_date;
        }
        $last_date = date('w', $end_timestamp); 
        if ($last_date !== 6) 
        {
            $last_week_start_date = date('Y-m-d', strtotime("-$last_date day", $end_timestamp));
            $last_week_end_date = date('Y-m-d', strtotime("-$last_date day + 6 day", $end_timestamp));
            $ret[] = $last_week_start_date . '~' . $last_week_end_date;
        }
        return $ret;
    }
}
