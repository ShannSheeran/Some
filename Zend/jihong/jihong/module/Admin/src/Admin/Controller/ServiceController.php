<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Zend\Http\Header\Warning;
use Core\System\UploadfileApi;

class ServiceController extends CommonController
{

    public function indexAction()
    {
        $this->checkLogin('service_list'); 
        $type  = $this->params()->fromRoute('type' , 0);
        $type = isset($_GET['type']) ? trim($_GET['type']) : $type;
        
        $status  = $this->params()->fromRoute('status' , 0);
        $status = isset($_GET['status']) ? trim($_GET['status']) : $status;
        
        $page = $this->params()->fromRoute('page');
        
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $keyword;
        $start_time = $this->params()->fromRoute('start_time' , '');
        $end_time  = $this->params()->fromRoute('end_time' , '');
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $start_time;
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $start_time;
        
        $where = new Where();
        $where->equalTo('delete', '0');
        if($type)
        {
            $where->equalTo('customer_service_apply_type',$type);
        }
        if($status)
        {
            $where->equalTo('status',$status);
        }
        if($start_time)
        {
            $where->greaterThanOrEqualTo('customer_service_apply_timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where->lessThanOrEqualTo('customer_service_apply_timestamp', $end_time);
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
            $like['order_sn'] = $keyword;
        }
        $service_list = $this->getViewCustomerServiceApplyTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
        $view=new ViewModel(array(
            'services' => $service_list['list'], 
            'paginator' => $service_list['paginator'],
            'serviceType' => $this->serviceType(),
            'serviceStatus' => $this->serviceStatus(),
            'condition' => array(
                 'action' => 'index',
                 'status' => $status,
                 'type'    => $type,
                 'page'   => $page,
                 'keyword' => $keyword,
                 'where' => array(
                     'start_time'=>$start_time,
                     'end_time'=>$end_time,
                 ),
             ),
            'type' => $type,
            'status' => $status,
            'keyword'=>$keyword,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
        ));
        $view->setTemplate('admin/service/index');
        return $this->setMenu($view,1);
    }
    
    public function detailAction()
    {
        $this->checkLogin('service_detail');
        $id = $this->params()->fromRoute('id');
        if(!$id)
        {
            $this->showMessage('非法操作！');
        }
        $viewService = $this->getViewCustomerServiceApplyTable()->getOne(array('delete'=>0,'id'=>$id));
//         $this->dump($viewService);exit;
        if(!$viewService)
        {
            $this->showMessage('没有此条售后服务');
        }
        
        if(!$viewService->order_goods_id)
        {
            $this->showMessage("此售后服务没有商品");
        }
        
        $order_goods_id_arr = explode(',', $viewService->order_goods_id);
        
//         $this->dump($order_goods_arr);exit;
        $order_goods_arr = array();
        foreach ($order_goods_id_arr as $k=>$v)
        {
            $order_good = $this->getOrderGoodsTable()->getOne(array('delete'=>0,'id'=>$v));
//             $this->dump($order_good);exit;
            $good = $this->getGoodsTable()->getOne(array('delete'=>0,'id'=>$order_good->goods_id),array('code','level','name'));
            $order_good->code = $good->code;
            $order_good->level = $good->level;
            $order_good->name = $good->name;
            $order_goods_arr[$k]=$order_good;
        }
//         $this->dump($order_goods_arr);exit;
        
        $service_goods_id_arr = explode(',', $viewService->image);
        
        $service_goods_images = '';
        if($service_goods_id_arr)
        {
            $service_goods_images = $this->getImageTable()->getImages($service_goods_id_arr);
        }
//         $this->dump($service_goods_images);exit;
        $view=new ViewModel(array(
            'id' => $id,
            'viewService' => $viewService,
            'order_goods_arr' => $order_goods_arr,
            'service_goods_images' => $service_goods_images,
            'serviceType' => $this->serviceType(),
            'serviceStatus' => $this->serviceStatus(),
        ));
        $view->setTemplate('admin/service/detail');
        return $this->setMenu($view,1);
    }
    
    /**
     * changeServiceStatus
     */
    public function changeServiceStatusAction()
    {
        $this->checkLogin('service_deal');
        $id = $this->params()->fromPost('id');
        $deal = $this->params()->fromPost('deal');//处理完成
        $invalid = $this->params()->fromPost('invalid');//无效申请
//         $this->dump($_POST);exit;
        if(!$id)
        {
            $this->showMessage("非法操作");
        }
        $where = array('id'=>$id);
        if($deal)
        {
            $this->getCustomerServiceApplyTable()->updateData(array('status'=>2), $where);
            return $this->redirect()->toRoute('admin-service',array('action'=>'index'));
        }
        if($invalid)
        {
            $this->getCustomerServiceApplyTable()->updateData(array('status'=>3), $where);
            return $this->redirect()->toRoute('admin-service',array('action'=>'index'));
        }
        die;
     
    }
    
    public function statisticAction()
    {
        $this->checkLogin('service_statistic');
        
        $page = $this->params()->fromRoute('page');

        $start_time = $this->params()->fromRoute('start_time' , '');
        $end_time  = $this->params()->fromRoute('end_time' , '');
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $start_time;
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $end_time;

        if(isset($_GET['prev']) && $_GET['prev'])
        {
            if($start_time)
            {
                $start_time = date('Y-m-1 00:00:00' , strtotime( "$start_time -1month"));
            }
            else 
            {
                $start_time = date('Y-m-1 00:00:00' , strtotime( "-1month"));
            }
            
            if($end_time)
            {
                $end_time = date('Y-m-t 23:59:59' , strtotime( "$end_time -3day -1month"));
            }
            else 
            {
                $end_time = date('Y-m-t 23:59:59' , strtotime( "-3day -1month"));
            }
        }
        
        if(isset($_GET['next']) && $_GET['next'])
        {
            if($start_time)
            {
                $start_time = date('Y-m-1 00:00:00' , strtotime( "$start_time +1month"));
            }
            else 
            {
                $start_time = date('Y-m-1 00:00:00' , strtotime( "+1month"));
            }
            
            if($end_time)
            {
                $end_time = date('Y-m-t 23:59:59' , strtotime( "$end_time -3day +1month"));
            }
            else 
            {
                $end_time = date('Y-m-t 23:59:59' , strtotime( "-3day +1month"));
            }
        }
        
        $where_quality = new Where();
        $where_quality->equalTo('delete', '0');
        if($start_time)
        {
            $where_quality->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($start_time && $start_time > $end_time)
            {
                $end_time = $start_time;
            }
            $where_quality->lessThanOrEqualTo('timestamp', $end_time);
        }
        $where_quality->equalTo('type', '1');
        $count_quality = $this->getCustomerServiceApplyTable()->getServiceApplyCount($where_quality);
//         $this->dump($count_quality);exit;
        
        $where_logistics = new Where();
        $where_logistics->equalTo('delete', '0');
        if($start_time)
        {
            $where_logistics->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where_logistics->lessThanOrEqualTo('timestamp', $end_time);
            }
        }
        $where_logistics->equalTo('type', '2');
        $count_logistics = $this->getCustomerServiceApplyTable()->getServiceApplyCount($where_logistics);
//         $this->dump($count_logistics);exit;
        
        $where_other = new Where();
        $where_other->equalTo('delete', '0');
        if($start_time)
        {
            $where_other->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where_other->lessThanOrEqualTo('timestamp', $end_time);
            }
        }
        $where_other->equalTo('type', '3');
        $count_other = $this->getCustomerServiceApplyTable()->getServiceApplyCount($where_other);
        
