<?php 
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Api3\Controller\Order;
use Api3\Controller\CommonController as api;
use Zend\View\View;
use Api3\Controller\SMSCode;
use Core\System\WxApi\WxApi;
use Core\System\WxPayApi\AiiWxPay;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Validator\InArray;
use Core\System\WxApi\WxJsApi;
use Admin\Model\UserAddressTable;
use Admin\Model\orderTable;
use Api\Controller\User;
use Api\Controller\CardDetails;
use Index\Controller\WeixinController;



class CommodityController extends CommonController
{
	/**
     * 购买
	 *
     */
	public function buyAction()
	{
		//获取微信OPENID
		/*$tools = new WxJsApi();
		$openId = $tools->GetOpenid();*/
		/*if($openId)
		{
			setcookie('openId',$openId,time()+3600,ROOT_PATH);
		}*/
		$view = new ViewModel(array(
			/*'openId'=>$openId*/

		));
		
        $view->setTemplate('index/commodity/buy');
		return $this->setMenu($view, 2);
	}
	
	/**
     * 登录
	 *
     */
	public function wxloginAction()
	{
		
		
		$view = new ViewModel(array(
		
		));
        $view->setTemplate('index/commodity/wxlogin');
		return $this->setMenu($view, 2);
	}
	
	
	/**
     * 下单信息
	 *
     */
	public function indentAction()
	{ 
		/*$this->wxlogin();*/
		/*$user_id=$this->params()->fromRoute('id');*/
		
		/* $user_id = 163; */
		
		/*$codes=$this->getCodeUseTable()->getAll(array('user_id'=>$user_id,'delete'=>0));
		$code_arr=array();
		foreach($codes['list'] as $v){
			$code_arr[]=$v['code_id'];
		}
		if($code_arr)
		{
			$code_str=implode(',',$code_arr);
		}
		//从主名片提取
        $info = $this->getUserAddressTable()->getOne(array('user_id'=>$user_id));
      if (! $info) {
			//第一次获取地址信息，自动名片信息加到地址信息上
            $user_info = $this->getUserTable()->getOne(array('id' => $user_id));
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
                'user_id' => $user_id,
                'timestamp' => $this->getTime(),
            );
            
            $info['id'] = $this->getUserAddressTable()->insertData($info);
        }*/
		if($_POST){

		}
		$view = new ViewModel(array(
			/*'carte'=>$info,
			'user_id'=>$user_id,
			'code'=>$codes,
			'code_str'=>@$code_str ? $code_str : '',*/
		));
        $view->setTemplate('index/commodity/indent');
       return $this->setMenu($view, 2);
	}

	/**
     * 订单提交
	 *
     */
	public function orderSubmitAction()
	{
		if($_POST)
		{
			$amount=30;
			$region_info = $this->encode($_POST['province_id'],$_POST['city_id'],$_POST['county']);
			$region_id = $_POST['county'];
			$address = $this->getProvinceCityCountryName($region_info);
			$street = $_POST['street'];
			$name = $_POST['name'];
			$mobile = $_POST['mobile'];
			$code_name = $_POST['code'];
			$code = '';
			if(trim($code_name)){
				$codes = $this->getInvitationCodeTable()->getOne(array('code'=>$code_name,'status'=>0));
				if($codes){
					$code = $codes['id'];
				}else{
					$this->showTu('推荐码错误');
				}
			}
			$array = array(
				'name' => $name,
				'street' => $street,
				'address' => $address,
				'telephone' => $mobile,
				'region_id' => $region_id,
				'region_info' => $region_info,
				'timestamp' => $this->getTime(),
			);
			$add_id = $this->getUserAddressTable()->insertData($array);
			if($_POST['invoice_status']==0)
			{
				$inf['invoice_status']=2;
				$inf['invoice_type']='';
				$inf['invoice_name']='';
			}
			else
			{
				$inf['invoice_status']=$_POST['invoice_status'];
				$inf['invoice_type']=$_POST['invoice_type'];
				$inf['invoice_name']=$_POST['invoice_name'];
			}
			$inf['user_id']='';
			$inf['order_sn']=$this->makeSN();
			$inf['code_id']=$code;
			$inf['price']=$_POST['price'];
			$inf['number']=$_POST['number'];
			$total=$_POST['price']*$_POST['number']-$amount*$_POST['code_number'];
			$inf['total']=$total;
			$inf['status']=1;
			$inf['payment']=3;
			$inf['timestamp']=$this->getTime();
			$inf['address_id']=$add_id;
			$inf['address_name']=$name;
			$inf['address_telephone']=$mobile;
			$inf['address_street']=$street;
			$inf['address_region_info']=$region_info;

			$id=$this->getOrderTable()->insertData($inf);
			if($id){
				return $this->redirect()->toRoute('index',array('controller'=>'commodity','action'=>'orderDetails','id'=>$id));die;
			}else{
				$this->showTu('下单失败');die;
			}
			//此处循环改变优惠码状态
			/*$where=new where();
			$where->in('id',$code_arr);
			$this->getInvitationCodeTable()->updateData(array('status'=>1),$where);*/
			//删除临时表数据
			/*$back=$this->getCodeUseTable()->delete(array('user_id'=>$_POST['user_id']));

			$this->showNext(ROOT_PATH."wxpay/actions/buy.php?openId={$_COOKIE['openId']}&&order_sn={$inf['order_sn']}");
			exit;*/
		}
	}
	
	
	
	
	/**
     * 收货地址修改
	 *
     */
	public function siteAction()
	{

		$user_id=$this->params()->fromRoute('id');
        $info = $this->getUserAddressTable()->getOne(array('user_id'=>$user_id));
		if($_POST)
		{	
			$region_id=$this->getRegionTable()->getOne(array('id'=>$_POST['county']));
			$region = $this->getApiController()->getRegionInfoArray($region_id->id);
			$address = $this->getApiController()->regionInfoToString($region['region_info']).$_POST['street'];
			$set=array(
				'name'=>$_POST['address_name'],
				'telephone'=>$_POST['address_telephone'],
				'region_info'=>$region['region_info'],
				'region_id'=>$_POST['county'],
				'street'=>$_POST['street'],
				'address'=>$address,
				'timestamp_update'=>$this->getTime(),
			);
			$return=$this->getUserAddressTable()->updateData($set,array(' user_id'=>$_POST['user_id']));
			if($return)
			{
				$this->redirect()->toRoute('index',array('controller'=>'commodity','action'=>'indent','id'=>$_POST['user_id']));
			}
			
		}
		
		$view = new ViewModel(array(
			'carte'=>$info,
			'user'=>$user_id,
		));
        $view->setTemplate('index/commodity/site');
        return $this->setMenu($view, 2);
	}
	
