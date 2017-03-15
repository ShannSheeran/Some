<?php 
ini_set('date.timezone','Asia/Shanghai');
// ini_set('display_errors','On');
// error_reporting(E_ALL);
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';
require_once 'config.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//①、获取用户openid
$openId = isset($_REQUEST['open_id']) ? trim($_REQUEST['open_id']) : "";
$tools = new JsApiPay();
if (! $openId) {
    $openId = $tools->GetOpenid();
}

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("快摇名片随身设备");
$input->SetAttach("快摇名片随身设备");
// $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
$input->SetOut_trade_no(isset($_POST['order_sn']) ? $_POST['order_sn'] : (date('YmdHis') . mt_rand(10, 99)));
// $input->SetTotal_fee("1"); // 测试支付用
//$total_fee = isset($_POST['price']) ? round($_POST['price'] / 1000) : 1; // 测试用2
 $total_fee = isset($_POST['price']) ? (int) $_POST['price'] * 100 : "";
$input->SetTotal_fee((string) $total_fee);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("快摇名片随身设备");
$input->SetNotify_url(WXPAY_HTTP . "notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
$jsApiParameters = $tools->GetJsApiParameters($order);

echo $jsApiParameters;
//获取共享收货地址js函数参数
// $editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>
