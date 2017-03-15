<?php
namespace Api\Controller;

use Api\Controller\Request\OrderSubmitRequest;
 
/**
 * 新增(修改)商品
 */
class OrderSubmit extends CommonController
{
    
    public $total_number=0;
    
    public $total_cash=0;
    
    public $status = 1;  //订单状态默认为未支付状态1
    
    public $orderId = 0;  //订单id
    
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
        
        $user_id  = $this->getUserId();
        $action= $request->action; //a:1购物车下单；2立即下单 3重新下单 4修改订单 
        $contacts_id = $request->contacts_id; //收货地址id:
        $pay_type = $request->pay_type;  //支付方式  1余款支付；2转账支付
        $expect_date = $request->expect_date?$request->expect_date:''; //期望发货日期
        $content = $request->messages ? $request->messages :array();
        $contentList = array();
        foreach ($content as $val)
        {//组装留言数组
            if (isset($val->message)){
                $message = $val->message;
                $contentList[$message->id]['content']=$message->content;
            }
        }
        if (!in_array($action, array(1,2,3,4)) || !$contacts_id )
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;//9010
        }
        if ($action!=4)
        {//修改订单时不需要传支付方式
            if (!in_array($pay_type, array(1,2)))
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;//9010
            }
        }
        //地址信息
        $contactsInfo = $this->getContactsTable()->getOne(array('id'=>$contacts_id,'delete'=>DELETE_FALSE));
        if (!$contactsInfo)
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9010
        }
        //用户信息
        $userInfo = $this->getUserTable()->getOne(array('id'=>$user_id));
        $status = 1;
        $orderId = 0;
        if ($action==1)
        {//购物车下单
             
             $returnId = 0;  //子订单返回id，当购物车下单只有一个商家时不生产主订单orderId为0，用returnId返回
             $cart_ids = $request->cart_ids;  //购物车id
             $merchant =array();
             if (!$cart_ids)
             {
                 return STATUS_PARAMETERS_CONDITIONAL_ERROR;//9010
             }
             $cartInfo = $this->getViewCartTable()->fetchAll(array('id'=>$cart_ids));
             if (!$cartInfo){
                 return STATUS_NODATA; //1011
             }
             foreach ($cartInfo as $k=>$v)
             {//组装不同商家商品
                 if ($v['g_delete']==DELETE_TRUE || $v['s_delete']==DELETE_TRUE)
                 {
                     return STATUS_NODATA; //1011
                 }
                 if ($v['cart_number']>$v['s_number'] || $v['cart_number']>$v['g_number'])
                 {
                     $response->status = STATUS_UNDER_STOVK;
                     $response->id = $v['id'];
                     return $response;
                 }
                $goods_number[$v['goods_id']]['s_number'] = $v['s_number'];//保存商品规格库存值
                $goods_number[$v['goods_id']]['s_sale_number'] = $v['s_sale_number'];//保存商品规格库存值
                $goods_number[$v['goods_id']]['g_number'] = $v['g_number'];//保存商品规格库存值
                $goods_number[$v['goods_id']]['g_sale_number'] = $v['g_sale_number'];//保存商品规格库存值
                $goods_number[$v['goods_id']]['goods_id'] = $v['goods_id'];//保存商品规格库存值
                $goods_number[$v['goods_id']]['specification_id'] = $v['specification_id'];//保存商品规格库存值
                $merchant[$v['merchant_id']][]=$v;
             }
             //组装购物车订单
             $merchant = $this->checkNumber($merchant,$contentList,$pay_type,$expect_date,$contactsInfo);
             if(count($merchant)>1 && $pay_type == 2)
             {//组装主订单信息数组 ,#订单支付方式为转账2，时才生产主订单
                 $data = array(
                     'order_sn' => $this->getAdminController()->generate(),
                     'total_cash' => $this->total_cash,
                     'pay_type' => $pay_type,
                     'children' =>1,
                     'total_number' => $this->total_number,
                     'total_supply_number' => $this->total_number,
                     'user_id' => $user_id,
                     'contacts_id' => $contacts_id,
                     'timestamp' => $this->getTime()
                 );
                 $orderId = $this->getOrderTable()->insertData($data);  //生成主订单
             }
             if ($pay_type==1)
             {//余款支付
                 $status = 2;
                $this->getUserTable()->updateKey($user_id,2,'cash',$this->total_cash);//减账户余额
             }
             foreach ($merchant as $val)
             {//遍历生成子订单
                 $order = $val;
                 $order_goods = $val['goods'];
                 unset($order['goods']);
                 $order['parent_id'] = $orderId;
                 $order['status'] = $status;
                 $returnId = $this->getOrderTable()->insertData($order);  //生成子订单
                 foreach ($order_goods as $value)
                 {//生产订单商品记录
                     $value['order_id'] = $returnId;
                     $this->getOrderGoodsTable()->insertData($value); //插入订单商品
                     //减库存
                     $info = $goods_number[$value['goods_id']];
                     $g_number = array(//更新商品数量，及销量
                         'number'=>$info['g_number']-$value['number'],
                         'sale_number'=>$info['g_sale_number']+$value['number']
                     );
                     $s_number = array(//更新商品规格数量，及销量
                         'number'=>$info['s_number']-$value['number'],
                         'sale_number'=>$info['s_sale_number']+$value['number']
                     );
                     $this->getGoodsTable()->update($g_number,array('id'=>$value['goods_id']));
                     $this->getGoodsSpecificationTable()->update($s_number,array('id'=>$value['specification_id']));
                 }
                 //订单跟踪记录
                 $set = array(
                     'status'=>1,
                     'user_id'=>$user_id,
                     'order_id'=>$returnId,
                     'timestamp'=>$this->getTime()
                 );
                 $this->getOrderTrackingTable()->indateKey($set,1,1,$val['order_sn']);
                 if ($status==2)
                 {//如果订单为余款支付，则马上生成待审核跟踪记录
                     $data['status'] = 2;
                     $this->getOrderTrackingTable()->indateKey($set,1,2,$val['order_sn']);
                 }
             }
             $orderId = $orderId?$orderId:$returnId;
             //删除购物车
             $this->getCartTable()->delete(array('id'=>$cart_ids));
        }
        elseif($action==2)
        {//立即下单
            $id = $request->id;  //产品id
            $specification_id = $request->specification_id; //产品规格id
            $number = $request->number;
            if (!$id || !$specification_id || $number<=0)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;//9010
            }
            $goodsInfo = $this->getGoodsTable()->getOne(array('delete'=>DELETE_FALSE,'status'=>3,'id'=>$id));
            $specificationInfo = $this->getGoodsSpecificationTable()->getOne(array('id'=>$specification_id,'delete'=>DELETE_FALSE));
            if (!$specificationInfo||!$goodsInfo)
            {//判断商品,规格是否已删除
                return STATUS_NODATA; //1011
            }
            if ($specificationInfo->number-$number<0 || $goodsInfo->number-$number<0)
            {//判断商品规格库存
                return STATUS_UNDER_STOVK;  //1022
            }
            //生成订单
            $total_cash = $specificationInfo['cash']*$number;
            $set = array(
                'order_sn' => $this->getAdminController()->generate(),
                'total_cash' => $total_cash,
                'type' => $goodsInfo['type'],
                'pay_type' => $pay_type,
                'total_number' => $number,
                'total_supply_number' => $number,
                'expect_date' => $expect_date,
                'user_id' => $user_id,
                'merchant_id' => $goodsInfo['user_id'],
                'contacts_id' => $contacts_id,
                'name' => $contactsInfo['name'],
                'mobile' => $contactsInfo->mobile,
                'address' => $contactsInfo->address,
                'description' => $contentList[$goodsInfo['user_id']] ? $contentList[$goodsInfo['user_id']]['content']:'',
                'timestamp' => $this->getTime()
            );
            //订单商品
            $data = array(
                'price_cash'=>$specificationInfo['cash'],
                'number'=>$number,
                'supply_number'=>$number,
                'size'=>$specificationInfo['size'],
                'model'=>$specificationInfo['model'],
                'cash'=>$total_cash,
                'pack_number'=>$specificationInfo['pack_number'],
                'specification_id'=>$specificationInfo['id'],
                'goods_id'=>$specificationInfo['goods_id'],
                'timestamp'=>$this->getTime()
            );
           $orderId = $this->getOrderTable()->insertData($set);  //生成订单
           $data['order_id'] = $orderId;
           $this->getOrderGoodsTable()->insert($data); //插入订单商品
           //减库存
           $g_number = array(//更新商品数量，及销量
               'number'=>$goodsInfo['number']-$number,
               'sale_number'=>$goodsInfo['sale_number']+$number
           );
           $s_number = array(//更新商品规格数量，及销量
               'number'=>$specificationInfo['number']-$number,
               'sale_number'=>$specificationInfo['sale_number']+$number
           );
           $this->getGoodsTable()->update($g_number,array('id'=>$goodsInfo['id']));
           $this->getGoodsSpecificationTable()->update($s_number,array('id'=>$specificationInfo['id']));
           //订单跟踪记录
           $value = array(
               'status'=>1,
               'user_id'=>$user_id,
               'order_id'=>$orderId,
               'timestamp'=>$this->getTime()
           );
           $this->getOrderTrackingTable()->indateKey($value,1,1,$set['order_sn']);
           if ($pay_type==1)
           {//余款支付

                $this->getUserTable()->updateKey($user_id,2,'cash',$total_cash);
                $this->getOrderTable()->update(array('status'=>'2'),array('id'=>$orderId));
                $value['status'] = 2;
                $this->getOrderTrackingTable()->indateKey($value,1,2,$set['order_sn']);
           }
        }
        elseif($action==3)
        {//重新下单
            $order_id = $request->order_id;
            if (!$order_id)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $orderInfo = $this->getOrderTable()->getOne(array('id'=>$order_id));
            $ordergoodsInfo = $this->getOrderGoodsTable()->fetchAll(array('order_id'=>$order_id));
            foreach ($ordergoodsInfo as $k=>$v)
            {//检查库存
                $goodsNumber = $this->getGoodsSpecificationTable()->getOne(array('id'=>$v['specification_id']));
                if ($v['number']>$goodsNumber['number'])
                {
                    return STATUS_UNDER_STOVK;  //1022
                }
                if ($goodsNumber['delete']==DELETE_TRUE)
                {
                    return STATUS_IS_TOP;
                }
                //用商品规格里的价格，覆盖订单商品里面的价格
                $ordergoodsInfo[$k]['price_cash'] = $goodsNumber['cash'];
            }
            if ($pay_type==1)
            {//余款支付
                $status = $data['status'] = 2;
                $this->getUserTable()->updateKey($user_id,2,'cash',$this->total_cash);
            }
            ///$content =$content?$content[0]:'';
            $set = array(
                'order_sn' => $this->getAdminController()->generate(),
                'total_cash' => $orderInfo['total_cash'],
                'type' => $orderInfo['type'],
                'pay_type' => $pay_type,
                'status' => $status,
                'total_number' => $orderInfo['total_number'],
                'total_supply_number' => $orderInfo['total_supply_number'],
                'expect_date' => $expect_date,
                'user_id' => $user_id,
                'merchant_id' => $orderInfo['merchant_id'],
                'contacts_id' => $contacts_id,
                'name' => $contactsInfo['name'],
                'mobile' => $contactsInfo->mobile,
                'address' => $contactsInfo->address,
                'description' => $contentList[$orderInfo['merchant_id']] ? $contentList[$orderInfo['merchant_id']]['content']:'',
                'timestamp' => $this->getTime()
            );
            $orderId = $this->getOrderTable()->insertData($set);  //生成子订单
            foreach ($ordergoodsInfo as $v)
            {
                $data = array(
                    'price_cash'=>$v->price_cash,
                    'number'=>$v->number,
                    'supply_number'=>$v->supply_number,
                    'size'=>$v['size'],
                    'model'=>$v['model'],
                    'order_id'=>$orderId,
                    'cash'=>$v->cash,
                    'pack_number'=>$v['pack_number'],
                    'specification_id'=>$v['specification_id'],
                    'goods_id'=>$v['goods_id'],
                    'timestamp'=>$this->getTime()
                );
                $this->getOrderGoodsTable()->insertData($data); //插入订单商品
                //减库存
                $number = $v->number;
                $this->getGoodsTable()->updateKey($v['goods_id'], 2, 'number', $number);
                $this->getGoodsTable()->updateKey($v['goods_id'], 1, 'sale_number', $number);
                $this->getGoodsSpecificationTable()->updateKey($v['specification_id'], 2, 'number', $number);
                $this->getGoodsSpecificationTable()->updateKey($v['specification_id'], 1, 'sale_number', $number);
            }
            //订单跟踪记录
            $value = array(
                'status'=>1,
                'user_id'=>$user_id,
                'order_id'=>$orderId,
                'timestamp'=>$this->getTime()
            );
            $this->getOrderTrackingTable()->indateKey($value,1,1,$set['order_sn']);
            if ($pay_type==1)
            {//余款支付
                $value['status'] = 2;
                $this->getOrderTrackingTable()->indateKey($value,1,2,$set['order_sn']);
            }
        }
        else
        {//修改订单
            $orderId = $request->order_id;
            if (!$orderId)
            {
                return STATUS_PARAMETERS_INCOMPLETE;
            }
            $orderInfo = $this->getOrderTable()->getOne(array('id'=>$orderId));
            if (!$orderInfo)
            {
                return STATUS_NODATA;
            }
            $set = array(
                // 'pay_type' => $pay_type,
                'expect_date' => $expect_date,
                'contacts_id' => $contacts_id,
                'name' => $contactsInfo['name'],
                'mobile' => $contactsInfo->mobile,
                'address' => $contactsInfo->address,
                'description' => $contentList[$orderInfo['merchant_id']] ? $contentList[$orderInfo['merchant_id']]['content']:'',
            );
             $this->getOrderTable()->updateData($set, array('id'=>$orderId));  //生成子订单
        }
        $response->status = STATUS_SUCCESS;
        $response->id = $orderId;
        return $response;
    }
    
    /**
     * 格式化列表
     *
     * @param array $response
     *            结果
     * @param
     *            array $content 理由数组；
     * @return array $list 返回出去的结果
     *
     */
    public function checkNumber($response, $contentList,$pay_type,$expect_date,$contactsInfo)
    {
        $list = array();
        if (! $response)
        {
            return $list;
        }
        foreach ($response as $value)
        {
            $cash =0;
            $number =0;
            $merchant_id = 0;
            $user_id = 0;
            $order_good = array();
            foreach ($value as $v)
            {
                $cash += $v->cart_number*$v->s_cash;
                $number += $v->cart_number;
                $user_id = $v->user_id;
                $merchant_id = $v->merchant_id;
                //订单商品
                $set = array(
                    'price_cash'=>$v->s_cash,
                    'number'=>$v->cart_number,
                    'supply_number'=>$v->cart_number,
                    'size'=>$v['s_size'],
                    'model'=>$v['s_model'],
                    'cash'=>$v->cart_number*$v->s_cash,
                    'pack_number'=>$v['s_pack_number'],
                    'specification_id'=>$v['specification_id'],
                    'goods_id'=>$v['goods_id'],
                    'timestamp'=>$this->getTime()
                );
                 $order_good[]=$set;
            }
            $data = array(
                'order_sn' => $this->getAdminController()->generate(),
                'total_cash' => $cash,
                'type' => $v['g_type'],
                'pay_type' => $pay_type,
                'total_number' => $number,
                'total_supply_number' => $number,
                'expect_date' => $expect_date,
                'user_id' => $user_id,
                'merchant_id' => $merchant_id,
                'contacts_id' => $contactsInfo['id'],
                'name' => $contactsInfo['name'],
                'mobile' => $contactsInfo->mobile,
                'address' => $contactsInfo->address,
                'description' => isset($contentList[$merchant_id])?$contentList[$merchant_id]['content']:'',
                'timestamp' => $this->getTime()
            ); 

            $data['goods']=$order_good;
            $list[] = $data;
            $this->total_cash+=$cash;
            $this->total_number+=$number;
        }
        return $list;
    }
}
