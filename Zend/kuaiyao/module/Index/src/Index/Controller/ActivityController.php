<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Api3\Controller\Activity;
use Api3\Controller\CommonController as api;
use Admin\Controller\CommonController as AdminController;
use Api3\Controller\ActivityDetails;
use Zend\View\View;
use Api3\Controller\SMSCode;
use Core\System\WxApi\WxApi;
use Core\System\WxPayApi\AiiWxPay;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Validator\InArray;
use Core\System\WxApi\WxJsApi;
use Admin\Model\ActivityTable;


class ActivityController extends CommonController
{
    
    /**
     * 个人中心
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    { 
	   $id = (int) $this->params()->fromRoute('id');
       $activity = $this->getActivityTable()->getOne(array('delete'=>0,'id'=>$id));
		//活动统计
		$this->getAdminController()->statOporation(7,1,null,$id);
		/*print_r($activity);die;*/
		//活动总量统计
		$visit = $this->getStatisticsDayTable()->getAll(array('type' =>7,'activity_id' =>$id));
		$total_visit = array();
		foreach($visit['list'] as $v){
			$total_visit[]=$v['value'];
		}
		$total = array_sum($total_visit);
		$this->getActivityTable()->updateData(array('stat_visit'=>$total),array('id'=>$id));

		$Img=$activity['image'].','.$activity['images'];
	   $ids=explode(',',trim($Img,','));
		if($ids)
		{
			$path=$aImage = $this->getImageTable()->getImages($ids);
		}
	   //母公司
       $company =$this->getCompanyTable()->getOne(array('id'=>$activity['company_id']));
	   $pLogo=$this->getImageTable()->getOne(array('id'=>$company['image']));
	   $pRegion=$this->getRegionTable()->getOne(array('id'=>$company['region_id']));

	   //子公司 活动，公司，图片
	   $son=$this->getCompanyTable()->fetchAll(array('parent_id'=>$company['id']));
	   $logo=array();
	   $region=array();
	   foreach($son as $v){
		   $v['images']=$this->getImageTable()->getOne(array('id'=>$v['image']));
		   $v['region']=$this->getRegionTable()->getOne(array('id'=>$v['region_id']));
	   }
	   
       if(!$activity){
           die();
       }
       
       $view = new ViewModel(array(
			'region'=>$pRegion,
            'logo'=>$pLogo,
			'son'=>$son,
			'company'=>$company,
			'activity' => $activity,
			'path'=>isset($path)?$path:'',
            'scale' => $this->getAdminController()->scale(),
            'category' => $this->getAdminController()->category(),
            ));
       $view->setTemplate('index/activity/index');   
       return $this->setMenu($view, 2);

    }


	/*
    * 公司访问量折线图每月数据统计
    * */
	public function activityMonthAction()
	{
		//传入年份
		$activity_id=$alert=$this->params()->fromRoute('id');
		$alert=$this->params()->fromRoute('alert');
		$date = isset($alert)? $alert : date('Y', time());
		$time = $this->getAdminController()->getFirstLastTime($date);

		$where = new where();
		$where->equalTo('type', 7);
		$where->equalTo('activity_id', $activity_id);
		$where->greaterThanOrEqualTo('date', $time['first']);
		$where->lessThanOrEqualTo('date', $time['last']);
		$data = $this->getStatisticsDayTable()->getAll($where);
		$num = 0;
		$info=array();
		foreach ($data['list'] as $val) {
			$num += $val['value'];
		}
		if ($num < 1) {
			$firstday = $date.'-01-01';
			$lastday = $date.'-01-31';
			$TimeArray = array('first'=>$firstday, 'last'=>$lastday);
			$where = new where();
			$where->equalTo('type', 7);
			$where->equalTo('activity_id', $activity_id);
			$where->greaterThanOrEqualTo('date', $TimeArray['first']);
			$where->lessThanOrEqualTo('date', $TimeArray['last']);
			$data = $this->getStatisticsDayTable()->getAll($where);
			$infoArray = array();
			foreach ($data['list'] as $val){
				$infoArray[$val['date']] = $val['value'];
			}
			$info = $infoArray;

		} else {
			$array = array();
			for ($i = 1; $i <= 12; $i ++) {
				$array[] = $this->getAdminController()->getStasticsData('activity_id',$i,7, $date,$activity_id);
			}
			$info = $array;

		}
		$t = array();
		$d = array();
		foreach ($info as $v) {
			$d[] = intval($v);
			$t[] = '';
		}
		$view = new ViewModel(array(
			'data' => json_encode($d),
			'date' => json_encode($t),
			'info' => isset($info)?$info:'',
			'num'=>$num,
			'year'=>$date,
			'companyId'=>$activity_id,
		));
		$view->setTemplate('index/activity/activityMonth');
		return $view;
	}

	/*
     * 公司访问量折线图每日数据统计
     *
     * */
	public function activityDayAction()
	{
		//传入年月
		$activity_id=$alert=$this->params()->fromRoute('id');
		$year=$this->params()->fromRoute('alert');
		$month=$this->params()->fromRoute('page');
		$parame=$year.$month;
		$title=$year.'年'.$month;
		$date = isset($parame) ? $parame : date('Ym', time());
		$time = $this->getAdminController()->getTheMonths($date);
		$where = new where();
		$where->equalTo('type', 7);
		$where->equalTo('activity_id',$activity_id);
		$where->greaterThanOrEqualTo('date', $time['first']);
		$where->lessThanOrEqualTo('date', $time['last']);
		$data = $this->getStatisticsDayTable()->getAll($where);
		$t = array();
		$d = array();
		foreach ($data['list'] as $v) {
			$d[] = intval($v['value']);
			$t[] = '';
		}
		$view = new ViewModel(array(
			'data' => json_encode($d),
			'date' => json_encode($t),
			'info' => $data,
			'title'=>$title,
		));
		$view->setTemplate('index/activity/activityDay');
		return $view;
	}
	/*
     * 每年数据
     *
     * */
	public function activityYearAction()
	{
		$activity_id=isset($_GET['activityId'])?$_GET['activityId']:1;
		$year=array();
		for($i = 1 ;$i <= 1 ; $i++ )
		{
			$year[] = 2015+ $i;
		}
		$info = array();
		foreach($year as $key=>$val){
			$info[$val] = $this->getAdminController()->getYearData('activity_id',7,$val,$activity_id);
		}
		$d = array();
		$t = array();
		foreach ($info as $k=>$v) {
			$d[] = intval($v);
			$t[] = $k;
		}
		$view = new ViewModel(array(
			'data'=>$info,
			'datas' => json_encode($d),
			'date'=>json_encode($t),
			'companyId'=>$activity_id,
		));
		$view->setTemplate('index/activity/activityYear');
		return $view;
	}

}