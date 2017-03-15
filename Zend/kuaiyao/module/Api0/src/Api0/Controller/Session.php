<?php
namespace Api0\Controller;
use Api0\Controller\Request\SessionRequest;

/**
 * 获取会话ID，根据设备号生成32位由小写英文字母与数字组成的随机字符串。<br />
 * 设备号与Session Id是一对一关系。
 *
 * @author WZ
 * @version 1.0.140717 WZ
 */
class Session extends CommonController
{
    /**
     * 设备号长度限制
     * 
     * @var number
     */
    const DEVICE_LENGTH = 16;
    
    /**
     * 生成Session的长度
     * 
     * @var number
     */
    const SESSION_LENGTH = 32;

    public function __construct()
    {
        $this->myRequest = new SessionRequest();
        parent::__construct();
    }

    /**
     * 返回一个数组或者Result类
     *
     * @return \Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        // 判断数据表中没有记录此设备，若没有，则记录
        if (strlen($request->device_token) < self::DEVICE_LENGTH)
        {
            $request->device_token = $this->makeSessionId();
            $request->device_type = 32;
            // return STATUS_NO_DEVICETOKEN;
        }
        
        $where = array(
            'device_token' => $request->device_token
        );
        $login = $this->getLoginTable()->getOne($where);
        
        if ($login)
        {
            // 已经有过该设备直接返回session id
            $this->setSessionId($login->session_id);
            $data = array(
                'version' => $request->version
            );
            // 更新客户端的版本号
            $this->getLoginTable()->update($data, array(
                'id' => $login->id
            ));
            
//             $this->saveDeviceUser(); // 插入到device_user表
            
            // 设置最终结果的值
            $response->status = STATUS_SUCCESS;
            $response->expire = $login->expire;
        }
        else
        {
            // 第一次访问没有sessionId的时候生成一个sessionId
            $this->setSessionId($this->makeSessionId());
            // 插入到login表
            $data = array(
                'session_id' => $this->getSessionId(),
                'lang' => $request->lang,
                'model' => $request->model,
                'version' => $request->version,
                'resolution' => $request->resolution,
                'screen_size' => $request->screen_size,
                'device_token' => $request->device_token,
                'device_type' => $request->device_type,
                'info' => $request->info,
                'status' => LOGIN_STATUS_TEMP,
                'timestamp' => $this->getTime()
            );
            $dbResult = $this->getLoginTable()->insertData($data);
            
            $this->saveDeviceUser(); // 插入到device_user表
            
            // 设置最终结果的值
            $response->status = ($dbResult ? STATUS_SUCCESS : STATUS_UNKNOWN);
            $response->expire = '';
        }
        return $response;
    }
    
    /**
     * 保存用户设备表信息
     * 
     * @return boolean
     * @version 2014-11-6 WZ
     */
    private function saveDeviceUser()
    {
        $request = $this->getAiiRequest();
        
        if((1|2|4|8) & $request->device_type)
        {
            $device_user = $this->getDeviceUserTable()->getOne(array(
                'device_token' => $request->device_token
            ));
            if(! $device_user)
            {
                $data = array(
                    'device_token' => $request->device_token,
                    'device_type' => $request->device_type,
                    'notification' => OPEN_TRUE,
                    'vibrate' => OPEN_TRUE,
                    'alert' => OPEN_TRUE,
                    'sound' => OPEN_TRUE,
                    'timestamp' => $this->getTime()
                );
                return $this->getDeviceUserTable()->insertData($data);
            }
        }
        return false;
    }

    /**
     * 给session_id赋值
     *
     * @param string $session_id            
     * @author WZ
     * @version 1.0.140514 WZ
     */
    private function setSessionId($session_id)
    {
        $this->session_id = $session_id;
    }

    /**
     * 生成随机字符串
     *
     * @param int $length            
     * @return string
     */
    private function makeSessionId()
    {
        return $this->makeCode(self::SESSION_LENGTH, self::CODE_TYPE_LOWERCASE + self::CODE_TYPE_NUMBER);
    }
}