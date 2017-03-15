<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "../lib/WxPay.Api.php";
require_once '../lib/WxPay.Notify.php';
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);

if ($notify->GetReturn_code() == 'SUCCESS') {
    // 处理自己的业务 by:WZ
    $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
    // 如果返回成功则验证签名
    try {
        $result = WxPayResults::Init($xml);
    } catch (WxPayException $e) {
        $msg = $e->errorMessage();
        exit;
    }
    require_once 'config.php';
    $url = INDEX_HTTP . '/commodity/orderStatus.html';
//     file_put_contents("../logs/" . time() . ".txt", json_encode(array('order_sn' => $result['out_trade_no'], 'url' => $url)));
    urlExec($url, array('order_sn' => $result['out_trade_no'], 'open_id' => $result['openid']), 'post');
    $log->INFO(json_encode($result));
}
