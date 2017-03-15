<?php
namespace Api3\Controller;
use Api3\Controller\Request\DeviceTokenSwitchRequest;

/**
 * 开启/关闭消息推送
 *
 * @author WZ
 * @version 1.0.140513 WZ
 */
class DeviceTokenSwitch extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new DeviceTokenSwitchRequest();
        parent::__construct();
    }

    /**
     * 返回一个数组或者Result类
     * @return \Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $session_id = $this->getSessionId();
        
        if (empty($request->device_token)) {
            // 传进来的设备号是空
            if (empty($session_id)) {
                // 设备号和session_id都不传就退出
                return STATUS_SESSION_EMPTY;
            } else {
                // 用session_id查找device_token
                $where = array(
                    "session_id" => $session_id
                );
                $login = $this->getLoginTable()->getOne($where);
                if ($login) {
                    $request->device_token = $login->device_token;
                } else {
                        // 找不到就是数据不存在 1011
                    return STATUS_NODATA;
                }
            }
        }
        
        $where = array(
            "device_token" => $request->device_token
        );
        
        $set = array();
        if ($request->open) {
            $set['notification'] = $request->open == 1 ? 1 : 2;
        }
        if ($request->style->sound) {
            $set['sound'] = $request->style->sound == 1 ? 1 : 2;
        }
        if ($request->style->vibrate) {
            $set['vibrate'] = $request->style->vibrate == 1 ? 1 : 2;
        }
        if ($request->style->quiet) {
            $set['quiet_start_time'] = $request->style->quiet == 1 ? '22:00:00' : '00:00:00';
            $set['quiet_end_time'] = $request->style->quiet == 1 ? '08:00:00' : '00:00:00';
        }
        $dbResult = $this->getDeviceUserTable()->updateData($set, $where);
        // 设置最终结果的值
        
        return STATUS_SUCCESS; // 成功或未知错误
    }
}