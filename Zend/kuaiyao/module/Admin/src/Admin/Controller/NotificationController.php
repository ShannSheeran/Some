<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\AiiPush\AiiPush;
use Api21\Controller\Item\PushTemplateItem;
use Core\System\ImgCache;
class NotificationController extends CommonController
{
    /**
     * 推送列表
     * !CodeTemplates.overridecomment.nonjd!
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->checkLogin('officialNotificationLook');
        $this->table = $this->getNotificationTable();
        $this->seach = array('title','content');
        $this->template = array('notification/index','notification'); 
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '官方'
        		),
        		array (
        				'url' => '',
        				'title' => '信息推送列表'
        		)
        );
        return $this->getList();
    }
    
    /**
     * 添加推送页
     * @return \Zend\View\Model\ViewModel
     * @version 2014-12-18 liujun
     */
    public function addAction()
    {
        $this->checkLogin('officialNotificationEdit');
        $this->breadcrumb = array (
        		array (
        				'url' => '#',
        				'title' => '官方'
        		),
        		array (
        				'url' => $this->plugin ( 'url' )->fromRoute ( 'admin-notification', array (
        						'action' => 'index')),
        				'title' => '信息推送列表'
        		
        		),
        		array (
        				'url' => '',
        				'title' =>"推送详细"
        		)
        );
        $view = new ViewModel();
        $view->setTemplate('admin/notification/add');
        return $this->setMenu($view, 'notification');
    }

    /**
     * 推送内容提交
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     * @version 2014-12-18 liujun
     */
    public function addnotificationAction()
    {
        $this->checkLogin('officialNotificationEdit');
        $title = addslashes($_POST['title']);
        $content = addslashes($_POST['content']);
        $device_type = $_POST['device_type'];
        $send_to = 0;
        if ($content)
        {
            $notification_id = $this->getNotificationTable()->insertData(array(
                'title' => $title,
                'content' => $content,
                'device_type' => $device_type,
                'send_to' => $send_to,
                'status' => 2,
                'admin_id' => $_SESSION['admin_id'],
                'admin_name' => $_SESSION['admin_name'],
                'timestamp' => date('Y-m-d H:i:s')
            ));
            return $this->redirect()->toRoute('admin-notification', array(
                'action' => 'sendMessage',
                'id' => $notification_id
            ));
        }
        else
        {
            echo '发送内容不能为空！';
            exit();
        }
    }
    
    // 发送推送，发送短信
    // //2014-03-29
    // 上线测试
    public function sendMessageAction()
    {
        $id = $this->params()->fromRoute('id');
		if(!PUSH_SWITCH)
		{
			return false;
		}
        
        if ($id)
        {
            $push = new AiiPush();
            
            $notification = $this->getNotificationTable()->getOne(array(
                'id' => $id,
                'status' => 2
            )); // 等待发送
            
            if (! $notification)
            {
                return $this->redirect()->toRoute('admin-notification');
            }
            
            if ($notification->send_to)
            {
//                 $this->sendMessageByUserType($notification);
            }
            else
            {
                $push->pushAllDevices($notification->content, $notification->title, array('from'=>'admin'), $notification->device_type);
            }
            
            $this->getNotificationTable()->update(array(
                'status' => 1
            ), array(
                'id' => $id
            )); // 已发送
            return $this->redirect()->toRoute('admin-notification');
            exit();
        }
        exit();
    }

    /**
     * 推送给特定用户群
     *
     * @param $notification 推送对象            
     *
     */
    private function sendMessageByUserType($notification)
    {
        $where = new Where();
        $where->equalTo('status', '1');
        $where->equalTo('delete', DELETE_FALSE);
        // $type 1注册用户；2快应人；3未提交认证用户；
        switch ($notification->send_to)
        {
            case 1:
                break;
            case 2:
                
                // 快应人
                $where->equalTo('auth_status', 2);
                break;
            case 3:
                $where->in('auth_status', array(
                    0,
                    3
                ));
                break;
            default:
                break;
        }
        
        $user_data = $this->getUserTable()->getData($where, array(
            'id'
        ));
        
        $ids = array();
        foreach ($user_data as $key => $value)
        {
            $ids[] = $value['id'];
        }
        
        $template = new PushTemplateItem();
        $template->content = $notification->content;
        $template->title = $notification->title;
        $this->getApiController()->pushForController($ids, 0, null, null, $template);
    }

    /*
     * 用户体验群
     * */
    public function experienceAction()
    {
        if($_POST){

            if(isset($_FILES) && $this->check_file_type($_FILES['up_logo']['tmp_name'])){
                $file = $this->getApiController()->uploadImageForController("up_logo");
                $data['image']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
            }else{
                $data['image'] = isset($_POST['up_logo'])?$_POST['up_logo']:'';
            }
            $data['timestampUpdate']=$_POST['time'];
            $setup=$this->getSetupTable()->getOne(array('id'=>1));
            if($setup){
                $datas = json_encode($data);
                $this->getSetupTable()->update(array('value'=>$datas,'timestamp'=>$this->getTime()),array('id'=>1));
            }else{
                $datas = json_encode($data);
                $this->getSetupTable()->insertData(array('value'=>$datas,'timestamp'=>$this->getTime()));
            }
        }
        $setup=$this->getSetupTable()->getOne(array('id'=>1));
        $data=json_decode($setup['value']);
        $array=(array)($data);
        $img=$this->getImageTable()->getOne(array('id'=>$array['image']));
        if($img){
            $path=$img['path'].$img['filename'];
        }
        $view = new ViewModel(array(
            'setup'=>$setup,
            'path'=>isset($path)?$path:'',
            'id'=>$array['image'],
            'time'=>$array['timestampUpdate'],
        ));
        $view->setTemplate('admin/notification/experience');
        return $this->setMenu($view,1);
    }


}