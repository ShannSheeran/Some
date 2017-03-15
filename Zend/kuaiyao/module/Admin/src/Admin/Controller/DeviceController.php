<?php

namespace Admin\Controller;

use Core\System\WxApi\WxApi;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\UploadfileApi;

class DeviceController extends CommonController
{
	
	/**
	 * 设备列表
	 *
	 * @return multitype:
	 * @version 2014-12-29 liujun
	 */
	public function indexAction()
	{
		$this->checkLogin ();
		$status = $this->params('cid',0);
		$page = $this->params('page',1);
		$keyword = $this->params('keyword','');
		$where = new Where();
		if($status==1)
		{
		  $where->equalTo('status', 0);
		}
		elseif($status==2)
		{
		    $where->equalTo('status', 1)->or->equalTo('status', 2);
		}

		$like = null;
		$this->seach = array('id','minor','major');
		if ((isset($_POST['submit']) && $_POST['keyword'] != '') || $keyword)
		{
		    $keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : $keyword;
		    if ($keyword && is_array($this->seach))
		    {
		        foreach ( $this->seach as $v )
		        {
		            $like[$v] = $keyword;
		        }
		    }
		}

		$list = $this->getDeviceTable ()->getAll($where,null,array('id'=>'desc'),true,$page,PAGE_NUMBER,$like);
		
		$username = array();
        foreach($list['list'] as $val){
            if($val['user_id'])
            {
                $user_id_array[] =  $val['user_id'];
              
            }
        }
        if($user_id_array)
        {
            $where =new Where();
            $where->in('id',$user_id_array);
            $username = array();
            $user  = $this->getUserTable()->fetchAll($where);
            foreach ($user as $v)
            {
                $username[$v->id]= $v->name;
            }
        }
		$this->breadcrumb = array (
				array (
						'url' => '#',
						'title' => '设备' 
				),
				array (
						'url' => '',
						'title' => '设备列表' 
				) 
		);

		$date_two = array(
		    'paginator' => $list['paginator'],
		    'list' => $list['list'],
		    'condition' => array(
		        'cid' => $status,
		        'page' => $page,
		        'where' => $where,
		    ) // 提交的where参数用get参数传递
		    ,
		    'where' => $where,
		    'cid' => $status,
		    'page' => $page,
		    'keyword' => $keyword,
		    'username' => $username
		);
		if($keyword)
		{
		    $date_two['condition']['keyword'] = $keyword;
		}
		$view = new ViewModel ($date_two);
		$view->setTemplate ( 'admin/device/index' );
		return $this->setMenu ( $view, 'device' );
	}
	
