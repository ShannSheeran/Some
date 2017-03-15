<?php
/*
 * 关于此项目独有常量
 */
/**
 * 名片原价
 */
define('GOODS_PRICE', 198);

/**
 * 名片优惠价原价
 */
define('GOODS_DISCOUNT_PRICE', 168);

/**
 *  名片标题
*/
define('GOODS_TITLE', "快摇名片用户体验群");

/**
 *  名片内容
 */
define('GOODS_CONTENT',"微信扫一扫  加入群聊");

//支付宝配置信息
define('ALIPAY_PARTNER', '2088021690058425'); //合作身份者id，以2088开头的16位纯数字
define('ALIPAY_KEY', 'dfvcfn4gfau75njvtfh8el8icv0lsf5d'); //安全检验码，以数字和字母组成的32位字符
define('ALIPAY_SELLER_EMAIL', '2334131157@qq.com'); //卖家支付宝帐户
define('ALIPAY_NOTIFY_URL', ' http://2009.aiitec.net:88/kuaiyao/public/api/v5/api/index/getAlipayNotify');   //服务器异步通知页面路径   需http://格式的完整路径，不能加?id=123这类自定义参数
define('ALIPAY_RETURN_URL', 'http://2009.aiitec.net:88/kuaiyao/public/api/v5/api/index/getAlipayReturn');    //页面跳转同步通知页面路径需http://格式的完整路径，不能加?id=123这类自定义参数

/* 微信APP支付 */
/**
 * 支付模式，1原价，2测试价0.01
 * @var 1
 */
define('WX_APP_PAY_MODE', 2);
define('WX_APP_APPID' , 'wxbc13859af58f6b80');//微信应用ID
define('WX_APP_APPSECRET','d4624c36b6795d1d99dcf0547af5443d');//微信应用钥匙
define('WX_APP_MCHID','1292931701'); //商户号
define('WX_APP_PRIVATEKEY','wxbc13859af58f6b80bc13859af58f6b'); //私钥
define('WX_APP_NOTIFY_URL','http://2009.aiitec.net:88/kuaiyao/public/api/v5/api/index/getWxPayNotify'); //异步回调
/* http://2009.aiitec.net:88/kuaiyao/public/api/v5/api/index/getWxPayNotify */
/* http://api.kuaiyao.name/v5/api/index/getWxPayNotify */
/*WX_APP_NOTIFY_URL','http://api.kuaiyao.name/v5/api/index/getWxPayNotify*/
define('YL_APP_PAY_MODE', 1);