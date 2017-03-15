<?php
namespace Api\Controller;

use Api\Controller\Request\GoodsSubmitRequest;
 
/**
 * 发布商品
 */
class GoodsSubmit extends CommonController
{
    public function __construct()
    {
        $this->myRequest = new GoodsSubmitRequest();
        parent::__construct();
    }
    public function index()
    {
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $goods = $request->goods;
        $specification = $goods->specification;
        $this->checkLogin();
        if($this->getUserType()==1)
        {
            return STATUS_ILLEGAL_OPERATION;//1406
        }
        if ($goods->id)
        {//编辑商品时，先判断该商品是否为当前用户发布的
            $info = $this->getGoodsTable()->getOne(array('id' => $goods->id,'user_id' =>$this->getUserId()));
            if (!$info)
            {
                return STATUS_NODATA;
            }
        }
        $check_goods= array("name","category");
        $check_specification = array("size","cash","number","height","canopy","packNumber");
        foreach ($check_goods as $v)
        {//判断数据是否符合要求
            if($goods->$v == ''){
                return STATUS_PARAMETERS_INCOMPLETE;
            }
        }
        if ($specification->size=='')
        {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        foreach ($check_specification as $v)
        {//判断数据是否符合要求
            if($specification->$v <=0){
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
            }
        }
        if (($goods->salse_type==1 && strtotime($goods->delivery_date)<time())||!in_array($goods->salse_type, array(0,1)))
        {//预售日期必须大于当前日期
            return STATUS_PARAMETERS_CONDITIONAL_ERROR;
        }
        //商品详情
        $set = array(
            'name' =>$goods->name,
            'salse_type' => $goods->salse_type,
            'delivery_date' => isset($goods->delivery_date)?$goods->delivery_date:'',
            'image' => implode(',',$goods->image),
            'category_id' => $goods->category,
            'description' => $goods->description,
            'message' => $goods->message,
            'number' => $specification->number,
            'min_cash' => $specification->cash,
            'max_cash' => $specification->cash
        );  
        //规格详情
        $data = array(
            'size' => $specification->size,
            'model' => $specification->model,
            'cash' => $specification->cash,
            'number' => $specification->number,
            'height' => $specification->height,
            'bud_number' => $specification->budNumber,
            'canopy' => $specification->canopy,
            'pack_number' => $specification->packNumber 
        );
        if ($goods->id)
        {// 有id就是修改
            $where = array(
                'id' => $goods->id,
            );
            $this->getGoodsTable()->update($set, $where);
            $id = $goods->id;
            $specification_id = $specification->specificationId;
            if ($specification_id)
            {
                $specification_info = $this->getGoodsSpecificationTable()->getOne(array('id' => $specification_id,'goods_id' =>$id));
                if ($specification_info)
                {//更新规格
                     $this->getGoodsSpecificationTable()->update($data, array('id'=>$specification_id));

                     $response->status = STATUS_SUCCESS;
                     $response->id = $id;
                     return $response;
                }
            }
        }
        else
        {//没id就是插入新的
            $set['type'] = 1;
            $set['goods_sn'] = $this->getAdminController()->generate();
            $set['user_id'] = $this->getUserId();
            $set['delete'] = DELETE_FALSE;
            $set['timestamp'] = $this->getTime();
            $id = $this->getGoodsTable()->insertData($set);
            //发布商品跟踪
            $goodsInfo = $this->getGoodsTable()->getOne(array('id'=>$id));
            $value['status'] = 1;
            $value['user_id'] = $this->getUserId();
            $value['goods_id'] = $id;
            $value['timestamp'] = $this->getTime();
            $this->getGoodsTrackingTable()->indateKey($value,2,1,$goodsInfo['goods_sn']);
        }
        //插入商品规格
        $data['goods_id'] = $id;
        $data['timestamp'] = $this->getTime();
        $this->getGoodsSpecificationTable()->insertData($data);
        $response->status = STATUS_SUCCESS;
        $response->id = $id;
        return $response;
    }
}
