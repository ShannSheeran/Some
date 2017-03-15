<?php
namespace Api\Controller;

use Zend\Db\Sql\Where;
use Api\Controller\Request\GoodsWhereRequest;

/**
 * 业务，商品列表
 */
class GoodsList extends CommonController
{

    
    public $status = 3;
    
    public $action = 1;
    
    
    public function __construct()
    {
        $this->myWhereRequest = new GoodsWhereRequest();
        parent::__construct();
    }

    public function index()
    {  
        $requset = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $table_where = $this->getTableWhere();
        
        // 1全部商品（默认）；2普通商品；3新品上市；4 促销活动；5为您推荐
        $action  = $this->action =$requset->action ? $requset->action : 1;
        $order_by = $requset->table->order_by ? $requset->table->order_by : 1;//1智能排序（默认）；2价格；3销量；
        $order_type = $requset->table->order_type ? $requset->table->order_type : 1;//注意：1默认，需要传2（正序）
        $status =  $table_where->status?$table_where->status:3;  //判断是否传商品状态，默认3上架状态
        $type = $table_where->type ? $table_where->type :1;  //判断是我的商品列表还是，所有供应商商品的列表，默认为所有供应商
        $category_id = $table_where->category_id ? $table_where->category_id:0;
        if(!in_array($action, array(1,2,3,4,5))||!in_array($type, array(1,2))||!in_array($status, array(1,2,3,4,5,6,7)))
        {
            return STATUS_PARAMETERS_CONDITIONAL_ERROR; //9010
        }     
        $list = array();
        $where = new Where();
        $where->equalTo('delete',DELETE_FALSE)->and->equalTo('type',1);
        if ($type==2)
        {//供应商发布的商品
            $this->checkLogin();
            if ($this->getUserType()==1)
            {//判断用户类型是否为供应商
                return STATUS_ILLEGAL_OPERATION; //1046
            }
            $where->equalTo('user_id', $this->getUserId());
        }
        if ($status!=7)
        {//判断是否传商品状态
            $where->equalTo('status', $status);
        }
        if ($action!=1)
        {//判断是否传商品类型
            $good_type=array(
                2=>0,
                3=>1,
                4=>2,
                5=>3
            );
            $where->equalTo('referrer_type',$good_type[$action]);
        }
        
        if ($category_id)
        {//判断是否传分类id
            $categoryInfo = $this->getGoodsCategoryTable()->fetchAll(array('parent_id'=>$category_id));
            if ($categoryInfo)
            {//判断分类是大类还是小类
                $category_ids = array();
                foreach ($categoryInfo as $v)
                {
                    $category_ids[]=$v['id'];
                }
                if ($category_ids)
                {
                    $category_ids[] = $category_id;
                    $where->in('category_id',$category_ids);
                }
            }
            else
            {
                $where->equalTo('category_id',$table_where->category_id);
            }
        }
        if ($table_where->start_time &&$table_where->end_time)
        {
            $start_time = $table_where->start_time;
            $end_time = $table_where->end_time;
            if($start_time > date("Y-m-d 00:00:00") || $start_time >= $end_time)
            {
                return STATUS_PARAMETERS_CONDITIONAL_ERROR;
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
        $goodsList = $this->getAll($this->getViewGoodsTable(),$where);
        $total = $goodsList['total'];
        $list = $this->setList($goodsList['list'], $action);
        $response->status = STATUS_SUCCESS;
        $response->total = $total;
        $response->goodses = $list;
        return $response;
    }
    /**
     * 
     * @abstract order_by 1、timestamp
     * @param number $order_by
     * @return string
     */
    public function OrderBy($order_by = 1)
    { 
        if ($order_by==1)
        {
            $result = 'timestamp desc , sale_number';
        }elseif ($order_by==2)
        {
            $result= 'min_cash';
        }elseif ($order_by==3)
        {
            $result = 'sale_number';
        }
        return $result;
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
    public function setList($response, $type)
    {
        $table_where = $this->getTableWhere();
        $list = array();
        $image_ids = array();
        if (! $response)
        {
            return $list;
        }
        
        foreach ($response as $i)
        {
            if ($i->image)
            {
                $image_id = explode(',', trim($i->image,","));
                $image_ids[] = $image_id[0];
            }
        }
        $images = $this->getImageTable()->getImages($image_ids, 1);
        foreach ($response as $k => $v)
        {
            $item = array();
            $item['id'] = $v->id;
            $item['name'] = $v->name;
            $item['goodsSn'] = $v->goods_sn;
            $item['cashRange'] = $v->min_cash;
            $item['originalPrice'] = $v->original_price;
            $item['number'] = $v->number;
            $item['saleNumber'] = $v->sale_number;
            $item['companyName'] = $v->company_name;
            $item['status'] = $v->status;
            $item['categoryId'] = $v->category_id;
            $item['categoryName'] = $v->category_name;
            $item['referrer_type'] = $v->referrer_type;
            $img_id = array();
            $img_id = $v->image ? explode(',', trim($v->image,",")) : array();     
            $item['imagePath'] = (isset($img_id[0]) && isset($images[$img_id[0]]['path'])) ? $images[$img_id[0]]['path'].$images[$img_id[0]]['filename'] : '';       
            $item['timestamp'] = $v->timestamp;
            $list[]['goods'] = $item;
        }
        return $list;
    }
}
