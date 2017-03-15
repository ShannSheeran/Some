<?php
namespace Api5\Controller;

use Api5\Controller\Request\CompanySubmitRequest;
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
        $user_id = $this->getUserId(); // 65
        $action = $request->action ? $request->action : 1;
        $id = $request->company->id ? $request->company->id : '';
        
        $action_array = array(
            1
        );
        // 2,
        
        
        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        
        $name = $request->company->name;
        
        if (! $name) {
            return STATUS_PARAMETERS_INCOMPLETE;
        }
        
        if (! $id) {
            $company_checkName = $this->getCompanyTable()->getOne(array(
                'name' => $name,
                'delete' => 0
            ));
            
            if ($company_checkName) { // 返回公司名已存在
                return STATUS_COMPANY_NAMEEXIST;
            }
        }
        
        if ($id) { // 修改
            $where = new Where();
            $where->equalTo('id', $id);
            $where->equalTo('user_id', $user_id);
            $where->equalTo('delete', DELETE_FALSE);
            $company_info = $this->getCompanyTable()->getOne($where);
            if (! $company_info) {
                // 返回公司名已存在
                return STATUS_COMPANY_NOPOWER;
            }
        }
        
        $region_info = $this->getRegionInfoArray($request->company->address->region_id);
        $address = $this->regionInfoToString($region_info['region_info'])  . ' ' /*. $request->company->address->street */;
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
            'license_image' => $request->company->license_Image->id,
            'ID_image' => $request->company->faren_id_Image->id,
            'user_id' => $user_id
        );
     /*    foreach( $arr as $k=>$v){
            if( !$v )
                unset( $arr[$k] );
        } */
        
        if ($request->company->provideTags) {
            $pro_array = array();
            foreach ($request->company->provideTags as $k => $tag) {
                if(trim($tag->tag->name)){
                    $tag = $tag->tag;
                    $pro_array[] = $tag->name; 
                }
            }
        }
       
        if ($request->company->needsTags) {
            $need_array = array();
            foreach ($request->company->needsTags as $k => $need_tag) {
               
                if(trim($need_tag->tag->name)){
                    $need_tag = $need_tag->tag;
                    $need_array[] = $need_tag->name;
                }
            }
        }

        if ($id) {
            $company_info = $this->getCompanyTable()->getOne(array(
                'id' => $id,
                'user_id' => $user_id
            ));
            if ($company_info) {
                $this->getCompanyTable()->updateData($data, array(
                    'id' => $id
                ));
                if ($request->company->provideTags) {
                    
                    $ids = array(
                        'foreign_id' => $id
                    );
                    $this->getTagsRelationsTable()->delete($ids);
                    $this->tags_add($pro_array, 1, $id);
                }else{
                    $ids = array(
                        'foreign_id' => $id
                    );
                    $this->getTagsRelationsTable()->delete($ids);
                }
                if ($request->company->needsTags) {
                    $this->tags_add($need_array, 2, $id);
                }
            } else {
                return STATUS_NODATA;
            }
        } else {
            
            $id = $this->getCompanyTable()->insertData($data);
            if ($request->company->provideTags) {
                $this->tags_add($pro_array, 1, $id);
            }
            if ($request->company->needsTags) {
                $this->tags_add($need_array, 2, $id);
            }
        }
        
        $response->id = $id; // 返回company_id
        return $response;
    }

    public function tags_add($length, $type, $id)
    {
        // 插入操作
        $tage_id = array();
        for ($i = 0; $i <= count($length) - 1; $i ++) {
            $data = array(
                'name' => $length[$i]
            );
            $data_id = $this->getTagsTable()->getOne($data);
            if ($data_id) {
                $tage_id[] = $data_id->id;
            } else {
                $tag_data = $this->getTagsTable()->insert($data);
                $data_id = $this->getTagsTable()->getOne($data);
                $tage_id[] = $data_id->id;
            }
        }
        
        for ($i = 0; $i <= count($tage_id) - 1; $i ++) {
            $data = array(
                'tag_id' => $tage_id[$i],
                'type' => $type,
                'foreign_id' => $id
            );
            
            $data_id = $this->getTagsRelationsTable()->getOne($data);
            
            if (! $data_id) {
                $re_data = $this->getTagsRelationsTable()->insert($data);
            }
        }
    }
}