	/**
     * 删除推荐码
	 *
     */
	public function deleteAction()
	{
		$cid=$this->params()->fromRoute('id');
		$userId=$this->params()->fromRoute('alert');
		if($cid && $userId)
		{
			$this->getCodeUseTable()->deleteData($cid);
			$this->redirect()->toRoute('index',array('controller'=>'commodity','action'=>'indent','id'=>$userId));
			
		}
	}
	
	/**
     * 查看推荐码
	 *
     */
	public function ReferralCodeAction()
	{
		$num=isset($_GET['num']) ? $_GET['num'] : '';
		$user_id=$this->params()->fromRoute('id');
		$code_info=$this->getCodeUseTable()->getAll(array('user_id'=>$user_id,'delete'=>0));
		$view = new ViewModel(array(
			'code'=>$code_info,
			'user_id'=>$user_id,
			'num'=>$num,
		));
        $view->setTemplate('index/commodity/ReferralCode');
		return $this->setMenu($view, 2);
	}
	
	/**
     * 查看推荐码
	 *
     */
	public function couponAction()
	{
		$user_id=$this->params()->fromRoute('id');
		//必须取到user_id;
		if($_POST)
		{
			$user_id=$_POST['user_id'];
			$code= trim($_POST['recommend_code']);
			$code= $this->getInvitationCodeTable()->getOne(array('code'=>$code,'status'=>0));
			//print_r($code);die();
			if($code)
			{ 	$checkCode=$this->getCodeUseTable()->getOne(array('code'=>trim($code['code']),'delete'=>0));
				if($checkCode)
				{
					$this->showMessage('请不要输入重复的优惠码');
				}
				else
				{
					$return=$this->getCodeUseTable()->insertData(array('user_id'=>$user_id,'code_id'=>$code['id'],'code'=>$code['code']));
					if($return)
					{
						$this->redirect()->toRoute('index',array('controller'=>'commodity','action'=>'ReferralCode','id'=>$user_id));
					}
				}
				
			}
			else
			{
				$this->showMessage('请输入有效的优惠码');
			}
			
		}
		$view = new ViewModel(array(
			'user_id'=>$user_id,
		));
        $view->setTemplate('index/commodity/coupon');
		return $this->setMenu($view, 2);
	}

	/*
	 *订单详情
	 *
	 * */
	public function orderDetailsAction(){
		$id = $this->params()->fromRoute('id');
		$order_info = $this->getOrderTable()->getOne(array('id' => $id));
		$address = $this->getProvinceCityCountryName($order_info['address_region_info']);
		$code_arr = array();
		if($order_info['code_id']){
			$codes = explode('',$order_info['code_id']);
			$where = new where();
			$where->in('id',$codes);
			$info = $this->getInvitationCodeTable()->getAll($where);
			foreach($info['list'] as $v){
				$code_arr[] = $v['name'];
			}
		}
		$view = new ViewModel(array(
			'order_info' => $order_info,
			'address' => isset($address) ? $address :'',
			'code_arr' => $code_arr,
			'order_id' => $id
		));
		$view->setTemplate('index/commodity/orderdetails');
		return $this->setMenu($view, 2);
	}


	/**
	 * api用结束
	 *
	 * @param unknown $msg
	 * @version 2015-8-11 WZ
	 */
	public function apiExit($msg) {
		if (is_array($msg)) {
			echo json_encode($msg);
		}
		else {
			echo $msg;
		}
		exit;
	}


