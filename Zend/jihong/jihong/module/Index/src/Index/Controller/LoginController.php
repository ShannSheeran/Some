<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Api\Controller\UserResetPassword;
use Api\Controller\CommonController as Api;
use Admin\Controller\CommonController;

class LoginController extends CommonController
{
    public function indexAction()
    {
        $login_number = isset($_SESSION['login_number']) ? $_SESSION['login_number'] : '';
        $view = new ViewModel(array(
            'login_number' => $login_number,
        ));
        $view->setTemplate('index/login/sylogin');
        return $view;
    }
    
    public function loginAction()
    {
        if($_POST)
        {
            $name =  (! get_magic_quotes_gpc ()) ? addslashes (trim($_POST['name'])) : trim($_POST['name']) ; 
            $password =   trim($_POST['password']);
            $type = $_POST['type'];
            
            $result = array();
            
            if (isset($_SESSION['login_number']) && $_SESSION['login_number'] > 2)
            {
                $captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
                if ($_SESSION['captcha'] != $captcha) {
                    $result['status'] = 1;
                    $result['description'] = '验证码错误';
                    $result['login_number'] = $_SESSION['login_number'];
                    echo  json_encode($result);
                    die;
                }
            }
            
            $info = $this->getUserTable()->getOne(array(
                'name' => $name,
                'password' => $password, 
                'type' => $type,
                'register_status' => 3,
                'status' => 1,
             ));
            
            if($info)
            {
                $_SESSION['login_number'] = 0;
                $_SESSION['index_user_id'] = $info['id'];
                $_SESSION['index_name'] = $info['name'];
                $_SESSION['user_type'] = $info['type'];
                //setcookie('index_user_id',$info['id'],time()+3600*24*30,ROOT_PATH);
                //setcookie('index_name',$info['name'],time()+3600*24*30,ROOT_PATH);
                //setcookie('mobile',$info['mobile'],time()+3600*24*30,ROOT_PATH);
                //setcookie('code',md5($info['id'].$info['mobile'].$info['status']),time()+3600*24*30,ROOT_PATH);
                $result['status'] = 0;
                $result['description'] = '登陆成功';
                //更新最后一次登录时间
                $set = array(
                    'last_login_time' => $this->getTime()
                );
                $where = array(
                    'id' => $info['id']
                );
                $this->getUserTable()->update($set, $where);
                //return $this->redirect()->toRoute();
            }
            else
            {
                $_SESSION['login_number'] = isset($_SESSION['login_number']) ? $_SESSION['login_number'] + 1 : 1;
                $result['status'] = 1;
                $result['description'] = '用户名或密码错误';
                $result['login_number'] = $_SESSION['login_number'];
                //$url = $this->plugin('url')->fromRoute('index', array('controller' => 'index' ,'action' => 'index'));
                //$this->domainRedirection($url, 3,'你的账号或密码有误,3秒后跳转到登录页面');
            }
            echo json_encode($result);
            die;
        }
    }
    
    public function logoutAction()
    {
        session_destroy();
		return $this->redirect()->toRoute('index', array(
				'controller' => 'login' ,
		        'action' => 'index',
		));
    }
    