	/**
	 * 申请设备
	 *
	 * @version 2015-1-20 liujun
	 */
	public function pleaseDeviceAction()
	{
		$this->checkLogin ( 'user_index' );
		
		if (isset ( $_POST ['submit'] ) && $_POST ['submit'])
		{
			$quantity = isset ( $_POST ['quantity'] ) ? ( int ) $_POST ['quantity'] : 0;
			
			if ($quantity <= 0 || $quantity > 499)
			{
				$this->showMessage ( '单次新增设备数应大于0个，小于500个!' );
			}
			
			$apply_reason = isset ( $_POST ['apply_reason'] ) ? $_POST ['apply_reason'] : '';
			
			if (! $apply_reason)
			{
				$this->showMessage ( '申请理由不能为空!' );
			}
			
			if (strlen ( $apply_reason ) > 500)
			{
				$this->showMessage ( '申请理由不能超过500字!' );
			}
			
			$comment = isset ( $_POST ['comment'] ) ? $_POST ['comment'] : '';
			
			if ($comment)
			{
				if (strlen ( $comment ) > 30)
				{
					$this->showMessage ( '备注，不超过15个汉字或30个英文字母 ' );
				}
			}
			
			//$poi_id = isset ( $_POST ['poi_id'] ) ? ( int ) $_POST ['poi_id'] : '';
			
			$wxApi = $this->getWxApi ();
			
			$result = $wxApi->wxDeviceApply ( json_encode ( array (
					'quantity' => $quantity,
					'apply_reason' => $apply_reason,
					'comment' => $comment,
					//'poi_id' => $poi_id
			) ) );

			if ($result ['errcode'] == 0)
			{
				$setup_info = $this->getSetupTable()->getOne(array('id'=>1));
				foreach ( $result ['data'] ['device_identifiers'] as $v )
				{
					$set = array (
							'comment' => $comment,
							'device_id' => $v ['device_id'],
							'uuid' => $v ['uuid'],
							'major' => $v ['major'],
							'minor' => $v ['minor'],
							'audit_status' => 1,
							'apply_id' => $result ['data'] ['apply_id'],
							'timestamp' => $this->getTime()
					);
					
					$id = $this->getDeviceTable ()->insertData( $set );
					/*$setup_info = $this->getSetupTable()->getOne(array('id'=>1));
					 if($id && $setup_info->value>0)
					{
						for ($i=0;$i<$setup_info->value;$i++)
						{
							$code = $this->getApiController()->makeCode(6, 6);//生成6位随机字母加数字为推荐码
							$this->getInvitationCodeTable()->insert(array('device_id'=>$id,'timestamp'=>$this->getTime(),'code'=>$code));//生成推荐码
						}
					} */ //liujun 已改成添加用户生成推荐码
				}
			}
			else
			{
			     $this->showMessage('设备申请失败！错误代码'.$result ['errcode']);    
			}
			
			return $this->redirect ()->toRoute ( 'admin-device', array (
					'action' => 'index',
					'cid' => 0
			) );
		}
		$this->breadcrumb = array (
				array (
						'url' => '#',
						'title' => '设备'
				),
				array (
						'url' => $this->plugin ( 'url' )->fromRoute ( 'admin-device', array (
								'action' => 'index'
						) ),
						'title' => '设备列表'
				),
				array (
						'url' => '',
						'title' => '申请设备'
				)
		);
		$view = new ViewModel ();
		$view->setTemplate ( 'admin/device/details' );
		return $this->setMenu ( $view, 'device' );
	}
	
	/**
	 * 设备详情
	 */
	public function deviceDetailsAction()
	{
		$this->checkLogin ( 'user_index' );
		$id = ( int ) $this->params ()->fromRoute ( 'id' );
        if(!$id)
        {
            $this->showMessage('请求参数错误！');
        }
		$id = isset ( $_POST ['id'] ) ? $_POST ['id'] : $id;

		if (isset ( $_POST ['submit'] ) && $_POST ['submit'])
		{
			$device = array (
                'device_id'=>(int)trim($_POST['device_id']),
                'uuid'=>trim($_POST['uuid']),
                'major'=>trim($_POST['major']),
                'minor'=>trim($_POST['minor']),
            );
            $jsonData = json_encode(array(
                'device_identifier'=>$device,
                'comment'=>trim($_POST['comment']),
            ));

            $wxApi = new WxApi();
            $res = $wxApi->wxDeviceUpdate($jsonData);
            if($res['errcode'] == 0)
            {
                $device['comment'] = trim($_POST['comment']);
                $this->getDeviceTable()->update($device,array('id'=>$id));
                return $this->redirect()->toRoute ('admin-device');
            }
            else{
                $this->showMessage('请求参数错误！');
            }

		}
		$info = '';
        $page_info = array();
	
		if ($id)
		{
			$info = $this->getDeviceTable()->getOne(array('id'=>$id));
            if($info->page_ids)
            {
                $page_ids = explode(",",$info->page_ids);
                $page_info = $this->getPageTable()->fetchAll(array('page_id'=>$page_ids));
            }
		}

		$view = new ViewModel ( array (
				'info' => $info,
				'page_info' => $page_info,
		) );
		$this->breadcrumb = array (
				array (
						'url' => '#',
						'title' => '设备' 
				),
				array (
						'url' => $this->plugin ( 'url' )->fromRoute ( 'admin-device', array (
								'action' => 'index' 
						) ),
						'title' => '设备列表' 
				),
				array (
						'url' => '',
						'title' => '设备详细' 
				) 
		);

		$view->setTemplate ( 'admin/device/deviceDetails' );
		return $this->setMenu ( $view, 'device' );
	}

