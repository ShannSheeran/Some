<?php
namespace Api\Controller;
use Api\Controller\Request\UserRequest;
use Core\System\AiiPush\AiiPush;
use Core\System\AiiPush\AiiMyFile;

/**
 * 重置密码
 */
class UserResetPassword extends User
{

    public function __construct()
    {
        $this->myRequest = new UserRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();

        // 验证短信
        $this->checkSmsComplete(self::MOBILE_VALIDATE_TYPE_RESET, $request->smscode_id, $request->mobile);
        
        $user_info = $this->getUserTable()->getOne(array(
            "name" => $request->name
        ));
        
        /* 用户不存在 */
        if (! $user_info) {
            return STATUS_USER_NOT_EXIST; // 1103
        } else 
        {
            $randStr = str_shuffle('qwertyuiopasdfghjklzxcvbnm1234567890');
            //$password = substr($randStr,0,6);
            $password = 123456;
            $content = '尊敬的客户，您的吉宏园艺用户账号 '.$user_info['name'].'，在'.date('Y年-m月-d日 h时:m分:s秒').'进行了密码重置，新密码为'.$password.'请勿泄露。如非本人操作，请及时与吉宏联系。';
            $set = array(
                "password" => md5($password)
            );
            $where = array(
                "id" => $user_info["id"]
            );
            $this->getUserTable()->update($set, $where);
            $this->smsPush($content, array($request->mobile));
            return STATUS_SUCCESS;
        }
        return STATUS_UNKNOWN; //1000
    }
    
    /**
     * 发送新密码
     *
     * @author WZ
     * @param unknown $content
     * @param array $mobile
     * @return multitype:boolean
     */
    public function smsPush($content, array $mobile)   
    {
        $push = new AiiPush();
        $return = array();
        foreach ($mobile as $m)
        {
            if (SMSCODE_SWITCH)
            {
                if ($m)
                {
                    $result = array('success' => array(1));
                    $result = $push->pushSingleDevice($m, 8, $content);
                    $return[] = $result['success'] ? true : false;
                }
                else
                {
                    $return[] = false;
                }
            }
            else
            {
                $return[] = true;
            }
            if (PUSH_LOG_SWITCH)
            { // 开启了推送与短信的日志记录
                if (isset($result))
                {
                    if ($result)
                    {
                        $temp = '短信，短信发送成功， mobile：' . $m . '，content：' . $content;
                    }
                    else
                    {
                        $temp = '短信，短信发送失败不能进行验证， mobile：' . $m . '，content：' . $content;
                    }
                }
                else
                {
                    $temp = '短信，没有开启短信发送，mobile：' . $m . '，content：' . $content;
                }
                if(! isset($file))
                {
                    $file = new AiiMyFile();
                }
                $file->setFileToPublicLog();
                $file->putAtStart($temp);
            }
        }
        return $return;
    }
}