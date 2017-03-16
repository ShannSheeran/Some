<?php

namespace Common\Common;


class ErrorCode
{

    //==================================系统相关====================================
    /**
     * 预定义状态码，1为正确返回，小于0为对应的错误，
     * 自定义错误码必须从-1000以后开始定义，
     * 并以模块名称为前缀防止重名（如优惠券模块:COUPON_XX)
     */
    const SUCCESS = 1;                              //成功状态
    const SYS_REQUEST_METHOD_ERROR = -1;            // 请求方法错误
    const SYS_REQUEST_PARAMS_ERROR = -2;            // 请求参数错误
    const SYS_REQUEST_PARAMS_MISSING = -3;          // 缺少请求参数
    const SYS_SYSTEM_ERROR = -100;                  // 系统异常
    const SYS_USER_VERIFY_FAIL = -402;              // 用户认证失败，请重新登录
    const SYS_INTERFACE_NOT_EXIST = -404;           // 接口不存在
    const SYS_DB_ERROR = -999;                      // 数据库操作错误
    const SYS_DATA_NOT_EXISTS = -1000;              // 您查找数据不存在

    // 预定义错误信息
    public static $systemMessage = [
        //==================================系统相关====================================
        self::SUCCESS => '',
        self::SYS_REQUEST_METHOD_ERROR => '请求方法错误',
        self::SYS_REQUEST_PARAMS_ERROR => '请求参数错误',
        self::SYS_REQUEST_PARAMS_MISSING => '缺少请求参数',
        self::SYS_SYSTEM_ERROR => '系统繁忙，请稍后再试',
        self::SYS_USER_VERIFY_FAIL => '用户认证失败，请重新登录',
        self::SYS_INTERFACE_NOT_EXIST => '接口不存在',
        self::SYS_DB_ERROR => '数据库操作错误',
        self::SYS_DATA_NOT_EXISTS => '您查找数据不存在',
    ];

    // 自定义的错误信息放到该数组下，参考systemMessage
    public static $customMessage = [];

    // 包括预定义与自定义的错误信息
    protected static $errorMessage = null;

    public static function getAllErrorMessage()
    {
        static::initErrorMessage();
        return static::$errorMessage;
    }

    public static function getMessage($status)
    {
        static::initErrorMessage();
        return isset(static::$errorMessage[$status]) ? static::$errorMessage[$status] : static::$errorMessage[static::SYSTEM_ERROR];
    }

    private static function initErrorMessage()
    {
        if (!static::$errorMessage) {
            $error_code = MODULE_NAME . '\\Common\\ErrorCode';
            if (!class_exists($error_code)) {
                $error_code = static::class;
            }
            static::$errorMessage = static::$systemMessage + $error_code::$customMessage;
        }
    }
}
