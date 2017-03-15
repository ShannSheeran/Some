<?php
namespace Api3\Controller;


use Api3\Controller\Request\CompanySubmitRequest;
use Index\Controller\CommonController as Index;
use Zend\Db\Sql\Where;

/**
 * 名片提交
 */
class CompanySubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new CompanySubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_id = $this->getUserId();
        $action = $request->action ? $request->action : 1;
        
        $action_array = array(
            1,
            //2,
        );
        
        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
       
        $name = $request->company->name;
        $id = $request->company->id ? $request->company->id : '';
        if (!$name) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        
        if(!$id){
            $company_checkName = $this->getCompanyTable()->getOne(array('name'=>$name,'delete'=>0));
            if ($company_checkName) {// 返回公司名已存在
                return STATUS_COMPANY_NAMEEXIST;
            }
        }
        
        if($id){//修改
            $where = new Where();
            $where->equalTo('id', $id);
            $where->equalTo('user_id', $user_id);
            $where->equalTo('delete', DELETE_FALSE);
            $company_info = $this->getCompanyTable()->getOne($where);
            if (!$company_info) {
                // 返回公司名已存在
                return STATUS_COMPANY_NOPOWER;
            }
        }

        $region_info = $this->getRegionInfoArray($request->company->address->region_id);
        $address = $this->regionInfoToString($region_info['region_info']) . ' ' . $request->company->address->street;
        $data = array(
            'name' => $request->company->name,
            'image' => $request->company->id_image,
            'region_id' => $request->company->address->region_id,
            'region_info' => $region_info['region_info'],
            'street' => $request->company->address->street,
            'address' => $address,
            'latitude' => $request->company->address->latitude,
            'longitude' => $request->company->address->longitude,
            'category_id' => $request->company->category_id,
            'scale' => $request->company->scale,
            'home' => $request->company->home,
            'telephone' => $request->company->telephone,
            'timestamp' => $this->getTime(),
            'user_id' => $user_id
        );
        
        if($id){
            $company_info = $this->getCompanyTable()->getOne(array('id'=>$id,'user_id'=>$user_id));
            if($company_info){
                $this->getCompanyTable()->updateData($data,array('id'=>$id));
            }else{
                return STATUS_NODATA;
            }
        }else{
            $id = $this->getCompanyTable()->insertData($data);
        }
        
        $response->id = $id;//返回company_id
        return $response;
        
    }
}







