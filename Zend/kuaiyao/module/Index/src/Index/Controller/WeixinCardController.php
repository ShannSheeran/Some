<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Api3\Controller\Order;
use Api3\Controller\CommonController as api;
use Zend\View\View;
use Api3\Controller\SMSCode;
use Core\System\WxApi\WxApi;
use Core\System\WxPayApi\AiiWxPay;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Validator\InArray;
use Core\System\WxApi\WxJsApi;
use Admin\Model\UserAddressTable;
use Admin\Model\orderTable;
use Api\Controller\User;
use Api\Controller\CardDetails;
use Index\Controller\WeixinController;




class WeixinCardController extends CommonController
{
    public function indexAction()
    {
        $this->wxlogin();
        $page_id=$this->params()->fromRoute('id');
        $info=$this->getViewPageCarteTable()->getOne(array('id'=>$page_id));
        $head_icon=$this->getImageTable()->getOne(array('id'=>$info['head_icon']));
        $wx_code=$this->getImageTable()->getOne(array('id'=>$info['wx_code']));
        if($_POST)
        {
            /*
             *头像上传
             */
            $list='';
            if(isset($_FILES) && $this->check_file_type($_FILES['head_icon']['tmp_name'])){
                $file = $this->getApiController()->uploadImageForController("head_icon");
                $list['head_icon']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
            }else{
                $list['head_icon'] = $_POST['image'];
            }
            /*
             * 微信二维码的上传
             */
            if(isset($_FILES) && $this->check_file_type(@$_FILES['wx_code']['tmp_name'])){
                $file = $this->getApiController()->uploadImageForController("wx_code");
                $list['wx_code']=$image_id =isset($file["ids"][0]) ? $file["ids"][0] : 0;
            }else{
                $list['wx_code'] = $_POST['wx_code'];
            }
            $list['name']=$_POST['realName'];
            $list['englist_name']=$_POST['englishName'];
            $list['position']=$_POST['position'];
            $list['email']=$_POST['email'];
            $list['qq']=$_POST['qq'];
            $list['company']=$_POST['companyName'];
            $list['timestamp_update']=$this->getTime();
            $card_info=$this->getViewPageCarteTable()->getOne(array('id'=>$_POST['page_id']));
            $company=$this->getCompanyTable()->getOne(array('name'=>trim($_POST['companyName'])));
            if($company)
            {
                $list['company_id']=$company['id'];
            }
            if($card_info)
            {
                $return=$this->getCarteTable()->updateData($list,array('id'=>$card_info['c_id']));
                if($return)
                {
                    //$this->redirect()->toRoute('index',array('controller'=>'WeixinCard','action'=>'pageDetails','id'=>$card_info['id']));
                    $this->redirect()->toRoute('index',array('controller'=>'commodity','action'=>'indent','id'=>$card_info['user_id']));
                }
            }
        }


        $view = new ViewModel(array(
            'page_id'=>$page_id,
            'info'=>$info,
            'head_icon'=>$head_icon,
            'wx_code'=>$wx_code,
        ));
        $view->setTemplate('index/wxcard/index');
        return $this->setMenu($view, 2);
    }




    public function pageDetailsAction()
    {
        $this->wxlogin();
        $id = isset($_GET['id']) ? $_GET['id'] : '';

        $cardDate = $this->getViewPageCarteTable()->getOne(array('id'=>$id));
        if($cardDate['name']==''){
            $this->redirect()->toRoute('index',array('controller'=>'WeixinCard','action'=>'index','id'=>$cardDate['id']));
        }
        $companyName=$this->getCompanyTable()->getOne(array('name'=>trim($cardDate['company'])));
        $companyinfo='';
        if($companyName)
        {
            $newCompany=$this->getImageTable()->getOneImage($companyName['image']);
            if($newCompany)
            {
                $companyinfo['logo']=$newCompany['path'].@$newCompany['filename'];
                $companyinfo['address']=$companyName['address'];
                $companyinfo['id']=$companyName['id'];
            }

        }
        $carte_info = $this->getCarteTable()->getOne(array('id' => $cardDate['c_id']));
        $company = $this->getCompanyTable()->getOne(array('id' => $cardDate['company_id']));
        if($company){
            $logo = $this->getImageTable()->getOne(array('id'=>$company['image']));
            $company['imagepath'] = $logo['path'] . $logo['filename'];
        }

        $vcard = null;
        $wx_code_path = "";
        if ($carte_info) {
            $vcard_path = $this->getImageTable()->getOne(array('id'=>$carte_info['qr_code']));
            $vcard = array(
                'image_id' => $carte_info['qr_code'],
                'image_path' => $vcard_path ? $vcard_path['path'] . $vcard_path['filename'] : '',
                'vcard' => $carte_info['vcard']
            );
            if ($carte_info['wx_code']) {
                $wx_code_path = $this->getImageTable()->getOne(array('id'=>$carte_info['wx_code']));
                $wx_code_path = $wx_code_path ? $wx_code_path['path'] . $wx_code_path['filename'] : "";
            }
        }
        $image = $this->getImageTable()->getOne(array('id'=>$cardDate['company_logo']));
        $head_icon = '';
        if ($cardDate['head_icon']) {
            $head_icon = $this->getImageTable()->getOne(array('id'=>$cardDate['head_icon']));
            $head_icon = $head_icon['path'] . $head_icon['filename'];
        }

        $show = explode(',', $cardDate['show']);
        if(!in_array('erweima', $show)){
            $cardDate['erweima'] = '';
        }

        $erweima_path = '';
        if ($cardDate['erweima']) {
            $erweima_path = $this->getImageTable()->getOne(array('id'=>$cardDate['erweima']));
            $erweima_path = $erweima_path['path'] . $erweima_path['filename'];
        }
        //print_r($cardDate);die();
        $view = new ViewModel(array(
            'card' => $cardDate,
            'image' =>$image,
            'head_icon' => $head_icon,
            'erweima_path' => $erweima_path,
            'wx_code_path' => $wx_code_path,
            'vcard' => $vcard,
            'show' => $show,
            //'type' => $type,
            'company' => $company,
            'newCompany'=>$companyinfo,
            'id'=>$id,
        ));
        $device= $this->getDeviceTable()->getOne(array('user_id'=>$cardDate['user_id']));


        if($device)
        {
            $this->getPageTable()->updateKey($id, 1, 'count', 1);
        }

        $view->setTemplate('index/wxcard/WxDetails');

        return $this->setMenu($view, 2);
    }

}
?>