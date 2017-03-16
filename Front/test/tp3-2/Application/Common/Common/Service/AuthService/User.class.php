<?php
/**
 * File: User.class.php
 * User: xieguoqiu
 * Date: 2016/11/22 15:11
 */

namespace Common\Common\Service\AuthService;

class User implements AuthInterface, \ArrayAccess
{
    
    use AuthDataTrait;

    public function loadData($id)
    {
        $this->data = D('User')->where(['id' => $id, 'is_delete' => 0])->find();
    }
}
