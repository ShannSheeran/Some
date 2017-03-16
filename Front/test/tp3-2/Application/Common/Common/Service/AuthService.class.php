<?php
/**
 * File: AuthService.class.php
 * User: xieguoqiu
 * Date: 2016/11/22 15:52
 */

namespace Common\Common\Service;

use Common\Common\Service\AuthService\AuthInterface;
use Common\Common\Service\AuthService\User;

class AuthService
{

    private static $auth;
    private static $model;

    /**
     * @param $model
     * @return AuthInterface
     * @throws \Exception
     */
    public static function create($model)
    {
        !isset(static::$model) && (static::$model = $model);
        switch (strtolower(static::$model)) {
            case 'user':
                if (!isset(static::$model)) {
                    static::$auth = new User();
                }
                break;
            default:
                throw new \Exception('Model not exists');
        }
        return static::$auth;
    }

    /**
     * 获取最后使用的认证类型
     * @return string
     */
    public static function getAuthModelName()
    {
        return static::$model;
    }

    /**
     * @return AuthInterface
     */
    public static function getAuth()
    {
        return static::$auth;
    }
    
}
