<?php
namespace Api3\Controller;

use Api3\Controller\Request\CompanyDetailsRequest;

/**
 * 名片详情
 *
 * @author
 *
 */
class CompanyDetails extends CommonController
{

    function __construct()
    {
        $this->myRequest = new CompanyDetailsRequest();
        parent::__construct();
    }

    /**
     *
     * @return string|\Api21\Controller\Common\Response
     */
    public function index()
    {
        $request = $this->getAiiRequest(); // 获取请求参数
        $response = $this->getAiiResponse(); // 初始化返回参数
        
        $where = array();
        $id = (int)$request->id;//请求得到公司id

        if($id){
            $company_info = $this->getCompanyTable()->getOne(array('id' => $id));
            if(!$company_info){
                return STATUS_NODATA;
            }
            
            $image = $this->getImageTable()->getOne(array('id' => $company_info['image']));
            
            $address = array(
                'regionId' => $company_info['region_id'] ? $company_info['region_id'] : '0',
                'regionInfo' => (array)json_decode($company_info['region_info']),
                'street' => $company_info['street'],
                'latitude' => $company_info['latitude'],
                'longitude' => $company_info['longitude'],
            );          
            
            $user = array('id'=>$company_info['user_id']);
            
            $details = array(
                'id' => $company_info['id'],
                'name' => $company_info['name'],
                'imageId' => $company_info['image'],
                'imagePath' => $image['path'].$image['filename'],
                'address' => $address,
                'categoryId' => $company_info['category_id'],
                'scale' => $company_info['scale'],
                'home' => $company_info['home'],
                'telephone' => $company_info['telephone'],
                'description' => strip_tags($company_info['description']),
                'project' => strip_tags($company_info['project']),
                'user' => $user,
            );
            
        }else{
            return STATUS_NODATA;
        }
        
        
        $response->company = $details;
        return $response;
    }
}