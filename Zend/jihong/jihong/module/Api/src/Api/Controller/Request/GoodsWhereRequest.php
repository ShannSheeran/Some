<?php
namespace Api\Controller\Request;

use Api\Controller\Common\WhereRequest;
/**
 * 定义接收类的属性
 * 继承基础BeseQuery
 *
 * @author WZ
 *        
 */
class GoodsWhereRequest extends WhereRequest
{
    /**
     * @var type:1不区分供应商商品（默认）；2我的商品
     */
    public $type;
    /**
     * @var 分类id
     */
    public $category_id ;
    
    /**
     * 0全部 1待审核；2审核通过（默认）；3上架；4下架；5已取消 6审核失败
     * @var 上下架
     */
    public $status;
    
    /**
     * 开始时间
     * @var 
     */
    public $start_time;
    
    /**
     * 结束时间
     * @var 
     */
    public $end_time;
    

    function __construct()
    {
        parent::__construct();
        $key = array(
            'type' => 'type',
            'category_id' => 'categoryId',
            'start_time' => 'startTime',
            'end_time' => 'endTime'
        );
        $this->setOptions('key', $key);
    }
}