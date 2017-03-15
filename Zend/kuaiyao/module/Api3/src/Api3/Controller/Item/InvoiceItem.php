<?php
namespace Api3\Controller\Item;

use Api3\Controller\Common\Item;
use Api3\Controller\Common\AddressItem;

/**
 * 发票对象
 * 
 * @author WZ
 *        
 */
class InvoiceItem extends Item
{

    public $status;
    
    public $type;

    public $name;
}