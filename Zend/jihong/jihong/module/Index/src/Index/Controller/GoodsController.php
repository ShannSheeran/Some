<?php
namespace Index\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\IsNull;
class GoodsController extends CommonController
{
    /**
     * 商品列表
     */
    public function shopListAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $keyword = $this->params()->fromRoute('keyword') ? $this->params()->fromRoute('keyword') : "";
        $keyword = isset($_GET['keyword'])&&$_GET['keyword'] ? $_GET['keyword'] : $keyword;
        
        //盆栽商品列表还是资材商品列表
        $type = $this->params()->fromRoute('type') ? $this->params()->fromRoute('type') : '';
        if(!$type)
        {
            if($_SESSION['user_type'] == 2)
            {
                $type = 2;
            }
            else
            {
                $type = 1;
            }
        }
        else 
        {
            if($_SESSION['user_type'] == 2)
            {
                $type = 2;
            }
        }
        
        $action = ($type == 2) ? 'purchaseEquip' : 'purchaseGoods';
        
        if(!in_array($type, array(1,2)))
        {
            die;
        }
        
        $page = (int)$this->params()->fromRoute('page',1);
        
        $big_category = $this->params()->fromRoute('bc') ? $this->params()->fromRoute('bc') : "0";
        $big_category = isset($_GET['bc'])&&$_GET['bc'] ? $_GET['bc'] : $big_category;
        $small_category = $this->params()->fromRoute('sc') ? $this->params()->fromRoute('sc') : "0";
        $small_category = isset($_GET['sc'])&&$_GET['sc'] ? $_GET['sc'] : $small_category;
        
        //排序
        $sort = $this->params()->fromRoute('sort') ? $this->params()->fromRoute('sort') : "1";
        $sort = isset($_GET['sort'])&&$_GET['sort'] ? $_GET['sort'] : $sort;
        
