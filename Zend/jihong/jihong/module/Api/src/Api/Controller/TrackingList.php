<?php
namespace Api\Controller;

use Api\Controller\Request\TrackingRequest;

/**
 * 跟踪列表
 */
class TrackingList extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new TrackingRequest();//TrackingWhereRequest();
        parent::__construct();
    }
    
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $table_where = $this->getTableWhere();
        $this->checkLogin();
        
        $type = (int)$request->type;
        if (!in_array($type, array(0,1)))
        {
            return  STATUS_PARAMETERS_CONDITIONAL_ERROR; //9010
        }
        $where = array();
        $where['user_id'] = $this->getUserId();
        $list = array();
        if ($type==1)
        {//发布商品状态跟踪
            if($request->id)
            {
                $where['goods_id'] = $request->id;
            }
            $list = $this->getAll($this->getViewGoodsTrackingTable(),$where);
        }else
        {//订单状态跟踪
            if($request->id)
            {
                $where['order_id'] = $request->id;
            }
            $list = $this->getAll($this->getOrderTrackingTable(),$where);
        }
        
        $list_array = array();
        $images =array();
        if ($type==1)
        {//商品发布
            $image_ids = array();
            foreach ($list['list'] as $i)
            {//拿到所有订单商品的第一个图片id
                if ($i->image)
                {
                    $image_id = explode(',', trim($i->image,","));
                    $image_ids[] = $image_id[0];
                }
            }
            $images = $this->getImageTable()->getImages($image_ids, 1);
        }
        foreach ($list['list'] as $v)
        {
            $item = array();
            $images = array();
            $item['status']= $v->status;
            $item['description']= $v->description;
            $img_id = array();
            if ($type==1)
            {//商品发布
                $item['id'] = $v->goods_id;
                $img_id = $v->image ? explode(',', trim($v->image,",")) : array();
            }else
            {//订单商品图片查询
                $item['id'] = $v->order_id;
                $order_good = $this->getViewOrderGoodsTable()->getOne(array('order_id'=>$v->id));
                $img_id = $order_good && $order_good->image ? explode(',', trim($order_good->image,",")) :'' ;
                if ($img_id)
                {
                    $images = $this->getImageTable()->getImages(array($img_id[0]), 1);
                }
            }
            $item['imagePath'] = $images ? $images[$img_id[0]]['path'].$images[$img_id[0]]['filename']:'';
            $item['timestamp']= $v->timestamp;
            $list_array[]['tracking'] = $item;
        }
        $response->trackings = $list_array;
        $response->total = $list['total'];
        return $response;
    }
}
