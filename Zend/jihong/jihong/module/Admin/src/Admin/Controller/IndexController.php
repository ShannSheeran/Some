<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class IndexController extends CommonController
{
    /**
     * 登入成功跳转页
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $this->checkLogin('main_index'); // 判断是否登录
        
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        switch ($type)
        {
            case 1:
                $title = '盆栽订单量';
                break;
            case 2:
                $title = '资材订单量';
                break;
            default:
                $title = '总订单量';
                break;
        }
        
        $goods_order_count = $this->getOrderTable()->getOrderCount(array('type' => 1, 'status' => 2 , 'delete' => DELETE_FALSE));
        $equipment_order_count = $this->getOrderTable()->getOrderCount(array('type' => 2, 'status' => 2 , 'delete' => DELETE_FALSE));
        $users_count = $this->getUserTable()->getUserCount(array('register_status' => array(1,2) , 'delete' => DELETE_FALSE));
        $service_apply_count = $this->getCustomerServiceApplyTable()->getServiceApplyCount(array('status' => 1 , 'delete' => DELETE_FALSE));
        $message_count = $this->getLeaveMessageTable()->getMessageCount(array('is_read' => 1 , 'parent_id' => 0 , 'type' => array(1,2) ,  'delete' => DELETE_FALSE));
        $goods_count = $this->getGoodsTable()->getGoodsCount(array('status' => 1 , 'delete' => DELETE_FALSE));
        
        $timestamp = strtotime('now');
        $date = array();
        $data = array();
        for($i = 29 ; $i >=0  ; $i--)
        {
            $stamp = $timestamp - $i * 86400;
            $where = new Where();
            if($type)
            {
                $where->equalTo('type', $type);
            }
            $where->between('timestamp', date('Y-m-d 00:00:00' , $stamp), date('Y-m-d 23:59:59' , $stamp));
            $count = $this->getOrderTable()->getOrderCount($where);
            
            if($count)
            {
                $date[] = date('Y-m-d' , $stamp);
                $data[] = $count;
            }
            
        }
        
        $view=new ViewModel(array(
            'goods_order_count' => $goods_order_count,
            'equipment_order_count' => $equipment_order_count,
            'users_count' => $users_count,
            'service_apply_count' => $service_apply_count,
            'message_count' => $message_count,
            'goods_count' => $goods_count,
            'date' => json_encode($date),
            'data' => json_encode($data),
            'title' => $title,
            'type' => $type,
        ));
        $view->setTemplate('admin/index/index');
        return $this->setMenu($view,1);
    }


    
    // 登录
    public function loginAction()
    {
        $url = $_SERVER['HTTP_HOST'];
        if (isset($_POST['submit'])) {
            
            if (isset($_SESSION['login_number']) && $_SESSION['login_number'] > 2) {
                $captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';
                if ($_SESSION['captcha'] != $captcha) {
                    echo "<script type='text/javascript'>alert('验证码错误!请重新登录。');history.back(-1);</script>;";
                    die();
                }
            }
            $admin_name = addslashes(trim($_POST['admin_name']));
            $password = md5(addslashes($_POST['password']));
            $admin_info = $this->getAdminTable()->getOne(array(
                'name' => $admin_name,
                'password' => $password,
                'status' => 1
            ));
            
            if (! $admin_info) {
                $_SESSION['login_number'] = isset($_SESSION['login_number']) ? $_SESSION['login_number'] + 1 : 1;
                echo "<script type='text/javascript'>alert('用户名或密码错误!请重新登录。');</script>;";
                unset($_POST['submit']);
                $view = new ViewModel();
                $view->setTemplate('admin/index/login');
                return $view;
            } else {
                $_SESSION['login_number'] = 0;
                //吉宏数据库并无此字段
                $this->getAdminTable()->update(array(
                    'last_time' => date('Y-m-d H:i:s')
                ), array(
                    'id' => $admin_info['id']
                ));
                
                $admin_category = $this->getAdminCategoryTable()->getOne(array('id' => $admin_info->admin_category_id));
                
                $_SESSION['admin_name'] = $admin_info['name'];
                $_SESSION['admin_id'] = $admin_info['id'];
                $_SESSION['super'] = $admin_info['super'];
                //$_SESSION['action_list'] = 'all'; // 给所有权限
                if($admin_info->admin_category_id == 1)
                {
                    $_SESSION['action_list'] = 'all'; // 给所有权限
                }
                else 
                {
                    $_SESSION['action_list'] = $admin_category['action_list']; 
                }
                
                return $this->redirect()->toRoute('admin');
            }
        }
        $view = new ViewModel();
        $view->setTemplate('admin/index/login');
        return $view;
    }
    // 退出
    public function logoutAction()
    {
        $this->quit();
    }

    /**
     * 计划任务查询前一天的数据
     * 获取设备流量数据控制器
     * 只能查当天的前数据
     * 
     * @author
     *         chenzy
     */
    public function getStatisticsAction()
    {
        set_time_limit(0);
        $id = $this->params('id', 0);
        $param = array(
            'device_id',
            'statistical_time'
        );
        
        if ($id) {
            
            $start = strtotime(date("Y-m-d", strtotime("-1 month")));
            $end = strtotime(date("Y-m-d"), strtotime("-2 day")) - 1; // 临时参数查询6月份的
        } else {
            
            $start = strtotime(date("Y-m-d", strtotime("-2 day")));
            $end = strtotime(date("Y-m-d")) - 1; // 查询今天00:00:00之前
        }
        $data = $this->getStatistics($start, $end);

        $localDataArray = array();
        $localData = $this->getStatisticsTable()->getAll(null, $param);
        
        foreach ($localData['list'] as $v) {
            $localDataArray[$v->device_id . $v->statistical_time] = $v->device_id;
        }
        
        foreach ($data as $val) {
            foreach ($val as $v) {
                // 判断数据库是否已经存在相同数据
                if (! isset($localDataArray[$v['device_id'] . date("Y-m-d", $v['ftime'])])) {
                    $v['statistical_time'] = date("Y-m-d", $v['ftime']);
                    $this->getStatisticsTable()->insert($v);
                } else {
                    continue;
                }
            }
        }
        exit();
    }

    /**
     * 获取设备流量数据
     *
     * @param $start $end            
     * @author
     *         chenzy
     */
 /*    public function getStatistics($start, $end)
    {
        $allDeviceData = array();
        $where_activated = new Where();
        $where_activated->equalTo('status', 1)->equalTo('delete', 0);
        $where_active = new Where();
        $where_active->equalTo('status', 2)->equalTo('delete', 0);
        $where_active->orPredicate($where_activated);
        
        $device_info = $this->getDeviceTable()->fetchAll($where_active);
        $allDeviceData = array();
        foreach ($device_info as $k => $v) {
            $jsonData = json_encode(array(
                'device_identifier' => array(
                    'device_id' => (int) $v['device_id']
                ),
                'begin_date' => (int) $start,
                'end_date' => (int) $end
            ));
            
            $wxApi = new WxApi();
            $res = $wxApi->wxGetShakeStatistics($jsonData);
            
            if ($res['errcode'] == 0) 
            {
                if(isset($res['data']) && $res['data'])
                {
                    foreach ($res['data'] as $key => $val) {
                        $val['device_id'] = $v['device_id'];
                        $allDeviceData[$k][$key] = $val;
                    }
                }
               
            }
        }
        return $allDeviceData;
    } */
    
    public function platformBusinessStatisticsAction()
    {
        set_time_limit(0);
        $start_time = date('Y-m-d 00:00:00' , strtotime("-1 day"));
        $end_time = date('Y-m-d 23:59:59' , strtotime("-1 day"));
        
        $where = new Where();
        $where->between("timestamp", $start_time , $end_time);
        $where->equalTo('status', 2);
        $where->equalTo('type', 5);
        $where->greaterThan('order_id', 0);
        
        $financial_list = $this->getViewFinancialTable()->fetchAll($where);
        
        $total_statistics = 0;
        $total_number = 0;
        $goods_statistics = 0;
        $goods_number = 0;
        $equipment_statistics = 0;
        $equipment_number = 0;
        
        if($financial_list)
        {
            foreach ($financial_list as $item)
            {
                if($item->order_type == 1)
                {
                    $goods_statistics += $item->cash;
                    $goods_number  += $item->total_number;
                }
                if($item->order_type == 2)
                {
                    $equipment_statistics += $item->cash;
                    $equipment_number  += $item->total_number;
                }
            
                $total_statistics += $item->cash;
                $total_number  += $item->total_number;
            }
        }
        
        $data = array();
        $data['income_cash_goods'] = $goods_statistics;
        $data['income_cash_equipment'] = $equipment_statistics;
        $data['income_cash_total'] = $total_statistics;
        $data['goods_number'] = $goods_number;
        $data['equipment_number'] = $equipment_number;
        if ($goods_number)
        {
            $data['goods_average'] = round($goods_statistics / $goods_number , 2);
        }
        else 
        {
            $data['goods_average'] = 0.00;
        }
        if($equipment_number)
        {
            $data['equipment_average'] = round($equipment_statistics / $equipment_number , 2);
        }
        else 
        {
            $data['equipment_average'] = 0.00;
        }
        $data['delete'] = DELETE_FALSE;
        $data['timestamp'] = date("Y-m-d H:i:s" , strtotime('-1 day'));
        
        $this->getDataStatisticsTable()->insertData($data);

        die;
    }
    
    public function goodsStatisticsAction()//统计一天内
    {
        set_time_limit(0);
        $start_time = date('Y-m-d 00:00:00' , strtotime("-1 day"));
        $end_time  = date('Y-m-d 23:59:59' , strtotime("-1 day"));
        
        $where = new Where();
        $where->between("timestamp", $start_time , $end_time);
        $where->equalTo('delete', DELETE_FALSE);
        
        $order_goods_list = $this->getOrderGoodsTable()->fetchAll($where);
        
        if($order_goods_list)
        {
            $new_order_goods_list = array();
            foreach ($order_goods_list as $value)
            {
                $new_order_goods_list[$value->goods_id][] = $value;
            }
        
            foreach ($new_order_goods_list as $key => $value)
            {
                $number = 0;
                $cash = 0;
                foreach ($value as $item)
                {
                    $number += $item->number;
                    $cash += $item->price_cash * $item->number;
                }
        
                $data['goods_id'] = $key;
                $data['number'] = $number;
                if($number)
                {
                    $data['average_price'] = round($cash / $number , 2);
                }
                else
                {
                    $data['average_price'] = 0.00;
                }
                $data['delete'] = DELETE_FALSE;
                $data['timestamp'] = date('Y-m-d H:i:s' , strtotime("-1 day"));
        
                $this->getGoodsStatisticsTable()->insertData($data);
            }
        }
        die;
    }
    
    public function goodsWeekStatisticsAction()//统计一周内
    {
        set_time_limit(0);
        $start_time = date('Y-m-d 00:00:00' , strtotime("-7 day"));
        $end_time  = date('Y-m-d 23:59:59' , time());
        $where = new Where();
        $where->between("timestamp", $start_time , $end_time);
        $where->equalTo('delete', DELETE_FALSE);
    
        $order_goods_list = $this->getOrderGoodsTable()->fetchAll($where);
    
        if($order_goods_list)
        {
            $new_order_goods_list = array();
            foreach ($order_goods_list as $value)
            {
                $new_order_goods_list[$value->goods_id][] = $value;
            }
    
            foreach ($new_order_goods_list as $key => $value)
            {
                $number = 0;
                $cash = 0;
                foreach ($value as $item)
                {
                    $number += $item->number;
                    $cash += $item->price_cash * $item->number;
                }
    
                $data['goods_id'] = $key;
                $data['number'] = $number;
                if($number)
                {
                    $data['average_price'] = round($cash / $number , 2);
                }
                else
                {
                    $data['average_price'] = 0.00;
                }
                $data['delete'] = DELETE_FALSE;
                $data['type'] = 2;//每周
                $data['timestamp'] = date('Y-m-d H:i:s' , strtotime("-1 day"));
    
                $this->getGoodsStatisticsTable()->insertData($data);
            }
        }
    
        die;
    }
    
    public function automaticDeliveryAction()
    {
        set_time_limit(0);
        $week_plan = $this->getWeekPlanTable()->fetchAll(array('date_time' => date('Y-m-d') , 'delete' => DELETE_FALSE));
        
        foreach ($week_plan as $value)
        {
            $order_goods_info = $this->getOrderGoodsTable()->getOne(array('id' => $value->order_goods_id , 'delete' => DELETE_FALSE));
            
            $not_supply_number = $order_goods_info->supply_number - $value->number;
            
            $not_supply_number < 0 &&$ $not_supply_number = 0;
            $this->getOrderGoodsTable()->updateData(array('supply_number' => $not_supply_number), array('id' => $value->order_goods_id , 'delete' => DELETE_FALSE));
        }
        die;
    }
    
    public function automaticReceiveAction()
    {
        set_time_limit(0);
        
        $no_delay_date = date('Y-m-d H:i:s' , strtotime('-7 day'));
        $delay_date = date('Y-m-d H:i:s' , strtotime('-14 day'));
        
        $no_delay_where = new Where();
        $no_delay_where->equalTo('children', 0);
        $no_delay_where->equalTo('delay', 0);
        $no_delay_where->equalTo('status', 4);
        $no_delay_where->lessThan('shipments', $no_delay_date);
        
        $delay_date_where = new Where();
        $delay_date_where->equalTo('children', 0);
        $delay_date_where->equalTo('delay', 1);
        $delay_date_where->equalTo('status', 4);
        $delay_date_where->lessThan('shipments', $delay_date);
        
        $no_delay_where->orPredicate($delay_date_where);
        
        $order_list = $this->getOrderTable()->fetchAll($no_delay_where);
        if($order_list)
        {
            
            foreach ($order_list as $value)
            {
                $this->dump($value->order_sn);
                $this->getOrderTable()->updateData(array('status' => 5) , array('id' => $value->id));
                
                $data['status'] = 5;
                $data['user_id'] = $value->user_id;
                $data['order_id'] = $value->id;
                $data['timestamp'] = $this->getTime();
                $this->getOrderTrackingTable()->indateKey($data,1,5, $value->order_sn);
            }
        }
        die;
        
    }
    
    public function importGoodsCategoryDataAction()
    {
        set_time_limit(0);
        if(isset($_POST['leadExcel']) && $_POST['leadExcel'] == "true")
        {
            $type = isset($_POST['type']) ? $_POST['type'] : 1;
            
            $filename = $_FILES['inputExcel']['name'];
            $tmp_name = $_FILES['inputExcel']['tmp_name'];
            
            $time=date("y-m-d-H-i-s");
            $extend=strrchr ($filename,'.');
            $name=$time.$extend;
            $uploadfile = 'F:/xampp/htdocs/jihong/'.$name;
            $result=move_uploaded_file($tmp_name,$uploadfile);
            
            if($result)
            {
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/PHPExcel/IOFactory.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel/Reader/Excel5.php';
                
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($uploadfile);
                $objWorksheet = $objPHPExcel->getActiveSheet();
                $highestRow = $objWorksheet->getHighestRow(); 
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
                $headtitle=array();
                $goods_id = '';
                for ($row = 2;$row <= $highestRow;$row++) 
                {
                    $strs=array();
                    for ($col = 0;$col < $highestColumnIndex;$col++)
                    {
                        $strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
                    
                    $data = array();
                    $data['type'] = $type;
                    if($strs[1])
                    {
                        $data['name'] = $strs[2];
                        $data['timestamp'] = $this->getTime();
                        $goods_id = $this->getGoodsCategoryTable()->insertData($data);
                    }
                    elseif($strs[3])
                    {
                        $data['parent_id'] = $goods_id;
                        $data['name'] = $strs[4];
                        $data['timestamp'] = $this->getTime();
                        $this->getGoodsCategoryTable()->insertData($data);
                    }
                }
            }
            echo '导入分类成功';
            die;
        }
        
        $view=new ViewModel(array());
        return $view->setTemplate('admin/index/upload_category');
    }
    
    public function importGoodsDataAction()
    {
        set_time_limit(0);
        if(isset($_POST['leadExcel']) && $_POST['leadExcel'] == "true")
        {
            $type = isset($_POST['type']) ? $_POST['type'] : 1;
            
            $filename = $_FILES['inputExcel']['name'];
            $tmp_name = $_FILES['inputExcel']['tmp_name'];
            
            $time=date("y-m-d-H-i-s");
            $extend=strrchr ($filename,'.');
            $name=$time.$extend;
            $uploadfile = 'F:/xampp/htdocs/jihong/'.$name;
            $result=move_uploaded_file($tmp_name,$uploadfile);
            
            if($result)
            {
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/PHPExcel/IOFactory.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel/Reader/Excel5.php';
                
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($uploadfile);
                $objWorksheet = $objPHPExcel->getActiveSheet();
                $highestRow = $objWorksheet->getHighestRow(); 
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
                $headtitle=array();
                $where = new Where();
                $user_id = 0;
                for ($row = 2;$row <= $highestRow;$row++) 
                {
                    $strs=array();
                    for ($col = 0;$col < $highestColumnIndex;$col++)
                    {
                        $strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
                    
                    $this->dump($strs);
                    
                    if($strs[4])
                    {
                        $unit_exists =$this->getGoodsUnitTable()->getOne(array('name' => $strs[4]));
                        if(!$unit_exists)
                        {
                            $unit_data = array();
                            $unit_data['name'] = $strs[4];
                            $unit_data['delete'] = DELETE_FALSE;
                            $unit_data['timestamp'] = $this->getTime();
                            
                            $unit_id = $this->getGoodsUnitTable()->insertData($unit_data);
                        }
                        else
                        {
                            $unit_id = $unit_exists->id;
                        }
                    }
                    else 
                    {
                        $unit_info = $this->getGoodsUnitTable()->getOne(array('name' => '盆' , 'delete'=> DELETE_FALSE));
                        
                        $unit_id = $unit_info->id;
                    }
                    
                    if($strs[6])
                    {
                        $where->like('company_name', '%' . $strs[6] . '%');
                        $where->equalTo('type', 2);
                        $user_info = $this->getUserTable()->getOne($where);
                        if($user_info)
                        {
                            $user_id = $user_info->id;
                        }
                    }
                    
                    $data = array();
                    $data['type'] = $type;
                    $data['name'] = $strs[2];
                    $data['code'] = $strs[1];
                    $data['unit_id'] = $unit_id;
                    $data['goods_sn'] = $strs[1];
                    $data['max_cash'] = $strs[8];
                    $data['min_cash'] = $strs[8];
                    $data['number'] = 0;
                    $data['sale_number'] = 0;
                    $data['original_price'] = $strs[8];
                    $data['status'] = 3;
                    $data['salse_type'] = 0;
                    $data['user_id'] = $user_id;
                    $data['category_id'] = 20; //要改
                    $data['delete'] = DELETE_FALSE;
                    $data['timestamp'] = $this->getTime();
                    
                    $goods_id = $this->getGoodsTable()->insertData($data);
                    
                    $spec_data = array();
                    $spec_data['size'] = $strs[3];
                    $spec_data['model'] = '';
                    $spec_data['goods_id'] = $goods_id;
                    
                    $specifician_exists = $this->getGoodsSpecificationTable()->getOne($spec_data);
                    if(!$specifician_exists)
                    {
                        $spec_data['cash'] = $strs[8];
                        $spec_data['number'] = 0;
                        $spec_data['sale_number'] = 0;
                        $spec_data['pack_number'] = $strs[9];
                        $spec_data['delete'] = DELETE_FALSE;
                        $spec_data['timestamp'] = $this->getTime();
                        
                        $this->getGoodsSpecificationTable()->insertData($spec_data);
                    }
                }
            }
            echo '导入商品成功';
            die;
        }
        $view=new ViewModel(array());
        return $view->setTemplate('admin/index/upload_goods');
    }
    
    public function importSupplierDataAction()
    {
        set_time_limit(0);
        if(isset($_POST['leadExcel']) && $_POST['leadExcel'] == "true")
        {
            $filename = $_FILES['inputExcel']['name'];
            $tmp_name = $_FILES['inputExcel']['tmp_name'];
        
            $time=date("y-m-d-H-i-s");
            $extend=strrchr ($filename,'.');
            $name=$time.$extend;
            $uploadfile = 'F:/xampp/htdocs/jihong/'.$name;
            $result=move_uploaded_file($tmp_name,$uploadfile);
        
            if($result)
            {
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/PHPExcel/IOFactory.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel/Reader/Excel5.php';
        
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($uploadfile);
                $objWorksheet = $objPHPExcel->getActiveSheet();
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
                $headtitle=array();
                for ($row = 2;$row <= $highestRow;$row++)
                {
                    $strs=array();
                    for ($col = 0;$col < $highestColumnIndex;$col++)
                    {
                        $strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
                    
                    if($strs[5])
                    {
                        $admin_exists = $this->getAdminTable()->getOne(array('name' => $strs[5]));
                        
                        if(!$admin_exists)
                        {
                            $admin_data = array();
                            $admin_data['name'] = $strs[5];
                            $admin_data['mobile'] = '13719289416';
                            $admin_data['qq'] = '604625124';
                            $admin_data['password'] = md5('123456');
                            $admin_data['real_name'] = $strs[5];
                            $admin_data['admin_category_id'] = 2;
                            $admin_data['status'] = 1;
                            $admin_data['delete'] = DELETE_FALSE;
                            $admin_data['timestamp'] = $this->getTime();
                            
                            $admin_id = $this->getAdminTable()->insertData($admin_data);
                        }
                        else 
                        {
                            $admin_id = $admin_exists->id;
                        }
                    }
                    
                    if($strs[8])
                    {
                        $user_exists = $this->getUserTable()->getOne(array('name' => $strs[8]));
                        
                        if($user_exists)
                        {
                            continue;
                        }
                        
                        $data = array();
                        $data['type'] = 2;
                        $data['register_status'] = 3;
                        $data['status'] = 1;
                        $data['company_name'] = $strs[2];
                        $data['contacts_name'] = $strs[9];
                        $data['name'] = $strs[8];
                        $data['password'] = md5('123456');
                        $data['mobile'] = $strs[8];
                        $data['admin_id'] = $admin_id;
                        $data['cash'] = $strs[7];
                        $data['address'] = $strs[10];
                        $data['delete'] = DELETE_FALSE;
                        $data['timestamp'] = $this->getTime();
                        
                        $this->getUserTable()->insertData($data);
                    }
                 }
             }
             echo '导入供应商成功';
             die;
         }
         $view=new ViewModel(array());
         return $view->setTemplate('admin/index/upload_supplier');
    }
    
    public function importDealerDataAction()
    {
        set_time_limit(0);
        if(isset($_POST['leadExcel']) && $_POST['leadExcel'] == "true")
        {
            $filename = $_FILES['inputExcel']['name'];
            $tmp_name = $_FILES['inputExcel']['tmp_name'];
        
            $time=date("y-m-d-H-i-s");
            $extend=strrchr ($filename,'.');
            $name=$time.$extend;
            $uploadfile = 'F:/xampp/htdocs/jihong/'.$name;
            $result=move_uploaded_file($tmp_name,$uploadfile);
        
            if($result)
            {
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/PHPExcel/IOFactory.php';
                include APP_PATH . '/vendor/Core/System/phpExcel/phpExcel/Reader/Excel5.php';
        
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($uploadfile);
                $objWorksheet = $objPHPExcel->getActiveSheet();
                $highestRow = $objWorksheet->getHighestRow();
                $highestColumn = $objWorksheet->getHighestColumn();
                $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);//总列数
                $headtitle=array();
                for ($row = 2;$row <= $highestRow;$row++)
                {
                    $strs=array();
                    for ($col = 0;$col < $highestColumnIndex;$col++)
                    {
                        $strs[$col] =$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                    }
        
                    
                    $this->dump($strs);
                    if($strs[3])
                    {
                        $admin_exists = $this->getAdminTable()->getOne(array('name' => $strs[3]));
        
                        if(!$admin_exists)
                        {
                            $admin_data = array();
                            $admin_data['name'] = $strs[3];
                            $admin_data['mobile'] = '13719289416';
                            $admin_data['qq'] = '604625124';
                            $admin_data['password'] = md5('123456');
                            $admin_data['real_name'] = $strs[3];
                            $admin_data['admin_category_id'] = 2;
                            $admin_data['status'] = 1;
                            $admin_data['delete'] = DELETE_FALSE;
                            $admin_data['timestamp'] = $this->getTime();
                
                            $admin_id = $this->getAdminTable()->insertData($admin_data);
                        }
                        else
                        {
                            $admin_id = $admin_exists->id;
                        }
                    }
                    
                    if($strs[7])
                    {
                        $user_exists = $this->getUserTable()->getOne(array('name' => $strs[8]));
                    
                        if($user_exists)
                        {
                            continue;
                        }
                    
                        $data = array();
                        $data['type'] = 1;
                        $data['register_status'] = 3;
                        $data['status'] = 1;
                        $data['company_name'] = $strs[2];
                        $data['contacts_name'] = $strs[5];
                        $data['name'] = $strs[7];
                        $data['password'] = md5('123456');
                        $data['mobile'] = $strs[7];
                        $data['admin_id'] = $admin_id;
                        $data['cash'] = $strs[4];
                        $data['address'] = $strs[6];
                        $data['delete'] = DELETE_FALSE;
                        $data['timestamp'] = $this->getTime();
                    
                        $this->getUserTable()->insertData($data);
                    }
                }
            }
            echo '导入经销商成功';
            die;
        }
        $view=new ViewModel(array());
        return $view->setTemplate('admin/index/upload_dealer');
    }
}
