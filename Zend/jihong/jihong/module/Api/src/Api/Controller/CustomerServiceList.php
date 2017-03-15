<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
use Api\Controller\Request\CustomerServiceListRequest;
/**
 * 订单售后列表
 */
class CustomerServiceList extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new CustomerServiceListRequest();
        parent::__construct();
    }
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where =new Where();
        $where->equalTo('user_id', $this->getUserId())->AND->equalTo('delete', DELETE_FALSE);
        if ($request->action==1)
        {
            $where->in('status',array(2,3));
        }
        else
        {
            $where->equalTo('status', 1);
        }
        $list = $this->getAll($this->getCustomerServiceApplyTable(),$where);
        $total = $list['total'];
        $list_array=array();
        $item=array();
        foreach ($list['list'] as $v)
        {
            $orderInfo = $this->getOrderTable()->getOne(array('id'=>$v['order_id']));//获取申请售后的订单编号
            $item =array(
                'id'=>$v['id'],
                'orderSn'=>$orderInfo['order_sn'],
                'status'=>$v['status'],
                'type'=>$v['type'],
                //'reason'=>$v['reason']
            );
            $images =array();
            $image_list = array();
            if ($v['image'])
            {//申请售后图片
                $image_id = explode(',', trim($v['image'],","));
                $images = $this->getImageTable()->getImages($image_id, 0);
            }
            if ($images)
            {
                foreach ($images as $i)
                {
                    $info['id']=$i['id'];
                    $info['path']=isset($i['path']) ? $i['path'].$i['filename'] : '';
                    $image_list[]['image'] = $info;
                }
            }
            $item['images'] = $image_list;
            //申请售后的订单商品
            $order_goods_id = explode(',', trim($v['order_goods_id'],","));
            $orderGoodsInfo = $this->getViewOrderGoodsTable()->fetchAll(array('id'=>$order_goods_id));
            $goodses = array();
            foreach ($orderGoodsInfo as $val)
            {//遍历订单商品组装数组
                $goods['id'] = $val['id'];
                $goods['name'] = $val['name'];
                $goods['number'] = $val['number'];
                $goods['cash'] = $val['cash'];
                $goods['size'] = $val['size'];
                $goods['model'] = $val['model'];
                $goodsImage = array();
                if ($val['image'])
                {//商品图片
                    $imageId = explode(',', trim($val['image'],","));
                    $goodsImage = $this->getImageTable()->getImages(array($imageId[0]), 0);
                }
                $goods['imagePath']=isset($goodsImage[$imageId[0]]['path']) ? $goodsImage[$imageId[0]]['path'].$goodsImage[$imageId[0]]['filename'] : '';
                $goodses[]['goods'] = $goods;
            }
            $item['goodses'] = $goodses;
            $item['timestamp']=$v['timestamp'];
            $list_array[]['service']=$item;
        }
        $response->total = $total;
        $response->services = $list_array;
        return $response;
    }
}
