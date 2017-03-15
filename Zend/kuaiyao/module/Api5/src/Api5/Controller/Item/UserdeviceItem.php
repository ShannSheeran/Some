<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class UserdeviceItem extends Item
{
   
    public $uuid;
    
    public $major;
    
    public $minor;
    
    public function __construct()
    {
        parent::__construct();
    }
}