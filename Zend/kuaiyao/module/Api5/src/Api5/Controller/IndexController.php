<?php
namespace Api5\Controller;

use Core\System\AiiUtility\AiiEncryptVerify\AiiEncryptVerify;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;
use Index\Controller\CommonController as IndexCommonController;
use Core\System\AiiUtility\AiiWxPayV3\AiiWxPayNotify;
use Core\System\AiiPush\AiiMyFile;
use Core\System\Ylpay\utf8\func\common;
use Core\System\Ylpay\utf8\func\secureUtil;
include APP_PATH . '/vendor/Core/System/alipayapi.php';

class IndexController extends CommonController
{

    public function __construct()
    {
    }

    public function indexAction()
    {
        $startTime = microtime();
        $this->startTime = $startTime;
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
            Case 'AdList':
                $obj = new AdList();
                break;
            case 'Setting':
                $obj = new Setting();
                break;
            case 'RegionList':
                $obj = new RegionList();
                break;
            case 'UploadFiles':
                $obj = new UploadFiles();
                break;
            Case 'CategoryList':
                $obj = new CategoryList();
                break;
            case 'UserLogin':
                $obj = new UserLogin();
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
            case 'UserUpdatePassword':
                $obj = new UserUpdatePassword();
                break;
            case 'UserResetPassword':
                $obj = new UserResetPassword();
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
            case 'UserAddressDetails':
                $obj = new UserAddressDetails();
                break;
            case 'UserAddressSubmit':
                $obj = new UserAddressSubmit();
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
	        Case 'CheckAction':
	            $obj = new CheckAction();
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
            Case 'ReferenceItemList': 
                $obj = new ReferenceItemList();
                break;
            Case 'FinancialList':
                $obj = new FinancialList();
                break;
            Case 'FinancialSubmit':
                $obj = new FinancialSubmit();
                break;
            Case 'ActivityList':
                $obj = new ActivityList();
                break;
            case 'ActivityDetails':
                $obj = new ActivityDetails();
                break;
            Case 'CompanyList':
                $obj = new CompanyList();
                break;
            Case 'CompanySubmit':
                $obj = new CompanySubmit();
                break;
            Case 'CompanyDetails':
                $obj = new CompanyDetails();
                break;
            case 'OrderSubmit':
                $obj = new OrderSubmit();
                break;
            case 'CardSubmitTest':
                  $obj = new CardSubmitTest();
                  break;
            case 'UserBindDevice':
                  $obj = new UserBindDevice();
                  break;
            case 'UserUpdateTest':
                   $obj = new UserUpdateTest();
                   break;
            case 'CompanySwitch':
                    $obj = new CompanySwitch();
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
        echo '功能屏蔽，需要使用请联系管理员';
        exit;
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
    
    public function smsPushAction() {
        echo '暂不使用';
        exit;
        $mobile = array('13750598284',
'13928856325',
'13670788001',
'13850367997',
'13328288855',
'13823884308',
'13696836788',
'15959091683',
'18605069072',
'13400533966',
'15821879081',
'18796365056',
'13913980126',
'13382002318',
'15280049815',
'13802990094',
'13543861364',
'15327829666',
'15990744445',
'13859669811',
'15659386935',
'15980117862',
'13328667018',
'18650733877',
'13290982552',
'18350001331',
'13960767119',
'13655044210',
'18659174740',
'13823899779',
'15905184456',
'18060610988',
'15505908883',
'15005009886',
'15900677708',
'18620042165');
        
        $content = TEMPLATE_SMS_BUY;
        $sms = new SMSCode();
        $sms->smsPush($content, $mobile);
        exit;
    }


    /*
     *支付宝同步回调
     *
     * */
    public function getAlipayReturnAction()
    {
        return $this->redirect()->toRoute('index',array('controller'=>'user','action'=>'PaySuccess'));
        die();
    }

    /**
     * 支付宝回调
     *
     * @version 2014-12-24 WZ
     */
    public function getAlipayNotifyAction()
    {
        $type = '';
        if($_POST['sign_type'] == 'RSA'){
            $type = 2;
        }else if($_POST['sign_type'] == 'MD5'){
            $type = 1;
        }
        $alipay = new \alipayapi();
        $verify_result = $alipay->getAlipayNotifySuccess($type);    // 1 pc 2 app

        $file = new AiiMyFile();
        $file->setFileToPublicLog();
         /*  $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : "";
         $file->putAtStart("xml:" . $xml);  */
        $file->putAtStart("result:" . var_export($_POST,true));
        
         if ($verify_result) { // 验证成功 
            // 商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            // 支付宝交易号
            $trade_no = $_POST['trade_no'];
            // 交易金额
            $total_fee = $_POST['total_fee'];
            // 交易状态
            $trade_status = $_POST['trade_status'];
            // 买家支付宝帐号
            $buyer_account = $_POST['buyer_email'];
    
            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                $this->getIndexController()->orderUpdateStatus($out_trade_no, 2);
                echo "success"; // 请不要修改或删除
            }
        } else {
            // 验证失败
            echo "fail";
        } 
        die();
    }
    
    /**
     * 微信回调
     *
     * @version 2015-4-10 WZ
     */
    public function getWxPayNotifyAction()
    {
        $file = new AiiMyFile();
        $file->setFileToPublicLog();
        
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
//         $file->putAtStart("xml:" . $xml);
        if (! $xml) {
            die();
        }
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if (! $array_data) {
            die();
        }
        
        if ($array_data['trade_type'] == 'NATIVE') {
            include_once APP_PATH . '/vendor/Core/System/WxPayApi/lib/WxPay.Api.php';
            $wxpay = new \WxPayApi();
            $notify = $wxpay->notify();
            if ($notify['result']['return_code'] != 'SUCCESS') {
                // 未完成订单
                die();
            }
            $out_trade_no = $notify['result']['out_trade_no'];
        }
        elseif ($array_data['trade_type'] == 'APP') {
            $notify = new AiiWxPayNotify();
            $result = $notify->getResult();
            if ($result['status']) {
                // 有错误
                die();
            }
            $out_trade_no = $result['out_trade_no'];
        }
        else {
            // 未知类型
        }
        
        if ($out_trade_no) {
            $this->getIndexController()->orderUpdateStatus($out_trade_no, 3);
        }
        exit();
    }

    /**
     * 银联回调
     *
     * @version 2015-12-22 WZ
     */
    public function getYlpayNotifyAction()
    {  
       
       /*  include_once $_SERVER ['DOCUMENT_ROOT'].'/kuaiyao/vendor/Core/System/Ylpay/utf8/func/common.php';
        include_once $_SERVER ['DOCUMENT_ROOT'].'/kuaiyao/vendor/Core/System/Ylpay/utf8/func/secureUtil.php'; */
        $result = $_POST;        
        if ($result['respMsg'] == "Success!"){            
            $out_trade_no = $result['orderId'];
            $this->getIndexController()->orderUpdateStatus($out_trade_no, 3);
        }
        exit;
    }
}