        $order = '';
        if($sort){
            switch ($sort)
            {
                case 1://默认
                    $order = array('sort esc');
                    break;
                case 2://销量排序
                    $order = array('sale_number desc');
                    break;
                case 3://价格排序
                    $order = array('min_cash esc');
                    break;
                case 4://新品排序
                    $order = array('timestamp desc');
                    break;
                default:
                    $order = "";
                    break;
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        
        $small_category_info = '';
        $big_category_info = '';
        $small_category_ids_arr = array();
        if($small_category)
        {
            $small_category_info = $this->getGoodsCategoryTable()->getOne(array('id'=>$small_category,'delete'=>'0','status'=>'1'),array('name','parent_id'));
            $big_category_info = $this->getGoodsCategoryTable()->getOne(array('id'=>$small_category_info->parent_id,'delete'=>'0','status'=>'1'),array('name'));

            $where = new Where();
            $where->equalTo('delete', '0');
            $where->equalTo('status', '3');
            $where->equalTo('type', $type);
            $where->equalTo('category_id', $small_category);
        }
        elseif(!$small_category && $big_category)
        {
            $big_category_info = $this->getGoodsCategoryTable()->getOne(array('id'=>$big_category,'delete'=>'0','status'=>'1'),array('name'));
            $where = new Where();
            $where->equalTo('delete', '0');
            $where->equalTo('status', '3');
            $where->equalTo('type', $type);
//             $where->equalTo('category_id', $big_category);
            
            $small_category_arr = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','status'=>'1','type'=>$type,'parent_id'=>$big_category),null,null,array('id'));
//             $this->dump($small_category_arr);exit;
            if(!empty($small_category_arr))
            {
                foreach ($small_category_arr as $k=>$v)
                {
                    $small_category_ids_arr[$k] = $v->id;
                }
            }
            $small_category_ids_arr[] =  $big_category;
            $where->in('category_id',$small_category_ids_arr);
        }
        else
        {
            $where = new Where();
            $where->equalTo('delete', '0');
            $where->equalTo('status', '3');
            $where->equalTo('type', $type);
        }
//         $this->dump($small_category_ids_arr);
        //商品列表
        $page_size = 16;
        $goods_list = $this->getGoodsTable()->getAll($where,null,$order,true,$page,$page_size,$like);
//         $this->dump($goods_list['paginator']);exit;
        $goods = array();
        if(!empty($goods_list['list']))
        {
            foreach ($goods_list['list'] as $k=>$v)
            {
                if($v->image)
                {
                    $image_arr = explode(',', $v->image);
                    $image_info = $this->getImageTable()->getOne(array('id'=>$image_arr[0]));
//                     $this->dump($image_info);exit;
                    $v->image_path = ROOT_PATH . UPLOAD_PATH.'thumb/190X190X3/'.$image_info->path.$image_info->filename;
                }
                $goods[$k] = $v;
            }
        }
        
        $cartInfo = $this->getCartTable()->fetchAll(array('user_id'=>$_SESSION['index_user_id']),array('id','number'));
        $carNumber =0;
        foreach ($cartInfo as $v)
        {
            $carNumber +=$v['number'];
        }
//         var_dump($cartInfo);exit;
        
        //新品上市4个
        $goods_new = $this->getGoodsTable()->fetchAll(array('delete'=>'0','status'=>'3','type'=>$type),array('timestamp desc'),4);

        $goods_new_list = array();
        if($goods_new)
        {
            foreach ($goods_new as $k=>$v)
            {
                if($v->image)
                {
                    $image_arr = explode(',', $v->image);
                    $image_info = $this->getImageTable()->getOne(array('id'=>$image_arr[0]));
                    //                     $this->dump($image_info);exit;
                    $v->image_path = ROOT_PATH . UPLOAD_PATH.'thumb/183X183X3/'.$image_info->path.$image_info->filename;
                }
                $goods_new_list[$k] = $v;
            }
        }
        //$this->dump($goods_new_list);exit;
//         $this->dump($goods_new);
        $firstCategory = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','parent_id'=>'0','status'=>'1','type'=>$type));
//         $this->dump($firstCategory);exit;
        $where_cate = new Where();
        $where_cate->equalTo('delete', '0');
        $where_cate->equalTo('status', '1');
        $where_cate->equalTo('type', $type);
        if($big_category)
        {
            $where_cate->EqualTo('parent_id', $big_category);
        }
        else 
        {
            $where_cate->notEqualTo('parent_id', '0');
        }
        
        $secondCategory = '';
        $bc = $this->params()->fromRoute('bc');
        $sc = $this->params()->fromRoute('sc');
//         var_dump($sc);exit;
        if(($bc !== "0") || $small_category)
        {
            $secondCategory = $this->getGoodsCategoryTable()->fetchAll($where_cate);
        }
        $view = new ViewModel(array(
            'paginator' => $goods_list['paginator'],
            'condition' => array(
                'controller' => 'goods',
                'action' => 'shopList',
                'page'   => $page,
                'keyword' => $keyword,
                'bc' => $big_category,
                'sc' => $small_category,
                'sort' => $sort,
                'type' => $type,
                'where' => array(
                    'search'=>$keyword
                )
            ),
            'goods_new' => $goods_new_list,
            'goods_list' => $goods,
            'firstCategory' => $firstCategory,
            'secondCategory' => $secondCategory,
            'big_category' => $big_category,
            'small_category' => $small_category,
            'big_category_info' => $big_category_info,
            'small_category_info' => $small_category_info,
            'sort' => $sort,
            'page' => $page,
            'bc' => $big_category,
            'sc' => $small_category,
            'type'=>$type,
            'search'=>$keyword,
            'page_count' => ceil($goods_list['total']/$page_size),
            'cart_type' => $action,
            'carNumber' => $carNumber
        ));
        $view->setTemplate('index/goods/shop_list');
        return $this->setMenu($view, 3 , $action );
    }
    
    /**
     * 商品详情
     */
    public function detailAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $id = $this->params()->fromRoute('id');
        
        /* if($_SESSION['user_type'] == 1)
        {
            $type = array(1,2);
        }
        elseif($_SESSION['user_type'] == 2)
        {
            $type = 2;
        }
        else 
        {
            $this->showMessage('非法操作');
        } */
        
        $goods_info = $this->getGoodsTable()->getOne(array('id' => $id , 'status' => 3 ,'delete'=>DELETE_FALSE));
        if(empty($goods_info))
        {
            $this->showMessage("商品不存在或已下架");
        }
        
        if($goods_info->type == 1 && $_SESSION['user_type'] == 2)
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'goods' , 'action' => 'hotSellerDetail' , 'id' => $id));
        }
        
        $where = new Where();
        $where->equalTo('category_id', $goods_info->category_id);
        $where->equalTo('status', 3);
        $where->equalTo('delete', DELETE_FALSE);
        $where->notEqualTo('id', $goods_info->id);
        $goods_list = $this->getGoodsTable()->fetchAll($where , array('sale_number desc') , 4);
        $image = array();
        if($goods_list || count($goods_list) > 3)
        {
            foreach ($goods_list as $value)
            {
                if($value->image)
                {
                    $image_id_array = explode(',', trim($value->image , ','));
                    $image_ids[] = $image_id_array[0];
                }
            }
            !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
        }
        else
        {
            $where = new Where();
            $where->equalTo('status', 3);
            $where->equalTo('delete', DELETE_FALSE);
            $where->notEqualTo('id', $goods_info->id);
            if($_SESSION['user_type'] == 2)
            {
                $where->equalTo('type', 2);
            }
            $goods_list = $this->getGoodsTable()->fetchAll($where , array('sale_number desc') , 4);
            
            if($goods_list)
            {
                foreach ($goods_list as $value)
                {
                    if($value->image)
                    {
                        $image_id_array = explode(',', trim($value->image , ','));
                        $image_ids[] = $image_id_array[0];
                    }
                }
                !empty($image_ids) && $image = $this->getImageTable()->getImages($image_ids);
            }
        }
        
        $cartInfo = $this->getCartTable()->fetchAll(array('user_id'=>$_SESSION['index_user_id']),array('id','number'));
        $carNumber =0;
        foreach ($cartInfo as $v)
        {
            $carNumber +=$v['number'];
        }
        
        $action = ($goods_info->type == 2) ? 'purchaseEquip' : 'purchaseGoods';
