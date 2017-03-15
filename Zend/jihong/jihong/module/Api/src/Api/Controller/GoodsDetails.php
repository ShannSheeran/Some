<?php
namespace Api\Controller;

use Api\Controller\Request\GoodsDetailsRequest;

/**
 * 业务，商品列表
 */
class GoodsDetails extends CommonController
{ 
    public function __construct()
    {
        $this->myRequest = new GoodsDetailsRequest();
        parent::__construct();
    }

    public function index()
    {  
       $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $where = array();
        $where['id'] = $request->id;
        $where['delete'] = DELETE_FALSE;
        $data = $this->getViewGoodsTable()->getOne($where);
        //商品规格信息
        $info = $this->getGoodsSpecificationTable()->fetchAll(array('delete'=>DELETE_FALSE,'goods_id'=>$data['id']));
        if (!$data || !$info){
            return STATUS_NODATA;
        }
        $item = array(
            'id' => $data['id'],
            'name' => $data['name'],
            'goodsSn' => $data['goods_sn'],
            //'level' => $data['level'],
            'salseType' => $data['salse_type'],
            'status' => $data['status'],
            'deliveryDate' => $data['delivery_date'],
            'cashRange' => 0,
            'originalPrice' => $data['original_price'],
            'number' => $data['number'],
            'unitName' => $data['unit_name'],
            'merchantId' => $data['user_id'],
            'merchantName' => $data['company_name']?$data['company_name']:'吉宏园艺自营',
            'reason' => $data['reason'],
            'message' => $data['message'],
            'saleNumber' => $data['sale_number'],
            'categoryId' => $data['category_id'],
            'categoryName' => $data['category_name'],
            'description' => $data['description'],
            'timestamp' => $data['timestamp'],
            'timestampUpdate' => $data['timestamp_update']
        );
        if ($data->min_cash>0 && $data->max_cash>0)
        {
            $item['cashRange']= floatval($data->min_cash).'-'.floatval($data->max_cash);
        }
        elseif ($data->max_cash>0 && $data->min_cash<=0)
        {
            $item['cashRange'] = floatval($data->max_cash);
        }
        else
        {
            $item['cashRange'] = floatval($data->min_cash);
        }
        $images = array();
        $image_list =array();
        if ($data['image'])
        {//商品图片
            $image_id = explode(',', trim($data['image'],","));
            $images = $this->getImageTable()->getImages($image_id, 0);
        }
        if ($images){
            foreach ($images as $v){
                $imageInfo['id']=$v['id'];
                $imageInfo['path']=isset($v['path']) ? $v['path'].$v['filename'] : '';
                $image_list[]['image'] = $imageInfo;
            }
        }
        $item['images'] = $image_list;
        //商品规格信息
        $specifications =array();
        foreach ($info as $v)
        {
            $specification = array(
                'id' => $v['id'],
                'size' => $v['size'],
                'model' => $v['model'],
                'cash' => floatval($v['cash']),
                'number' => $v['number'],
                'saleNumber' => $v['sale_number'],
                'height' => $v['height'],
                'budNumber' => $v['bud_number'],
                'canopy' => $v['canopy'],
                'packNumber' => $v['pack_number'],
            );
            $specifications[]['specification'] = $specification;
        }
        $item['specifications'] = $specifications;
        $response->goods = $item;
        return $response;
    }
}