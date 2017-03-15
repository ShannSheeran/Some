<?php
namespace Api\Controller;

use Api\Controller\Request\GoodsDetailsRequest;

/**
 * 业务，商品列表
 */
class GoodsSpecification extends CommonController
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
        $where['id'] = $request->specification_id;
        $where['goods_id'] = $request->id;
        $where['delete'] = DELETE_FALSE;
        $data = $this->getGoodsSpecificationTable()->getOne($where);
        if (!$data){
            return STATUS_NODATA;
        }
        $item = array(
            'id' => $data['id'],
            'size' => $data['size'],
            'model' => $data['model'],
            'cash' => $data['cash'],
            'number' => $data['number']
        );

        $response->info = $item;
        return $response;
    }
}