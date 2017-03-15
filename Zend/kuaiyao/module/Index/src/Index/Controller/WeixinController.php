<?php
namespace Index\Controller;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\View\Model\ViewModel;
use Core\System\AiiUtility\AiiEasemobApi\AiiEasemobApi;
use Api\Controller\SMSCode;


class WeixinController extends CommonController
{
    public function checkInvationCodeAction() {
        $result = 1; // 成功
        $code = isset($_POST['code']) ? trim ($_POST['code']) : '';
        if (! $code) {
            $result = 2;// 不存在
        }
        
        $check = $this->getInvitationCodeTable()->getOne(array('code' => $code, 'status' => 0));
        if (! $check) {
            $result = 2; 
        }
        echo $result;
        exit;
    }
    
    /**
     * 订单查询
     * 
     * @version 2015-8-11 WZ
     */
    public function orderAction() {
        $open_id = isset($_POST['open_id']) ? trim($_POST['open_id']) : "";
        $user_id = 0;
        if ($open_id) {
            $where = array(
                'open_id' => $open_id,
                'partner' => 3,
            );
            $data = $this->getUserPartnerTable()->getOne($where);
            $user_id = $data ? $data['user_id'] : 0;
        }
        
        $list = array();
        if ($user_id) {
            $where = new Where();
            $where->equalTo('user_id', $user_id);
            $where->greaterThan('status', 1);
            $list = $this->getOrderTable()->fetchAll($where);
        }
        
        echo json_encode($list);
        exit;
    }
    
