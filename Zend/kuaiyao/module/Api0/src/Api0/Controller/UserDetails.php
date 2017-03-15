<?php
namespace Api0\Controller;

use Zend\Db\Sql\Where;

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
        
        $action = array(1,2);
        if (!in_array($request->action, $action))
        {
            return STATUS_UNKNOWN;
        }
        if ($request->action==1) {
            $where['id'] = $this->getUserId();
            $data = $this->getUserTable()->getOne($where);
            $total = $this->getUserTable()->countData(array());
            
            $beyond_where = new Where();
            $beyond_where->greaterThan('recommend_stat', $data['recommend_stat']);
            $beyond = $this->getUserTable()->countData($beyond_where);
            $beyond_a = $beyond + 1;
            $beyonds = $total - $beyond_a;
            
            $item = array(
                'id' => $this->getUserId(),
                'mobile' => $data['mobile'],
                'recommendStat' => $data['recommend_stat'],
                'recommendBonus' => $data['recommend_bonus'],
                'recommendRanking' => $beyond_a,
                'recommendBeyond' => $beyonds
            );
        }
        if ($request->action==2)
        {
            $where['user_id_2'] = $request->id;
            $where['user_id_1'] = $this->getUserId();
            $data = $this->getUserRelationTable()->getOne($where);
            if (!$data){
                return STATUS_NODATA;
            }
            $item = array(
                'id' => $request->id,
                'isTop'=>$data['top'],
                'isShield' =>$data['shield'],
                'isDoNotRemind'=>$data['disturb']
            );
        }
        
        $response->user = $item;
        return $response;
    }
}