    public function forgetPasswordAction()
    {
        $captcha = $this->params()->fromPost('code' ,'');
        $timestamp = $this->params()->fromPost('timestamp');
        if($captcha)
        {
            $information = array();
            if ($_SESSION['captcha'] != $captcha) {
                $information['status'] = 2;
                $information['title'] = '验证码错误';
                $information['description'] = '很抱歉，您输入的验证码有误，请输入正确的验证码';
                echo json_encode($information);
                die();
            }
            
            $name = (! get_magic_quotes_gpc ()) ? addslashes (trim($this->params()->fromPost('name' , ''))) : trim($this->params()->fromPost('name' , '')) ; 
            $mobile = htmlspecialchars($this->filterWords($this->params()->fromPost('mobile' , '')));
            $type = (int)$this->params()->fromPost('type' ,'');
            $user_info = $this->getUserTable()->getOne(array('name' => $name ,'type' =>$type ,'status'=>1 ,'register_status'=>3 , 'delete' => DELETE_FALSE ));
            if($user_info)
            {
                if($user_info->mobile != $mobile)
                {
                    $information['status'] = 2;
                    $information['title'] = '手机号码有误或不存在';
                    $information['description'] = '很抱歉，您输入的手机号码有误，请输入正确的手机号码';
                    echo json_encode($information);
                    die();
                }
                
                $randStr = str_shuffle('qwertyuiopasdfghjklzxcvbnm1234567890');
                $password = substr($randStr,0,6);
                $content = sprintf(TEMPLATE_RESET_PASSWORD, $user_info['name'] , date('Y年-m月-d日 h时:m分:s秒') , $password );
                $set = array(
                    "password" => md5($password)
                );
                $where = array(
                    "id" => $user_info["id"]
                );
                $this->getUserTable()->update($set, $where);
                UserResetPassword::smsPush($content, array($request->mobile));
                $information['status'] = 1;
                $information['title'] = '密码已重置';
                $information['description'] = '登录密码已发送至您的手机，请查看短信';
                echo json_encode($information);
                die;
            }
            else 
            {
                $information['status'] = 2;
                $information['title'] = '账号有误或不存在';
                $information['description'] = '很抱歉，您输入的账号不存在，请输入正确的账号信息';
                echo json_encode($information);
                die();
            }
        }
        
        $view = new ViewModel(array());
        $view->setTemplate('index/login/forget_password');
        return $view;
    }
    
    public function registerAction()
    {
        $view = new ViewModel(array(

        ));
        $view->setTemplate('index/login/register');
        return $view;
    }

    public function findCodeAction()
    {
        $view = new ViewModel(array(

        ));
        $view->setTemplate('index/login/retrievePassword');
        return $view;
    }

    public function passAction()
    {
        $where = array(
            'mobile' => isset($_POST['mobile']) ? $_POST['mobile'] : '',
        );
        $info = $this->getUserTable()->getOne($where);
        if(isset($_POST['submit']) && $_POST['submit'])
        {

            $mobile = $_POST['mobile'];
            $pass   = trim($_POST['pass']);
            $url    = $this->plugin('url')->fromRoute('index', array('controller' => 'login', 'action' => 'findCode'));
            if($mobile && $pass)
            {
                $user = $this->getUserTable()->getOne(array('mobile' =>$mobile));

                if($user)
                {
                    $back = $this->getUserTable()->updateData(array('password' => MD5($pass), 'timestamp_update' =>$this->getTime()), array('id' => $user['id']));
                    if($back)
                    {
                        return $this->redirect()->toRoute('index', array('controller' => 'login', 'action' => 'password'));
                    }
                }
                else
                {
                    $this->domainRedirection($url, 3, '操作失败，即将返回密码找回页面');
                }
            }
            else
            {
                $this->domainRedirection($url, 3, '操作失败，即将返回密码找回页面');
            }
        }

        $view = new ViewModel(array(
            'info' => $info
        ));
        $view->setTemplate('index/login/retrievePassword2');
        return $view;
    }

    public function passwordAction()
    {


        $view = new ViewModel(array(

        ));
        $view->setTemplate('index/login/retrievePassword3');
        return $view;
    }

    public function successAction()
    {
        $view = new ViewModel(array(

        ));
        $view->setTemplate('index/login/success');
        return $view;
    }

    public function checkAction()
    {
        $mobile    = $_POST['mobile'];
        $password  = MD5(trim($_POST['pass']));
        $info      = $this->getUserTable()->getOne(array('mobile' => $mobile));

        if($info)
        {
            if($info['password']!=$password)
            {
                $result = array(
                    'status' => 2,
                    'msg'    => '密码错误'
                );
                $this->apiExit($result);
            }
            else
            {
                $result = array(
                    'status' => 1,
                    'msg'    => '操作成功'
                );
                $this->apiExit($result);
            }
        }
        else
        {
            $result = array(
                'status' => 3,
                'msg'    => '用户不存在'
            );
            $this->apiExit($result);
        }
    }




