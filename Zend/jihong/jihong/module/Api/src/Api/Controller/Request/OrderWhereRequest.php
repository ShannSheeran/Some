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
class OrderWhereRequest extends WhereRequest
{
    /**
     * 订单状态：0全部 1待付款 2 待审核 3待发货（已审核） 4待收货 5已完成6 已取消 7审核失败
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
    
    function __construct(){
        parent::__construct();
        $key = array(
            'start_time' => 'startTime',
            'end_time' =>'endTime'
        );
        $this->setOptions('key', $key);
    }
}