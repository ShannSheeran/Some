<?php
namespace Core\System\AiiPush;

/**
 * 基于信鸽开发的iOS类
 *
 * @author WZ
 *        
 */
class XingeIos extends AiiPushBase
{

    /**
     * 设置参数，子类注意改写此方法
     */
    public function init()
    {
        $this->_access_id = XINGE_IOS_ACCESS_ID;
        $this->_secret_key = XINGE_IOS_SECRET_KEY;
        $this->_iosenv = XINGE_IOSENV;
    }

    /**
     * 根据设备号发送给单个设备
     *
     * @param int $msg_content
     *            发送的内容
     * @return array $res_arr 反馈信息
     */
    public function pushSingleDevice($deviceToken, $content, $title, $args = array())
    {
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new MessageIOS();
        // $mess->setSendTime("2014-03-13 16:00:00");
        $mess->setAlert($content);
        // $mess->setAlert(array('key1'=>'value1'));
        $mess->setBadge(1);
        // $mess->setSound("beep.wav");
        // $custom = array('key1'=>'value1', 'key2'=>'value2');
        $mess->setCustom((array)$args);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushSingleDevice($deviceToken, $mess, $this->_iosenv);
        return $ret;
    }

    /**
     * 推送给所有iOS设备
     * 
     * @param string $content 正文
     * @param string $title 标题，其实是没用，只是跟安卓推送保持一致所以才有的参数
     * @param array $args 其它参数
     * @version 2014-11-5 WZ
     */
    public function pushAllDevices($content, $title = '', $args = array())
    {
        $push = new XingeApp($this->_access_id, $this->_secret_key);
        $mess = new MessageIOS();
        $mess->setAlert($content);
        $mess->setBadge(1);
        $mess->setCustom($args);
        $acceptTime = new TimeInterval(0, 0, 23, 59);
        $mess->addAcceptTime($acceptTime);
        $ret = $push->PushAllDevices(XingeApp::DEVICE_IOS, $mess, $this->_iosenv);
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
            $result = $this->pushSingleDevice($value['device_token'], $content, $title, $args);
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