    /**
     * 订单提交
     * 
     * @version 2015-8-11 WZ
     */
    public function orderSubmitAction() {
        $user_id = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;
        $code = (isset($_POST['code']) && $_POST['code'])? trim ($_POST['code']) : "";
        
        $result = array(
            'status' => 0, // 正常
            'msg' => '下单成功', // 描述 
            'price' => 198, // 正常价
            'order_sn' => $this->makeSN(), // 订单号
            'page_id' => 0,
        );
        
        if (! $user_id) {
            $result ['status'] = 1;
            $result ['msg'] = '用户未绑定手机号码';
            $this->apiExit($result);
        }
        
        if ($code) {
            $code_where = new Where();
            $code_where->equalTo('code', $code);
            $code_where->equalTo('status', 0);
            $code_where->equalTo('user_id', $user_id);
            $code_info = $this->getInvitationCodeTable()->getOne($code_where);
            if ($code_info) {
                $result ['status'] = 2;
                $result ['msg'] = '用户您自己的k码只能分享给朋友使用';
                $this->apiExit($result);
            }
//             if (!$code_info) {
//                 $result ['status'] = 2;
//                 $result ['msg'] = 'K码无效';
//                 $this->apiExit($result);
//             }
            
            $code_where1 = new Where();
            $code_where1->equalTo('code', $code);
            $code_where1->equalTo('status', 0);
            $code_res = $this->getInvitationCodeTable()->getOne($code_where1);
            
            if (!$code_res) {
                $result ['status'] = 2;
                $result ['msg'] = 'K码无效';
                $this->apiExit($result);
            }
            
            
            $result['price'] = 168; // K码价
            
            if(!$code_info && $code_res){
                $code = $code_res['id'];
            }
//             $this->getInvitationCodeTable()->updateData(array('status' => 1,'timestamp_update' => $this->getTime(), 'recommended_user_id' => $user_id), $code_where);
        }
        
        
        
        $order_data = array(
            'order_sn' => $result['order_sn'],
            'code_id' => $code,//isset($code_info) ? $code_info['id'] : 0,//11111
            'price' => $result['price'],
            'number' => 1,
            'total' => $result['price'],
            'status' => 1,
            'user_id' => $user_id,
            'timestamp' => $this->getTime()
        );
        $this->getOrderTable()->insertData($order_data);
        
        //$usedCode = $this->getInvitationCodeTable()->updateData(array('status'=>1,'recommended_user_id'=>$user_id), array('code'=>$code));//k码状态
       
            $user_info = $this->getUserTable()->getOne(array('id' => $user_id));
            if ($user_info && $user_info['page_id']) {
                $result['page_id'] = $user_info['page_id'];
            }
        
        $this->apiExit($result);
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
     * 检查openId是否存在
     * 
     * @version 2015-8-10 WZ
     */
    public function checkOpenIdAction() {
        $open_id = isset($_POST['open_id']) ? trim($_POST['open_id']) : "";
        
        $user_where = array('open_id' => $open_id, 'partner' => 3);
        $user_partner = $this->getUserPartnerTable()->getOne($user_where);
        if ($user_partner && $user_partner['user_id']) {
            echo $user_partner['user_id'];
            exit;
        }
        echo 0;
        exit;
    }
    
    /**
     * 绑定openId与用户的关系，并返回userid
     * 
     * @version 2015-8-10 WZ
     */
    public function bindMobileAction() {
        $open_id = isset($_POST['open_id']) ? trim($_POST['open_id']) : "";
        $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : "";
        if (!$open_id || !$mobile) {
            // 请求参数不完整
            echo 0;
            exit;
        }
        
        $user_where = array('open_id' => $open_id, 'partner' => 3);
        $user_partner = $this->getUserPartnerTable()->getOne($user_where);
        if ($user_partner && $user_partner['user_id']) {
            echo $user_partner['user_id'];
            exit;
        }
        
        $user = $this->getUserTable()->getOne(array('mobile' => $mobile));
        if (! $user) {
            $data = array('mobile' => $mobile,'timestamp' => $this->getTime());
            $user_id = $this->getUserTable()->insertData($data);
            
            $easemob = new AiiEasemobApi();
            $easemob->userRegister($user_id, md5($user_id));
            $easemob->userUpdateNickname($user_id, $mobile);
        }
        else {
            $user_id = $user['id'];
        }
        
        if ($user_partner) {
            $this->getUserPartnerTable()->updateData(array('user_id' => $user_id), $user_partner);
        }
        else {
            $this->getUserPartnerTable()->insertData(array('user_id' => $user_id, 'partner' => 3, 'open_id' => $open_id));
        }
        echo $user_id;
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
     * 短信验证码
     * 
     * @version 2015-8-11 WZ
     */
    public function smscodeAction() {
        $action = isset($_REQUEST['action']) ? (int) $_REQUEST['action'] : 0;
        $type = 5;
        $mobile = isset($_REQUEST['mobile']) ? trim ($_REQUEST['mobile']) : "";
        $code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : "";
        
        $msg = array(
            'status' => 0,
            'msg' => '成功',
        );
        if (! $action || ! $mobile) {
            $msg = array(
                'status' => 1,
                'msg' => '请求参数不完整1'
            );
            $this->apiExit($msg);
        }
        if ($action == 2 && ! $code) {
            $msg = array(
                'status' => 2,
                'msg' => '请求参数不完整2' . var_export($_REQUEST['code'], true)
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
        $result = json_decode($result, true);
        
        $msg = array(
            'status' => $result['q']['s'],
            'msg' => $result['q']['d']
        );
        
        $this->apiExit($msg);
    }
    
    /**
     * 购买成功的页面的数据
     *
     * @version 2015-9-28 HY
     */
    public function showInvitationAction(){
        $user_id = (int) $_POST['user_id'];
        if ($user_id) {
            $where = new Where();
            $where->equalTo('user_id', $user_id);
            $order = array(
                'status' => 'ASC'
            );
            $K_code = $this->getInvitationCodeTable()->fetchAll($where, $order); // 用户个人提现信息
            $msg = array(
                'status' => '0',
                'msg' => '正常',
                'data' => $K_code,
            );
        } else {
            $msg = array(
                'status' => '1000',
                'msg' => '未知错误',
                'data' => array()
            );
        }
        
        echo json_encode($msg);
        exit();
    }
    /**
     * 购买成功的页面的数据
     * 
     * @version 2015-8-13 WZ
     */
    public function successAction() {
        $open_id = isset($_POST['open_id']) ? trim($_POST['open_id']) : "";
        $user_id = 0;
        if ($open_id) {
            $where = array(
                'open_id' => $open_id,
                'partner' => 3,
            );
            $data = $this->getUserPartnerTable()->getOne($where);
            $user_id = $data ? $data['user_id'] : 0;
        }
        
        $list = array();
        if ($user_id) {
            $where = new Where();
            $where->equalTo('user_id', $user_id);
            $data = $this->getInvitationCodeTable()->getAll($where, null, array('id' => 'desc'), true, 1, 8);
            $list = $data['list'];
        }
        
        echo json_encode($list);
        exit;
    }
    
    /**
     * 获取微信access token
     * 
     * @return unknown
     * @version 2015-8-13 WZ
     */
    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        include_once APP_PATH . '/public/wxpay/lib/WxPay.Config.php';
        $wx_pay_config = new \WxPayConfig();
        
        $filename = APP_PATH . '/vendor/Core/System/WxApi/access_token.json';
        $data = null;
        if (is_file($filename)) {
            $data = json_decode(file_get_contents($filename));
        }
        if ($data && $data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$wx_pay_config::APPID."&secret=" . $wx_pay_config::APPSECRET;
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                file_put_contents($filename, json_encode($data));
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }
    
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
    
        $res = curl_exec($curl);
        curl_close($curl);
    
        return $res;
    }
}