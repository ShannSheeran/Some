<?php
namespace Api\Controller;

use Api\Controller\Request\OrderDetailsRequest;

/**
 * 业务，订单详情
 */
class OrderDetails extends CommonController
{ 
    public function __construct()
    {
        $this->myRequest = new OrderDetailsRequest();
        parent::__construct();
    }

    public function index()
    {  
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $where = array();
        $where['id'] = $request->id;
        $where['delete'] = DELETE_FALSE;
        $order = $this->getViewOrderTable()->getOne($where);
        if (!$order){
            return STATUS_NODATA;
        }
        $item = array(
            'id' => $order['id'],
            'name' => $order['name'],
            'timestamp' => $order['timestamp']
        );
        $item = array();
        $goods_list = array();
        $item['id'] = $order->id;
        $item['orderSn'] = $order->order_sn;
        $item['totalCash'] = $order->total_cash;
        $item['status'] = $order->status;
        $item['merchantId'] = $order->merchant_id;
        $item['merchantName'] = $order->merchant_name?$order->merchant_name:'吉宏园艺自营';
        $item['delay'] = $order->delay;
        $item['delayReason'] = $order->delay_reason;
        $item['description'] = $order->description;
        $item['reason'] = $order->reason;
        $item['expectDate'] = strtotime($order->expect_date)>0?$order->expect_date:'';
        $item['shippingSn'] = $order->shipping_sn;
        $item['expressName'] = $order->express_name;
        $item['contactsId'] = $order->contacts_id;
        $item['payType'] = $order->pay_type;
        if ($order->pay_type==2)
        {//支付凭证图片
            $info = $this->getImageTable()->getImages(array($order->pay_img), 1);
            $item['payImagePath'] = isset($info[$order->pay_img]['path'])?$info[$order->pay_img]['path'].$info[$order->pay_img]['filename']:'';
        }
        //组装地址对象
        $contacts['name'] =  $order->name;
        $contacts['mobile'] =  $order->mobile;
        $contacts['address'] =  $order->address;
        $item['contacts'] = $contacts;
        $goodsInfo = $this->getViewOrderGoodsTable()->fetchAll(array('order_id'=>$order->id,'delete'=>DELETE_FALSE));
        if (!$goodsInfo){//判断订单商品是否为空
            return STATUS_NODATA;
        } 

        foreach ($goodsInfo as $i)
        {//拿到所有订单商品的第一个图片id
            if ($i->image)
            {
                $image_id = explode(',', trim($i->image,","));
                $image_ids[] = $image_id[0];
            }
        }
        $images =array();
        $images = $this->getImageTable()->getImages($image_ids, 1);
        foreach ($goodsInfo as $val)
        {//组装订单商品数据
           $goods = array();
           $image_list =array();
           $goods['id'] = $val->id;
           $goods['name'] = $val->name;
           $goods['unit'] = $val->unit;
           $goods['number'] = $val->number;
           $goods['cash'] = $val->price_cash;
           $goods['size'] = $val->size;
           $goods['model'] = $val->model;
           $goods['customerServiceId'] = $val->customer_service_id;
           $img_id = $val->image ? explode(',', trim($val->image,",")) : array();
             $goods['imagePath'] = $images[$img_id[0]]?$images[$img_id[0]]['path'].$images[$img_id[0]]['filename']:'';
           $goods_list[]['goods'] = $goods;
        }
        $item['goodses'] = $goods_list;
        $item['timestamp'] = $order->timestamp;
        $item['timestampUpdate'] = $order['timestamp_update'];
        $response->order = $item;
        return $response;
    }
}