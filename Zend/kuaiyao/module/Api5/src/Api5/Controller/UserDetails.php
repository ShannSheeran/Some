<?php
namespace Api5\Controller;

use Zend\Db\Sql\Where;
use Zend\Form\Annotation\Object;

/**
 * 查询个人信息
 */
class UserDetails extends User
{

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $where = array();
        
        $action = array(
            1,
            2
        );
        if (! in_array($request->action, $action)) {
            return STATUS_UNKNOWN;
        }
        if ($request->action == 1) {
            $where['id'] = $this->getUserId();
            $decive_where['user_id'] = $this->getUserId();
            $data = $this->getUserTable()->getOne($where);
            
            $data_device = $this->getDeviceTable()->getOne($decive_where);
            if ($data_device['user_id'] == 0 && $data_device['page_ids'] == 0) {
                $device = array();
            } else {
                $device = array(
                    'uuid' => $data_device['uuid'] ? $data_device['uuid'] : "",
                    'major' => $data_device['major'] ? (int) $data_device['major'] : 0,
                    'minor' => $data_device['minor'] ? (int) $data_device['minor'] : 0
                );
            }
            $total = $this->getUserTable()->countData(array());
            
            $beyond_where = new Where();
            $beyond_where->greaterThan('recommend_stat', $data['recommend_stat']);
            $beyond = $this->getUserTable()->countData($beyond_where);
            $beyond_a = $beyond + 1;
            $beyonds = $total - $beyond_a;
            
            $cate_user_id['id'] = $this->getUserId();
            $page_id = $this->getUserTable()->getOne($cate_user_id);
            $carte_data = $this->getViewPageCarteTable()->getOne(array('id'=>$page_id['page_id']));  // 841
            $region_num = '';
            $category_num = '';
            $company_num = '';
           if($carte_data){
               // 获取同城数据
               if($carte_data->c_region_id == 0){
                   $carte_datas = $this->getCompanyTable()->getOne(array('id'=>$carte_data->company_id));
                   /* $region=$this->getRegionTable()->getOne(array('id'=>$carte_datas->region_id)); */
                   $city_info= json_decode($carte_datas->region_info,TRUE);
                   if(isset($city_info[1]) && $city_info[1]){
                       $v['id']=$city_info[1]['region']['id'];
                   }
                   $carte_data->c_region_id = $v['id'];
               }
               $region_a = new where();
               $region_a->equalTo('c_region_id', $carte_data->c_region_id);
               $region_a->equalTo('delete', 0);
               $region_a->notEqualTo('user_id', 0);
               $region_number = $this->getViewPageCarteTable()->fetchAll($region_a);
               $array_fiy_1 = $this->quchu($region_number,$carte_data['c_id']);
               $num_1 = array_unique($array_fiy_1);
               if(!$num_1){
                   $num_1 = array(
                       '0' => 0,
                   );
               }
               $where_1 = new where();
               $where_1->in('carte_id', $num_1);
               $datas = $this->getAll($this->getViewUserPageTable(), $where_1);
               $region_num = $datas['total'] ? $datas['total'] : "";
               
               
               // 同行数据
               if($carte_data->c_category_id == 0){
                   $carte_datas = $this->getCompanyTable()->getOne(array('id'=>$carte_data->company_id));
                   $carte_data->c_category_id = $carte_datas['category_id'];
               }
               $category_a = new where();
               $category_a->equalTo('c_category_id', $carte_data->c_category_id);
               $category_a->equalTo('delete', 0);
               $category_a->notEqualTo('user_id', 0);
               $category_number = $this->getViewPageCarteTable()->fetchAll($category_a);
               $array_fiy_2 = $this->quchu($category_number,$carte_data['c_id']);
               $num_2 = array_unique($array_fiy_2);
               if(!$num_2){
                   $num_2 = array(
                       '0' => 0,
                   );
               }
               $where_2 = new where();
               $where_2->in('carte_id', $num_2);
               $datas = $this->getAll($this->getViewUserPageTable(), $where_2);
               $category_num = $datas['total'] ? $datas['total'] : "";
               
               // 同事数据
               $num_3 = '';
               if($carte_data['card_status'] == 3){
                   $company_a = new where();
                   $company_a->equalTo('company_id', $carte_data->company_id);
                   $company_a->equalTo('delete', 0);
                   $company_a->equalTo('card_status', 3);
                   $company_a->notEqualTo('user_id', 0);
                   $company_number = $this->getViewPageCarteTable()->fetchAll($company_a);
                   $array_fiy_3 = $this->quchu($company_number,$carte_data['c_id']);
                   $num_3 = array_unique($array_fiy_3);
               }
               if(!$num_3){
                   $num_3 = array(
                       '0' => 0,
                   );
               }
               $where_3 = new where();
               $where_3->in('carte_id', $num_3);
               $datas = $this->getAll($this->getViewUserPageTable(), $where_3);
               $company_num = $datas['total'] ? $datas['total'] : "";
           }
           
            // 我发布的人脉圈
            $statmessage_id = $this->getChatTable()->fetchAll(array(
                'user_id' => $this->getUserId(),
                'delete' => 0
            ));
            
            $item = array(
                'id' => $this->getUserId(),
                'mobile' => $data['mobile'],
                'device' => (object) $device,
                'kydeviceId' => (int)$data_device['id'],
                'recommendStat' => (int)$data['recommend_stat'] ? $data['recommend_stat'] : 0,
                'recommendBonus' => (int)$data['recommend_bonus'] ? $data['recommend_bonus'] : 0,
                'recommendBeyond' => (int)$beyonds,
                'recommendRanking' => (int)$beyond_a,
                'money' => (int)$data['money'] ? $data['money'] : 0,
                'statUserCity' => (int)$region_num ? (int)$region_num : 0,  //同城
                'statUserJob' => (int)$category_num ? (int)$category_num : 0, //同行   
                'statUserColleague' => (int)$company_num ? (int)$company_num : 0, //同事
                'statMessage' => count($statmessage_id) ? count($statmessage_id) : 0
            );
        }
        if ($request->action == 2) {
            $where['user_id_2'] = $request->id; // 用户乙ID
            $where['user_id_1'] = $this->getUserId(); // 用户甲ID
            $where['attention'] = 3;
            $data = $this->getUserRelationTable()->getOne($where);
            if (! $data) {
                return STATUS_NODATA;
            }
            $item = array(
                'id' => (int)$this->getUserId(),
                'isTop' => (int) $data['top'],
                'isShield' => (int) $data['shield'],
                'isDoNotRemind' => (int) $data['disturb']
            )
            ;
        }
        
        $response->user = $item;
        return $response;
    }
    
    public function quchu($number,$page_id)
    {
        $array = array();
        foreach ($number as $val) {
            $array[] = (int) $val['c_id'];
        }
        $array_me[] = $page_id;
        return $array_fiy = array_diff($array,$array_me);
    }
}
