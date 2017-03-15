<?php
namespace Api\Controller\Item;

use Api\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class UserDetailsItem extends Item
{
    /**
     * type
     *
     * @var String
     */
    public $type;
    /**
     * 企业名称
     *
     * @var String
     */
    public $company_name;
    /**
     * 联系人
     *
     * @var String
     */
    public $contacts_name;
    
    /**
     * 手机号码
     *
     * @var String
     */
    public $mobile;
    
    public $fax;
    
    public $email;
    
    public $qq;
    
    public $admin_id;
    
    public $description;
    
    public $region_id;
    
    public $street;
    
    public function __construct()
    {
        parent::__construct();
        $key = array(
            'type' => 'type',
            'company_name' => 'companyName',
            'contacts_name' => 'contactsName',
            'mobile' => 'mobile',
            'fax' => 'fax',
            'email' => 'email',
            'qq' => 'qq',
            'admin_id' => 'adminId',
            'description' => 'description',
            'region_id' => 'regionId',
            'street' => 'street'
        );
        $this->setOptions('key', $key);
    }
}