<?php
namespace Core\System\AiiPush;

/**
 * 基于信鸽推送开发的安卓类
 *
 * @author WZ
 *        
 */
class XingeAndroid extends AiiPushBase
{

    /**
     * 初始化
     */
    public function init()
    {
        $this->_access_id = XINGE_ANDROID_ACCESS_ID;
        $this->_secret_key = XINGE_ANDROID_SECRET_KEY;
    }

    /**
     * 发送
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title, $args = array(), $ring = 1, $vibrate = 1)
    {
//         var_dump($deviceToken, $content, $title, $args, $ring, $vibrate);exit;
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new Message();
        $mess->setType(Message::TYPE_NOTIFICATION);
        $mess->setTitle($title);
        $mess->setContent($content);
        // 义：样式编号0，响铃，震动，不可从通知栏清除，不影响先前通知
        $style = new Style(1, (int)$ring, (int)$vibrate);
        $mess->setStyle($style);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        // $action->setUrl("http://xg.qq.com");
        // 开url需要用户确认
        // $action->setComfirmOnUrl(1);
        $mess->setAction($action);
        // $custom = array('key1'=>'value1', 'key2'=>'value2');
        if($args)
        {
            $mess->setCustom((array)$args);
        }
//         $acceptTime1 = new TimeInterval(0, 0, 23, 59);
//         $mess->addAcceptTime($acceptTime1);
        $ret = $push->PushSingleDevice($deviceToken, $mess);
//         var_dump($ret);
        return $ret;
    }

    /**
     * 推送给所有安卓设备，分客户端和商家端
     *
     * @param string $content
     * @param string $title
     * @param array $args
     * @param number $user_type            
     * @version 2014-11-5 WZ
     */
    public function pushAllDevices($content, $title = '', $args = array())
    {
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new Message();
        $mess->setTitle($title);
        $mess->setContent($content);
        $mess->setType(Message::TYPE_NOTIFICATION);
        // 义：样式编号0，响铃，震动，可从通知栏清除，不影响先前通知
        $style = new Style(0, 1, 1, 1, 0);
        $action = new ClickAction();
        $action->setActionType(ClickAction::TYPE_ACTIVITY);
        // $action->setUrl("http://xg.qq.com");
        // 开url需要用户确认
        // $action->setComfirmOnUrl(1);
        // $custom = array('key1'=>'value1', 'key2'=>'value2');
        $mess->setStyle($style);
        $mess->setAction($action);
        $mess->setCustom($args);
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
    public function pushCollectionDevice($deviceTokens, $content, $title = '', $args = array())
    {
        $res_arr = array(
            'success' => array(),
            'fail' => array()
        );
        
        foreach ($deviceTokens as $value)
        {
            $result = $this->pushSingleDevice($value['device_token'], $content, $title, $args, $value['ring'], $value['vibrate']);
            if (0 == $result['ret_code'])
            {
                $res_arr['success'][] = $value['id'];
            }
            else
            {
                $res_arr['fail'][] = $value['id'];
                $content = $result['ret_code'];
                $this->myfile->putAtEnd($content, 0);
            }
        }
        return $res_arr;
    }
}
?>