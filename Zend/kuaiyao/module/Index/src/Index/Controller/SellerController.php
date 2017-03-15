<?php
namespace Index\Controller;

use Zend\Db\Sql\Where;
use Api\Controller\Request\GoodsWhereRequest;
use Zend\Db\Sql\Expression;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Validator\StringLength;
use Zend\Console\Prompt\Select;
use Zend\Http\Client\Adapter\Socket;


class SellerController extends CommonController
{

    /**
     * 联盟商家首页
     */
    public function indexAction()
    {

        $img_arr = array();
        $id =$this->params()->fromRoute('id',0);
        $a = $this->params()->fromRoute('alert',0);
        $b = $this->params()->fromRoute('between',0);
        $t = $this->params()->fromRoute('type',0);
        $s = $this->HtmlFilter( $this->params()->fromRoute('search','') );
        $_SESSION['s'] = $s;
        $page = $this->params()->fromRoute('page',1);
        $City_id = isset($_COOKIE['city_id']) && $_COOKIE['city_id'] != 0 ? $_COOKIE['city_id'] : '';
        $Region = array();

        $order = array('id'=>'desc');

        $GoodsCategory = $this->getGoodsCategoryTable()->fetchAll(array('type'=>1,'delete'=>0,'status'=>0));

        //筛选 市区分组
        $City = $this->getRegionTable()->fetchAll(array('status'=>1),array('id asc'));
        foreach($City as $v){
            $Region[$v['id']] = $this->getRegionTable()->fetchAll(array('parent_id'=>$v['id']),array('id'=>'asc'));
        }



        $Evaluate = $this->getEvaluateTable()->fetchAll();

        $where = new Where();
        $where->equalTo('auth_status', 1);
        if($City_id != ''){
            $where->equalTo('status',1)->equalTo('delete',0)->equalTo('city_id',$City_id);
        }else{
            $where->equalTo('status',1)->equalTo('delete',0);
        }

        if($id>0){
            $where->equalTo('category_id', $id);
        }
        if($a>0){
            $where->equalTo('region_id', $a);
        }
        if($t>0){
            $order = array(
                'stars'=>'desc',
                'timestamp'=>'desc',
            );
        }
        if($b>0){
            $order = array(
                'income_golden_cat'=>'desc',
            );
        }
        if($s!=''){
            $where->like('company_name', '%'.$s.'%');
        }
		
        
        $List = $this->getMerchantTable()->getAll($where,null,$order,1,$page,52);


        foreach($List['list'] as $k=>$v){
            if($v['image']){
                $shop_img = explode(',',$v['image']);
                $img_arr = array_merge($img_arr,$shop_img);
            }
        }

        $images = $this->getImageTable()->getImages($img_arr,1);

        $List_shuffle = $List['list'];
        shuffle($List_shuffle);

        $view = new ViewModel(array(
            'paginator' => $List['paginator'],
            'List' => $List['list'],
            'condition' => array(
                'controller' => 'seller',
                'action' => 'index',
                'page' => $page,
                'id' => $id,
                'alert' => $a,
                'between' => $b,
                'type' => $t,
                'search' => $s,
            ),
            'List_shuffle'=>$List_shuffle,
            'Evaluate'=>$Evaluate,
            'city_id'=>$City_id,
            'Region'=>$Region,
            'images'=>$images,
            'GoodsCategory'=>$GoodsCategory,
            'id' => $id,
            'a' => $a,
            'b' => $b,
            't' => $t,
            's' => $s,
        ));
        $view->setTemplate('index/seller/seller');
        return $this->setMenu($view,'l');
    }

