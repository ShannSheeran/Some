<?php
namespace Api3\Controller;

// use Zend\Db\Sql\Where;
// use Api3\Controller\Common\WhereRequest;
// use Admin\Controller\FinancialController;

/**
 * 财务列表
 * @author HY
 * 9.6
 */
class FinancialList extends CommonController
{

//     public function __construct()
//     {
//         $this->myWhereRequest = new FinancialListWhereRequest();
//         parent::__construct();
//    }
    public function index(){
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        $action = $request->action;
        $action_array = array(
            3     
        );
        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        
        if($action && $action == 3)
        {
        $where = array('user_id' => $this->getUserId(),'type' => 3);
        $data = $this->getAll($this->getFinancialTable(), $where);
        
        $list = array();
        if($data){
        foreach ($data['list'] as $val) {
            $list[]['financial'] = array(
                'id' => $val['id'],
                'amount' => $val['amount'],
                'status' => $val['status'],
                'cardOwner' => $val['card_owner'],
                'bankId' => $val['bank'],
                'timestamp' => $val['timestamp'],
                );
            }
        }

        }
        $response->financials = $list;
        $response->total = isset($data['total']) ? $data['total'] : 0;
        return $response;
    }  
}







