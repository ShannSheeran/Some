<?php
namespace Api3\Controller;

use Api3\Controller\Request\OrderSubmitRequest;
/**
 * 订单，订单提交
 *
 * @author
 *         WZ
 *        
 */
class OrderSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new OrderSubmitRequest();
        parent::__construct();
    }

    public function index()
    { 
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $user_id = $this->getUserId();
        $order_data = $this->getIndexController()->orderSubmit($user_id, $request->order);
        if ($order_data['status']) {
            return $order_data;
        }
        
        if (2 == $request->order->payment)
        {
            // 微信支付
            $response->wxpay = $this->getWxPayInfo($order_data['info']['order_sn'], $order_data['info']['total']);
        }

        if (3 == $request->order->payment)
        {
            // 银联支付
            $response->tn = $this->getYlPayInfo($order_data['info']['order_sn'], $order_data['info']['total'],2);
        }
         
        $response->id = $order_data['info']['id'];
        $response->orderSn = $order_data['info']['order_sn'];
        $response->amount = $order_data['info']['total'];
        return $response;
    }
}