<?php
namespace Core\System\AiiPush;

/**
 * 推送接口
 *
 * @author WZ
 *        
 */
class AiiPushBase
{

    /**
     * 文件类
     */
    public $myfile;

    /**
     * 推送接口的id
     * 
     * @var unknown
     */
    public $_access_id;

    /**
     * 推送接口的key
     * 
     * @var unknown
     */
    public $vibrate;

    /**
     * iOS的使用版本
     * 1 PROD ; 2 DEV
     * 
     * @var number
     */
    public $_iosenv;

    /**
     * 构造函数
     */
    function __construct()
    {
        require_once __DIR__ . '/config/config.php';
        $this->myfile = new AiiMyFile();
        $this->init();
    }

    /**
     * 设置参数，子类注意改写此方法
     */
    public function init()
    {
    }

    /**
     * 模拟post进行url请求
     * 
     * @param string $url            
     * @param string $param            
     */
    public function http_post($url = '', $param = '')
    {
        if (empty($url) || empty($param))
        {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); // 初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); // 抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); // 设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); // post提交方式
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 超时时间
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch); // 运行curl
        curl_close($ch);
        
        return $data;
    }

    /**
     * 暂时没写，用到再写
     * 模拟get操作回去内容
     * 
     * @param string $url 地址
     * @param string $param 参数，字符串或数组
     * @version 2014-11-6 WZ
     */
    public function http_get($url = '', $param = '')
    {
        if(is_array($param))
        {
            $new_param = '';
            foreach($param as $key => $value)
            {
                $new_param .= ($new_param ? '&' : '') . $key.'='.$value;
            }
        }
        elseif(is_string($param))
        {
            $new_param = $param;
        }
        
        $new_url = $url . ($new_param ? ('?' . $new_param) : '');
        return file_get_contents($new_url);
    }
}
?>