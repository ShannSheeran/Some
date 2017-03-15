<?php
namespace Core\System\YlPay;

class YlPay
{
    private $orderId;
    
    private $txnAmt;
    
    function __construct() {
        include_once __DIR__.'/utf8/func/common.php';
        include_once __DIR__.'/utf8/func/SDKConfig.php';
        include_once __DIR__.'/utf8/func/secureUtil.php';
        include_once __DIR__.'/utf8/func/httpClient.php';
//         include_once __DIR__.'/utf8/func/log.class.php';
    }
    
    function setValue($value)
    {
        $list = array(
            'orderId',
            'txnAmt',
        );
        foreach ($list as $key)
        {
            if (isset($value[$key])) {
                $this->$key = $value[$key];
            }
        }
        return $this;
    }
    
    function getOutParams()
    {
        if (!$this->orderId || !$this->txnAmt) {
            return array();//错误
        }
        
        $params = array(
            'version' => '5.0.0',				//版本号
            'encoding' => 'utf-8',				//编码方式
            'certId' => getSignCertId (),			//证书ID
            'txnType' => '01',				//交易类型
            'txnSubType' => '01',				//交易子类
            'bizType' => '000201',				//业务类型
            'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  		//前台通知地址，控件接入的时候不会起作用
            'backUrl' => SDK_BACK_NOTIFY_URL,		//后台通知地址
            'signMethod' => '01',		//签名方法
            'channelType' => '08',		//渠道类型，07-PC，08-手机
            'accessType' => '0',		//接入类型
            'merId' => '104440148990263',	//商户代码，请改自己的测试商户号
            'orderId' => $this->orderId,	//商户订单号，8-40位数字字母
            'txnTime' => date('YmdHis'),	//订单发送时间
            'txnAmt' => (string)($this->txnAmt * 100),		//交易金额，单位分
            'currencyCode' => '156',	//交易币种
            'orderDesc' => '订单描述',  //订单描述，可不上送，上送时控件中会显示该信息
            'reqReserved' =>' 透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
        );
        
        
        // 签名
        sign ( $params );
        
//         echo "请求：" . getRequestParamString ( $params );
//         $log->LogInfo ( "后台请求地址为>" . SDK_App_Request_Url );
        // 发送信息到后台
        $result = sendHttpRequest ( $params, SDK_App_Request_Url );
//         $log->LogInfo ( "后台返回结果为>" . $result );
        
//         echo "应答：" . $result;
        
        //返回结果展示
        $result_arr = coverStringToArray ( $result );
       
//         $verify = verify ( $result_arr);
        
//         if ($verify) {
            return $result_arr;
//         }
//         else {
//             return array(); // 错误
//         }
    }
    
    function getOutParam()
    {
        if (!$this->orderId || !$this->txnAmt) {
            return array();//错误
        }
    
//         $log = new PhpLog ( SDK_LOG_FILE_PATH, "PRC", SDK_LOG_LEVEL );
//         $log->LogInfo ( "============处理前台请求开始===============" );
        // 初始化日志
        $params = array(
                'version' => '5.0.0',               //版本号
                'encoding' => 'utf-8',              //编码方式
                'certId' => getSignCertId (),           //证书ID
                'txnType' => '01',              //交易类型  
                'txnSubType' => '01',               //交易子类
                'bizType' => '000201',              //业务类型
                'frontUrl' =>  SDK_FRONT_NOTIFY_URL,        //前台通知地址
                'backUrl' => SDK_BACK_NOTIFY_URL,       //后台通知地址    
                'signMethod' => '01',       //签名方法
                'channelType' => '08',      //渠道类型，07-PC，08-手机
                'accessType' => '0',        //接入类型
                'merId' => '104440148990263',               //商户代码，请改自己的测试商户号
                'orderId' => $this->orderId,    //商户订单号
                'txnTime' => date('YmdHis'),    //订单发送时间
                'txnAmt' => (string)($this->txnAmt * 100),      //交易金额，单位分
                'currencyCode' => '156',    //交易币种
                'defaultPayType' => '0001', //默认支付方式    
                //'orderDesc' => '订单描述',  //订单描述，网关支付和wap支付暂时不起作用
                'reqReserved' =>' 透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现
                );
        
        // 签名
        sign ( $params );
        
        // 前台请求地址
        $front_uri = SDK_FRONT_TRANS_URL;
//         $log->LogInfo ( "前台请求地址为>" . $front_uri );
        // 构造 自动提交的表单
        $html_form = create_html ( $params, $front_uri );
        
//         $verify = verify ( $result_arr);
        
        //         if ($verify) {
        return $html_form;
        //         }
        //         else {
        //             return array(); // 错误
        //         }
        
//         $log->LogInfo ( "-------前台交易自动提交表单>--begin----" );
//         $log->LogInfo ( $html_form );
//         $log->LogInfo ( "-------前台交易自动提交表单>--end-------" );
//         $log->LogInfo ( "============处理前台请求 结束===========" );
//         echo $html_form;
    }
}
