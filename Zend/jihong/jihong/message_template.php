<?php

/*
 * 短信和推送模版配置文件。
 */
/**
 * 短信验证码。
 * 
 * @var String
 */
define('TEMPLATE_SMS_CAPTCHA', '验证码：%s，10分钟内使用有效。');


/*
 * 下面是推送的 为了方便调用，用数字做编号， 具体查看相关文档。
 * 1money数字，2point数字，3number数字，4name字符串，5name2字符串2
 */
define('TEMPLATE_PUSH_CONTENT_1', '您提交的提现申请未通过审核！金额为：%1$s，未通过审核原因：%4$s');
define('TEMPLATE_PUSH_TITLE_1', '提现提醒');

define('TEMPLATE_PUSH_CONTENT_2', '您提交的提现申请通过审核！金额为：%1$s。');
define('TEMPLATE_PUSH_TITLE_2', '提现提醒');

/**
 * 重置密码
 * 1用户账号；2发送时间Y年-m月-d日 h时:m分:s秒；3密码
 */
define('TEMPLATE_RESET_PASSWORD', '尊敬的客户，您的吉宏园艺用户账号 %1$s，在%2$s进行了密码重置，新密码为%3$s请勿泄露。如非本人操作，请及时与吉宏联系。');