	/**
	 * 短信验证码
	 *
	 */
	public function smscodeAction() {
		$action = isset($_POST['action']) ? (int) $_POST['action'] : "";
		$mobile = isset($_POST['mobile']) ? trim ($_POST['mobile']) : "";
		$code = isset($_POST['code']) ? trim($_POST['code']) : "";
		if ($action == 1) {
			$user_info = $this->getUserTable()->getOne(array('mobile' => $mobile));
			$type = 2;
			if (! $user_info) {
				$type = 1;
			}
		}
		if($action==2)
		{
			$userINfo=$this->getUserTable()->getOne(array('mobile' => $mobile));
			if($userINfo)
			{
				$type=2;
			}else{
				$type=1;
			}
		}

		$msg = array(
			'status' => 0,
			'msg' => '成功',
		);
		if (! $action || ! $mobile ) {
			$msg = array(
				'status' => 1,
				'msg' => '请求参数不完整1'
			);
			$this->apiExit($msg);
		}
		if ($action == 2 && ! $code) {
			$msg = array(
				'status' => 2,
				'msg' => '请求参数不完整2' . var_export($_POST['code'], true)
			);
			$this->apiExit($msg);
		}

		$json = array(
			's' => '',
			'q' => array(
				'a' => $action,
				'type' => $type,
				'mobile' => $mobile,
				'w' => array(
					'code' => $code
				)
			)
		);
		$_REQUEST['json'] = json_encode($json);
		$api = new SMSCode();
		$api->index();
		$result = $api->response(null, true);
		//echo $result;
		$result = json_decode($result, true);

		$msg = array(
			'status' => $result['q']['s'],
			'msg' => $result['q']['d']
		);
		if ($result['q']['s'] == 0 && $action == 2) {
			$user_info=$this->getUserTable()->getOne(array('id'=>$result['q']['id']));
			if($user_info['page_id']==0){
				$carte_id=$this->getCarteTable()->insertData(array('mobile'=>$mobile));
				$page_id=$this->getPageTable()->insertData(array('carte_id'=>$carte_id,'user_id'=>$result['q']['id'],'timestamp'=>$this->getTime()));
				$this->getUserTable()->updateData(array('page_id'=>$page_id),array('id'=>$result['q']['id']));
				setcookie('wx_page_id',$page_id,time()+3600*24*30,ROOT_PATH);
				$msg['page']=$page_id;
			}else{
				setcookie('wx_page_id',$user_info['page_id'],time()+3600*24*30,ROOT_PATH);
				$msg['page']=$user_info['page_id'];
			}
			setcookie('wx_user_id',$result['q']['id'],time()+3600*24*30,ROOT_PATH);
			setcookie('wx_mobile',$mobile,time()+3600*24*30,ROOT_PATH);

		}
		$msg['type'] = $type;
		$this->apiExit($msg);
	}


	/**
	 * 支付回调，改变订单状态，插入财务记录
	 *
	 * @version 2015-8-11 WZ
	 */
	function orderStatusAction() {
		$order_sn = isset($_REQUEST['order_sn']) ? trim($_REQUEST['order_sn']) : "";
		if (! $order_sn) {
			exit;
		}

		$this->orderUpdateStatus($order_sn);
		exit;
	}

	/**
	 * 订单数量统计
	 *
	 * @version 2015-8-10 WZ
	 */
	public function orderStatAction() {
		$where = new Where();
		$where->greaterThan('status', 1);
		echo $this->getOrderTable()->countData($where);
		exit;
	}

	/**
	 * 订单信息
	 *
	 * @version 2015-8-10 WZ
	 */
	public function orderInfoAction()
	{
		$order_sn=isset($_POST['order_sn'])?$_POST['order_sn']:'';
		$result=array();
		$result['status']=0;
		if($order_sn)
		{
			$orderinfo=$this->getOrderTable()->getOne(array('order_sn'=>$order_sn));
			if($orderinfo)
			{
				/*$code=explode(',',$orderinfo['code_id']);
				$code_num=count($code);*/
				$result['order_sn']=$orderinfo['order_sn'];
				$result['total']=$orderinfo['total'];
				$result['status']=2;
			}else{
				$result['status']=1;
			}
		}
		$this->apiExit($result);
	}
	public function apiExit1($msg) {
		if (is_array($msg)) {
			echo json_encode($msg,true);
		}
		else {
			echo $msg;
		}
		exit;
	}
	/*
	 * 微信支付请求订单数据
	 *
	 * */
	public function getOrderInfoAction(){
		$id = $_POST['id'];
		$tools = new WxJsApi();
		$openId = $tools->GetOpenid();
		$order = $this->getOrderTable()->getOne(array('id' =>$id));
		$code_num = 0;
		if($order['code_id']){
			$code_num = count(explode(',',$order['code_id']));
		}
		$result = array(
			'status' => 0, // 正常
			'price' => 198, // 正常价
			'order_sn' => $order['id'], // 订单号
			'toatl' => $order['total'], //优惠码数量
		);
		$this->apiExit1($result);
	}
	
}
?>