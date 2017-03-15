<?php

class XingeAndroid
{

    private $config_file = 'config/xinge_android_config.php';

    private $myfile;

    private $_access_id;

    private $_secret_key;

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
        if (is_file(PUSH_ROOT . '/' . $this->config_file))
        {
            require_once PUSH_ROOT . '/' . $this->config_file;
            $this->_access_id = XINGE_ANDROID_ACCESS_ID;
            $this->_secret_key = XINGE_ANDROID_SECRET_KEY;
        }
        else
        {
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
        // $push = new XingeApp($this->_access_id, $this->_secret_key);
        // $mess = new Message();
        // $mess->setTitle($title);
        // $mess->setContent($content);
        // $mess->setType(Message::TYPE_MESSAGE);
        // $ret = $push->PushSingleDevice($deviceToken, $mess);
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle($title);
        $mess->setContent($content);
        // $style = new Style(0);
        // 义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0, 1, 1, 1, 0);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        // $action->setUrl("http://xg.qq.com");
        // 开url需要用户确认
        // $action->setComfirmOnUrl(1);
        $mess->setAction($action);
        $mess->setStyle($style);
        
        if (isset($args['time']))
        {
            $mess->setSendTime($args['time']);
            unset($args['time']);
        }
        if ($args)
        {
            // $custom = array('key1'=>'value1', 'key2'=>'value2');
            $custom = $args;
            $mess->setCustom($custom);
        }
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        $ret = $push->PushSingleDevice($deviceToken, $mess);
        return $ret;
    }

    public function sendAll($content, $title = '', $args = array())
    {
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        // $style = new Style(0);
        // 义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(0, 1, 1, 1, 0);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        // $action->setUrl("http://xg.qq.com");
        // 开url需要用户确认
        // $action->setComfirmOnUrl(1);
        
        if (isset($args['time']))
        {
            $mess->setSendTime($args['time']);
            unset($args['time']);
        }
        
        $mess->setStyle($style);
        $mess->setAction($action);
        
        if ($args)
        {
            $custom = $args;
            $mess->setCustom($custom);
        }
        
        $acceptTime1 = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime1);
        $ret = $push->PushAllDevices(XingeApp::DEVICE_ANDROID, $mess);
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
        
        foreach ($deviceTokens as $value)
        {
            $result = $this->sendOne($value['device_token'], $content, $title, $args);
            if (0 == $result['ret_code'])
            {
                $res_arr['success'][] = $value['id'];
            }
            else
            {
                $res_arr['fail'][] = $value['id'];
                $content = $result['ret_code'];
                $this->myfile->put($content, 0);
            }
        }
        return $res_arr;
    }
}
?>