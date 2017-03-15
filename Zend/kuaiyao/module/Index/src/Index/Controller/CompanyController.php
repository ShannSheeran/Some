<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Api3\Controller\Company;
use Api3\Controller\CommonController as api;
use Admin\Controller\CommonController as AdminController;
use Api3\Controller\CompanyDetails;
use Zend\View\View;
use Api3\Controller\SMSCode;
use Core\System\WxApi\WxApi;
use Core\System\WxPayApi\AiiWxPay;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Validator\InArray;
use Core\System\WxApi\WxJsApi;

class CompanyController extends CommonController
{

    /**
     * 涓汉涓績
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $id = (int) $this->params()->fromRoute('id'); // 拼接

        //统计公司访问数据                                              // 传入公司
                                                 // 1http://127.0.0.1/kuaiyao/public/company/1.html
        $company = $this->getCompanyTable()->getOne(array(
            'delete' => 0,
            'id' => $id
        ));
        $region1 = '';
        if($company['region_info'])
        {
            $info_1 = json_decode($company['region_info'],true);
            if(isset($info_1[1]) && $info_1[1])
            {
                $region1 = $info_1[1]['region']['name'];
            }
        }




        if($company['audit_status']==2)
        {
            $this->getAdminController()->statOporation(5,1,$id);
        }

        /* print_r($company);*/
        $image = $this->getImageTable()->getOne(array(
            'id' => $company['image']
        ));
        $company['imagePath'] = $image['path'] . $image['filename'];
        $activity = $this->getActivityTable()->getLimit(array(
            'company_id' => $company['id'],'delete'=>0
        ));
        $tag_offer = $this->getTagsRelationsTable()->getAll(array(
            'foreign_id' => $company['id'],
            'type' => 1
        ));
        if ($tag_offer) {
            $offers = array();
            foreach ($tag_offer['list'] as $v) {
                $offer = $this->getTagsTable()->getOne(array(
                    'id' => $v['tag_id']
                ));
                $offers[] = $offer['name'];
            }
        }
        
        $tag_need = $this->getTagsRelationsTable()->getAll(array(
            'foreign_id' => $company['id'],
            'type' => 2
        ));
        if ($tag_need) {
            $needs = array();
            foreach ($tag_need['list'] as $v) {
                $need = $this->getTagsTable()->getOne(array(
                    'id' => $v['tag_id']
                ));
                $needs[] = $need['name'];
            }
        }
        if (! $company) {
            die();
        }
        
        $view = new ViewModel(array(
            'id' => $id,
            'company' => $company,
            'companyLogo' => $company['imagePath'],
            'region' => $region1,
            'scale' => $this->getAdminController()->scale(),
            'category' => $this->getAdminController()->category(),
            'activity' => isset($activity) ? $activity : '',
            'tag_offer' => isset($offers) ? $offers : '',
            'tag_need' => isset($needs) ? $needs : ''
        ));
        