        $where_order = new Where();
        $where_order->equalTo('delete', '0');
        if($start_time)
        {
            $where_order->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where_order->lessThanOrEqualTo('timestamp', $end_time);
            }
        }
        $count_order = $this->getOrderTable()->getOrderCount($where_order);
        

//         $this->dump($count_order);exit;
        $view=new ViewModel(array(
            'count_order' => $count_order,
            'count_other'=>$count_other,
            'count_logistics' => $count_logistics,
            'count_quality' => $count_quality,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
        ));
        $view->setTemplate('admin/service/statistic');
        return $this->setMenu($view,1);
    }
    
    public function messageAction()
    {
        $this->checkLogin('message_list'); 
        $is_read  = $this->params()->fromRoute('is_read' , 0);
        $is_read = isset($_GET['is_read']) ? trim($_GET['is_read']) : $is_read;
        
        $type  = $this->params()->fromRoute('type' , 0);
        $type = isset($_GET['type']) ? trim($_GET['type']) : $type;
        
        $page = $this->params()->fromRoute('page');
        
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $keyword;
        $start_time = $this->params()->fromRoute('start_time' , '');
        $end_time  = $this->params()->fromRoute('end_time' , '');
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $start_time;
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $start_time;
        
        $where = new Where();
        $where->equalTo('delete', '0');
        $where->equalTo('parent_id', '0');
        if($is_read)
        {
            $where->equalTo('is_read',$is_read);
        }
        
        if($type)
        {
            $where->equalTo('message_type',$type);
        }
        else 
        {
            $where->notEqualTo('message_type', 3);
        }
        
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where->lessThanOrEqualTo('timestamp', $end_time);
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
        }
        $message_list = $this->getViewMessageTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
