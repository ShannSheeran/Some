<?php
namespace Api\Controller;

use Api\Controller\Request\StatusUpdateRequest;
use Api\Controller\Item\PushTemplateItem;
 
/**
 * 订单（发布商品）状态更新
 */
class StatusUpdate extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new StatusUpdateRequest();
        parent::__construct();
    }
    public function index()
    {
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $user_id = $this->getUserId();
        $id = $request->id;
        $type = $request->type;
        $pay_type = $request->pay_type; //订单支付方式1余款支付；2转账支付
        $status = $request->action ? $request->action :1; //action:1确认收货（默认）2支付订单3取消订单 4订单延期
        if (!$id||!in_array($type, array(1,2))){
           return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9010
        }
        $where = array(
            'id' =>$id,
            'user_id' =>$user_id,
            'delete'=>DELETE_FALSE
        );
        //推送
        $template = new PushTemplateItem();
        $set = array();
        if ($type==1)
        {//修改订单状态     订单状态：1待付款 2 待审核 3待发货（已审核） 4待收货 5已完成6 已取消 7审核失败
            $info = $this->getOrderTable()->getOne($where);
            if (!$info){
                return STATUS_NODATA; //1011
            }
            if ($info['status']==4 && $status==1)
            {//1确认收货
                $set['status'] = 5;
            }
            elseif ($info['status']==1 && $status==2)
            {//2支付订单
                $set['status'] = 2;
                if ($info['pay_type']!=$pay_type)
                {
                    return Alipay_type_Error; //1118
                }
                if ($pay_type==2)
                {//转账支付
                    $image =$request->image;
                    if (!$image->id)
                    {
                        return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9010
                    }
                    $set['pay_img'] = $image->id;
                }
               /*  elseif ($pay_type==1)
                {//余款支付
                    $user_info = $this->getUserTable()->getOne(array('id'=>$user_id));
                    $this->getUserTable()->updateKey($user_id,2,'cash',$info['total_cash']);
                }
                else
                {
                    return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9010
                }  */
            }
            elseif (($info['status']==1||$info['status']==2) && $status==3)
            {//3取消订单 
                $set['status'] = 6;
                //$this->getOrderTable()->update($set,array('id'=>$id));
                //获得订单商品
                $order_good = $this->getOrderGoodsTable()->fetchAll(array('order_id'=>$id));
                foreach ($order_good as $v)
                {//加库存
                    $this->getGoodsTable()->updateKey($v->goods_id, 1 , 'number' , $v->number);
                    $this->getGoodsTable()->updateKey($v->goods_id, 2 , 'sale_number' , $v->number);
                    $this->getGoodsSpecificationTable()->updateKey($v->specification_id, 1 , 'number' , $v->number);
                    $this->getGoodsSpecificationTable()->updateKey($v->specification_id, 2 , 'sale_number' , $v->number);
                }
            }
            elseif ($info['status']==4 && $status==4)
            {//4订单延期
                $set['delay'] = 1;
                $set['delay_reason'] = $request->delay_reason;
                $this->getOrderTable()->update($set,array('id'=>$id));
                return STATUS_SUCCESS;
            }
            else
            {
                return STATUS_ILLEGAL_OPERATION; //1406
            }
            //更新订单状态
            $this->getOrderTable()->update($set,array('id'=>$id));
            if ($info['children']==1)
            {//如果上传的凭证的订单id 为主订单时，同步更新子订单状态
                $orderInfo = $this->getOrderTable()->fetchAll(array('children'=>0,'parent_id'=>$id),NULL,NULL,array('id','user_id','order_sn'));
                foreach ($orderInfo as $v)
                {
                    
                    $ids[] = $v['id'];
                    //更新子订单跟踪状态
                    $data['status'] = 2;
                    $data['user_id'] = $v['user_id'];
                    $data['order_id'] = $v['id'];
                    $data['timestamp'] = $this->getTime();
                    $this->getOrderTrackingTable()->indateKey($data,1,2,$v['order_sn']);
                }
                $this->getOrderTable()->update($set,array('id'=>$ids));
            }
            else
            {//更新订单跟踪状态
                $data['status'] = $set['status'];
                $data['user_id'] = $user_id;
                $data['order_id'] = $id;
                $data['timestamp'] = $this->getTime();
                $this->getOrderTrackingTable()->indateKey($data,1,$set['status'],$info['order_sn']);
            }
            return STATUS_SUCCESS;
        }
        else
        {//修改发布商品状态
            $info = $this->getViewGoodsTable()->getOne($where);
            if (!$info)
            {
                return STATUS_NODATA; //1011
            }
            if ($info['status']==1)
            {
                $set['status'] = 5;
                $this->getGoodsTable()->update($set,array('id'=>$id));
                $value['status'] = $set['status'];
                $value['user_id'] = $user_id;
                $value['goods_id'] = $id;
                $value['timestamp'] = $this->getTime();
                $this->getGoodsTrackingTable()->indateKey($value,2,$set['status'],$info['goods_sn']);
            }
            else
            {
                return STATUS_ILLEGAL_OPERATION;
            }
        }
        return STATUS_SUCCESS;
    }
}
