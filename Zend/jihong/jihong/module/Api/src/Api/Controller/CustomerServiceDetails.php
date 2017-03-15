<?php
namespace Api\Controller;

use Api\Controller\Request\CustomerServiceDetailsRequest;

/**
 * 业务，订单售后详情
 */
class CustomerServiceDetails extends CommonController
{ 
    public function __construct()
    {
        $this->myRequest = new CustomerServiceDetailsRequest();
        parent::__construct();
    }

    public function index()
    {  
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $where = array();
        $where['id'] = $request->id;
        $where['delete'] = DELETE_FALSE;
        $data = $this->getCustomerServiceApplyTable()->getOne($where);
        $orderInfo = $this->getOrderTable()->getOne(array('id'=>$data['order_id']));
        if (!$data){
            return STATUS_NODATA;
        }
        $item =array(
            'orderSn'=>$orderInfo['order_sn'],
            'status'=>$data['status'],
            'type'=>$data['type'],
            'reason'=>$data['reason']
        );
        $images =array();
        $image_list = array();
        if ($data['image'])
        {//申请售后图片
            $image_id = explode(',', trim($data['image'],","));
            $images = $this->getImageTable()->getImages($image_id, 0);
        }
        if ($images)
        {//遍历组装图片数组
            foreach ($images as $i){
                $info['id']=$i['id'];
                $info['path']=isset($i['path']) ? $i['path'].$i['filename'] : '';
                $image_list[]['image'] = $info;
            }
        }
        $item['images'] = $image_list;
        $order_goods_id = explode(',', trim($data['order_goods_id'],","));
        $orderGoodsInfo = $this->getViewOrderGoodsTable()->fetchAll(array('id'=>$order_goods_id));
        $goodses = array();
        foreach ($orderGoodsInfo as $v)
        {
            $goods['id'] = $v['id'];
            $goods['name'] = $v['name'];
            $goods['number'] = $v['number'];
            $goods['cash'] = $v['cash'];
            $goods['size'] = $v['size'];
            $goods['model'] = $v['model'];
            $goodsImage = array();
            if ($v['image'])
            {//商品图片
                $imageId = explode(',', trim($v['image'],","));
                $goodsImage = $this->getImageTable()->getImages(array($imageId[0]), 0);
            }
            $goods['imagePath']=isset($goodsImage[$imageId[0]]['path']) ? $goodsImage[$imageId[0]]['path'].$goodsImage[$imageId[0]]['filename'] : '';
            $goodses[]['goods'] = $goods;
        }
        $item['goodses'] = $goodses;
        $item['timestamp']=$data['timestamp'];
        $response->service = $item;
        return $response;
    }
}