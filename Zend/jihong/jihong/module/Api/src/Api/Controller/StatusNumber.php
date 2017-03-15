<?php
namespace Api\Controller;

/**
 * 订单、售后、发布商品的各种状态的数量
 */
class StatusNumber extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin(); 
        $where['user_id']=$this->getUserId();
        $where['delete'] = DELETE_FALSE;
        $type = $this->getUserType();
        $item=array(
            'orderPay'=>0,
            'orderAudit'=>0,
            'orderGoods'=>0,
            'serviceNumber'=>0,
            'goodsAudit'=>0,
            'goodsNumber'=>0,
            'goodsNormal'=>0
        );
        if ($type==1)
        {
            $where['children'] = 0;
            $orderList = $this->getOrderTable()->getAll($where,array('id','status'));
            foreach ($orderList['list'] as $v)
            {//订单状态数量计算
                if ($v['status']==1)
                {
                    $item['orderPay']++;
                }
                elseif ($v['status']==2)
                {
                    $item['orderAudit']++;
                }
                elseif ($v['status']==4)
                {
                    $item['orderGoods']++;
                }
            }
            unset($where['children']);
            unset($where['type']);
            $where['status'] = 1;
            $service = $this->getCustomerServiceApplyTable()->getAll($where,array('id'));
            $item['serviceNumber']=intval($service['total']);
        }
        else
        {
            $goodList = $this->getGoodsTable()->getAll($where,array('id','status'));

            foreach ($goodList['list'] as $v)
            {//产品状态数量计算
                if ($v['status']==1)
                {
                    $item['goodsAudit']++;
                }
                elseif ($v['status']==2)
                {
                    $item['goodsNumber']++;
                }
                elseif ($v['status']==3)
                {
                    $item['goodsNormal']++;
                }
            }
        }
        $response->statusNumber = $item;
        return $response;
    }
}
