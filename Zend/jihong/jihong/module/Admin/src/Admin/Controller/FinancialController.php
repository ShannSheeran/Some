<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class FinancialController extends CommonController
{
    public function indexAction()
    {
        $this->checkLogin('financial_list'); 
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword']:'';
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
        $type = isset($_GET['type']) ? $_GET['type']:'';
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        if($start_time)
        {
            $where->greaterThanOrEqualTo('timestamp', $start_time);
        }
        if($end_time)
        {
            if($start_time && $start_time > $end_time)
            {
                $end_time = $start_time;
            }
            $where->lessThanOrEqualTo('timestamp', $end_time);
        }
        if($type)
        {
            $where->equalTo('type', $type);
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
        }
        
        /* $is_excel = isset($_GET['is_excel']) ? $_GET['is_excel']:'';
        if($is_excel)
        {
            if(isset($_GET['is_page']))
            {
                set_time_limit(0);
                $list =  $this->getViewFinancialTable()->getAll($where, null, array('timestamp_update desc'), true, $page,6000, $like);
                $key = array(
                    'id',
                    'transfer_no',
                    'name',
                    'user_type',
                    'company_name',
                    'type',
                    'status',
                    'timestamp',
                );
        
                $data_array = array();
                foreach ($list['list'] as $v)
                {
                    $data = array();
                    foreach ($v as $k=> $val)
                    {
                        if(in_array($k, $key))
                        {
                            $data[$k] = $v->$k;
                        }
                    }
                    $data_array[] = $data;
                }
                return $this->createExeclAction($data_array);
                die();
            }
        } */
        
        $enterpris_type = $this->enterprisType();
        $financial_type = $this->financialType();
        $financial_list = $this->getViewFinancialTable()->getAll($where,null, array('id' => 'DESC'), true, $page, 10,$like);
        $view=new ViewModel(array(
            'paginator' => $financial_list['paginator'],
            'condition' => array(
                 'action' => 'index',
                 'page'   => $page,
                 'keyword'   => $keyword,
                 'where' => array(
                     'start_time' => $start_time,
                     'end_time' => $end_time,
                 )
             ),
            'financial_list' => $financial_list['list'],
            'enterpris_type' =>$enterpris_type,
            'financial_type' => $financial_type,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'keyword' => $keyword,
        ));
        $view->setTemplate('admin/financial/index');
        return $this->setMenu($view,1);
    }
    
    public function memberAction()
    {
        $this->checkLogin('member_list'); 
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] :'';
        $level = isset($_GET['user_level']) ? $_GET['user_level'] :'';
        $type = isset($_GET['type']) ? $_GET['type'] :'';
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
            $like['name'] = $keyword;
        }
        
        $where = array();
        $where['register_status'] = 3;
        $where['status'] = 1;
        $where['delete'] = DELETE_FALSE;
        if($level)
        {
            $where['user_level'] = $level;
        }
        if($type)
        {
            $where['type'] = $type;
        }
        
        $user_list = $this->getUserTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10 ,$like);
        $enterpris_type = $this->enterprisType();
        $user_level = $this->userLevel();
        $view=new ViewModel(array(
            'paginator' => $user_list['paginator'],
            'condition' => array(
                 'action' => 'member',
                 'page'   => $page,
                 'type' => $type,
                 'level' => $level,
                 'keyword' => $keyword,
             ),
            'user_list' => $user_list['list'],
            'enterpris_type' => $enterpris_type,
            'user_level' => $user_level,
            'type' => $type,
            'level' => $level,
            'keyword' => $keyword,
        ));
        $view->setTemplate('admin/financial/member');
        return $this->setMenu($view,1);
    }
    
    public function memberRechargeAction()
    {
        $this->checkLogin('member_detail'); 
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $enterpris_type = $this->enterprisType();
            $user_level = $this->userLevel();
            $user_info = $this->getUserTable()->getOne(array( 'id' => $id, 'register_status' => 3 , 'status' => 1 , 'delete' => DELETE_FALSE));
        }
        else 
        {
            echo '<script>history.back()</script>';
            die;
        }
        
        $view=new ViewModel(array(
            'id' => $id,
            'user_info' => $user_info,
            'enterpris_type' => $enterpris_type,
            'user_level' => $user_level,
        ));
        $view->setTemplate('admin/financial/member_recharge');
        return $this->setMenu($view,1);
    }

    public function actRechargeAction()
    {
        $this->checkLogin('user_recharge');
        $submit = $this->params()->fromPost('submit');
        $user_id = $this->params()->fromPost('user_id' , '');
        $type = $this->params()->fromPost('type' , '');
        $cash = $this->params()->fromPost('cash' , '');
        $description = $this->params()->fromPost('description' , '');
        
        if($user_id && $submit)
        {
            if(!$cash)
            {
                echo '<script>alert("请输现金");history.back();</scrupt>';
                die;
            }
            
            $user_info = $this->getUserTable()->getOne(array( 'id' => $user_id, 'register_status' => 3 , 'status' => 1 , 'delete' => DELETE_FALSE));
            
            $data = array();
            $data['type'] = $type;
            $data['cash_before'] = $user_info->cash;
            if($type == 1 || $type == 2)
            {
                $data['cash_after'] = $user_info->cash + $cash;
            }
            else if($type == 3 || $type == 4)
            {
                $data['cash_after'] = $user_info->cash - $cash;
            }
            $data['cash'] = $cash;
            $data['transfer_no'] = $this->generate();
            $data['status'] = 1;
            $data['order_id'] = 0;
            $data['user_id'] = $user_id;
            $data['recharge_admin_id'] = $_SESSION['admin_id'];
            $data['recharge_admin_name'] = $_SESSION['admin_name'];
            $data['description'] = $description;
            $data['delete'] = DELETE_FALSE;
            $data['timestamp'] = $this->getTime();
            
            $financial_id = $this->getFinancialTable()->insertData($data);
            if($financial_id)
            {
                return $this->redirect()->toRoute('admin-financial' , array('action' => 'rechargeList' ));
            }
        }
    }
    
    public function rechargeListAction()
    {
        $this->checkLogin('user_recharge_list');
        $page = $this->params()->fromRoute('page');
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] :'';
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $where = array();
        $where['delete'] = DELETE_FALSE;
        if($type)
        {
            $where['type'] = $type;
        }
        if($status)
        {
            $where['status'] = $status;
        }
        
        $like = array();
        if($keyword)
        {
            $like['company_name'] = $keyword;
        }
    
        $enterpris_type = $this->enterprisType();
        $financial_type = $this->financialType();
        $financial_list = $this->getViewFinancialTable()->getAll($where ,null, array('id' => 'DESC'), true, $page, 10 , $like);
        $view=new ViewModel(array(
            'paginator' => $financial_list['paginator'],
            'condition' => array(
                'action' => 'rechargeList',
                'page'   => $page,
                'type' => $type,
                'status' => $status,
                'keyword' => $keyword,
            ),
            'financial_list' => $financial_list['list'],
            'enterpris_type' => $enterpris_type,
            'financial_type' => $financial_type,
            'type' => $type,
            'status' => $status,
            'keyword' => $keyword,
        ));
        $view->setTemplate('admin/financial/recharge_list');
        return $this->setMenu($view,1);
    }
    
    public function rechargeCheckAction()
    {
        $this->checkLogin('user_recharge_detail');
        $id = $this->params()->fromRoute('id');
        
        if($id)
        {
            $user_level = $this->userLevel();
            $enterpris_type = $this->enterprisType();
            $financial_type = $this->financialType();
            $financial_info = $this->getViewFinancialTable()->getOne(array('id' => $id , 'delete' => DELETE_FALSE));
        }
        else 
        {
            echo '<script>history.back();</script>';
            die;
        }
        
        $view=new ViewModel(array(
            'enterpris_type' => $enterpris_type,
            'financial_info' => $financial_info,
            'user_level' => $user_level,
            'financial_type' => $financial_type,
        ));
        $view->setTemplate('admin/financial/recharge_check');
        return $this->setMenu($view,1);
    }
    
    public function dealRechargeFinancialAction()
    {
        $this->checkLogin('user_recharge_review');
        $id = $this->params()->fromPost('id');
        $pass = $this->params()->fromPost('pass');
        $nopass = $this->params()->fromPost('nopass');
        if($id)
        {
            $data = array();
            $data['audit_admin_id'] = $_SESSION['admin_id'];
            $data['audit_admin_name'] = $_SESSION['admin_name'];
            $data['refuse_reason'] = $this->params()->fromPost('refuse_reason' , '');
            
            if($pass)
            {
                $data['status'] = 2;
            }
            else if($nopass)
            {
                if(!$data['refuse_reason'])
                {
                    echo '<script>alert("审核不通过请输入不通过理由");history.back();</script>';
                    die;
                }
                $data['status'] = 3;
            }
            
            $this->getFinancialTable()->updateData($data, array('id' => $id));
            
            if($pass)
            {
                $financial_info = $this->getViewFinancialTable()->getOne(array('id' => $id));
                if($financial_info->type == 1 || $financial_info->type == 2)
                {
                    $cash = $financial_info->cash + $financial_info->user_cash;
                }
                elseif($financial_info->type == 3 || $financial_info->type == 4)
                {
                    $cash = $financial_info->user_cash - $financial_info->cash;
                }
                $this->getUserTable()->updateData(array('cash' =>$cash ), array('id' => $financial_info->user_id));
            }
            
            return $this->redirect()->toRoute('admin-financial' , array('action' => 'rechargeCheck' , 'id' =>$id));
        }
    }
    
    public function turnoverStatisticsAction()
    {
        $this->checkLogin('business_detail');
        
        $show_start_time = date('Y-1-1');
        $show_end_time = date('Y-m-d' , strtotime('-1 day'));
        $show_start_time = isset($_GET['start_time']) ? $_GET['start_time'] :$show_start_time;
        $show_end_time =  isset($_GET['end_time']) ? $_GET['end_time'] :$show_end_time;
        
        $start_time = $show_start_time . ' 00:00:00';
        $end_time = $show_end_time . ' 23:59:59';
        $where = new Where();
        $where->between("timestamp", $start_time , $end_time);
        $where->equalTo('delete', DELETE_FALSE);
        $statistics = $this->getDataStatisticsTable()->fetchAll($where);

        $total_statistics = 0;
        $goods_statistics = 0;
        $equipment_statistics = 0;
        foreach ($statistics as $value)
        {
            $total_statistics += $value->income_cash_total;
            $goods_statistics += $value->income_cash_goods;
            $equipment_statistics += $value->income_cash_equipment;
        }
        
        $view=new ViewModel(array(
            'total_statistics' => $total_statistics,
            'goods_statistics' => $goods_statistics,
            'equipment_statistics' => $equipment_statistics,
            'start_time' => $show_start_time,
            'end_time' => $show_end_time,
        ));
        $view->setTemplate('admin/financial/turnover_statistics');
        return $this->setMenu($view,1);
    }
    
    public function statisticsListAction()
    {
        $this->checkLogin('business_detail');
        $page = $this->params()->fromRoute('page');
        
        $show_start_time = date('Y-1-1');
        $show_end_time = date('Y-m-d' , strtotime('-1 day'));
        $show_start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $show_start_time;
        $show_end_time =  isset($_GET['end_time']) ? $_GET['end_time'] : $show_end_time;
        $start_time = $show_start_time . ' 00:00:00';
        $end_time = $show_end_time . ' 23:59:59';
        
        $where = new Where();
        $where->between("timestamp", $start_time , $end_time);
        $where->equalTo('delete', DELETE_FALSE);
        $statistics_list = $this->getDataStatisticsTable()->getAll($where,null, array('id' => 'DESC'), true, $page, 10);
        
        $view=new ViewModel(array(
            'paginator' => $statistics_list['paginator'],
            'condition' => array(
                'action' => 'statisticsList',
                'page'   => $page,
                'where' => array(
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                )
            ),
            'statistics_list' => $statistics_list['list'],
            'start_time' => $start_time,
            'end_time' => $end_time,
        ));
        $view->setTemplate('admin/financial/statistics_list');
        return $view;
    }
    
    public function goodsStatisticsAction()
    {
        $this->checkLogin('business_detail');
        $page = $this->params()->fromRoute('page');
        $type = $this->params()->fromRoute('type' , 1);
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '';
        $goods_id = isset($_GET['goods_id']) ? $_GET['goods_id'] : '';
        
        $show_start_time = date('Y-1-1');
        $show_end_time = date('Y-m-d' , strtotime('-1 day'));
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : $show_start_time;
        $end_time =  isset($_GET['end_time']) ? $_GET['end_time'] : $show_end_time;
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        $where->equalTo('type', $type);//商品类型
        $where->equalTo('goods_statistics_type', 1);//一日内统计
        if($start_time)
        {
            $where->greaterThan('timestamp', $start_time.' 00:00:00');
        }
        if($end_time)
        {
            $where->lessThan('timestamp', $end_time.' 23:59:59');
        }
        if($category_id)
        {
            if($goods_id)
            {
                $where->equalTo('goods_id', $goods_id);
            }
            else 
            {
                $where->equalTo('category_id', $category_id);
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        
        $goods_statistics = $this->getViewGoodsStatisticsTable()->getAll($where,null, array('id' => 'DESC'), true, $page, 10 ,$like);
        
        $category_where = new Where();
        $category_where->greaterThan('parent_id', 0);
        $category_where->equalTo('status', 1);
        $category_where->equalTo('type', $type);
        $category_where->equalTo('delete', DELETE_FALSE);
        $category_list = $this->getGoodsCategoryTable()->fetchAll($category_where);
        
        $goods_where = array();
        $goods_where['type'] = $type;
        $goods_where['delete'] = DELETE_FALSE;
        if($category_id)
        {
            $goods_where['category_id'] = $category_id;
        }
        
        $goods_list = $this->getGoodsTable()->fetchAll($goods_where);
        
        $view=new ViewModel(array(
            'paginator' => $goods_statistics['paginator'],
            'condition' => array(
                'action' => 'goodsStatistics',
                'page'   => $page,
                'type' => $type,
                'keyword' => $keyword,
                'where' => array(
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'category_id' => $category_id,
                    'goods_id' => $goods_id,
                )
            ),
            'goods_statistics' => $goods_statistics['list'],
            'start_time' => $start_time,
            'end_time' => $end_time,
            'keyword' => $keyword,
            'category_list' => $category_list,
            'goods_list' => $goods_list,
            'type' => $type,
            'category_id' => $category_id,
            'goods_id' => $goods_id,
        ));
        $view->setTemplate('admin/financial/goods_statistics');
        return $this->setMenu($view,1);
    }
    
    public function selectGoodsAction()
    {
        $category =  isset($_POST['category']) ?  $_POST['category'] :'';
        $type =  isset($_POST['type']) ?  $_POST['type'] :'';
        $goods_list = $this->getGoodsTable()->fetchAll(array('type' =>  $type, 'category_id' =>$category , 'delete' => DELETE_FALSE ));
        
        echo json_encode($goods_list);
        die;
    }
    
    public function makeOrderFinancialRecordAction($type , $status , $order_id , $is_admin_check = false , $change_user_cash = true , $cash = '')
    {
        $order_info = $this->getOrderTable()->getOne(array('id' => $order_id));
        if($order_info->pay_type == 1)
        {
            $cash = !empty($cash) ? $cash : $order_info->total_cash;
            
            $user_info = $this->getUserTable()->getOne(array('id' => $order_info->user_id ));
            $financial_data = array();
            $financial_data['type'] = $type;
            $financial_data['cash_before'] = $user_info->cash;
            $financial_data['cash'] = $cash;
            $financial_data['transfer_no'] = $this->generate();
            $financial_data['status'] = $status;
            $financial_data['order_id'] = $order_id;
            $financial_data['user_id'] = $order_info->user_id;
            $financial_data['recharge_admin_id'] = 0;
            $financial_data['recharge_admin_name'] = '';
            $financial_data['delete'] = DELETE_FALSE;
            $financial_data['timestamp'] = $this->getTime();
            if(in_array($type, array(1,2,6)))
            {
                $financial_data['cash_after'] = $user_info->cash + $cash ;
            }
            else if(in_array($type, array(3,4,5)))
            {
                if($change_user_cash)
                {
                    $financial_data['cash_after'] = $user_info->cash - $cash ;
                }
                else 
                {
                    $financial_data['cash_after'] = $user_info->cash;
                }
            }
            
            if($is_admin_check)
            {
                $financial_data['audit_admin_id'] = $_SESSION['admin_id'];
                $financial_data['audit_admin_name'] = $_SESSION['admin_name'];
            }
            
            $this->getFinancialTable()->insertData($financial_data);
            
            if($change_user_cash)
            {
                $this->getUserTable()->updateData(array('cash' =>$financial_data['cash_after']) , array('id' => $user_info->id));
            }
        }
    }
    
    
    
    /* public function createExeclAction($list)
    {
        include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel.php';
        include APP_PATH . '/vendor/Core/System/phpExcel/PHPExcel/IOFactory.php';
         
        $title = array(
            'A' => 'ID|id',
            'B' => '交易流水号|transfer_no',
            'C' => '用户名称|name',
            'D' => '用户类型|user_type',
            'E' => '公司名称|company_name',
            'F' => '交易方式|type',
            'G' => '状态|status',
            'H' => '添加时间|timestamp'
        );
        $file_name = '用户财务';
    
    
        //设定缓存模式为经gzip压缩后存入cache
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()
        ->setCreator("PHP")
        ->setLastModifiedBy("PHP")
        ->setTitle("Office 2007 XLSX Test Document")
        ->setSubject("Office 2007 XLSX Test Document")
        ->setDescription("Document for Office 2007 XLSX, generated using PHP classes.")
        ->setKeywords("office 2007 openxml php")
        ->setCategory("Test result file");
    
        $objPHPExcel->setActiveSheetIndex(0);

        //设置单元格的表头
        foreach ($title as $key=>$val)
        {
            $val = explode('|', $val);
            $objPHPExcel->getActiveSheet()->setCellValue($key.'1', $val[0]);
        }
    
        $objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());
    
        $i = 2;
        foreach ($list as $v)
        {
            foreach ($title as $key=>$val)
            {
                $val = explode('|', $val);
                $val = $val[1];
                $v[$val];
    
                $objPHPExcel->getActiveSheet()->setCellValue($key.$i, $v[$val]);
            }
            $i++;
        }
         header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$file_name.date("Ymd").'.xlsx"');
        header("Content-Transfer-Encoding:binary"); 
    
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save(date("Ymd").".xlsx");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        //return $objWriter->save('php://output');
        
        
    } */
}
