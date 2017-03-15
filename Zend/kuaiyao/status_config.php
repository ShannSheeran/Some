<?php
/*
 * STATUS字头
 * 协议返回用 start
 */
/*
 *  公共
 */
/** 1测试环境,2生产环境 
  * @var 1
  */
define('ENVIRONMENT_TYPE', 1);
// define('REQUEST_DESCRIPTION_TYPE',1);

/** 操作成功 @var 0 */
define('STATUS_SUCCESS' , '0' );
define('DESCRIPTION_0' , '操作成功');
define('REAL_DESCRIPTION_0' , '操作成功');
/** 未知错误 @var 1000 */
define('STATUS_UNKNOWN' , '1000' );
define('DESCRIPTION_1000' , '未知错误');
define('REAL_DESCRIPTION_1000' , '哎呀，您的网络打瞌睡啦！');
/** 协议版本过低，服务器已经不支持 @var 1001 */
define('STATUS_VERSION_LOW' , '1001' );
define('DESCRIPTION_1001' , '协议版本过低，服务器已经不支持');
define('REAL_DESCRIPTION_1001' , '您的版本过低啦，请尽快更新客户端！');
/** session id为空或不存在 @var 1002 */
define('STATUS_SESSION_EMPTY' , '1002' );
define('DESCRIPTION_1002' , 'session id为空或不存在');
define('REAL_DESCRIPTION_1002' , '哎呀，您的网络打瞌睡啦，请重新打开APP！'.'(1002)');
/** 验证码错误 @var 1003 */
define('STATUS_CAPTCHA_ERROR' , '1003' );
define('DESCRIPTION_1003' , '验证码错误');
define('REAL_DESCRIPTION_1003' , '验证码错误');
/** 请求参数不完整 @var 1004 */
define('STATUS_PARAMETERS_INCOMPLETE' , '1004' );
define('DESCRIPTION_1004' , '请求参数不完整');
define('REAL_DESCRIPTION_1004' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 没有获取设备号 @var 1005 */
define('STATUS_NO_DEVICETOKEN' , '1005' );
define('DESCRIPTION_1005' , '没有获取设备号');
define('REAL_DESCRIPTION_1005' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 设备号已经被绑定 @var 1006 */
define('STATUS_DEVICE_BOUND' , '1006' );
define('DESCRIPTION_1006' , '该设备已被绑定');
define('REAL_DESCRIPTION_1006' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 请求超时 @var 1010 */
define('STATUS_TIMEOUT' , '1010' );
define('DESCRIPTION_1010' , '请求超时');
define('REAL_DESCRIPTION_1010' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** 数据已删除 / 数据不存在 @var 1011 */
define('STATUS_NODATA' , '1011' );
define('DESCRIPTION_1011' , '数据已删除 / 数据不存在');
define('REAL_DESCRIPTION_1011' , '您访问的内容无法地球连接！');
/** session id 会话过期 @var 1012 */
define('STATUS_SESSION_TIMEOUT' , '1012' );
define('DESCRIPTION_1012' , 'session id 会话过期');
define('REAL_DESCRIPTION_1012' , '抱歉！请您重新登录账户！');
/** 未更新任何数据 @var 1013 */
define('STATUS_NOT_UPDATE' , '1013' );
define('DESCRIPTION_1013' , '未更新任何数据');
define('REAL_DESCRIPTION_1013' , '未更新任何数据');
/** 已经提交，不需要重复提交 @var 1014 */
define('STATUS_CAN_NOT_RESEND' , '1014' );
define('DESCRIPTION_1014' , '已经提交，不需要重复提交');
define('REAL_DESCRIPTION_1014' , '已经提交，不需要重复提交');
/** 短信发送失败 @var 1015 */
define('STATUS_SEND_SMSCODE_FAIL' , '1015' );
define('DESCRIPTION_1015' , '短信发送失败');
define('REAL_DESCRIPTION_1015' , '短信发送失败');
/** 数据包含敏感词汇 @var 1016 */
define('STATUS_SENSITIVE_WORD' , '1016' );
define('DESCRIPTION_1016' , '数据包含敏感词汇');
define('REAL_DESCRIPTION_1016' , '内容含有敏感词');
/** 安全验证不通过 @var 1017 */
define('STATUS_MD5' , '1017' );
define('DESCRIPTION_1017' , '安全验证不通过');
define('REAL_DESCRIPTION_1017' , '安全验证不通过');
/** 缓存数据可用 @var 1020 */
define('STATUS_CACHE_AVAILABLE' , '1020' );
define('DESCRIPTION_1020' , '缓存数据可用');
define('REAL_DESCRIPTION_1020' , '缓存数据可用');
/** 操作太快，请稍后再试 @var 1021 */
define('STATUS_TOO_FAST' , '1021' );
define('DESCRIPTION_1021' , '操作太快，请稍后再试');
define('REAL_DESCRIPTION_1021' , '操作太快，请稍后再试');
// 用户
/** （用户）未登录 @var 1100 */
define('STATUS_USER_NOT_LOGIN' , '1100' );
define('DESCRIPTION_1100' , '（用户）未登录');
define('REAL_DESCRIPTION_1100' , '抱歉！请您登录后再进行操作！');
/** 用户名或密码错误 @var 1101 */
define('STATUS_PASSWORD_ERROR' , '1101' );
define('DESCRIPTION_1101' , '用户名或密码错误');
define('REAL_DESCRIPTION_1101' , '密码错误');
/** （用户）标识非法 @var 1102 */
define('STATUS_USER_IDENTIFY_ILLEGAL' , '1102' );
define('DESCRIPTION_1102' , '（用户）标识非法');
define('REAL_DESCRIPTION_1102' , '哎呀，您的网络打瞌睡啦，请重新打开APP！');
/** （用户）不存在 @var 1103 */
define('STATUS_USER_NOT_EXIST' , '1103' );
define('DESCRIPTION_1103' , '（用户）不存在');
define('REAL_DESCRIPTION_1103' , '抱歉，该用户还没有注册！');
/** （用户）已存在 @var 1104 */
define('STATUS_USER_EXIST' , '1104' );
define('DESCRIPTION_1104' , '（用户）已存在');
define('REAL_DESCRIPTION_1104' , '抱歉，该用户已被人占用！');
/** （用户）已注销 @var 1105 */
define('STATUS_USER_CANCEL' , '1105' );
define('DESCRIPTION_1105' , '（用户）已注销');
define('REAL_DESCRIPTION_1105' , '抱歉，您的用户被禁用了！');
/** （用户）被锁定 @var 1106 */
define('STATUS_USER_LOCKED' , '1106' );
define('DESCRIPTION_1106' , '（用户）被锁定');
define('REAL_DESCRIPTION_1106' , '抱歉，您的用户被锁定了！');
/** （用户）在别处登录 @var 1107 */
define('STATUS_USER_OTHER_LOGIN' , '1107' );
define('DESCRIPTION_1107' , '（用户）在别处登录');
define('REAL_DESCRIPTION_1107' , '请注意，您的用户在别处登录了！');
/** （用户）个人认证还没通过 @var 1108 */
define('STATUS_USER_AUTH_FAIL' , '1108' );
define('DESCRIPTION_1108' , '（用户）个人资料未填写');
define('REAL_DESCRIPTION_1108' , '（用户）个人资料未填写');
/** （用户）财务认证未通过 @var 1109 */
define('STATUS_AUDIT_AUTH_FAIL' , '1109' );
define('DESCRIPTION_1109' , '（用户）财务认证未通过');
define('REAL_DESCRIPTION_1109' , '（用户）财务认证未通过');
/** （用户）身份证已经注册 @var 1110 */
define('STATUS_ID_NUMBER_EXIST' , '1110' );
define('DESCRIPTION_1110' , '（用户）身份证已经注册');
define('REAL_DESCRIPTION_1110' , '抱歉，该身份证已经注册！');
/** 身份证验证错误 @var 1111 */
define('STATUS_ID_NUMBER_ERROR' , '1111' );
define('DESCRIPTION_1111' , '身份证验证错误');
define('REAL_DESCRIPTION_1111' , '身份证验证错误');
/** （用户）已存在 @var 1112 */
define('STATUS_NICKNAME_EXIST' , '1112' );
define('DESCRIPTION_1112' , '（用户）昵称已存在');
define('REAL_DESCRIPTION_1112' , '抱歉，该昵称已被人占用！');
/** （公司）已存在 @var 1113 */
define('STATUS_COMPANY_NOTEXIST' , '1113' );
define('DESCRIPTION_1113' , '公司不存在');
define('REAL_DESCRIPTION_1113' , '抱歉，该公司不存在！');
/** （公司名）已存在 @var 1114 */
define('STATUS_COMPANY_NAMEEXIST' , '1114' );
define('DESCRIPTION_1114' , '公司名已存在');
define('REAL_DESCRIPTION_1114' , '抱歉，该公司名已存在！');
/** （公司）已存在 @var 1115 */
define('STATUS_COMPANY_NOPOWER' , '1115' );
define('DESCRIPTION_1115' , '没有权限修改');
define('REAL_DESCRIPTION_1115' , '抱歉，您没有权限修改！');
/*
 * 文件
 */
/** 文件大小超过限制 @var 1200 */
define('STATUS_FILESIZE_EXCEEDS_LIMIT' , '1200' );
define('DESCRIPTION_1200' , '文件大小超过限制');
define('REAL_DESCRIPTION_1200' , '您上传的内容不能超过2M！');
/** 文件类型非法 @var 1201 */
define('STATUS_FILETYPE_ILLEGAL' , '1201' );
define('DESCRIPTION_1201' , '文件类型非法');
define('REAL_DESCRIPTION_1201' , '哎呀，不要上传别的内容。');
/** 文件不存在 @var 1202 */
define('STATUS_FILE_LOST' , '1202' );
define('DESCRIPTION_1202' , '文件不存在');
define('REAL_DESCRIPTION_1202' , '文件不存在');
/*
 * 其它
 */
/** 没有返回状态码 @var 9000 */
define('STATUS_NOSTATUS' , '9000' );
define('DESCRIPTION_9000' , '没有返回状态码');
define('REAL_DESCRIPTION_9000' , '有重大更新，请更新客户端。');
/** 协议格式不正确 @var 9001 */
define('STATUS_INCORRECT_FORMAT' , '9001' );
define('DESCRIPTION_9001' , '请求参数格式不正确');
define('REAL_DESCRIPTION_9001' , '有重大更新，请更新客户端。');
/** 协议不存在 @var 9002 */
define('STATUS_NO_PROTOCOL' , '9002' );
define('DESCRIPTION_9002' , '协议不存在');
define('REAL_DESCRIPTION_9002' , '有重大更新，请更新客户端。');
/** 提现协议 金额不能为零 @var 9003 */
define('STATUS_MONEYZONE' , '9003' );
define('DESCRIPTION_9003' , '提现金额不能为零。');
define('REAL_DESCRIPTION_9003' , '提现金额不能为零。');
/** 提现协议 金额必须大于用户个人账户金额 @var 9004 */
define('STATUS_LACKMONEY' , '9004' );
define('DESCRIPTION_9004' , '余额不足。');
define('REAL_DESCRIPTION_9004' , '余额不足。');
/** 提现协议 提现数据插入数据库失败 @var 9005 */
define('STATUS_WITHDRAWFAIL' , '9005' );
define('DESCRIPTION_9005' , '提现申请失败。');
define('REAL_DESCRIPTION_9005' , '提现申请失败。');
/** 提现协议 金额不能不能小于零 @var 9006 */
define('STATUS_MONEYOVERZONE' , '9006' );
define('DESCRIPTION_9006' , '提现金额不能小于零。');
define('REAL_DESCRIPTION_9006' , '提现金额不能小于零。');
/** 提现协议 缺少银行卡号 @var 9007 */
define('STATUS_LACKCARDNUMBER' , '9007' );
define('DESCRIPTION_9007' , '请输入银行卡号。');
define('REAL_DESCRIPTION_9007' , '请输入银行卡号。');
/** 提现协议 缺少银行卡号 @var 9008 */
define('STATUS_LACKCARDOWNER' , '9008' );
define('DESCRIPTION_9008' , '请输入开卡人姓名。');
define('REAL_DESCRIPTION_9008' , '请输入开卡人姓名。');
/** 提现协议 缺少银行卡号 @var 9009 */
define('STATUS_LACKBANKID' , '9009' );
define('DESCRIPTION_9009' , '请选择交易银行。');
define('REAL_DESCRIPTION_9009' , '请选择交易银行。');
/*
 * 协议返回用 end
 */
/*
 * 通用 start
 */
/** 打开 @var 1 */
define('OPEN_TRUE',1);
/** 关闭 @var 2 */
define('OPEN_FALSE',2);
/** 非删除 @var 0 */
define('DELETE_FALSE' , '0' );
/** 删除 @var 1 */
define('DELETE_TRUE' , '1' );
/** 正常 @var 1 */
define('STATUS_NORMAL' , '1' );
/** 停用 @var 2 */
define('STATUS_STOP' , '2' );
/*
 * 通用 end
 */
/*
 * login 表 LOGIN_STATUS字头
 * 登录状态 start
 */
/** 临时 @var 1 */
define('LOGIN_STATUS_TEMP' , '1' );
/** 登录 @var 2 */
define('LOGIN_STATUS_LOGIN' , '2' );
/** 登出 @var 3 */
define('LOGIN_STATUS_LOGOUT' , '3' );
/** （用户）在别处登录 @var 4 */
define('LOGIN_STATUS_OTHER_LOGIN' , '4' );
/*
 * 登录状态 end
 */
/*
 * device_user 表 DEVICEUSER_STATUS 字头
* 登录状态 start
*/
/** 开启 @var 1 */
define('DEVICEUSER_STATUS_OPEN' , '1' );
/** 关闭 @var 2 */
define('DEVICEUSER_STATUS_CLOSE' , '2' );
/*
 * 登录状态 end
*/
/*
 * comment 表 COMMENT_STATUS 字头
* 评论状态 start
*/
/** 评论状态：正常 @var 1 */
define('COMMENT_STATUS_NORMAL' , '1' );
/** 评论：审核 @var 2 */
define('COMMENT_STATUS_AUDIT' , '2' );
/** 评论：忽略 @var 3 */
define('COMMENT_STATUS_IGNORE' , '3' );
/*
 * 评论状态 end
*/
/*
 * user 表 USER_TYPE 字头
* 用户类型 start
*/
/** 手机注册 @var 1 */
define('USER_TYPE_MOBILE' , '1' );
/** 第三方登录：QQ @var 2 */
define('USER_TYPE_QQ' , '2' );
/** 第三方登录：新浪微博 @var 3 */
define('USER_TYPE_SINA' , '3' );


/*
 * 个人消息类型
 */
/**
 * 系统通知
 */
define('NOTIFICATION_TYPE_SYSTEM', 0);
/**
 * 任务相关
 */
define('NOTIFICATION_TYPE_TASK', 1);
/**
 * 活动通知
 */
define('NOTIFICATION_TYPE_EVENT', 2);
/**
 * 举报通知
 */
define('NOTIFICATION_TYPE_REPORT', 3);
/**
 * 投诉通知
 */
define('NOTIFICATION_TYPE_COMPLAINT', 4);

/*
 * 一些限制
*/
/**
 * 整数的极限
 */
define('LIMIT_INTEGER', 2147483647);
/**
 * 小数的极限
 */
define('LIMIT_DECIMAL', 99999999.99);