//         $this->dump($image);exit;
        $view = new ViewModel(array(
            'id'=>$id,
            'goods_list' => $goods_list,
            'image' => $image,
            'action' => $action,
            'carNumber' => $carNumber
        ));
        $view->setTemplate('index/goods/shop_detail');
        return $this->setMenu($view, 3 ,$action);
    }
    
    /**
     * 热销商品列表
     */
    public function hotSellerAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] == 1)
        {
            die;
        }
        $page = (int)$this->params()->fromRoute('page',1);
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $this->params()->fromRoute("keyword","");
        $bc = isset($_GET['bc']) ? $_GET['bc'] : 0;
        $sc = isset($_GET['sc']) ? $_GET['sc'] : 0;
        
        $where = new Where();
        $where->equalTo('delete', 0);
//         $where->equalTo('status', 3);//上架
        $where->equalTo('type', 1);//盆栽
        $where->equalTo('goods_statistics_type', 2);//一周内统计
        $where->greaterThanOrEqualTo('number', 40);//一周内销量大于等于40才是热销商品
        $small_category_ids_arr = array();
        if($sc)
        {
            $where->equalTo('category_id' , $sc);
        }
        elseif($bc)
        {
//             $where->equalTo('category_id' , $bc);
            $small_category_arr = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','status'=>'1','type'=>1,'parent_id'=>$bc),null,null,array('id'));
            //             $this->dump($small_category_arr);exit;
            if(!empty($small_category_arr))
            {
                foreach ($small_category_arr as $k=>$v)
                {
                    $small_category_ids_arr[$k] = $v->id;
                }
                $small_category_ids_arr[] =  $bc;
                $where->in('category_id',$small_category_ids_arr);
            }
        }
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
        }
        $goods_list = $this->getViewGoodsStatisticsTable()->getAll($where,null,array('number desc'),true,$page,10,$like);