    /**
     * 联盟商家详细页
     */
    public function sellerDetailAction()
    {
        $img_arr = array();
        $merchant_img = array();
        $goods_img = array();
        $id = $this->params()->fromRoute('id');
        $merchant = $this->getMerchantTable()->getOne(array('id'=>$id,'delete'=>0,'status'=>1));
        if(!$merchant){ $this->showMessage('该商家不存在或者已被停用！'); }
        $goods = $this->getGoodsTable()->fetchAll(array('user_id'=>$id,'delete'=>0,'type'=>1,'status'=>1),array('sale_number'=>'desc'));
        $favorites = $this->getFavoritesTable()->getOne(array('merchant_id'=>$id));


        if($merchant['image']){
            $merchant_img = explode(',', $merchant['image']);
        }
        foreach($goods as $k=>$v){
            if($v['image']){
                $goods_img = explode(',',$v['image']);
                $img_arr = array_merge($img_arr,$goods_img);
            }
        }
        if(!empty($favorites)){
            $status = $favorites->delete;
        }else{
            $status = '';
        }

        $img_arr = array_merge($img_arr,$merchant_img);
        $images = $this->getImageTable()->getImages($img_arr,1);


        $view = new ViewModel(array(
            'status'=>$status,
            'images'=>$images,
            'goods'=>$goods,
            'merchant'=>$merchant,
        ));
        $view->setTemplate('index/seller/detail');
        return $this->setMenu($view,'l');
    }

    /**
     * 商家产品详细页
     */
    public function sellerGoodsDetailAction()
    {
        $favorites = array();
        $id = (int)$this->params()->fromRoute('id',0);
        if(isset($_SESSION['index_id'])){
            $favorites = $this->getFavoritesTable()->getOne(array('goods_id'=>$id,'user_id'=>$_SESSION['index_id']));
        }
        $status = !empty($favorites) ? $favorites['delete'] : ''; //收藏状态

        $GoodsDetail = $this->getViewGoodsTable()->getOne(array(
            'id'=>$id,
            'delete'=>0,
            'status'=>1,
            'type'=>1,
            'm_status'=>1,
            'm_delete'=>0
        ));

        if(!$GoodsDetail){
            $this->showMessage('该商品不存在或者已被删除！');
        }

        $GoodsDetail['image'] = explode(',', $GoodsDetail['image']);
        $GoodsDetail['gallery'] = array();
        foreach ($GoodsDetail['image'] as $v){
            if($v>0){
                $image1 = $this->getImageTable()->getImages(array($v),1);
                $image0 = $this->getImageTable()->getImages(array($v),0);
                $GoodsDetail['gallery'][] = array(
                    0 => $image0[$v]['path1'],
                    1 => $image1[$v]['path1'],
                );
            }
        }

        $topSix = $this->getViewGoodsTable()->fetchAll(array(
            'delete'=>0,
            'type'=>1,
            'status'=>1,
            'm_status'=>1,
            'm_delete'=>0
        ),array(
            'sale_number'=>'desc'
        ),6);

        foreach($topSix as $k=>$v){
            $v['image'] = explode(',', $v['image']);
            $v['image'] = $v['image'][0];
            if($v['image']>0){
                $image = $this->getImageTable()->getImages(array($v['image']),1);
                $topSix[$k]['image_url'] = $image[$v['image']]['path1'];
            }
        }

        $view = new ViewModel(array(
            "id"=>$id,
            "GoodsDetail"=>$GoodsDetail,
            "topSix"=>$topSix,
            "status"=>$status,
        ));
        $view->setTemplate('index/seller/sellerGoodsDetail');
        return $this->setMenu($view,'l');
    }
    /*
     * 商家评论列表
     */
    public function sellerListAction()
    {
        $id = $this->params()->fromRoute('id');
        $page = $this->params()->fromRoute('page',1);
        $order = array('id'=>'desc');
        if ($id)
        {
            $where = array(
                'delete' => DELETE_FALSE,
                'merchant_id' => $id
            );
            $data = $this->getViewEvaluateTable()->getAll($where,null,$order,1,$page,20);
          
        }
        $view = new ViewModel(array(
            "data"=>$data,
            "page"=>$page,
        ));
        $view->setTemplate('index/seller/sellerList');
        return $this->setMenu($view,'l');
        
       
    }
}