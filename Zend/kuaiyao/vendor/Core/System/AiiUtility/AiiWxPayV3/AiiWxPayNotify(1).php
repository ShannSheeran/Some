<?php
namespace Core\System\AiiUtility\AiiWxPayV3;
include_once(__DIR__."/log_.php");
include_once(__DIR__."/WxPayPubHelper/WxPayPubHelper.php");

/**
 * 验证微信回调是否通过的类
 * 
 * @author WZ
 *
 */
class AiiWxPayNotify
{
    public function getResult()
    {
        //使用通用通知接口
        $notify = new \Notify_pub();
        
        //存储微信的回调
        $xml = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
        if (! $xml) {
            return array('status' => '3'); // 没有回调
        }
        $notify->saveData($xml);
        
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
        
        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        
        //以log文件形式记录回调信息
//         $log_ = new \Log_();
//         $log_name="./notify_url.log";//log文件路径
//         $log_->log_result($log_name,"【接收到的notify通知】:\n".$xml."\n");
        
        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
//                 $log_->log_result($log_name,"【通信出错】:\n".$xml."\n");
                
                return array('status' => 2); // WZ
            }
            elseif($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
//                 $log_->log_result($log_name,"【业务出错】:\n".$xml."\n");
                
                return array('status' => 2); // WZ
            }
            else{
                //此处应该更新一下订单状态，商户自行增删操作
//                 $log_->log_result($log_name,"【支付成功】:\n".$xml."\n");
                
                return array('status' => 0,"out_trade_no" => $notify->data['out_trade_no']); // WZ
            }

            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
        }
        else {
            return array('status' => 4); // 验证不通过
        }
    }
}
