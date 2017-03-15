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

/**
 * 成功购买设备
 *
 * @var String
 */
define('TEMPLATE_SMS_BUY', '恭喜您！您已免费获得由我们送出的%s个K码！凭K码购买产品立省30元，每激活一个K码返现30元！');

/**
 * 邀请码被使用短信模板
 *
 * @var String
 */
define('TEMPLATE_SMS_INVITATION', '恭喜您！您的K码成功激活%s个，您已获得返现%s元，请安装“快摇名片”APP领取吧！');

/**
 * 提现
 * 
 * @var String
 */
define('TEMPLATE_SMS_WITHDRAW', '您的提现申请成功提交！预计24小时内到账（节假日延顺）！温馨提示：K码激活越多，收益越高！');

/**
 * 注册
 *
 * @var String
 */
define('TEMPLATE_SMS_REGISTER', '您好，欢迎您登记快摇名片，工作人员将会在48小时之内联系您并帮您完善名片资料，敬请留意，快摇名片祝您生活愉快！');

/*
 * 下面是推送的 为了方便调用，用数字做编号， 具体查看相关文档。
 * 1money数字，2point数字，3number数字，4name字符串，5name2字符串2
 */
define('TEMPLATE_PUSH_CONTENT_1', '您提交的提现申请未通过审核！金额为：%1$s，未通过审核原因：%4$s');
define('TEMPLATE_PUSH_TITLE_1', '提现提醒');

define('TEMPLATE_PUSH_CONTENT_2', '您提交的提现申请通过审核！金额为：%1$s。');
define('TEMPLATE_PUSH_TITLE_2', '提现提醒');