        $view->setTemplate('index/company/index');
        return $this->setMenu($view, 2);
    }

    public function pageContentAction()
    {
        $id = (int) $this->params()->fromRoute('id');
        $cid = (int) $this->params()->fromRoute('alert');
        
        if ($id && $cid) {
            $info = $this->getCompanyTable()->getOne(array(
                'id' => $id
            ));
            if ($info) {
                if ($cid == 1) {
                    // 公司或项目描述
                    $content = $info['description'];
                    $title = "公司简介";
                } elseif ($cid == 2) {
                    // 经营项目
                    $content = $info['project'];
                    $title = "经营项目";
                }
            }
        }
        
        $view = new ViewModel(array(
            'content' => $content,
            'title' => $title
        ));
        $view->setTemplate('index/company/pageContent');
        return $this->setMenu($view, 2);
    }

    public function needAction()
    {
        $id = $this->params()->fromRoute('id');
        $company = $this->getCompanyTable()->getOne(array(
            'id' => $id
        ));
        $need_tag = $this->getTagsRelationsTable()->getAll(array(
            'foreign_id' => $id,
            'type' => 2
        ));
        if ($need_tag) {
            $needs = array();
            foreach ($need_tag['list'] as $v) {
                $need = $this->getTagsTable()->getOne(array(
                    'id' => $v['tag_id']
                ));
                $needs[] = $need['name'];
            }
        }
        
        $view = new ViewModel(array(
            'info' => isset($needs) ? $needs : '',
            'company' => isset($company) ? $company : ''
        ));
        $view->setTemplate('index/company/need');
        return $this->setMenu($view, 2);
    }

    public function offerAction()
    {
        $id = $this->params()->fromRoute('id');
        $company = $this->getCompanyTable()->getOne(array(
            'id' => $id
        ));
        $offer_tag = $this->getTagsRelationsTable()->getAll(array(
            'foreign_id' => $id,
            'type' => 1
        ));
        if ($offer_tag) {
            $offers = array();
            foreach ($offer_tag['list'] as $v) {
                $offer = $this->getTagsTable()->getOne(array(
                    'id' => $v['tag_id']
                ));
                $offers[] = $offer['name'];
            }
        }
        $view = new ViewModel(array(
            'info' => isset($offers) ? $offers : '',
            'company' => isset($company) ? $company : ''
        ));
        $view->setTemplate('index/company/offer');
        return $this->setMenu($view, 2);
    }

    public function favorableAction()
    {
        $id = $this->params()->fromRoute('id');
        $company = $this->getCompanyTable()->getOne(array(
            'delete' => 0,
            'id' => $id
        ));
        $image = $this->getImageTable()->getOne(array(
            'id' => $company['image']
        ));
        $company['imagePath'] = $image['path'] . $image['filename'];
        $activity = $this->getActivityTable()->getAll(array(
            'company_id' => $id
        ));
        $view = new ViewModel(array(
            'info' => $activity,
            'companyLogo' => $company['imagePath']
        ));
        $view->setTemplate('index/company/favorable');
        return $this->setMenu($view, 2);
    }

    /*
     * 公司访问量折线图每月数据统计
     * */
    public function pageMonthAction()
    {
        //传入年份
        $company_id=$alert=$this->params()->fromRoute('id');
        $alert=$this->params()->fromRoute('alert');
        $date = isset($alert)? $alert : date('Y', time());
        $time = $this->getAdminController()->getFirstLastTime($date);

        $where = new where();
        $where->equalTo('type', 5);
        $where->equalTo('foreign_id', $company_id);
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
            $where->equalTo('type', 5);
            $where->equalTo('foreign_id', $company_id);
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
                $array[] = $this->getAdminController()->getStasticsData('foreign_id',$i,5, $date,$company_id);
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
            'companyId'=>$company_id,
        ));
        $view->setTemplate('index/company/pageMonth');
        return $view;
    }

    /*
     * 公司访问量折线图每日数据统计
     *
     * */
    public function pageDayAction()
    {
        //传入年月
        $company_id=$alert=$this->params()->fromRoute('id');
        $year=$this->params()->fromRoute('alert');
        $month=$this->params()->fromRoute('page');
        $parame=$year.$month;
        $title=$year.'年'.$month;
        $date = isset($parame) ? $parame : date('Ym', time());
        $time = $this->getAdminController()->getTheMonths($date);
        $where = new where();
        $where->equalTo('type', 5);
        $where->equalTo('foreign_id',$company_id);
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
        $view->setTemplate('index/company/pageDay');
        return $view;
    }
    /*
     * 每年数据
     *
     * */
    public function pageYearAction()
    {
        $company_id=isset($_GET['companyId'])?$_GET['companyId']:1;
        $year=array();
        for($i = 1 ;$i <= 1 ; $i++ )
        {
            $year[] = 2015+ $i;
        }
        foreach($year as $key=>$val){
            $info[$val] = $this->getAdminController()->getYearData('foreign_id',5,$val,$company_id);
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
            'companyId'=>$company_id,
        ));
        $view->setTemplate('index/company/pageYear');
        return $view;
    }


}