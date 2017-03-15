<?php
namespace Api\Controller;

/**
 * 平台支付渠道列表
 */
class PayTypeList extends CommonController
{
    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        
        $where['delete'] = DELETE_FALSE; 
        $list = $this->getAll($this->getAccountListTable(),$where);
        $total = $list['total'];
        $list_array=array();
        $item=array();
        $bankInfo = array();
        $bank_ids = array();
        foreach ($list['list'] as $i)
        {
            $bank_ids[] = $i['bank_id'];
        }
        if ($bank_ids){
            $bankInfo = $this->getBankListTable()->getDataByIn(array('id'=>$bank_ids));//银行列表
        }
        foreach ($list['list'] as $v)
        {
            $item['id']=$v->id;
            $item['name']=$bankInfo[$v->bank_id]?$bankInfo[$v->bank_id]['name']:'';
            $item['openBank']=$v->branch;
            $item['userName']=$v->name;
            $item['number']=$v->number;
            $item['imagePath']=$bankInfo[$v->bank_id]?$bankInfo[$v->bank_id]['image_path']:'';
            $item['timestamp']=$v->timestamp; 
            $list_array[]['bankCard']=$item;
        }
        $response->total = $total;
        $response->bankCards = $list_array;
        return $response;
    }
}
