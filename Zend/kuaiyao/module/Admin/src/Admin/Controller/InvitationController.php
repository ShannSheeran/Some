<?php

namespace Admin\Controller;


use Api\Controller\SMSCode;
class InvitationController extends CommonController
{
	
	/**
	 * 邀请码列表
	 *
	 * @return multitype:
	 * @version 2014-12-29 liujun
	 */
	public function indexAction()
	{
        $this->checkLogin('user_index');
        $this->table = $this->getViewInvitationCodeTable();
        $this->seach = array('mobile','id','name');
        $this->template = array('invitation/index','invitation');
		$this->screening = 'status';
		$this->delete = false;
        $this->breadcrumb = array (
            array (
                'url' => '#',
                'title' => '邀请码'
            ),
            array (
                'url' =>'',
                'title' => '邀请码列表'
            ),
           
        );
      return  $this->getList();

	}

	/**
	 * 更新推荐码状态
	 * 
	 * @version 2015-6-25
	 * @author liujun
	 */
	public function statusAction()
	{
		$this->checkLogin();
		$id = $this->params('id');
		if($id)
		{
			$this->getInvitationCodeTable()->update(array('status'=>1,'timestamp_update'=>$this->getTime()),array('id'=>$id));
			return $this->showMessage('操作成功!');
		}else{
			return $this->showMessage('操作失败!');
		}
		return $this->showMessage('操作失败!');
	}
	
	/**
	 * 添加推荐码
	 * @version 2015年7月3日 
	 * @author liujun
	 */
	public function addAction()
	{

	    $this->checkLogin();
	    $number = 1;
	    if(isset($_POST['mobile']) && $_POST['mobile'])
	    {
// 	        echo 3;
// 	        die();
	        $number = $_POST['number'] ? (int) $_POST['number'] : $number;
	        $mobile= $_POST['mobile'];
	        if($mobile)
	        {
	            $user = $this->getUserTable()->getOne(array('mobile'=>$mobile,'delete'=>0));
	            if($user)
	            {
	                for($i=1;$i<=$number;$i++)
	                {
	                    $code = $this->getApiController()->makeCode(6, 6);//生成6位随机字母加数字为推荐码
	                    $set = array('code'=>$code,'user_id'=>$user->id,'timestamp'=>$this->getTime());
	                    $this->getInvitationCodeTable()->insert($set);
	                }
	                
	                $smscode = new SMSCode();
	                $content = TEMPLATE_SMS_BUY;
	                $content =sprintf(TEMPLATE_SMS_BUY, $number);
	                $smscode->smsPush($content, array($_POST['mobile']));
	                
	                echo 1;
	                die();
	            }
	        }
	        
	    }
	    echo 2;
	    die();
	}
	
	/**
	 * Ajax 获取设备信息
	 * 
	 * @version 2015年7月3日 
	 * @author liujun
	 */
	public function getDeviceAction()
	{
	    $this->checkLogin();
	    $id = isset($_POST['id']) ? $_POST['id'] : '';
	    if($id)
	    {
	        $device = $this->getDeviceTable()->getOne(array('device_id'=>$id,'delete'=>0));
	        if(!$device)
	        {
	            echo '*无此设备信息';
	            die();
	        }
	    }
	    die();
	}
	
	/**
	 * Ajax 获取设备信息
	 *
	 * @version 2015年7月3日
	 * @author liujun
	 */
	public function getUserAction()
	{
	    $this->checkLogin();
	    $mobile= isset($_POST['mobile']) ? $_POST['mobile'] : '';
	    if($mobile)
	    {
	        $user = $this->getUserTable()->getOne(array('mobile'=>$mobile,'delete'=>0));
	        if(!$user)
	        {
	            echo '*无此用户';
	            die();
	        }
	        else
	        {
	            echo "*用户存在";
	        }
	    }
	    die();
	}
	
	/**
	 * Ajax 补发推荐码短信
	 *
	 * @version 2015年10月8日
	 * @author HY
	 */
	
	public function messageAction()
	{
	    $this->checkLogin();
	    $mobile= isset($_POST['mobile']) ? $_POST['mobile'] : '';
	    if($mobile){
	        
	        $smscode = new SMSCode();
	        $content = TEMPLATE_SMS_BUY;
	        $smscode->smsPush($content, array($_POST['mobile']));
	        
	        echo 1;
	        die();
	    }
	    
	    echo 0;
	    die();
	}
}