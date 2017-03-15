<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
class ServiceController extends CommonController
{
    /**
     * 我的售后列表
     */
    public function serviceListAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $user_id = $_SESSION['index_user_id'];
        $page = $this->params()->fromRoute('page',1);
        $status = $this->params()->fromRoute("status",1);
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $this->params()->fromRoute('keyword','');
        
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $this->params()->fromRoute('start_time' , '');
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $this->params()->fromRoute('end_time' , '');
        
        $where = new Where();
        $where->equalTo('delete', '0');
        $where->equalTo('user_id', $user_id);
        if($status == '2')
        {
            $where->in('status',array(2,3));
        }
        else 
        {
            $where->equalTo('status', $status);
        }
        if($start_time)
        {
            $where->greaterThanOrEqualTo('customer_service_apply_timestamp', $start_time.' 00:00:00');
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time.' 00:00:00' > $end_time)
                {
                    $end_time = $start_time;
                }
                $where->lessThanOrEqualTo('customer_service_apply_timestamp', $end_time.' 23:59:59');
            }
        }
        $like = array();
        if($keyword)
        {
            $like['order_sn'] = $keyword;
        }
        $order = array("customer_service_apply_timestamp desc");
        $service_list  = $this->getViewCustomerServiceApplyTable()->getAll($where,null,$order,true,$page,10,$like);
        if(!empty($service_list['list']))
        {
            foreach ($service_list['list'] as $k=>&$v)
            {
                $order_goods_ids = explode(',', $v->order_goods_id);
                $where_sub = new Where();
                $where_sub->in('id',$order_goods_ids);
                $goods_name = $this->getViewOrderGoodsTable()->fetchAll($where_sub,null,null,array('name'));
                $names = array();
                foreach ($goods_name as $m)
                {
                    $names[] = $m->name;
                }
                $v->goods_name = implode(',', $names);
            }
        }
        $view = new ViewModel(array(
            'services' => $service_list['list'],
            'paginator' => $service_list['paginator'],
            'serviceType' => $this->serviceType(),
            'serviceStatus' => $this->serviceStatus(),
            'status' => $status,
            'condition' => array(
                'controller' => 'service',
                'action' => 'serviceList',
                'status' => $status,
                'page'   => $page,
                'status' => $status,
                'keyword' => $keyword,
                'where' => array(),
            ),
            'keyword'=>$keyword,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
        ));
        $view->setTemplate('index/service/my_aftersell');
        return $this->setMenu($view, 1 , 'service');
    }

    /**
     * 申请售后
     */
    public function applyQualityAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $order_goods_id = isset($_GET['order_goods_id']) ? (int)$_GET['order_goods_id'] : '';
        $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : '';
        
        if(!$order_id)
        {
            $this->showMessage("非法操作");
        }
        if(!empty($_POST))//提交售后申请
        {
            $data = array();
            foreach ($_POST as $k=>$v)
            {
                $data[$k] = $v;
            }
            $image_ids = $data['image_ids'];
            unset($data['image_ids']);
            if($image_ids)
            {
                $data['image'] = implode(',', $image_ids);
            }
            else 
            {
                $data['image'] = '';
            }
            $data['timestamp'] = $this->getTime();
            $res = $this->getCustomerServiceApplyTable()->insertData($data);
            if($res)
            {
                if($data['order_goods_id'])//update  order_goods表的customer_service_id字段
                {
                    $order_goods_ids_arr = explode(',', $data['order_goods_id']);
                    foreach ($order_goods_ids_arr as $v)
                    {
                        $this->getOrderGoodsTable()->updateData(array('customer_service_id'=>$res),array('id'=>$v,'order_id'=>$order_id));
                    }
                }
                echo "<script>alert('申请售后成功');</script>";
                return $this->redirect()->toRoute('index',array('controller'=>'service','action'=>'serviceList'));
                die;
            }
            else 
            {
                $this->showMessage("申请售后失败");
            }
            die;
        }
        $user_id = $_SESSION['index_user_id'];
        $goods_info = array();
        $goods_ids = '';
        if($order_goods_id && $order_id)//申请单个商品售后
        {
            $order_good = $this->getViewOrderGoodsTable()->getOne(array('delete'=>0,'order_id'=>$order_id,'id'=>$order_goods_id));
            $image_ids = explode(',', $order_good->image);
            $image_info = $this->getImageTable()->getImages(array($image_ids[0]));
            $order_good->image_path = $image_info[$image_ids[0]]['image_path'];
            $goods_info[] = $order_good;
            $goods_ids[] = $order_good->id;
//             $this->dump($goods_ids);exit;
        }
        else//整个订单申请售后 
        {
            $order_goods = $this->getViewOrderGoodsTable()->fetchAll(array('delete'=>0,'order_id'=>$order_id));
            foreach ($order_goods as $k=>&$v)
            {
                $image_ids = explode(',', $v->image);
                $image_info = $this->getImageTable()->getImages(array($image_ids[0]));
                $v->image_path = $image_info[$image_ids[0]]['image_path'];
                $goods_info[$k] = $v;
                $goods_ids[$k] = $v->id;
            }
        }
        $view = new ViewModel(array(
            'goods_ids' => implode(',', $goods_ids),
            'goods_info' => $goods_info,
            'user_id' => $user_id,
            'order_id' => $order_id,
            'serviceType' => $this->serviceType(),
        ));
        $view->setTemplate('index/service/apply_quality');
        return $this->setMenu($view, 1 , 'service');
    }

    /**
     * 售后详情
     */
    public function qualityDetailAction()
    {
         if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $id = $this->params()->fromRoute('id');
        if(!$id)
        {
            $this->showMessage("非法操作");
        }
        $service = $this->getCustomerServiceApplyTable()->getOne(array('delete'=>0,'id'=>$id));
        if(empty($service))
        {
            $this->showMessage("不存在该售后服务或已被删除");
        }
        $images_info = '';
        if($service->image)
        {
            $image_arr = explode(',', $service->image);
            $images_info = $this->getImageTable()->getImages($image_arr);
        }
        
        $order_goods_ids = explode(',', $service->order_goods_id);
        
        $where = new Where();
        $where->equalTo('delete', 0);
        $where->in('id',$order_goods_ids);
        $order_goods_info = $this->getViewOrderGoodsTable()->fetchAll($where);
//         $this->dump($order_goods_info);exit;
        foreach ($order_goods_info as &$v)
        {
            $image_ids = explode(',', $v->image);
            $image_info = $this->getImageTable()->getImages(array($image_ids[0]));
//             $this->dump($image_info);exit;
            $v->image_path = $image_info[$image_ids[0]]['image_path'];
        }
//         $this->dump($order_goods_info);exit;
        $user_info = $this->getViewUserTable()->getOne(array('id' => $_SESSION['index_user_id']));
        $qq = $user_info->admin_qq;
        $view = new ViewModel(array(
            'order_goods_info' => $order_goods_info,
            'images_info'  => $images_info,
            'service' => $service,
            'serviceType' => $this->serviceType(),
            'serviceStatus' => $this->serviceStatus(),
            'qq' => $qq,
        ));
        $view->setTemplate('index/service/quality_detail');
        return $this->setMenu($view, 1 , 'service');
    }
    
    /**
     * 市场反馈--供应商
     */
    public function marketFeedbackAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] != 2)
        {
            die;
        }
        $page = $this->params()->fromRoute('page',1);
        $user_id = $_SESSION['index_user_id'];
        $feedbacks = $this->getFeedbackTable()->getAll(array('delete'=>0,'user_id'=>$user_id),null,array('timestamp desc'),true,$page,10);
        $view = new ViewModel(array(
            'feedbacks' => $feedbacks['list'],
            'paginator' => $feedbacks['paginator'],
            'messageType' => $this->messageType(),
            'condition' => array(
                'controller' => 'service',
                'action' => 'marketFeedback',
                'page'   => $page,
                'where' => array(),
            ),
        ));
        $view->setTemplate('index/service/market_feedback');
        return $this->setMenu($view, 1 , 'marketFeedback');
    }
    
    /**
     * 回复市场反馈
     */
    public function feedbackReplyAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $param = $_POST;
        $user_id = $_SESSION['index_user_id'];
        $res = $this->getFeedbackTable()->updateData(array('response'=>$param['response'],'status'=>2,'timestamp_update'=>$this->getTime()), array('user_id'=>$user_id,'id'=>$param['id']));
        if($res)
        {
            die(json_encode(array('code'=>'1')));
        }
        else
        {
            die(json_encode(array('code'=>'0')));
        }
    }
    
    /**
     * 查看市场反馈
     */
    public function feedbackSeeReplyAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $param = $_POST;
        $user_id = $_SESSION['index_user_id'];
        $feedback = $this->getFeedbackTable()->getOne(array('delete'=>0,'status'=>2,'user_id'=>$user_id,'id'=>$param['id']));
        if($feedback)
        {
            die(json_encode(array('code'=>'1','reply'=>$feedback->response)));
        }
        else 
        {
            die(json_encode(array('code'=>'0')));
        }
        
    }
    
    /**
     * 我的留言---经销商
     */
    public function leaveMessageAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] != 1)
        {
            die;
        }
        $page = $this->params()->fromRoute('page',1);
        $user_id = $_SESSION['index_user_id'];
        $messages = $this->getLeaveMessageTable()->getAll(array('delete'=>0,'parent_id'=>0,'user_id'=>$user_id),null,array('timestamp desc'),true,$page,10);
        if(!empty($messages['list']))
        {
            foreach ($messages['list'] as $k=>&$v)
            {
                $reply = $this->getLeaveMessageTable()->getOne(array('delete'=>0,'parent_id'=>$v->id));
                if(!empty($reply))
                {
                    $v->reply = $reply->content;
                }
                else 
                {
                    $v->reply = "未回复";
                }
            }            
        }
        $view = new ViewModel(array(
            'messages' => $messages['list'],
            'paginator' => $messages['paginator'],
            'messageType' => $this->messageType(),
            'condition' => array(
                'controller' => 'service',
                'action' => 'leaveMessage',
                'page'   => $page,
                'where' => array(),
            ),
        ));
        $view->setTemplate('index/service/leave_message');
        return $this->setMenu($view, 1 ,'message');
    }

}