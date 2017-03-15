<?php
namespace Api\Controller;

use Api\Controller\Request\CartSubmitRequest;
 
/**
 * 新增(修改)我的购物车商品
 */
class CartSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new CartSubmitRequest();
        parent::__construct();
    }
    public function index()
    {
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $specification_id = intval($request->specification_id);
        $number = $request->number;
        $user_id =$this->getUserId();
        if ($number<=0||$specification_id<=0)
        {
           return STATUS_PARAMETERS_INCOMPLETE;
        }
        $info = $this->getGoodsSpecificationTable()->getOne(array('id'=>$specification_id));
        if ($info['number']<$number)
        {
            return STATUS_UNDER_STOVK;
        }
        $set = array(
            'number' => $number,
            'specification_id' => $specification_id,
            'delete' => DELETE_FALSE
        );
        if ($request->cart_id)
        {// 有id就是修改
            $where = array(
                'id' => $request->cart_id,
                'user_id' => $user_id
            );
            $info = $this->getCartTable()->getOne($where);
            if (!$info){
                return STATUS_NODATA;
            }
            $this->getCartTable()->update($set, $where);
            $id = $request->cart_id;
        }
        else
        {// 没id就是插入新的
            $goods_id = $request->goods_id;
            $data = array(
                'goods_id'=>$goods_id,
                'user_id'=>$user_id,
                'specification_id'=>$specification_id,
                'delete'=>DELETE_FALSE
            );
            $cartInfo = $this->getCartTable()->getOne($data);
            if ($cartInfo)
            {
                $set['number'] = $set['number']+$cartInfo['number'];
                $this->getCartTable()->update($set, array('id'=>$cartInfo['id']));
                $id = $cartInfo['id'];
            }else{
                $set['user_id'] = $user_id;
                $set['goods_id'] = $goods_id;
                $set['timestamp'] = $this->getTime();
                $id = $this->getCartTable()->insertData($set);
            }
        }
        $response->status = STATUS_SUCCESS;
        $response->id = $id;
        return $response;
    }
}
