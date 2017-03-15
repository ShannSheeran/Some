<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;
use Api5\Controller\Common\AddressItem;

/**
 * 名片提交
 * 
 * @author HY
 *        
 */
class FinancialSubmitItem extends Item
{

    /**
     * 提现提交
     *
     * @var string
     */
    public $amount;

    public $card_number;

    public $card_owner;

    public $bank_id;
    
    function __construct()
    {
        parent::__construct();
        $this->address = new AddressItem();
        
        $key = array(
            'card_number' => 'cardNumber',
            'card_owner' => 'cardOwner',
            'bank_id'=>'bankId',
        );
        $this->setOptions('key', $key); // key的转换
    }
}