    /**
     * 绑定页面 && 解除绑定
     */
    public function deviceBindPageAction()
    {
        //查询页面ajax
        //echo 1;exit;
        if(isset($_POST['request']) && $request = $_POST['request'])
        {
            $where_page_id = new Where();
            $where_page_id->like('page_id','%'.$request['s'].'%');
            $where = new Where();
            $where->like('title','%'.$request['s'].'%');
            $where->orPredicate($where_page_id);
            $info = $this->getPageTable()->fetchAll($where);
            exit(json_encode($info));
        }

        if(isset($_POST['submit']))
        {

            $page_id = isset($_POST['page_id']) && strpos($_POST['page_id'],'#') !==false ? explode('#',$_POST['page_id']) : '';

            if($page_id != '' && isset($_POST['id']) && isset($_POST['bind']))
            {
                $page_id = $page_id[0];
                $id = $_POST['id'];
                $bind = $_POST['bind'];
            }
            else
            {
                $id = '';
                $bind = '';
                $this->showMessage('请求参数错误！');
            }
            $device_info = $this->getDeviceTable()->getOne(array('id'=>$id));
            $jsonData = json_encode(array(
                'device_identifier'=>array(
                    'device_id'=>(int)$device_info->device_id,
                    'uuid'=>$device_info->uuid,
                    'major'=>$device_info->major,
                    'minor'=>$device_info->minor,
                ),
                'page_ids'=>array((int)$page_id),
                'bind'=>(int)$bind,  //0解除 1关联
                'append'=>0     //0覆盖 1新增
            ));
//            var_dump($jsonData);
//            exit;
            $wxApi = new WxApi();
            $res = $wxApi->wxDeviceBindPage($jsonData);
            if($res['errcode'] == 0)
            {
                switch($bind)
                {
                    case 1:
                        $page_ids = $device_info->page_ids.','.$page_id;break;
                    case 0:
                        $page_ids = str_replace(','.$page_id,'',$device_info->page_ids);
                        if($page_ids == $device_info->page_ids)
                        {
                            $page_ids = str_replace($page_id,'',$device_info->page_ids);
                        }
                        break;
                    default:
                        $page_ids = $device_info->page_ids;break;
                }

                $this->getDeviceTable()->update(array('page_ids'=>$page_ids),array('id'=>$id));
                return $this->redirect()->toRoute('admin-device',array('action'=>'deviceDetails','id'=>$id));
            }
        }

    }
    
    /**
     * 设备表与微信端同布
     * @version 2015-9-8
     * @author HY
     */
    public function userDevice(){
        $this->checkLogin();
        $id = $this->params()->fromRoute ( 'id' );//用户user_id
        if(!$id)
        {
            $this->showMessage('请求参数错误！');
        }
        
        $list = $this->getPageTable()->fetchAll(array('user_id'=>$id));
        $carte_id = array();
        foreach($list as $val){
            $carte_id[] = $val['carte_id'];
        }
        $cartes_info = $this->getCarteTable()->fetchAll(array('id'=>$carte_id));
        
        $view = new ViewModel ( array (
            'cartes_info' => $cartes_info
        ) );

        $view->setTemplate ( 'admin/device/userDevice' );
        return $this->setMenu ( $view, '1' );
    }
    
	/**
	 * 设备表与微信端同布 
	 * @version 2015-6-2
	 * @author liujun
	 */
    
	public function updateDeviceListAction()
	{
		$this->checkLogin();
        set_time_limit(0);
		$this->interfaceType= 1;
		$this->updateList();
		$this->showMessage('数据与微信更新成功！');
	}
	
}