<?php
// 目录路径配置
define("SYS_PATH", dirname(__DIR__)); // 根目录
define("APP_PATH", __DIR__); // 系统目录
@define('ROOT_PATH', $_SERVER['REDIRECT_BASE']); // 相对路径
                                                // $_SERVER['REDIRECT_BASE']
define("PBL_NAME", '/' . 'kuaiyao'); // 项目名称
define("UPLOAD_PATH", "uploadfiles/"); // 上传文件的路径
define("THUMB_IMAGE_PATH", APP_PATH . "/public/uploadfiles/thumb"); // 图片路径
define("HTTP", isset($_SERVER['SERVER_NAME']) ? 'http://' . $_SERVER['SERVER_NAME'] : ''); // 服务器路径
define("QRCODE_DIR", "qrcode/");
define("VCARD_DIR", "vcard/");
define("QRCODE_PATH", APP_PATH . '/' . 'public/' . UPLOAD_PATH . QRCODE_DIR);
define("VCARD_PATH", APP_PATH . '/' . 'public/' . UPLOAD_PATH . VCARD_DIR);
define("HTML_PATH", HTTP . '/static_html/'); // 外网访问HTML静态页面路径
                                          // '192.168.1.140'
define("PREVIEW_PATH", HTTP . ROOT_PATH . 'preview/'); // 查看模版路径
define("SAVE_TEMPLATE_PATH", APP_PATH . '/public/static_html/'); // 保存静态页面路径
define("WX_PAGE_URL", 'http://www.kuaiyao.name/'); //微信链接页面域名
/*
 * module配置
 */
define("SERVER_NAME",isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ''); // 域名
define('MODULE_API', '' == SERVER_NAME ? '/' : '/api');
define('MODULE_ADMIN', '' == SERVER_NAME ? '/' : '/admin');
define('MODULE_INDEX', '' == SERVER_NAME ? '/' : '/');


 //数据库配置
define("DB_HOST", '2009.aiitec.net'); // 数据链接IP 2009.aiitec.net
define("DB_NAME", 'kuaiyao'); // 数据库名
define("DB_USER", 'root'); // 数据库用户名
define("DB_PASSWORD", 'aiitecphp2009'); // 数据密码   aiitecphp2009
define("DB_PREFIX", 'ky_'); // 数据表前缀
define("DB_CHARSET", 'utf8mb4'); // 数据库编码utf8mb4
define("DB_SET_NAME", 'UTF8MB4'); // 数据库编码UTF8MB4

// 图片上传设置
define('THUMB_WIDTH', '560'); // 4:3缩略图宽
define('THUMB_HEIGHT', '420'); // 4:3缩略图高
define('THUMB_WIDTH_2', '600'); // 5:3缩略图宽
define('THUMB_HEIGHT_2', '360'); // 5:3缩略图高
define('THUMB_WIDTH_3', '66'); // 1:1缩略图宽
define('THUMB_HEIGHT_3', '66'); // 1:1缩略图高

//微信API配置

define('WEIXIN_APP_ID' , 'wx3c6ecca291e658d8');//微信应用ID
define('WEIXIN_APP_SECRET','0c24476a1c7e485220fdbbca46899701');//微信应用钥匙
define('WEIXIN_MCHID','1254222001'); //商户号
define('WEIXIN_PRIVATEKEY','wx3c6ecca291e658d83c6ecca291e658'); //私钥
define('WX_NOTIFY_URL','http://2009.aiitec.net:88/kuaiyao/public/api/v5/api/index/getWxPayNotify'); //异步回调
/**
 * 1本地保存，2服务器保存，3=1+2
 */
define('IMAGE_SAVE_MODE', 1);
/**
 * 服务器保存地址
 * 
 * @todo
 *       上线后要调整
 */
// define('IMAGE_SERVER',
// 'http://192.168.1.29/image/project/kuaiying/');
define('IMAGE_SERVER', 'http://192.168.1.12/php/image/project/kuaiyao/');
/**
 * 本地保存图片的目录
 * 
 * @var unknown
 */
define('LOCAL_SAVEPATH', APP_PATH . '/public/uploadfiles/');

// memcache
// 服务端配置
define("MEMCACHE_HOST", '192.168.1.12'); // 地址
define("MEMCACHE_PORT", '11211'); // 端口
define('MEMCACHE_CACHE_TIME', 5); // 缓存失效时间：单位秒
define("IS_MEMCACHE", 0); // 是否开启memcache
                          
// 分页设置
define('PAGE_NUMBER', 20);

/**
 * 单个设备登录
 */
define('SINGLE_SIGN_ON_TYPES', '1,2,4');

/**
 * 协议是否开启md5验证
 * 
 * @var boolean
 */
define('CHECK_API_MD5', false);
/**
 * 推送的开关
 * true：可以触发推送
 * false：不可以推送，都反馈推送成功。
 * 
 * @var true|false
 */
define('PUSH_SWITCH' , true);
/**
 * 推送与短信的记录日志开关。
 * 
 * @var true|false
 *
 */
define('PUSH_LOG_SWITCH', true);
/**
 * 短信的开关
 * true：可以发送短信
 * false：不发送短信，都反馈发送成功
 * 
 * @var true|false
 *
 */
define('SMSCODE_SWITCH', false);
/**
 * true：验证短信，false：短信验证（无论输什么）都会通过。
 * 
 * @var true|false
 *
 */
define('CHECK_SMSCODE', true);
/**
 * 短信有效时间，单位：秒
 * 
 * @var Number
 *
 */
define('SMSCODE_EXPIRE', 600);
/**
 * 是否启用快速短信验证码
 *
 * @var boolen
 *
 */
define('QUICK_SMSCODE_SWITCH', true);

include_once 'status_config.php';
include_once 'message_template.php';
include_once 'project_config.php';