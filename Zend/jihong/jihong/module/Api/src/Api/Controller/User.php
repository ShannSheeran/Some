<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;

class User extends CommonController
{

    /**
     * 注册
     *
     * @var 1
     */
    const MOBILE_VALIDATE_TYPE_REGISTER = 1;

    /**
     * 重置密码
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_TYPE_RESET = 2;
    
    /**
     * 绑定手机
     *
     * @var 2
     */
    const MOBILE_VALIDATE_TYPE_BIND = 3;

    /**
     * 临时/未验证
     *
     * @var 0
     */
    const MOBILE_VALIDATE_STATUS_TEMP = 0;

    /**
     * 已验证
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_USED = 1;

    /**
     * 发送失败
     *
     * @var unknown
     */
    const MOBILE_VALIDATE_STATUS_FAIL = 2;

    /**
     * 查看验证码 是否已经验证
     *
     * @author WZ
     * @param int $type
     *            1.注册,2.重置密码,3.绑定手机
     * @param int $id
     *            短信验证码id
     * @param string $mobile
     *            手机号码
     * @return Ambigous false|object 短信表记录信息
     * @version 1.0.140325
     */
    public function checkSmsComplete($type, $id, $mobile)
    {
        $status = STATUS_SUCCESS;
        if (! $id || ! $mobile)
        {
            $status = STATUS_PARAMETERS_INCOMPLETE;
        }
        else
        {
            $where = array(
                'id' => $id
            );
            
            $data = $this->getSmsCodeTable()->getOne($where);
            
            if (! $data || self::MOBILE_VALIDATE_STATUS_USED != $data['status'] || $mobile != $data['mobile'] || $type != $data['type'])
            {
                // 数据不存在 或 未验证 或 手机不匹配 或 请求类型不匹配 返回错误
                echo $data['status'];
                $status = STATUS_TIMEOUT;
            }
        }
        
        if (STATUS_SUCCESS != $status)
        {
            $this->response($status);
        }
    }

    /**
     * 登录成功之后更新login表和device_user表，用户的话还更新user表的last_time
     *
     * @param array $user_info            
     */
    public function loginUpdate($user_info)
    {
        // 更新登录信息
        $this->updateLoginTable($user_info);
        
        // 更新设备信息
        $this->updateDeviceUserTable($user_info);
        
        // 用户更新最后登录时间
        $this->updateUserTable($user_info);
    }

    /**
     * 更新登录表信息
     *
     * @param unknown $user_info            
     */
    private function updateLoginTable($user_info)
    {
        // login表 start
        // 其它session的登录状态设置成（用户）在别处登录
        $this->clearLoginUser($user_info['id']);
        // 再更新login表信息
        $set = array(
            'user_id' => $user_info['id'],
            'user_name' => $user_info['name'],
            'user_type' => $user_info['type'],
            'status' => LOGIN_STATUS_LOGIN,
            'expire' => date('Y-m-d H:i:s', time() + 3600 * 24 * 30)
        );
        $where = array(
            'session_id' => $this->getSessionId()
        );
        $this->getLoginTable()->update($set, $where);
        // login表 end
    }

    /**
     * 更新用户设备表的数据
     *
     * @param unknown $user_info            
     * @param unknown $login            
     */
    private function updateDeviceUserTable($user_info)
    {
        $session_id = $this->getSessionId();
        $where = array(
            'session_id' => $session_id
        );
        $login = $this->getLoginTable()->getOne($where);
        
        // 清除用户与设备的联系，再把这个设备绑定上这个用户。
        $this->clearDeviceUser($user_info['id']);
        
        $set = array(
            'user_id' => $user_info['id'],
            'user_type' => $user_info['type']
        );
        $where = array(
            'device_token' => $login['device_token']
        );
        $this->getDeviceUserTable()->update($set, $where);
    }

    /**
     * 更新用户表信息：1最后登录时间
     *
     * @param unknown $user_info            
     */
    private function updateUserTable($user_info)
    {
        $set = array(
            'last_login_time' => $this->getTime()
        );
        $where = array(
            'id' => $user_info['id']
        );
        $this->getUserTable()->update($set, $where);
    }

