<?php

class XingeIOS
{

    private $config_file = 'config/xinge_ios_config.php';

    private $myfile;

    private $_access_id;

    private $_secret_key;
    
    private $_iosenv;

    /**
     * 构造函数
     */
    function __construct()
    {
        $this->myfile = new myfile();
        $this->init();
    }

    /**
     * 初始化
     */
    private function init()
    {
        if (is_file(PUSH_ROOT . '/' . $this->config_file)) {
            require_once PUSH_ROOT . '/' . $this->config_file;
            $this->_access_id = XINGE_IOS_ACCESS_ID;
            $this->_secret_key = XINGE_IOS_SECRET_KEY;
            $this->_iosenv = XINGE_IOSENV;
        } else {
            $this->myfile->put(sprintf(STATUS_12001, $this->config_file), 1);
            die();
        }
    }

    /**
     * 发送
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function sendOne($deviceToken, $content, $title, $args = array())
    {
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new MessageIOS();
        // $mess->setSendTime("2014-03-13 16:00:00");
        $mess->setAlert($content);
        // $mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge(1);
        // $mess->setSound("beep.wav");
        if (isset($args['time'])) {
            $mess->setSendTime($args['time']);
            unset($args['time']);
        }
        if ($args) {
            // $custom = array('key1'=>'value1', 'key2'=>'value2');
            $custom = $args;
            $mess->setCustom($custom);
        }
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushSingleDevice($deviceToken, $mess, $this->_iosenv);
        return $ret;
    }
    
    public function sendAll($content, $title = '', $args = array())
    {
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $mess->setBadge(1);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushAllDevices(XingeApp::DEVICE_IOS, $mess , $this->_iosenv);
    }

    /**
     * 批量发送
     *
     * @param array $deviceTokens
     *            id,device_token
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function send($deviceTokens, $content, $title = '', $args = array())
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array()
        );
        
        foreach ($deviceTokens as $value) {
            $result = $this->sendOne($value['device_token'], $content, $title, $args);
            if (0 == $result['ret_code']) {
                $res_arr['success'][] = $value['id'];
            } else {
                $res_arr['fail'][] = $value['id'];
                $content = $result['ret_code'];
                $this->myfile->put($content, 0);
            }
        }
        return $res_arr;
    }
}
?>