    public function validateCheckAction()
    {
        /**
         * 注册获取验证码
         */
        if (isset($_POST['register_code']) && $_POST['register_code'])
        {
            $mobile=$_POST['register_code'];
            $cap=$_POST['cap'];
            if(empty($cap) || ($cap != $_SESSION['captcha'])){
                echo json_encode(array('status' => 1));
                exit;
            }
            if ($mobile)
            {
                $json =array(
                    'q'=>array(
                        'a'=>1,
                        'type'=>1,
                        'mobile'=>$mobile
                    )
                );
            }
        }

        /**
         * 登陆获取验证码
         */
        if (isset($_POST['login_code']) && $_POST['login_code'])
    {
        $mobile=$_POST['login_code'];
        $cap=$_POST['cap'];
        if(empty($cap) || ($cap != $_SESSION['captcha'])){
            echo json_encode(array('status' => 1));
            exit;   
        }
        if ($mobile)
        {

            $json =array(
                'q'=>array(
                    'a'=>1,
                    'type'=>2,
                    'mobile'=>$mobile
                )
            );
        }
    }
        /*
         * 注册登陆
         */
        if (isset($_POST['submit_register']) && $_POST['submit_register'])
        {
            $mobile = $_POST['submit_register'];
            $code = $_POST['code'];
            $cap=$_POST['cap'];
            if(empty($cap) || ($cap != $_SESSION['captcha'])){
                echo json_encode(array('status' => 1));
                exit;
            }
            if ($mobile)
            {
                $this->getAdminController()->statOporation(1,1);
                $json =array(
                    'q'=>array(
                        'a'=>2,
                        'type'=>1,
                        'mobile'=>$mobile,
                        'where'=>array(
                            'code'=>$code
                        ),
                    )
                );
            }
        }
        /*
         * 直接登陆
         */
        if (isset($_POST['submit_login']) && $_POST['submit_login'])
        {
            $mobile=$_POST['submit_login'];
            $code = $_POST['code'];
            $cap=$_POST['cap'];
            if(empty($cap) || ($cap != $_SESSION['captcha'])){
                echo json_encode(array('status' => 1));
                exit;
            }
            if ($mobile)
            {
                $json =array(
                    'q'=>array(
                        'a'=>2,
                        'type'=>2,
                        'mobile'=>$mobile,
                        'where'=>array(
                            'code'=>$code
                        ),
                    )
                );
            }
        }

      /*   if (isset($_POST['phone']) && $_POST['phone'])
        {
            $mobile=$_POST['phone'];
            if ($mobile)
            {

                $json =array(
                    'q'=>array(
                        'a'=>1,
                        'type'=>4,
                        'mobile'=>$mobile
                    )
                );
            }
        }

        if (isset($_POST['resetPass']) && $_POST['resetPass'])
        {
            $mobile=$_POST['resetPass'];
            if ($mobile)
            {

                $json =array(
                    'q'=>array(
                        'a'=>1,
                        'type'=>6,
                        'mobile'=>$mobile
                    )
                );
            }
        } */

        $_REQUEST['json'] = json_encode($json);
        $api = new SMSCode();
        $api->index();
        $result = $api->response(null, true);

         $result = json_decode($result, true);
        //$this->p(json_encode($result));

        if ($result['q']['s'] == 0 && ((isset($_POST['submit_login']) && $_POST['submit_login']) || (isset($_POST['submit_register']) && $_POST['submit_register']))) {
            $user_info = $this->getUserTable()->getOne(array('id'=>$result['q']['id']));
            if(isset($_POST['password']) && $_POST['password'])
            {
                $pass = MD5(trim($_POST['password']));
                $this->getUserTable()->updateData(array('password' => $pass), array('id' => $user_info['id']));
            }
            setcookie('index_user_id',$result['q']['id'],time()+3600*24*30,ROOT_PATH);
            setcookie('name',$user_info['name'],time()+3600*24*30,ROOT_PATH);
            setcookie('mobile',$user_info['mobile'],time()+3600*24*30,ROOT_PATH);
            setcookie('code',md5($result['q']['id'].$user_info['mobile'].$user_info['status']),time()+3600*24*30,ROOT_PATH);
        }
        //echo json_encode($result);die;

        if($result['q']['s'] == 0)
        {
            echo json_encode(array('status' => $result['q']['s'], 'msg'=> $result['q']['d'],));
        }


        exit();
    }
}
?>