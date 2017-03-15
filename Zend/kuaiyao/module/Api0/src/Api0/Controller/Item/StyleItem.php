<?php
namespace Api0\Controller\Item;

use Api0\Controller\Common\Item;
/**
 *
 * @author WZ
 *        
 */
class StyleItem extends Item
{

    /**
     * 响铃：1开；2关；
     */
    public $sound;
    
    /**
     * 振动：1开；2关；
     */
    public $vibrate;
    
    /**
     * 免打扰：1开；2关；
     * @var unknown
     */
    public $quiet;
    
    /**
     * 免打扰开始时间。
     */
    public $quiet_start_time;
    
    /**
     * 免打扰结束时间。
     */
    public $quiet_end_time;

    function __construct()
    {
        parent::__construct();
        $key = array(
            'quiet_start_time' => 'quietStartTime',
            'quiet_end_time' => 'quietEndTime',
        );
        $this->setOptions('key', $key);
    }
}