//         $this->dump($message_list['list']);exit;
        $view=new ViewModel(array(
            'message' => $message_list['list'], 
            'paginator' => $message_list['paginator'],
            'messageType' => $this->messageType(),
            'message_is_read' => $this->messageIsRead(),
            'condition' => array(
                 'action' => 'message',
                 'is_read'    => $is_read,
                 'page'   => $page,
                 'type' => $type,
                 'keyword' => $keyword,
                 'where' => array(
                     'start_time'=>$start_time,
                     'end_time'=>$end_time,
                 ),
             ),
            'is_read' => $is_read,
            'type' => $type,
            'keyword'=>$keyword,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
        ));
        $view->setTemplate('admin/service/message');
        return $this->setMenu($view,1);
    }
    
    public function replyAction()
    {
        $this->checkLogin('message_reply');
        $id = $this->params()->fromRoute('id');
        if(!empty($_GET))
        {
            $data = $_GET;
            unset($data['submit']);
            $now = $this->getTime();
            $data['timestamp'] = $now;
            $data['admin_id'] = $_SESSION['admin_id'];
            if(!$data['content'])
            {
                echo '<script>alert("请输入回复内容");history.back();</script>';
                die;
            }
            
            $res = $this->getMessageTable()->insertData($data);
            if($res)
            {
                $this->getMessageTable()->updateData(array('is_read'=>'2','timestamp_update'=>$now), array('id'=>$id));
                $this->redirect()->toRoute('admin-service',array('action'=>'message'));
            }
        }
        $reply_info = '';
        $admin_info = '';
        if($id)
        {
            $message = $this->getViewMessageTable()->getOne(array('delete'=>'0','id'=>$id));
//             $this->dump($message);exit;
            if($message->is_read == '2')//已回复
            {
                $reply_info = $this->getMessageTable()->getOne(array('delete'=>'0','parent_id'=>$id));
                $admin_info = $this->getAdminTable()->getOne(array('delete'=>'0','id'=>$reply_info->admin_id));
            }
        }
        
        $view=new ViewModel(array(
            'id' => $id,
            'message'=>$message,
            'admin_info'=>$admin_info,
            'reply_info' => $reply_info,
            'messageType'=>$this->messageType(),
            'message_is_read'=>$this->messageIsRead(),
        ));
        $view->setTemplate('admin/service/reply');
        return $this->setMenu($view,1);
    }
    
    public function marketFeedbackAction()
    {
        $this->checkLogin('market_feedback'); 
        $status  = $this->params()->fromRoute('status' , 0);
        $status = isset($_GET['status']) ? trim($_GET['status']) : $status;
        $page = $this->params()->fromRoute('page');
        
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $keyword;
        $start_time = $this->params()->fromRoute('start_time' , '');
        $end_time  = $this->params()->fromRoute('end_time' , '');
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $start_time;
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $start_time;
        
        $where = new Where();
        $where->equalTo('delete', '0');
        if($status)
        {
            $where->equalTo('status',$status);
        }
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where->lessThanOrEqualTo('timestamp', $end_time);
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
        }
        $marketFeedback_list = $this->getViewFeedbackTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
