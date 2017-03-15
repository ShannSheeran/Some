<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
use Api\Controller\Request\OrderWhereRequest;

/**
 * 业务，商品列表
 */
class OrderList extends CommonController
{
 
    public function __construct()
    {
        $this->myWhereRequest = new OrderWhereRequest();
        parent::__construct();
    }

    public function index()
    {  
        $requset = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $table_where = $this->getTableWhere();

        $this->checkLogin();
        $user_id = $this->getUserId();
        $status =  $table_where->status; //订单状态：0全部 1待付款 2 待审核 3待发货（已审核） 4待收货 5已完成6 已取消 7审核失败
        if(!in_array($status, array(0,1,2,3,4,5,6,7)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9091
        }       
        $list = array();
        $where = new Where();
        if ($status){
            $where->equalTo('status', $status);
        }
        $where->equalTo('user_id',$user_id)->and->equalTo('delete',0)->and->equalTo('children',0);
        if ($table_where->start_time &&$table_where->end_time)
        {
            $start_time = $table_where->start_time;
            $end_time = $table_where->end_time;
            if($start_time > date("Y-m-d 00:00:00") || $start_time >= $end_time)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9091
            }else
            {
                $where->lessThan('timestamp',$end_time);
                $where->greaterThan('timestamp', $start_time);
            }
        }

        if ($table_where->search_key)
        {
            $sub_where = new Where();
            $sub_where->like('name', '%'.$table_where->search_key.'%')->or->like('company_name', '%'.$table_where->search_key.'%');
            $where->andPredicate($sub_where);
        }
        
        $orderList = $this->getAll($this->getViewOrderTable(), $where);
        $total = $orderList['total'];
        $list = $this->setList($orderList['list']);  
        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->orders = $list;
        return $response;
    }

    /**
     * 格式化列表
     * 
     * @param array $response
     *            结果
     * @param
     *           
     * @return array $list 返回出去的结果
     *        
     */
    public function setList($response)
    {
        $table_where = $this->getTableWhere();
        $list = array();
        if (! $response)
        {
            return $list;
        } 
        foreach ($response as $k => $v)
        {
            $item = array();
            $images =array();
            $goods_list = array();
            $item['id'] = $v->id;
            $item['orderSn'] = $v->order_sn;
            $item['totalCash'] = $v->total_cash;
            $item['status'] = $v->status;
            $item['payType'] = $v->pay_type;
            $item['merchantId'] = $v->merchant_id;
            $item['merchantName'] = $v->merchant_name?$v->merchant_name:'吉宏园艺自营';
            $goodsInfo = $this->getViewOrderGoodsTable()->fetchAll(array('order_id'=>$v->id,'delete'=>DELETE_FALSE));
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
               $img_id = $val->image ? explode(',', trim($val->image,",")) : array();
               $goods['imagePath'] = $images[$img_id[0]]?$images[$img_id[0]]['path'].$images[$img_id[0]]['filename']:'';
               $goods_list[]['goods'] = $goods;
            }
            $item['goodses'] = $goods_list;
            $item['timestamp'] = $v->timestamp;
            $list[]['order'] = $item;
        }
        return $list;
    }
}
