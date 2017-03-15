<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;
use Api5\Controller\Common\AddressItem;

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