<?php
namespace Api5\Controller\Item;

use Api5\Controller\Common\Item;

/**
 *
 * @author WZ
 *        
 */
class UserDetailsItem extends Item
{
    /**
     * 用户密码
     */
    public $password;

    /**
     * 手机号码
     *
     * @var String
     */
    public $mobile;

    /**
     * 性别：1男；2女；0未知；
     *
     * @var String
     */
    public $sex;
    
    /**
     * 生日
     *
     * @var date
     */
    public $birthday;
    
    /**
     * 用户昵称
     *
     * @var String
     */
    public $nickname;
    
    public $image;
    
    public $image_path;
    
    public $integral;
    
    /**
     * 职业
     * @var unknown
     */
    public $profession;
    
    public $email;
    
    public $open_id;
    
    public $partner;
    
    public $company;
    
    public $seller;
    
    public $info_stat;
    
    public $shop_stat;
    
    public $forum_stat;
    
    public $sign_in;
    
    public $region_id;
    
    public $city_region_id;
    
    public $location_region_id;
    
    public $answers;
    
    public $timestamp;
    
    public $continue;
    
    public function __construct()
    {
        parent::__construct();
        $key = array(
            'image_path' => 'imagePath',
            'open_id' => 'openId',
//             'company' => 'isCompany',
            'info_stat' => 'statInfo',
            'shop_stat' => 'statGoods',
            'forum_stat' => 'statPost',
            'sign_in' => 'isSignIn',
            'integral' => 'point',
            'region_id' => 'regionId',
            'city_region_id' => 'regionIdCity',
            'location_region_id' => 'regionIdAuto',
            'continue' => 'continueSignDay',
        );
        $this->setOptions('key', $key);
        
        $functions = array(
            'nickname' => array(
                'key' => 'findSensitiveWord',
                'true' => STATUS_SENSITIVE_WORD
            ),
        );
        $this->setOptions('functions', $functions);
    }
}