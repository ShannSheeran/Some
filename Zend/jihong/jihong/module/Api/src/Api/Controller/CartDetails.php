<?php
namespace Api\Controller;

/**
 * 购物车
 */
class CartDetails extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $where['user_id']=$this->getUserId();
        $where['delete'] = DELETE_FALSE;
        $list = $this->getAll($this->getViewCartTable(),$where);
        $total = $list['total'];
        $list = $this->setList($list['list']);
        $response->total = $total;
        $response->merchants = $list;
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
    public function setList($list)
    {
        $list_array = array();
        $list_merchant = array();
        $item = array();
        $images = array();
        $image_ids = array();
        foreach ($list as $i)
        {//拿到所有订单商品的第一个图片id
            if ($i->image)
            {
                $image_id = explode(',', trim($i->image,","));
                $image_ids[] = $image_id[0];
            }
        }
        if($image_ids)
        {
            $images = $this->getImageTable()->getImages($image_ids, 1);
        }
        foreach ($list as $v)
        {
            $image_list = array();
            $item['id']=$v->id;
            $item['goodsId']=$v->goods_id;
            $item['name']=$v->g_name;
            $item['unit']=$v->unit;
            $item['number']=$v->cart_number;
            $img_id = $v->image ? explode(',', trim($v->image,",")) : array();
            if (isset($img_id[0]) && isset($images[$img_id[0]]['path']))
            {//组装图片数组对象
                $image_info['id']=$images[$img_id[0]]['id'];
                $image_info['path']= $images[$img_id[0]]['path'].$images[$img_id[0]]['filename'];
                $image_list[]['image'] = $image_info;
            }
            $item['images'] = $image_list;
            
            //组装商品规格信息
            $specification = array();
            $check_specification = array(); //检查选中商品规格是否存在
            $info = $this->getGoodsSpecificationTable()->fetchAll(array('goods_id'=>$v->goods_id,'delete'=>DELETE_FALSE));
            foreach ($info as $value)
            {
                $check_specification[]= $value->id;
                $arr['id'] = $value->id;
                $arr['size'] = $value->size;
                $arr['model'] = $value->model;
                $arr['cash'] = $value->cash;
                $arr['number'] = $value->number;
                $arr['height'] = $value->height;
                $specification[]['specification'] = $arr;
            }
            if (in_array($v->specification_id, $check_specification))
            {//判断选中属性是否存在，不存在则删除对应购物车id
                $item['specifications'] = $specification;
                $item['selectedId'] = $v->specification_id;
                
                //以商家id作为下标进行数据组装
                $list_array[$v->merchant_id]['merchantId']=$v->merchant_id;
                $list_array[$v->merchant_id]['merchantName']=$v['company_name']?$v['company_name']:'吉宏园艺自营';
                $list_array[$v->merchant_id]['goodses'][]['goods']=$item;
            }
            else
            {
                $this->getCartTable()->delete(array('id'=>$v->id,'user_id'=>$this->getUserId()));
            }
        }
        foreach ($list_array as $k=>$v)
        {//以商家作为对象进行数据重组
            $list_merchant[]['merchant'] = $v;
        }
        return $list_merchant;
    }
}
