<?php
namespace Api3\Controller;

use Zend\Db\Sql\Where;

use Api3\Controller\Request\ActivityListWhereRequest;


/**
 * 活动列表
 * @author HY
 * 10.26
 */
class ActivityList extends CommonController
{

    public function __construct()
    {
        $this->myWhereRequest = new ActivityListWhereRequest();
        parent::__construct();
    }
   
    public function index(){
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $table_where = $this->getTableWhere();
        $where = new Where();
        $where->equalTo('act_delete', DELETE_FALSE);
        
        if ($table_where->search_key) {
            $sub_where = new Where();
            $sub_where->like('name', '%' . $table_where->search_key . '%');
            $where->addPredicate($sub_where);
        }
        
        if ($table_where->address->region_id) {
            $where->equalTo('region_id', $table_where->address->region_id);
        }

        if ($table_where->categoryId) {
            $where->equalTo('category_id', $table_where->categoryId);
        }

        $data = $this->getAll($this->getViewActivityCompanyTable(),$where);
        $list = array();
        if($data){
        foreach ($data['list'] as $val) {
            $imagePath = '';
            if ($val['logo']) {
                $imagePath = $this->getImageTable()->getOne(array('id'=>$val['logo']));
                if ($imagePath) {
                    $imagePath = $imagePath['path'] . $imagePath['filename'];
                }
            }
            
            $company = $this->getCompanyTable()->getOne(array('id'=>$val['company_id']));
            
            $list[]['activity'] = array(
                'id' => $val['id'],
                'name' => $val['name'],
                'imagePath' => $imagePath,
                'company' => array(
                     'id' => $company['id'],
                   'name' => $company['name'],
                ),
                'timestamp' => $val['timestamp'],
                );
            }
        }

        $response->activitys = $list;
        $response->total = isset($data['total']) ? $data['total'] : 0;
        return $response;
    }  
}