//         $this->dump($marketFeedback_list['list']);exit;
        $view=new ViewModel(array(
            'marketFeedback' => $marketFeedback_list['list'], 
            'paginator' => $marketFeedback_list['paginator'],
            'marketFeedbackStatus' => $this->marketFeedbackStatus(),
            'condition' => array(
                 'action' => 'marketFeedback',
                 'page'   => $page,
                 'keyword' => $keyword,
                 'status' => $status,
                 'where' => array(
                     'start_time'=>$start_time,
                     'end_time'=>$end_time,
                 ),
             ),
            'status' => $status,
            'keyword'=>$keyword,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
        ));
        $view->setTemplate('admin/service/market_feedback');
        return $this->setMenu($view,1);
    }
    
    public function marketFeedbackAddAction()
    {
        $this->checkLogin('market_feedback_add');
        $data = array();
        if(!empty($_POST)){
            if($_POST['submit'])
            {
                $data['user_id'] = $_POST['user_id'];
                $data['content'] = $_POST['content'];
            }
            if(!$data['user_id'])
            {
                $this->showMessage("没有选择供应商");
            }
            if(!$data['content'])
            {
                $this->showMessage("内容不能为空");
            }
            if($_FILES['file']['name']&&$_FILES['file']['error'] == 0)
            {
                if($_FILES['file']['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $_FILES['file']['type'] == 'application/vnd.ms-excel')
                {
                    $res = $this->Uploadfile(LOCAL_SAVEPATH, false, 4, 2048,$_FILES);
                }
                else 
                {
                    echo "<script>alert('请上传2M内的excel文档');history.back();</script>";die;
                }
            }
//             else 
//             {
//                 $this->showMessage("文件上传失败");
//             }
            if(isset($res))
            {
                $data['file'] = $res;
            }
            $data['timestamp'] = $this->getTime();
            $this->getFeedbackTable()->insertData($data);
            
            return $this->redirect()->toRoute('admin-service',array('action'=>'marketFeedback'));
        }
        $view=new ViewModel(array(
            
        ));
        $view->setTemplate('admin/service/market_feedback_add');
        return $this->setMenu($view,1);
    }
    
    public function feedbackAction()
    {
        $this->checkLogin('user_feedback');
        
        $page = $this->params()->fromRoute('page');
        
        $keyword = $this->params()->fromRoute('keyword') ? trim($this->params()->fromRoute('keyword')) : '';
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : $keyword;
        $start_time = $this->params()->fromRoute('start_time' , '');
        $end_time  = $this->params()->fromRoute('end_time' , '');
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $start_time;
        $end_time  = isset($_GET['end_time']) ? $_GET['end_time'] : $start_time;
        
        $where = new Where();
        $where->equalTo('delete', '0');
        $where->equalTo('message_type',3);
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($end_time)
            {
                if($start_time && $start_time > $end_time)
                {
                    $end_time = $start_time;
                }
                $where->lessThanOrEqualTo('timestamp', $end_time);
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
        }
        $message_list = $this->getViewMessageTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10,$like);
//         $this->dump($message_list['list']);exit;
        $view=new ViewModel(array(
            'message' => $message_list['list'], 
            'paginator' => $message_list['paginator'],
            'enterprisType' => $this->enterprisType(),
            'condition' => array(
                 'action' => 'feedback',
                 'page'   => $page,
                 'keyword' => $keyword,
                 'where' => array(
                     'start_time'=>$start_time,
                     'end_time'=>$end_time,
                 ),
             ),
            'keyword'=>$keyword,
            'start_time'=>$start_time,
            'end_time'=>$end_time,
        ));
        $view->setTemplate('admin/service/feedback');
        return $this->setMenu($view,1);
    }
    
    public function Uploadfile($path = LOCAL_SAVEPATH, $is_thumb = true, $filetype = 1, $size = 2048, $source_file = array())
    {
        set_time_limit(0);
        $upload = new UploadfileApi($path, $size, $filetype, 'Ym/d');
        if ($source_file)
        {
            $upload->setFiles($source_file);
        }
        $upload->uploadfile();
        $filename = $upload->getUploadFileInfo();
        $name = substr($filename['file']['new_name'], strrpos($filename['file']['new_name'], '/') + 1);
        $path = $upload->imgPath.$name;
        return $path;
    }
}