    /**
     * 设置这个用户的其它设备登录状态为（用户）在别处登录
     * 用于登录时候使用。
     *
     * @param number $user_id            
     */
    private function clearLoginUser($user_id)
    {
        $sso_device_type = array();
        if (defined('SINGLE_SIGN_ON_TYPES') && SINGLE_SIGN_ON_TYPES)
        {
            $sso_device_type = explode(',', SINGLE_SIGN_ON_TYPES);
            if (! in_array($this->login->device_type, $sso_device_type))
            {
                return;
            }
        }
        
        $set = array(
            'status' => LOGIN_STATUS_OTHER_LOGIN
        );
        $where = new Where();
        if ($sso_device_type) {
            $where->in('device_type', $sso_device_type);
        }
        $where->equalTo('user_id', $user_id);
        $this->getLoginTable()->update($set, $where);
    }

    /**
     * 清除用户与设备的关联，
     * 用于登录时候使用。
     *
     * @param number $user_id            
     */
    public function clearDeviceUser($user_id)
    {
        $sso_device_type = array();
        if (defined('SINGLE_SIGN_ON_TYPES') && SINGLE_SIGN_ON_TYPES)
        {
            $sso_device_type = explode(',', SINGLE_SIGN_ON_TYPES);
            if (! in_array($this->login->device_type, $sso_device_type))
            {
                return;
            }
        }
        
        $set = array(
            'user_id' => 0
        );
        $where = new Where();
        if ($sso_device_type) {
            $where->in('device_type', $sso_device_type);
        }
        $where->equalTo('user_id', $user_id);
        $this->getDeviceUserTable()->update($set, $where);
    }

    /**
     * 保存链接图片到服务器
     *
     * @param unknown $user_pic
     * @return Ambigous <\Api21\Controller\multitype:multitype:multitype:multitype:unknown, multitype:multitype:multitype:multitype:unknown    multitype:unknown  >
     * @version 2014-12-9 WZ
     */
    public function saveUserPic($user_pic)
    {
        $zero = array(
            'ids' => array(0),
            'files' => array()
        );
        if (! $user_pic)
        {
            return $zero;
        }
        $this->file_key = 'file';
        $file = $this->getUrlImage($user_pic);
        if (! $file)
        {
            return $zero;
        }
        $source_file = array(
            $this->file_key => array(
                'name' => $user_pic,
                'type' => 0,
                'tmp_name' => $user_pic,
                'error' => 0,
                'size' => strlen($file),
                'data' => $file
            )
        );
        $data[] = $this->checkFileMd5($source_file);
        $files = $this->saveFileInfo($data);
        return $files;
    }

    /*
     * 用户登陆时执行，修改购物车商品所属
     *
     * #@author Waydy
     */
    public function shopCartLoginUpdate($user_id){
        $session_id = $this->getSessionId();
        
        //原购物车商品
        $cart_user_object = $this->getShopCartTable()->getAll(array(
            'user_id'=>$user_id,
        ));
        $cart_session_object = $this->getShopCartTable()->getAll(array(
            'user_id'=>0,
            'session_id'=>$session_id,
        ));
        $cart_user = array();
        foreach ($cart_user_object['list'] as $v){
            $cart_user[$v['goods_id'].'_'.$v['attr_id']] = $v;
        }
        $cart_session = array();
        foreach ($cart_session_object['list'] as $v){
            $cart_session[$v['goods_id'].'_'.$v['attr_id']] = $v;
        }
        
        //更新商品所属
        foreach ($cart_session as $k=>$v){
            if(isset($cart_user[$k])){//已存在
                list($goods_id,$attr_id) = explode('_', $k);
                $stock = $this->getShopStockTable()->getOne(array(
                    'goods_id'=>$goods_id,
                    'attr_id'=>$attr_id,
                ));
                if($stock){
                    $number = $cart_user[$k]['number'] + $cart_session[$k]['number'];
                    if($number > $stock['number']){
                        $number = $stock['number'];
                    }
                    $this->getShopCartTable()->updateData(array(
                        'number'=>$number,
                    ),array(
                        'user_id'=>$user_id,
                        'goods_id'=>$goods_id,
                        'attr_id'=>$attr_id,
                    ));
                }
                $this->getShopCartTable()->delete(array(
                    'user_id'=>0,
                    'session_id'=>$session_id,
                    'goods_id'=>$goods_id,
                    'attr_id'=>$attr_id,
                ));
            }else{
                $this->getShopCartTable()->updateData(array(
                    'user_id'=>$user_id,
                    'session_id'=>'',
                ), array(
                    'user_id'=>0,
                    'session_id'=>$session_id,
                ));
            }
        }
    }
}
