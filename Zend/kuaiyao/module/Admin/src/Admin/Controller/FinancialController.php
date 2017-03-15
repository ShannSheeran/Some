<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Index\Controller\IndexController as index;
use Api\Controller\Item\PushArgsItem;


class FinancialController extends CommonController
{

    /**
     * 财务列表
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->checkLogin('order');
        $this->table = $this->getViewFinancialTable();
        $this->delete = false;
        $this->template = array(
            'financial/index',
            'financial'
        );
        $this->seach = array(
            'transfer_no'
        );
        $this->screening = 'type';
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '订单'
            ),
            array(
                'url' => '',
                'title' => '财务'
            )
        );
        
        $this->other = array(
            'financialType' => $this->getFinancialType(),
            'financialIncome' => $this->getFinancialIncome()
        );
        return $this->getList();
    }
    
    /**
     * 返回订单类型
     * @return multitype:string
     * @version 2015-8-8 WZ
     */
    public function getFinancialType() {
        return array(
            1 => '订单',
            2 => '返利',
            3 => '提现'
        );
    }
    
    /**
     * 返回订单收支类型
     * @return multitype:string
     * @version 2015-8-8 WZ
     */
    public function getFinancialIncome() {
        return array(
            1 => '收入',
            2 => '支出',
        );
    }
    
    /**
     * 交易详细页
     * 
     * @version 2015-8-26 HY
     */
    public function financialdetailAction(){
        $this->checkLogin();
        $id = $this->params('id');//得到id参数
        //echo $id;
     
        $index = new Index();
        $bankList =  $index->bankList();
        $where = new Where();//交易信息
        $where->equalTo('id', $id);
        $financial_info = $this->getFinancialTable()->getOne($where);
        
        $where = new Where();//用户个人信息
        $where->equalTo('id',$financial_info['user_id']);
        $user_info = $this->getUserTable()->getOne($where);
        $name = $user_info['name'];
        
        $this->breadcrumb = array(
            array(
                'url' => '#',
                'title' => '订单'
            ),
            array(
                'url' => '',
                'title' => '财务详细'
            )
        );

        $view = new ViewModel(array(
            'financial_info'=>$financial_info,
            'name'=>$name,
            'bankList'=>$bankList,
            'super'=>$_SESSION['super']
        ));
        
       
        $view->setTemplate("admin/financial/financialdetail");
        return $this->setMenu($view, 'financial');
    }
    
    /**
     * 审核失败（添加原因）
     *
     * @version 2015-8-28 HY
     */
    public function addreasonAction(){
            $this->checkLogin();
//             $admin_name=$_COOKIE['name'];
//             $admin_id=$_COOKIE['index_user_id'];
            if(isset($_POST['reason']) && $_POST['id'])//判断有没有post这两个值过来
            {   
                $id = $_POST['id'];
                $where1 = new Where();
                $where1->equalTo('id', $id);//交易表id
                $fina_info = $this->getFinancialTable()->getOne($where1);
                //$fina_info['amount'];
                
                $where_user = new Where();
                $where_user->equalTo('id',$fina_info['user_id']);
                $user_info = $this->getUserTable()->getOne($where_user);
                //$user_info['money'];
                
                
                
                $where2 = array(//更新用户金额
                    'id'=>$fina_info['user_id'],
                );
                $total = $fina_info['amount']+$user_info['money'];
                $set1 = array(
                    'money' => $total,
                );
                
                if($this->getUserTable()->updateData($set1, $where2)){
                
                  $admin_id = $_SESSION['admin_id'];
                    $admin_name = $_SESSION['admin_name'];
                    
                $reason = trim($_POST['reason']);
                $set = array(
                   'reason'=>$reason,
                   'status'=>2,
                   'admin_id'=>$admin_id,
                   'admin_name'=>$admin_name
                );
                    
                $where = new Where();
                $where->equalTo('id', $id);
                    if($this->getFinancialTable()->updateData($set, $where)){
                        
                        $args = new PushArgsItem();
                        $args->name = $reason;
                        $args->money = $fina_info['amount'];
                        $this->getApiController()->pushForController($fina_info['user_id'], 1, $args);                        
                        
                            die("1");
                        }else{
                            die("0");
                        }
                }else{
                    die("0");
                }
            }else{
                die("0");//没有post数据过去
            }
            
    }
    
    /**
     * 审核成功
     *
     * @version 2015-8-28 HY
     */
    public function AuditthroughAction(){
        if(isset($_POST['id'])){
            $admin_id = $_SESSION['admin_id'];
            $admin_name = $_SESSION['admin_name'];
            $set = array(
                'status'=>1,
                'admin_id'=>$admin_id,
                'admin_name'=>$admin_name
            );
           
            $id = $_POST['id'];
            if($this->getFinancialTable()->updateData($set, array('id'=>$id))){
                
                $userFinancial = $this->getFinancialTable()->getOne(array('id'=>$id));

                $args = new PushArgsItem(); 
                $args->money = $userFinancial['amount'];
                $this->getApiController()->pushForController($userFinancial['user_id'], 2, $args);
                
                die("1");
            }else{
                die("0");//没有post数据过去
            }
        }
    }
}