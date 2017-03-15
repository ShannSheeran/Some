<?php
namespace Api\Controller;


use Api\Controller\Request\FinancialSubmitRequest;
use Index\Controller\CommonController as Index;
use Core\System\AiiPush\SmsPush;
use Api\Controller\Item\PushArgsItem;
/**
 * 名片提交
 */
class FinancialSubmit extends CommonController
{

    public function __construct()
    {
        $this->myRequest = new FinancialSubmitRequest();
        parent::__construct();
    }

    public function index()
    {
        $request = $this->getAiiRequest();
        $response = $this->getAiiResponse();
        $this->checkLogin();
        
        $action = $request->action;
        $action_array = array(
            3,
        );
        if (! in_array($action, $action_array)) {
            return STATUS_UNKNOWN;
        }
        
        if($action == 3){
            $list = array();   
            $index = new Index();
            $transfer_no = $index->generate();//前台方法有交易流水号函数
            $amount = round($request->financial->amount,2);
            if($amount == 0){
                return STATUS_MONEYZONE;
            }
            
            if($amount<0){
                return STATUS_MONEYOVERZONE;
            }
            
            $user = $this->getUserTable()->getOne(array('id'=>$this->getUserId()));
            if($amount>$user['money']){
                return STATUS_LACKMONEY;
            }
            
            $cardNumber = trim((int)$request->financial->card_number);
            if(!$cardNumber || $cardNumber==''){
                return STATUS_LACKCARDNUMBER;
            }
            
            $cardOwner = trim($request->financial->card_owner);
            if(!$cardOwner || $cardOwner==''){
                return STATUS_LACKCARDOWNER;
            }
            
            $bankId = trim((int)$request->financial->bank_id);
            $bankList = $index->bankList();
            $idList = array();
            foreach($bankList as $k => $v){
                $idList[] = $k;
            }
            
            if(!$bankId || $bankId=='' || !in_array($bankId,$idList)){
                return STATUS_LACKBANKID;
            }
            
            $list = array(
                'amount' => $amount,
                'card_number' => $cardNumber,
                'card_owner' => $cardOwner,
                'payment_type' => 4,
                'status' => 3,
                'type' => 3,
                'income' => 2,
                'timestamp' => $this->getTime(),
                'transfer_no' => $transfer_no,
                'bank' => $bankId,
                'user_id' => $this->getUserId(),
            );     
            
            $id = $this->getFinancialTable()->insertData($list);
            if($id){
                $rest_money = $user['money']-$list['amount'];//提现后余额
                $this->getUserTable()->updateData(array('money'=>$rest_money), array('id'=>$this->getUserId()));//如果添加提现添加成功 更新用户余额      

//                 $args = new PushArgsItem();
//                 $args->money = $amount;
//                 $this->getApiController()->pushForController($id, 2, $args);
                
                $response->id = $id;
                return $response;
            }else{
                return STATUS_WITHDRAWFAIL;
            }
        }
    }
}







