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


class ShopController extends CommonController
{
    /**
     * 精品商城列表页
     */
    public function indexAction()
    {
        $id = (int)$this->params()->fromRoute('id',0);
        $a = (int)$this->params()->fromRoute('alert',0);
        $b = (int)$this->params()->fromRoute('between',0);
        $t = (int)$this->params()->fromRoute('type',0);
        $s = $this->HtmlFilter( $this->params()->fromRoute('search','') );
        $_SESSION['s'] = $s;
        $page = (int)$this->params()->fromRoute('page',1);
        $where = array(
            'type'=>2,
            'status'=>0,
            'delete'=>0
        );
        $order = array(
            'sort'=>'asc',
            'id'=>'desc',
        );
        $GoodsCategory = $this->getGoodsCategoryTable()->fetchAll($where,$order);
        
        $where = new Where();
        $where->equalTo('type', 4)->equalTo('status', 1)->equalTo('delete',0);
        if($id>0){
            $where->equalTo('category_id', $id);
        }
        if($a>0){
            $array = $this->returnSort($a);
            if($array['between']>=0 && $array['to']>=0){
                if($array['to'] == '+')
                {
                    $where->greaterThan('golden_cat', $array['between']);
                }
                else{
                    $where->between('golden_cat', $array['between'], $array['to']);
                }


            }
        }
        if($b>0){
            $array = $this->returnSort($b);
            if($array['between']>=0)
            {
                if($array['to'] == '+')
                {
                    $where->greaterThan('silver_cat', $array['between']);
                }
                else
                {
                    $where->between('silver_cat', $array['between'], $array['to']);
                }
            }
        }
        if($s!=''){
            $where->like('name', '%'.$s.'%');
        }
        if($t>0){
            $order = array(
                'sale_number'=>'desc',
                'timestamp'=>'desc',
            );
        }else{
            $order = array(
                'sort'=>'asc',
                'timestamp'=>'desc',
            );
        }
        
        $List = $this->getGoodsTable()->getAll($where,null,$order,1,$page,50);
        foreach($List['list'] as $k=>$v){
            $v['image'] = explode(',', $v['image']);
            $v['image'] = $v['image'][0];
            if($v['image']>0){
                $image = $this->getImageTable()->getImages(array($v['image']),1);
                $List['list'][$k]['image_url'] = $image[$v['image']]['path1'];
            }
        }

        
        $view = new ViewModel(array(
            'paginator' => $List['paginator'],
            'List' => $List['list'],
            'condition' => array(
                'controller' => 'shop',
                'action' => 'index',
                'page' => $page,
                'id' => $id,
                'alert' => $a,
                'between' => $b,
                'type' => $t,
                'search' => $s,
            ),
            'GoodsCategory'=>$GoodsCategory,
            'id' => $id,
            'a' => $a,
            'b' => $b,
            't' => $t,
            's' => $s,
            'allSort'=>$this->returnSort('all'),
        ));
        $view->setTemplate('index/shop/index');
        return $this->setMenu($view,'j');
    }
    /**
     * 精品商城详情页
     */
    public function detailAction(){
        $id = (int)$this->params()->fromRoute('id',0);

        $add_id = '';
        if(isset($_SESSION['index_id'])){
            $uid = $_SESSION['index_id'];
            $contact = $this->getContactsTable()->getOne(array('user_id'=>$uid,'type'=>1,'delete'=>0));
            $add_id = !empty($contact)?$contact['id']:'';
        }

        $where = array(
            'type'=>2,
            'status'=>0,
        );
        $GoodsCategory = $this->getGoodsCategoryTable()->fetchAll($where);
        
        $GoodsDetail = $this->getGoodsTable()->getOne(array('id'=>$id,'delete'=>0,'status'=>1));
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
        //$ads_14 = $this->getImageTable()->getAdsImages(14);
        $topSix = $this->getGoodsTable()->fetchAll(array('delete'=>0,'type'=>4,'status'=>1),array('sale_number'=>'desc'),6);
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
            "GoodsCategory"=>$GoodsCategory,
            "GoodsDetail"=>$GoodsDetail,
            "topSix"=>$topSix,
            "add_id"=>$add_id,
            //'ads_14'=>$ads_14,
        ));
        $view->setTemplate('index/shop/detail');
        return $this->setMenu($view,'j');
    }
    /**
     * 精品商城兑换
     */
    public function exchangeAction(){
        if(!isset($_SESSION['index_id'])){
            return $this->back('请先登录',array('c'=>'user','a'=>'login','f'=>1));
        }
        $number = (int)$_POST["number"];
        $where = array(
            "user_id" => $_SESSION['index_id'],
            "delete" => 0,
        );
        $order = array(
            'type'=>'desc',
            'timestamp'=>'desc',
        );
        
        $List = $this->getContactsTable()->fetchAll($where,$order);
        if(count($List)>0){
            $id = (int)$this->params()->fromRoute('id',0);
            
            $GoodsDetail = $this->getGoodsTable()->getOne(array('id'=>$id));
            if(!$GoodsDetail){
                $this->redirect()->toRoute('index', array(
                    'controller' => 'shop',
                    'action' => 'index',
                ));
            }
            
            if($GoodsDetail['number'] < $number){
                return $this->back('库存不足，无法兑换');
            }
            $UserInfo = $this->getUserTable()->getOne(array('id'=>$_SESSION['index_id']));
            if($UserInfo['golden_cat']<$GoodsDetail['golden_cat'] * $number){
                return $this->back('金猫不足，无法兑换');
            }
            if($UserInfo['silver_cat']<$GoodsDetail['silver_cat'] * $number){
                return $this->back('银猫不足，无法兑换');
            }
            
            $GoodsDetail['image'] = explode(',', $GoodsDetail['image']);
            foreach ($GoodsDetail['image'] as $v){
                if($v>0){
                    $image1 = $this->getImageTable()->getImages(array($v),1);
                    $GoodsDetail['image'] = $image1[$v]['path1'];
                    break;
                }
            }

            $view = new ViewModel(array(
                "ContactList"=>$List,
                "GoodsDetail"=>$GoodsDetail,
                "number" => $number
            ));
            $view->setTemplate('index/shop/exchange');
            return $this->setMenu($view,1);
        }else{
            return $this->back('请先添加收货地址',array('c'=>'user','a'=>'address','f'=>1));
        }
    }
    /**
     * 精品商城兑换处理
     */
    public function exchangeCheckAction(){
        $cid = (int)$_POST['cid'];//收货地址id
        $gid = (int)$_POST['gid'];//商品id
        $number = (int)$_POST['number'];
        if($cid && $gid){
            
        }else{
            $this->redirect()->toRoute('index', array(
                'controller' => 'shop',
                'action' => 'index',
            ));
        }
        
        $ContactDetail = $this->getContactsTable()->getOne(array('id'=>$cid));
        if(!$ContactDetail){
            return $this->back('收货地址有误，请确认后重新操作',array('c'=>'user','a'=>'address','f'=>1));
        }

        $GoodsDetail = $this->getGoodsTable()->getOne(array('id'=>$gid));
        if(!$GoodsDetail){
            return $this->back('商品不存在，请确认后重新操作',array('c'=>'shop','a'=>'index','f'=>1));
        }
        
        if($number>$GoodsDetail['number']){//库存不足
            $this->back('库存不足，无法兑换',array('f'=>1));
        }
        $UserInfo = $this->getUserTable()->getOne(array('id'=>$_SESSION['index_id']));
        if($UserInfo['golden_cat']>=$GoodsDetail['golden_cat'] * $number){
            
        }else{
            $this->back('金猫不足，无法兑换',array('f'=>1));
        }
        if($UserInfo['silver_cat']>=$GoodsDetail['silver_cat'] * $number){
            
        }else{
            $this->back('银猫不足，无法兑换',array('f'=>1));
        }

        // 生成财务记录并扣款
        $param = array(
            'transfer_way' => 3,
            'golden_cat' => $GoodsDetail['golden_cat'] * $number,
            'silver_cat' => $GoodsDetail['silver_cat'] * $number,
            'user_id' => $_SESSION['index_id'],
        );
        $financial = $this->getFinancialObject($param);
        
        if($financial!=0)
        {
            return $this->back('兑换失败，请重新操作',array('f'=>1));
        }
        $financialId = $this->getFinancialTable()->getOne(array('user_id'=>$_SESSION['index_id'],'transfer_way'=>3));
        $financialId = $financialId['id'];

        // 生成订单
        $data=array();
        $data['order_sn'] = $this->getAdminController()->generate();
        $data['total_golden_cat'] = $GoodsDetail['golden_cat'] * $number;
        $data['total_silver_cat'] = $GoodsDetail['silver_cat'] * $number;
        $data['price_golden_cat'] = $GoodsDetail['golden_cat'];
        $data['price_silver_cat'] = $GoodsDetail['silver_cat'];
        $data['number'] = $number;
        $data['status'] = 1;
        $data['goods_id'] = $gid;
        $data['user_id'] = $_SESSION['index_id'];
        $data['contacts_id'] = $cid;
        $data['timestamp'] = $this->getTime();
        $order_id = $this->getOrderTable()->insertData($data);
        
        $this->getGoodsTable()->updateKey($gid, 2, 'number', $number);
        $this->getGoodsTable()->updateKey($gid, 1, 'sale_number', $number);
        $this->getApiController()->saveOrderTracking(1, $order_id);
        
        $this->getFinancialTable()->updateData(array('order_id' => $order_id),array('id' => $financialId));
         
        return $this->back('兑换成功，即将为您跳转到消费记录',array('c'=>'user','a'=>'record'));
    }
    /**
     * 返回商城条件参数
     * @param unknown $type
     * @return multitype:number string unknown multitype:number string unknown NULL
     */
    protected function returnSort($type){
        $array = array();
        switch($type){
            case 1:
                $array['id']=$type;
                $array['between']=0;
                $array['to']=1000;
                $array['str']='0~1000';
                break;
            case 2:
                $array['id']=$type;
                $array['between']=1000;
                $array['to']=5000;
                $array['str']='1000~5000';
                break;
            case 3:
                $array['id']=$type;
                $array['between']=5000;
                $array['to']=10000;
                $array['str']='5000~10000';
                break;
            case 4:
                $array['id']=$type;
                $array['between']=10000;
                $array['to']=15000;
                $array['str']='10000~15000';
                break;
            case 5:
                $array['id']=$type;
                $array['between']=15000;
                $array['to']=20000;
                $array['str']='15000~20000';
                break;
            case 6:
                $array['id']=$type;
                $array['between']=20000;
                $array['to']=30000;
                $array['str']='20000~30000';
                break;
            case 7:
                $array['id']=$type;
                $array['between']=30000;
                $array['to']=50000;
                $array['str']='30000~50000';
                break;
            case 8:
                $array['id']=$type;
                $array['between']=50000;
                $array['to']='+';
                $array['str']='50000以上';
                break;
            case 'all':
                for($i=1;$i<=8;$i++){
                    $array[]=$this->returnSort($i);
                }
                break;
            default:
                $array['between']=0;
                $array['to']=0;
                $array['str']='全部';
        }
        return $array;
    }
}
