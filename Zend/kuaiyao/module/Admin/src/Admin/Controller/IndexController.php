<?php
namespace Admin\Controller;

use Core\System\WxApi\WxApi;
use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Core\System\UploadfileApi;

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

        $type=$this->params()->fromRoute('type');
        $month=$this->params()->fromRoute('month');
        $year=$this->params()->fromRoute('year');
        if(!$type) {
            $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 1;
            $year = isset($_REQUEST['YYYY']) ? $_REQUEST['YYYY'] : date('Y', time());
            $month = isset($_REQUEST['MM']) ? $_REQUEST['MM'] : '01';
        }

        $year_array=array();
        for($i=1;$i<7;$i++)
        {
            $year_array[]=2014+$i;
        }

        $month_array=array();
        for($j=1;$j<=12;$j++)
        {
            if($j<10){
                $month_array[]='0'.$j;
            }else{
                $month_array[]=$j;
            }
        }

        if($type==1){
            $title='新增人数';
            $type=1;
        }else if($type==2){
            $title="摇一摇人数";
            $type=2;

        }else if($type==3){
            $title="入驻公司数";
            $type=3;
        }
        else if($type==4){
            $title="设备销量";
            $type=4;
        }
        $date=$year.$month;
        $time=$this->getTheMonths($date);
        $where=new where();
        $where->equalTo('type',$type);
        $where->greaterThanOrEqualTo('date',$time['first']);
        $where->lessThanOrEqualTo('date',$time['last']);
        $data=$this->getStatisticsDayTable()->getAll($where);
        $t=array();
        $d=array();
        $s = array();
        foreach($data['list'] as $v)
        {
            $d[]=intval($v['value']);
            $t[]=$v['date'];
            $s[] = array($v['date'],$v['value']);
        }
        //表格下载
        $downLoad=$this->params()->fromRoute('type');
        if($downLoad){
            $this->getcall($title, $s);
        }

        $this->breadcrumb = array(
            array(
                'url' => '',
                'title' => '主页'
            ),
            array(
                'url' => '',
                'title' => '数据统计'
            )
        );
        $view=new ViewModel(array(
            'data'=>json_encode($d),
            'date'=>json_encode($t),
            'info'=>$data,
            'title'=>$title,
            'year'=>$year,
            'month'=>$month,
            'se_year'=>$year_array,
            'se_month'=>$month_array,
            'type'=>$type
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
                $this->getAdminTable()->update(array(
                    'last_time' => date('Y-m-d H:i:s')
                ), array(
                    'id' => $admin_info['id']
                ));
                $_SESSION['admin_name'] = $admin_info['name'];
                $_SESSION['admin_id'] = $admin_info['id'];
                $_SESSION['super'] = $admin_info['super'];
                $_SESSION['action_list'] = 'all'; // 给所有权限
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
    public function getStatistics($start, $end)
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
    }
    /* 
    public function testAction()
    {
        $wxApi = new WxApi();
        $res =  $wxApi->wxUploadMedia("201508/04/170245_4262.jpg");
        
        $file = new UploadfileApi();
        $file->setPath(APP_PATH.'/public/'.UPLOAD_PATH);
        $path = $file->path;
        $media_id = $res['media_id'];
        echo "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$wxApi->wxAccessToken}&media_id=$media_id";
       var_dump(file_put_contents($path."86.jpg", file_get_contents("http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$wxApi->wxAccessToken}&media_id=$media_id")));
       die();
    } */
    
   public function getcall($title,$datas){
       include './vendor/Core/System/phpExcel/PHPExcel.php'; 
       //创建对象
       $excel = new\PHPExcel();
       //Excel表格式,这里简略写了8列
       $letter = array('A','B');
       //表头数组
       $tableheader = array('时间',$title);
       //填充表头信息
       for($i = 0;$i < count($tableheader);$i++) {
           $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
       }
       
       $data = $datas;
      /*  //表格数组
       $data = array(
           array('1','小王','男','20','100'),
           array('2','小李','男','20','101'),
           array('3','小张','女','20','102'),
           array('1','11');
       ); */

       //填充表格信息
       for ($i = 2;$i <= count($data) + 1;$i++) {
           $j = 0;
           foreach ($data[$i - 2] as $key=>$value) {
               $excel->getActiveSheet()->setCellValue("$letter[$j]$i","$value");
               $j++;
           }
       }
       $times = time().rand(1, 100);
       //创建Excel输入对象
       $write = new\PHPExcel_Writer_Excel5($excel);
       header("Pragma: public");
       header("Expires: 0");
       header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
       header("Content-Type:application/force-download");
       header("Content-Type:application/vnd.ms-execl");
       header("Content-Type:application/octet-stream");
       header("Content-Type:application/download");;
       header("Content-Disposition:attachment;filename=$times.xls");
       header("Content-Transfer-Encoding:binary");
       $write->save('php://output');
   }
}
