<?php
namespace Api\Controller;

use Api\Controller\Request\CustomerServiceSubmitRequest;
/**
 * 订单售后申请
 */
class CustomerServiceSubmit extends CommonController
{
 public function __construct()
    {
        $this->myRequest = new CustomerServiceSubmitRequest();
        parent::__construct();
    }
    public function index()
    {
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $order_id = $request->id;
        $goods_id = $request->goods_id ? $request->goods_id:'';
        $type = $request->type ? $request->type:3;  //售后类型1商品质量2物流配送3其他问题
        $reason = $request->reason ? $request->reason:'';
        $image = $request->image ? $request->image:array();
        $goods_ids =array();
        if (!$goods_id)
        {//整单申请售后
            
            $order_goods = $this->getOrderGoodsTable()->fetchAll(array('order_id'=>$order_id),NULL,NULL,array('id'));
            if (!$order_goods)
            {
                return STATUS_NODATA;
            }
            foreach ($order_goods as $v)
            {
                if ($v['customer_service_id']>0)
                {
                    return STATUS_ORDER_SERVICE_ERROR;//9011
                }
                $goods_ids[]=$v['id'];
            }
            $goods_id = implode(',', $goods_ids);
        }
        else 
        {//针对订单某个商品申请售后
            $order_goods = $this->getOrderGoodsTable()->getOne(array('id'=>$goods_id));
            if (!$order_goods)
            {
                return STATUS_NODATA;
            }
            if ($order_goods['customer_service_id']>0)
            {
                return STATUS_CAN_NOT_RESEND;//9011
            }
            $goods_ids[] =$goods_id;
        }
         $set = array(
            'order_id' => $order_id,
            'user_id' => $this->getUserId(),
            'order_goods_id' => $goods_id,
            'type' => $type,
            'reason' => $reason,
            'image' => implode(',', $image),
            'delete' => DELETE_FALSE,
            'timestamp' => $this->getTime()
        );
        $id = $this->getCustomerServiceApplyTable()->insertData($set);
        $this->getOrderGoodsTable()->update(array('customer_service_id'=>$id),array('id'=>$goods_ids));
        $response->status = STATUS_SUCCESS;
        $response->id = $id;
        return $response;
    }
}