//         $this->dump($goods_list['list']);exit;
        if(!empty($goods_list['list']))
        {
            foreach ($goods_list['list'] as $k => &$v)
            {
                $specs = $this->getGoodsSpecificationTable()->fetchAll(array('delete'=>0,'goods_id'=>$v->id),null,null,array('pack_number'));
                //获取商品规格表装箱数范围的数据
//                 $this->dump($specs);exit;
                $specs_pack_arr = array();
                if($specs){
                    foreach ($specs as $n => $m)
                    {
                        $specs_pack_arr[$n] = $m->pack_number;
                    }
                    $specs_pack_max =  $specs_pack_arr[array_search(max($specs_pack_arr), $specs_pack_arr)];
                    $specs_pack_min =  $specs_pack_arr[array_search(min($specs_pack_arr), $specs_pack_arr)];
                    if($specs_pack_max == $specs_pack_min)
                    {
                        $v->pack_number_range = $specs_pack_min;
                    }
                    else
                    {
                        $v->pack_number_range = $specs_pack_min."-".$specs_pack_max;
                    }
                }
            }
        }
        
        $firstCategory = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','parent_id'=>'0','status'=>'1','type'=>1),null,null,array('id','name'));
//         $this->dump($firstCategory);exit;
        
//         $where = new Where();
//         $where->equalTo('delete', '0');
//         $where->equalTo('status', '1');
//         $where->equalTo('type', 1);
//         $where->notEqualTo('parent_id', '0');
//         $secondCategory = $this->getGoodsCategoryTable()->fetchAll($where,null,null,array('id','name'));
//         $this->dump($secondCategory);exit;
        $view = new ViewModel(
            array(
                'paginator' => $goods_list['paginator'],
                'goods' => $goods_list['list'],
                'firstCategory' => $firstCategory,
//                 'secondCategory' => $secondCategory,
                'bc' => $bc,
                'sc' => $sc,
                'keyword' => $keyword,
                'condition' => array(
                    'controller' => 'goods',
                    'action' => 'hotSeller',
                    'page'   => $page,
                    'bc' => $bc,
                    'sc' => $sc,
                    'where' => array(
                        'search'=>$keyword
                    )
                ),
            ));
        $view->setTemplate('index/goods/hot_seller');
        return $this->setMenu($view, 3 , 'hotSell');
    }
    
    /**
     * 热销商品详情
     */
    public function hotSellerDetailAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] == 1)
        {
            die;
        }
        $id = $this->params()->fromRoute('id');
        $view = new ViewModel(
            array(
                'id'=>$id
            ));
        $view->setTemplate('index/goods/hot_seller_detail');
        return $this->setMenu($view, 3 , 'hotSell');
    }
    
    /**
     * 商品分类联动---盆栽
     */
    public function goodsCategoryLinkageAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        $param = $_POST;
        $categorys = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','status'=>'1','parent_id'=>$param['pid'],'type'=>$param['type']));
        if($categorys){
            die(json_encode(array('code'=>'1','categorys'=>$categorys)));
        }else{
            die(json_encode(array('code'=>'0')));
        }
    
    }
    
    /**
     * 发布商品
     */
    public function releaseGoodsAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] == 1)
        {
            die;
        }
        $id = $this->params()->fromRoute('id');
        $gid = $this->params()->fromRoute('cid');//发布成功返回的商品id
        $release_success = $this->params()->fromRoute('alert');
        $firstCategory = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','parent_id'=>'0','status'=>'1','type'=>1),null,null,array('id','name'));
        $good = '';
        $images = '';
        $spec = '';
        $fc = '';
        $sc = '';
        if($id)
        {
//             $good = $this->getGoodsTable()->getOne(array('delete'=>'0','status'=>3,'id'=>$id));
            $good = $this->getGoodsTable()->getOne(array('delete'=>'0','id'=>$id));
            if(empty($good))
            {
                $this->showMessage("不存该商品");
            }
            if($good->image)
            {
                $images_ids_arr = explode(',', $good->image);
                $images = $this->getImageTable()->getImages($images_ids_arr);
            }
//             $this->dump($good->category_id);exit;
            $cate = $this->getGoodsCategoryTable()->getOne(array('delete'=>0,'status'=>1,'id'=>$good->category_id));
//             $this->dump($cate);exit;
            if($cate->parent_id)//二级分类
            {   
                $fc = $this->getGoodsCategoryTable()->getOne(array('delete'=>0,'status'=>1,'id'=>$cate->parent_id)); 
                $sc = $cate;
                unset($cate);  
            }
            else //只有一级分类
            {
                $fc = $cate;
                unset($cate);
            }
            $spec = $this->getGoodsSpecificationTable()->getOne(array('delete'=>0,'goods_id'=>$id));
        }
