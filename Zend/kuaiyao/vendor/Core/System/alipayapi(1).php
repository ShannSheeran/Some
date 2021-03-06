<?php
/* *
 * 功能：标准双接口接入页
 * 版本：3.3
 * 修改日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************注意*************************
 * 如果您在接口集成过程中遇到问题，可以按照下面的途径来解决
 * 1、商户服务中心（https://b.alipay.com/support/helperApply.htm?action=consultationApply），提交申请集成协助，我们会有专业的技术工程师主动联系您协助解决
 * 2、商户帮助中心（http://help.alipay.com/support/232511-16307/0-16307.htm?sh=Y&info_type=9）
 * 3、支付宝论坛（http://club.alipay.com/read-htm-tid-8681712.html）
 * 如果不想使用扩展功能请把扩展功能参数赋空值。
 */
define('ALIPAY_ROOT_PATH' , __DIR__);

require_once("lib/alipay_submit.class.php");
require_once("lib/alipay_notify.class.php");
class alipayapi{
    
    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
    //合作身份者id，以2088开头的16位纯数字
   protected $partner = ALIPAY_PARTNER;   
    //安全检验码，以数字和字母组成的32位字符
   protected $key = ALIPAY_KEY;
    /**************************请求参数**************************/

    //支付类型  
    protected   $payment_type = "1";
    //必填，不能修改
    //服务器异步通知页面路径
   protected   $notify_url = ALIPAY_NOTIFY_URL;
    //需http://格式的完整路径，不能加?id=123这类自定义参数

    //页面跳转同步通知页面路径
    protected $return_url = ALIPAY_RETURN_URL;
    //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

    //卖家支付宝帐户
   protected $seller_email = ALIPAY_SELLER_EMAIL;
    //必填

    //商户订单号
   public   $out_trade_no = '';
    //商户网站订单系统中唯一订单号，必填

    //订单名称
   public  $subject = '';
    //必填

    //付款金额
   public $total_fee = '';
    //订单描述
    public $body = '';
    //商品展示地址
    public $show_url = '';
    //需以http://开头的完整路径，如：http://www.xxx.com/myorder.html

    public $exter_invoke_ip = '';
    
    public $anti_phishing_key = '';
    
    /************************************************************/
    
    //PC端支付配置
    public function alipay_config(){
    	return  $alipay_config =array(
            'partner' => $this->partner,
            'key' => $this->key,
            'sign_type' => strtoupper('MD5'),
            'input_charset' => strtolower('utf-8'),
            'cacert' => getcwd() . '\\cacert.pem',
            'transport' => 'http'   
        );
    }
    
    
    public function app_alipay_config(){
        return  $alipay_config =array(
            'partner' => $this->partner,
            'private_key_path' => ALIPAY_ROOT_PATH . '/key/rsa_private_key.pem',
            'ali_public_key_path' => ALIPAY_ROOT_PATH . '/key/rsa_public_key.pem',
            'sign_type' => strtoupper('RSA'),
            'input_charset' => strtolower('utf-8'),
            'cacert' => getcwd() . '\\cacert.pem',
            'transport' => 'http'
        );
    }
    public function parameter(){
        $config = $this->alipay_config();
        $input_charset = $config['input_charset'];
    	return  $parameter = array(
        		"service" => "create_direct_pay_by_user",
        		"partner" => trim($this->partner),
        		"payment_type"	=> $this->payment_type,
        		"notify_url"	=> $this->notify_url,
        		"return_url"	=> $this->return_url,
        		"seller_email"	=> $this->seller_email,
        		"out_trade_no"	=>$this-> out_trade_no,
        		"subject"	=> $this->subject,
        		"total_fee"	=> $this->total_fee,
        		"body"	=> $this->body,
        		"show_url"	=> $this->show_url,
        		"anti_phishing_key"	=> $this->anti_phishing_key,
		        "exter_invoke_ip"	=> $this->exter_invoke_ip,
        		"_input_charset"	=> trim(strtolower($input_charset))
        );
    }
    public function PostAlipay(){
        //建立请求
         $alipaySubmit = new AlipaySubmit($this->alipay_config());
         $html_text = $alipaySubmit->buildRequestForm($this->parameter(),"post", "确认");
         return $html_text;
    }
    
    //支付宝页面跳转同步通知页面
   
    public function getAlipayReturnSuccess(){

        $alipayNotify = new AlipayNotify($this->alipay_config());

        $verify_result = $alipayNotify->verifyReturn();
        return $verify_result;
    }
    
    //支付宝服务器异步通知页面
    //$type = 1 网站支付参数配置
    //$type=2 app 端支付参数
    public function getAlipayNotifySuccess($type=1){
        if($type==1){
            $alipayNotify = new AlipayNotify($this->alipay_config());
        }else{
            $alipayNotify = new AlipayNotify($this->app_alipay_config());
        }
    	$verify_result = $alipayNotify->verifyNotify();
    	return $verify_result;
    }
    
}
?>