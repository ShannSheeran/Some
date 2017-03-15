<?php

/**
 * 推送类，
 * 分类、分配，返回结果
 * @author WZ
 *
 */
class push
{

    private $config_file = 'config/push_config.php';

    private $ios_file = 'class/XingeIOS.php';

    private $android_file = 'class/XingeAndroid.php';

    private $sms_file = 'class/new_sms_push.php';

    public $myfile;

    private $ios_push;
    
    private $android_push;

    private $sms_push;

    private $result;

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->myfile = new myfile();
        $this->init();
    }

    function __destruct()
    {}

    /**
     * 没有参数，没有返回，把配置文件的内容录入。
     */
    private function init()
    {
        $this->checkType();
        if (is_file(PUSH_ROOT . '/' . $this->config_file)) {
            require_once PUSH_ROOT . '/' . $this->config_file; //
            $this->result = array(
                'errcode' => 0
            );
        } else {
            $this->result['errcode'] = 14001;
            $this->myfile->put(sprintf(STATUS_14001, $this->config_file), 1);
            return;
        }
    }

    /**
     * 推送一个设备号
     * 
     * @param string $device_token  设备号
     * @param int $type 设备类型
     * @param string $content   内容，正文
     * @param string $title 标题（安卓需要）
     * @param array $args 自定义参数（推送目标之类的参数）
     * @return array success,fail
     */
//     public function Sendbyone($device_token, $type, $badge = 1, $alert = 1, $sound = 1, $content, $title = '', $args = array())
    public function Sendbyone($device_token, $type, $content, $title = '', $args = array())
    {
        $device_collection = array(
            array(
                'id' => 1,
                'device_token' => $device_token,
                'device_type' => $type,
//                 'badge' => $badge,
//                 'alert' => $alert,
//                 'sound' => $sound
            )
        );
        return $this->Send($device_collection, $content, $title, $args);
    }
    
    /**
     * 通知，推送给所有用户
     * 
     * @param string $content   内容，正文
     * @param string $title 标题（安卓需要）
     * @param array $args 自定义参数（推送目标之类的参数）
     * @param int $type 0全部,1IOS,2安卓,3=1+2
     */
    public function SendAll($content , $title , $args = array() , $type = 0)
    {
        if (! $type || $type & 1) {
            $this->ios_push->sendAll($content, $title, $args);
        }
        if (! $type || $type & 2) {
            $this->android_push->sendAll($content, $title, $args);
        }
    }

    /**
     * 批量推送信息，
     * 分类，分配，获取结果。
     * 
     * @param array $device_collection
     *            array(id,device_token,device_type,user_type)
     * @param string $content
     *            内容
     * @param string $title
     *            标题（安卓需要）
     * @param array $args
     *            自定义参数
     * @return array $result success,fail
     */
    public function Send($device_collection, $content, $title = '', $args = array())
    {
        $result = array(
            'success' => array(),
            'fail' => array()
        );
        $temp_group = array();
        foreach ($device_collection as $value) {
            $temp_group[$value['device_type']][] = $value;
        }
        foreach ($temp_group as $key => $temp_devices) {
            if ($temp_devices) {
                $temp_result = $this->assign($temp_devices, $key, $content, $title, $args);
                if ($temp_result["success"]) {
                    $result['success'] = $result['success'] ? array_merge($result['success'], $temp_result['success']) : $temp_result['success'];
                }
                if ($temp_result["fail"]) {
                    $result['fail'] = $result['fail'] ? array_merge($result['fail'], $temp_result['fail']) : $temp_result['fail'];
                }
            }
        }
        return $result;
    }

    /**
     * 根据不同类型的设备号分配到不同的方法
     * 
     * @param array $device_collection
     *            array(id,device_token,device_type)
     * @param int $type
     *            1.ios 2.Android 8.sms
     * @param strng $content            
     * @param string $title            
     * @return array $result success,fail
     */
    private function assign($device_collection, $type, $content, $title = '', $args = array())
    {
        $result = array(
            "success" => array(),
            "fail" => array()
        );
        if ($type == 1) {
            $result = $this->SendToIos($device_collection, $content , $title , $args);
        } elseif ($type == 2) {
            $result = $this->SendToAndroid($device_collection, $content, $title, $args);
        } elseif ($type == 8) {
            $result = $this->SendToSMS($device_collection, $content);
        } else {}
        return $result;
    }

    /**
     * 调用ios类的方法，把信息推送出去
     * 
     * @param array $device_collection
     *            array(id,device_token,device_type)
     * @param strng $content            
     * @return array $result success,fail
     */
    private function SendToIOS($device_collection, $content, $title = '', $args = array())
    {
        return $this->ios_push->send($device_collection, $content , $title , $args);
    }

    /**
     * 调用Android类的方法，把信息推送出去
     * 
     * @param array $device_collection
     *            array(id,device_token,device_type,user_type)
     * @param strng $content            
     * @return array $result success,fail
     */
    private function SendToAndroid($device_collection, $content, $title = '', $args = array())
    {
        return $this->android_push->send($device_collection, $content, $title, $args);
    }

    /**
     * 调用sms类的方法，把信息推送出去
     * 
     * @param array $device_collection
     *            array(id,device_token,device_type)
     * @param strng $content            
     * @return array $result success,fail
     */
    private function SendToSMS($device_collection, $content)
    {
        return $this->sms_push->send($device_collection, $content);
    }

    /**
     * 检测类文件存不存在，存在就引入
     */
    private function checkType()
    {
        if (is_file(PUSH_ROOT . '/' . $this->ios_file)) {
            $this->ios_push = new XingeIOS();
        } else {
            $this->result['errcode'] = 14001;
            $this->myfile->put(sprintf(STATUS_14001, $this->ios_file), 1);
            return;
        }
        if (is_file(PUSH_ROOT . '/' . $this->android_file)) {
            $this->android_push = new XingeAndroid();
        } else {
            $this->result['errcode'] = 14001;
            $this->myfile->put(sprintf(STATUS_14001, $this->android_push), 1);
            echo $this->result['errcode'];
            return;
        }
        if (is_file(PUSH_ROOT . '/' . $this->sms_file)) {
            $this->sms_push = new new_sms_push();
        } else {
            $this->result['errcode'] = 14001;
            $this->myfile->put(sprintf(STATUS_14001, $this->sms_file), 1);
            return;
        }
    }
}

?>