//         $this->dump($fc);exit;
        $view = new ViewModel(array(
            'release_success' => $release_success,
            'id' => $id,
            'gid' => $gid,
            'firstCategory' => $firstCategory,
            'good' => $good,
            'spec' => $spec,
            'images' => $images,
            'fc' => $fc,
            'sc' => $sc,
        ));
        $view->setTemplate('index/goods/release_goods');
        return $this->setMenu($view, 1 , 'releaseGoods');
        
    }
    
    /**
     * 提交发布商品
     */
    public function submitGoodsAction()
    {
        if(!$this->login())
        {
            return $this->redirect()->toRoute('index' , array('controller' => 'login' , 'action' => 'index'));
        }
        if($_SESSION['user_type'] == 1)
        {
            die;
        }
        $data = array();
        if($_POST['submit'])
        {
            foreach ($_POST as $k=>$v)
            {
                $data[$k] = $v;
            }
        }

//         $this->dump($data);exit;
        $id = $data['id'];
        $specs = $data['spec'];//商品规格
        unset($data['spec'],$data['submit'],$data['id']);
        if(empty($data['image_ids']) || count($data['image_ids']) < 3)
        {
            $this->showMessage("请上传3~10张图片");
        }
        if(empty($data['fc']) || empty($data['sc']))
        {
            $this->showMessage("请选择商品分类");
        }
//         if($data['sc'])
//         {
//             $data['category_id'] = $data['sc'];
//         }
//         else 
//         {
//             $data['category_id'] = $data['fc'];
//         }
        $data['category_id'] = $data['sc'];
        unset($data['fc'],$data['sc']);
        $data['image'] = implode(',', $data['image_ids']);
        unset($data['image_ids']);
        if($data['salse_type'] != 1)
        {
            unset($data['deliveryDate']);
        }
        $data['number'] = $specs['number'];
        $data['max_cash'] = $data['min_cash'] = $specs['cash'];
        $data['timestamp'] = $this->getTime();
        $data['goods_sn'] = $this->generate();
        $data['user_id'] = $_SESSION['index_user_id'];
        $res_id = $this->getGoodsTable()->insertData($data);
        if(!$res_id)
        {
            $this->showMessage("发布商品失败");
        }
        $specs['timestamp'] = $this->getTime();
        $specs['goods_id'] = $res_id;
        $res = $this->getGoodsSpecificationTable()->insertData($specs);
        if($res)
        {
            $value['status'] = 1;
            $value['user_id'] = $_SESSION['index_user_id'];
            $value['goods_id'] = $res_id;
            $value['timestamp'] = $this->getTime();
            $result = $this->getGoodsTrackingTable()->indateKey($value, 2, 1, $data['goods_sn']);
            if($id)
            {
                return $this->redirect()->toRoute("index",array('controller'=>'goods','action'=>'releaseGoods','id'=>$id,'cid'=>$res_id,'alert'=>"1"));
            }
            else 
            {
                return $this->redirect()->toRoute("index",array('controller'=>'goods','action'=>'releaseGoods','cid'=>$res_id,'alert'=>"1"));
            }
        }
        else 
        {
            $this->showMessage("发布商品失败");
        }
        die;
    }
    
}
