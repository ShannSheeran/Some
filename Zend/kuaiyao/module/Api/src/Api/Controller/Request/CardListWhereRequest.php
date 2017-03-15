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
class CardListWhereRequest extends WhereRequest
{

    /**
     * 发送模版的参数可能由客户端提供
     *
     * @var Array
     */
    public $mobiles;
}