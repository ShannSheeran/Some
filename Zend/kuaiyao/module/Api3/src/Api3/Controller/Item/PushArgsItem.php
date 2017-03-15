<?php
namespace Api3\Controller\Item;

use Api3\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class PushArgsItem extends Item
{
    /**
     * 外键id，如任务id
     * @var Number
     */
    public $id;
    
    /**
     * 参数1：快应币
     * @var Number
     */
    public $money = 0;
    
    /**
     * 参数2：积分
     * @var Number
     */    
    public $point = 0;
    
    /**
     * 参数3：数量（人数/星星/...）
     * @var Number
     */
    public $number;
    
    /**
     * 参数4：名称（昵称/活动名称/...）
     * @var String
     */
    public $name;
    
    /**
     * 参数5：名称2（居然要两个名称的推送）
     * @var String
     */
    public $name2;
    
    /**
     * 安卓用推送通知id
     * @var number
     */
    public $nid;
    
    public function setThisValue($type, $value)
    {
        switch ($type)
        {
            case 1:
                $this->money = (int)$value;
                break;
            case 2:
                $this->point = (int)$value;
                break;
            case 3:
                $this->number = (int)$value;
                break;
            case 4:
                $this->name = $value;
                break;
            case 5:
                $this->name2 = $value;
                break;
            default:
                break;
        }
        return $this;
    }
}