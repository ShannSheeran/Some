<?php
/**
 * Created by PhpStorm.
 * User: sheeran
 * Date: 2017/1/3
 * Time: 14:28
 */

namespace Api\Controller;


class BaseController extends \Common\Common\Controller\BaseController
{
    public function __construct()
    {
        parent::__construct();
        $login_user = $this->requireAuth();
        $this->_login_user = $login_user;
        $this->_login_user_id = $login_user['user_id'];
        $this->_login_user_type = $login_user['type'];
    }
}