<?php
namespace Api5\Controller;

use Api5\Controller\Request\CompanyListWhereRequest;
use Zend\Db\Sql\Where;
use Api5\Controller\Common\WhereRequest;

/**
 * 名片列表
 */
class CompanyList extends CommonController
{

    public function __construct()
    {
        $this->myWhereRequest = new CompanyListWhereRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $user_id = $this->getUserId();
        $user = array('id' => $user_id);
        $action = $request->action;
        $action_array = array(
            1,
            2,
            3
        );
        if (!in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        
        if(isset($action) && $action == 1){
            $data = $this->getAll($this->getCompanyTable(), array('delete'=>0 ,'user_id'=>$user_id));
            if(!$data){
                return STATUS_NODATA;
            };
            $list = array();
            if ($data['list']) {
                foreach ($data['list'] as $val) {   
                           
                    $address = array(
                        'regionId' => (int)$val['region_id'],
                        'regionInfo' => (array)json_decode($val['region_info']),
                    );
                    
                    $list[]['company'] = array(
                        'id' => $val['id'],
                        'name'=>$val['name'],
                        'imagePath'=>$this->getImagePath($val['image'])['path'],
                        'address'=>$address,
                        'categoryId' => $val['category_id'],
                        'scale' => $val['scale'],
                        'home' => $val['home'],
                        'statAudit' => $val['stat_audit'],
                        'auditStatus' => $val['audit_status'],
                        'timestampUpdate' => $val['timestamp_update'],
                        'user' => $user,
                    );
               }
            }
        }
        
        if(isset($action) && $action == 2){
            
            $data = $this->getViewPageCarteTable()->fetchAll(array('user_id'=>$user_id,'delete'=>0));
            $list = array();
            if ($data) {
                $ids = array();
                foreach ($data as $val) { 
                    if (!$val['company_id'] || in_array($val['company_id'], $ids)){
                        continue;
                    }
                    $company = $this->getCompanyTable()->getOne(array('id'=>$val['company_id'],'delete'=>0));
                  
                    if(!$company){
                        continue;
                    }
                    
                    $ids[] = $val['company_id'];
                    $address = array(
                        'regionId' => (int)$val['region_id'],
                        'regionInfo' => (array)json_decode($val['region_info']),
                    );

                    $logo = $this->getImageTable()->getOne(array('id'=>$val['image']));
                    $image = $logo['path'] . $logo['filename'];
                    
                    $maker = array('id' => $val['company_makerid']);
                   
                    $list[]['company'] = array(
                        'id' => $val['companys_id'],
                        'name'=> $val['company_name'],
                        'imagePath' => $image,
                        'address' => $address,
                        'categoryId' => $val['category_id'],
                        'scale' => $val['scale'],
                        'home' => $val['home'],
                        'statAudit' => $company->stat_audit,
                        'auditStatus' => $company->audit_status,
                        'timestampUpdate' => $val['company_updatetime'],
                        'user' => $maker,
                    );
                }
            }        
           
            //$data = array();
            $data['total'] = count($list);
        }
        
        if(isset($action) && $action == 3){
            $table_where = $this->getTableWhere();
            $where = new where();
            $where->equalTo('delete', DELETE_FALSE);
            /* $where->equalTo('audit_status', 2); */
            if ($table_where->search_key) {
                $sub_where = new Where();
                $sub_where->like('name', '%' . $table_where->search_key . '%');
                $where->addPredicate($sub_where);
            }
            
            if ($table_where->address->region_id) {
                $where->equalTo('region_id', $table_where->address->region_id);
            }
            
            if ($table_where->parentId) {
                $where->equalTo('parent_id', $table_where->parentId);
            }
            
            if ($table_where->categoryId) {
                $where->equalTo('category_id', $table_where->categoryId);
            }

            $data = $this->getAll($this->getCompanyTable(), $where);
            $list = array();
            if ($data['list']) {
                foreach ($data['list'] as $val) {
                                        $address = array(
                        'regionId' => (int) $val['region_id'],
                        'regionInfo' => (array) json_decode($val['region_info'])
                    );
                    $maker = array('id' => $val['user_id']);
                    $list[]['company'] = array(
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'imagePath' => $this->getImagePath($val['image'])['path'],
                        'address' => $address,
                        'isRecommend' => $val['is_top'],
                        'categoryId' => $val['category_id'],
                        'scale' => $val['scale'],
                        'home' => $val['home'],
                        'statAudit' => $val['stat_audit'],
                        'auditStatus' => $val['audit_status'],
                        'timestampUpdate' => $val['timestamp_update'],
                        'user' => $maker,
                    );
                }
            }
        }
        $response->companys = (array)$list;
        $response->total = isset($data['total']) ? count($list) : 0;
        return $response;
    }
}







