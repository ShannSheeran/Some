<?php
namespace Api0\Controller;

use Core\System\AiiUtility\AiiEncryptVerify\AiiEncryptVerify;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;
use Zend\Db\Sql\Where;
use Core\System\File;
use Core\System\AiiUtility\AiiWxPayV3\AiiWxPayNotify;
include APP_PATH . '/vendor/Core/System/alipayapi.php';

class IndexController extends CommonController
{

    public function __construct()
    {
    }

    public function indexAction()
    {
        $startTime = microtime();
        $json = isset($_REQUEST['json']) ? $_REQUEST['json'] : false;
        if (! $json)
        {
            die(1);
        }
        $json_object = json_decode($json);
        if (! $json_object)
        {
            $this->response(STATUS_INCORRECT_FORMAT);
        }
        
        $this->namespace = isset($json_object->n) ? $json_object->n : '';
        $this->session_id = isset($json_object->s) ? $json_object->s : '';
        
        if (! $this->checkSubmit() ) {
            $this->response(STATUS_TOO_FAST);
        }
        
        // define('CHECK_API_DEBUG_SWITCH', true); // 开启调试
        $AiiEncryptVerify = new AiiEncryptVerify();
        $check = $AiiEncryptVerify->check($json); // 验证
        if (! $check)
        {
            $this->response(STATUS_MD5);
        }
        
        $className = isset($json_object->n) ? trim($json_object->n) : '';
        switch ($className)
        {
            case 'Session':
                $obj = new Session();
                break;
            case 'DeviceTokenSwitch':
                $obj = new DeviceTokenSwitch();
                break;
            case 'SMSCode':
                $obj = new SMSCode();
                break;
            case 'RegionList':
                $obj = new RegionList();
                break;
            case 'UploadFiles':
                $obj = new UploadFiles();
                break;
            case 'UserLogout':
                $obj = new UserLogout();
                break;
            case 'UserBindMobile':
                $obj = new UserBindMobile();
                break;
            case 'CardDetails':
                $obj = new CardDetails();
                break;
            case 'RecommendList':
                $obj = new RecommendList();
                break;
            case 'CommentSubmit':
                $obj = new CommentSubmit();
                break;
            case 'UserDetails':
                $obj = new UserDetails();
                break;
            case 'UserUpdate':
                $obj = new UserUpdate();
                break;
            Case 'UserFriendsSwitch':
                $obj = new UserFriendsSwitch();
                break;
            Case 'CardList':
                $obj = new CardList();
                break;
            Case 'CardSubmit':
            	$obj = new CardSubmit();
            	break;
        	Case 'MessageList':
        		$obj = new MessageList();
        		break;
    		Case 'MessageSubmit':
    			$obj = new MessageSubmit();
    			break;
			Case 'ChatUpdate':
			    $obj = new ChatUpdate();
			    break;
		    Case 'SwitchAction':
		        $obj = new SwitchAction();
		        break;
	        Case 'PraiseUserList':
	            $obj = new PraiseUserList();
	            break;
            Case 'CommentList':
                $obj = new CommentList();
                break;
            Case 'NotificationList':
                $obj = new NotificationList();
                break;
            Case 'DeleteAction':
                $obj = new DeleteAction();
                break;
            default:
                $this->namespace = isset($json_object->n) ? $json_object->n : '';
                $this->session_id = isset($json_object->s) ? $json_object->s : '';
                $this->response(STATUS_NO_PROTOCOL);
                break;
        }
        $sm = $this->getServiceLocator();
        $obj->setServiceLocator($sm);
        $obj->startTime = $startTime;
        $response = $obj->index();
        if ($response)
        {
            $obj->setResponse($response);
        }
        $obj->response();
        exit();
    }
    
    /**
     * 防止一秒请求多次
     *
     * @param unknown $json
     * @return boolean
     * @version 2015-5-26 WZ
     */
    function checkSubmit() {
        $json = $_REQUEST['json'];
        $key = md5(json_encode($json));
        //         $key = $json['s'] . '-' . $json['n'];
        if (isset($_SESSION[$key]) && time() - $_SESSION[$key] < 1) {
            return false;
        }
        else {
            $_SESSION[$key] = time();
            session_write_close();
            session_start();
            return true;
        }
    }
    
    /**
     * 重新注册环信用户
     *
     * @version 2015-5-5 WZ
     */
    public function flashEasemobUserRegAction()
    {
        set_time_limit(0);
        //         echo '功能屏蔽，需要使用请联系管理员';
        //         exit;
        $easemob = new AiiEasemobApi();
        $common = new CommonController();
        $users = $common->getUserTable()->fetchAll();
        foreach($users as $user) {
            $result = $easemob->userRegister($user['id'], md5($user['id']));
            $result = json_decode($result, true);
            if (isset($result['error'])) {
                echo 'id:' . $user['id'] . ',result:' . $result['error'] . '<br />';
            }
            else {
                echo 'id:' . $user['id'] . ',result:success' . '<br />';
            }
            exit;
        }
        echo 'End'.date('Y-m-d H:i:s');
        exit;
    }
}
