<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\Express;

class OrderController extends CommonController
{

    
	
	public function indexAction(){
		
		$this->checkLogin('order');
		//查询条件
		$status = $this->params()->fromRoute('cid');
		$_SESSION['status']=$status;
        if ($status) {
            $this->screening = 'status';
        }
        $this->table = $this->getViewOrderTable();
        $this->delete = false;
        $this->template = array(
            'order/index',
            'order'
        );
        $this->seach = array(
            'user_name',
            'order_sn'
        );
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '订单'
            ),
            array(
                'url' => '',
                'title' => '订单列表'
            )
        );
        return $this->getList();
		 
	}
	
	public function detailsAction()
	{
		
		$id=$this->params()->fromRoute('id');
		$other=$this->params()->fromRoute('other');
		$cid=$this->params()->fromRoute('cid');
		$order=$this->getViewOrderTable()->getOne(array('id'=>$id));
		$address=$this->getUserAddressTable()->getOne(array('user_id'=>$order['user_id']));//收货地址
		$code=explode(',',$order['code_id']);
		$where=new where();
		$where->in('id',$code);
		$invitation=$this->getInvitationCodeTable()->getAll($where);
		$order_array=array();
		foreach($invitation['list'] as $v){
			$order_array[]=$v['code'];
		}
		$order['codes']=$order_array;
		//退款处理
		if($_POST)
		{
			$set['reason']=$_POST['reason'];
			$set['status']=4;
			$set['application_time']=$this->getTime();
			$data['id']=$_POST['id'];
			$return=$this->getOrderTable()->updateData($set,$data);
			if($return){
				$this->redirect()->toRoute('admin-order',array('action'=>'details','id'=>$_POST['id']));
			}else{
				$this->showMessage('退款操作失败，请重新操作');
			}
		}
		
		//已收货(更改状态)
		if($other && $other==6)
		{
			$return=$this->getOrderTable()->updateData(array('status'=>$other,'delivery_time'=>$this->getTime()),array('id'=>$id));
			if($return)
			{
				$this->showMessage('收货成功');
			}
		}
		
		//已完成退款
		if($cid && $cid==5){
			$return=$this->getOrderTable()->updateData(array('status'=>5,'refund_time'=>$this->getTime()),array('id'=>$id));
			if($return)
			{
				$this->showMessage('退款完成');
				$this->redirect()->toRoute('admin-order',array('action'=>'details','id'=>$id));
			}
		}
		$this->breadcrumb = array(
            array(
                'url' =>$this->plugin('url')->fromRoute('admin-order', array('action' => 'index')),
                'title' => '列表'
            ),
            array(
                'url' => '',
                'title' => '查看详情'
            )
        );
		
		$view=new ViewModel(array(
			'order'=>$order,
			'address'=>$address,
		));
		$view->setTemplate('admin/order/details');
		return $this->setMenu($view, 'order');
	}
	
	 public function updateAction()
	{
		
		//发货处理
		if($_POST)
		{
			$data['shipping_code']=$_POST['shipping_code'];
			$data['shipping_company']=$_POST['shipping_company'];
			$data['status']=3;
			$data['shipping_time']=$this->getTime();
			$where['id']=$_POST['order_id'];
			$return=$this->getOrderTable()->updateData($data,$where);
			if($return)
			{
				$this->redirect()->toRoute('admin-order',array('action'=>'details','id'=>$_POST['order_id']));
			}
		}
		
	}
	
	//编辑运单信息
	public function modifyOrderAction()
	{
		if($_POST)
		{
			$set['shipping_company']=$_POST['company'];
			$set['shipping_code']=$_POST['shipping_code'];
			$set['shipping_time']=$this->getTime();
			$data['id']=$_POST['id'];
			$return=$this->getOrderTable()->updateData($set,$data);
			if($return)
			{
				$this->redirect()->toRoute('admin-order',array('action'=>'details','id'=>$_POST['id']));
			}
			
		}
		
	}
	
}