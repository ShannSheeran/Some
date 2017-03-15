<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;

use Zend\Db\Sql\Where;
use Api\Controller\CommonController as ApiController;
use Api\Controller\Item\PushTemplateItem;

class GoodsController extends CommonController
{
    /**
     * goodsList
     * 
     */
    public function indexAction()
    {
        $this->checkLogin('goods_list'); 
        $page = $this->params()->fromRoute('page');
        
        $keyword = $this->params()->fromRoute('keyword') ? $this->params()->fromRoute('keyword') : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $keyword;
        
        $status = $this->params()->fromRoute('status') ? $this->params()->fromRoute('status') : '';
        $status = isset($_GET['status']) ? $_GET['status'] : $status;
        
        $type = $this->params()->fromRoute('type') ? $this->params()->fromRoute('type') : '';
        $type = isset($_GET['type']) ? $_GET['type'] : $type;
        
        $firstCategory = $this->params()->fromRoute('firstCategory') ? $this->params()->fromRoute('firstCategory') : '';
        $firstCategory = $firstCategoryId = isset($_GET['firstCategory']) ? $_GET['firstCategory'] : $firstCategory;
        
        $secondCategory  = $this->params()->fromRoute('secondCategory') ? $this->params()->fromRoute('secondCategory') : '';
        $secondCategory = $secondCategoryId = isset($_GET['secondCategory']) ? $_GET['secondCategory'] : $secondCategory;
        
        if($secondCategory)
        {
            $category = $secondCategory;
        }
        else 
        {
            if($firstCategory)
            {
                $category = $firstCategory;
            }
        }
        
        
        if(!$status)
        {//没有区分上下架商品状态，查询上下架所有商品
            $where = new Where();
            $where->equalTo('status', 3);
            $where->equalTo('delete', DELETE_FALSE);
            if(isset($category)&&$category)
            {
                $where->equalTo('category_id', $category);
            }
            if($type)
            {
                $where->equalTo('type', $type);
            }
            
            $where_sub = new Where();
            $where_sub->equalTo('status', 4);
            $where_sub->equalTo('delete', DELETE_FALSE);
            if(isset($category)&&$category)
            {
                $where_sub->equalTo('category_id', $category);
            }
            if($type)
            {
                $where_sub->equalTo('type', $type);
            }
            $where->orPredicate($where_sub);
        }else{//区分
            $where = new Where();
            $where->equalTo('delete', DELETE_FALSE);
            $where->equalTo('status', $status);
            if(isset($category)&&$category)
            {
                $where->equalTo('category_id', $category);
            }
            if($type)
            {
                $where->equalTo('type', $type);
            }
        }
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
            $like['code'] = $keyword;
        }
        $goods_list = $this->getGoodsTable()->getAll($where ,null, array('sort' => 'ASC' , 'id' => 'DESC'), true, $page, 10 , $like);
        
//         $this->dump($goods_list['list']);exit;
        $goods = $goods_list['list'];
        if(!empty($goods))
        {
            $goods_new = '';
            foreach ($goods as $k=>$v)
            {
                $images = explode(',', $v['image']);
                $oneImage = $this->getImageTable()->getOne(array('id'=>$images[0]));//取得商品的图片之一
//                 $this->dump($oneImage);exit;
                $v['oneImage_path'] = $oneImage['path'].$oneImage['filename'];
                
                $category = $this->getGoodsCategoryTable()->getOne(array('delete'=>'0','id'=>$v['category_id']));
                if($category && $category['parent_id'] != '0')//有两级分类
                {
                    $secondCategory = $this->getGoodsCategoryTable()->getOne(array('delete'=>'0','id'=>$category['parent_id']));
                    if($secondCategory)
                    {
                        $v['category'] = $secondCategory->name ."-".$category->name;
                    }
                    else 
                    {
                        $v['category'] = $category->name;
                    }
                }
                elseif($category && $category['parent_id'] == '0') //只有一级分类
                {
                    $v['category'] = $category->name;
                }
                else
                {
                    $v['category'] = '';
                }
                $goods_new[$k] = $v;
            }
        }
        
//         $this->dump($goods_new);exit;
        $view=new ViewModel(array(
            'goods' => $goods_new,
            'type'=>$type,
            'firstCategory' => $firstCategory,
            'firstCategoryId' => $firstCategoryId,
            'secondCategory' => $secondCategory,
            'secondCategoryId' => $secondCategoryId,
            'keyword' => $keyword,
            'status' => $status,
            'categoryType' => $this->categoryType(),
            'goodsStatus' => $this->goodsStatus(),
            'paginator' => $goods_list['paginator'],
            'condition' => array(
                 'action' => $this->action,
                 'page'   => $page,
                 'keyword' => $keyword,
                 'status' => $status,
                 'type'=>$type,
                 'where' => array(
                     'firstCategoryId' => $firstCategoryId,
                     'secondCategoryId' => $secondCategoryId,
                 ),
             ),
        ));
        $view->setTemplate('admin/goods/index');
        return $this->setMenu($view,1);
    }
    
    /**
     * add or edit goods information--(展示)
     * 
     */
    public function addGoodsAction()
    {
        $this->checkLogin('goods_detail');
        $type = $this->params()->fromRoute('type');
//         //商品单位查询
        $goods_units = $this->getGoodsUnitTable()->fetchAll(array('delete'=>'0'));
        //商品分类查询 
        $goods_categorys = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','status'=>'1' , 'type' =>$type));
//         $this->dump($goods_units);exit;

        $user_list = $this->getUserTable()->getAll(array('type' => 2 , 'register_status' => 3 , 'delete' => DELETE_FALSE), '' ,null ,true, 1, 1 );
        
        $id = $this->params()->fromRoute('id');
        $images_arr = array();
        $user = array();
        $good = array();
        $specs = array();
        $category_info = array();
        if($id)//编辑商品信息
        {
            $good = $this->getGoodsTable()->getOne(array('delete'=>'0','id'=>$id));
            $user = $this->getUserTable()->getOne(array('delete'=>'0','id'=>$good['user_id']));
            if($good['image'])
            {
                $images_ids_arr = explode(',', $good['image']);
                $images_arr = $this->getImageTable()->getImages($images_ids_arr);
            }
            if($good['category_id'])
            {
                $category_info = $this->getGoodsCategoryTable()->getOne(array('id' => $good['category_id']));
            }
            //查询规格
            $specs = $this->getGoodsSpecificationTable()->fetchAll(array('delete'=>'0','goods_id'=>$id));
        }
        
        $view=new ViewModel(array(
           'type' => $type,
           'specs' => $specs,
           'good' => $good,
           'images' => $images_arr,
           'user' => $user,
           'goods_units' => $goods_units,
           'goods_categorys' => $goods_categorys,
           'category_info' => $category_info,
           'onSaleType' => $this->onSaleType(),
           'referrer_type' => $this->referrerType(),
            'paginator' => $user_list['paginator'],
            'user_list' => $user_list['list'],
            'condition' => array(
                'action' => 'addGoods',
                'page'   => 1,
                'where' => array(),
            ),
        ));
        $view->setTemplate('admin/goods/operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 删除商品
     */
    public function deleteGoodsAction()
    {
        $this->checkLogin('goods_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $this->getGoodsTable()->deleteData($id);
        }
        else
        {
            $this->showMessage("参数错误");
        }
        $this->redirect()->toRoute('admin-goods',array('action' => 'index'));
    }
    
    /**
     * 批量/单个上下架-ajax
     */
    public function changeGoodsStatusUpdownAction()
    {
        $this->checkLogin('goods_delete');
        $param = $_POST;
        $op = $param['op'];
        $ids_arr = $param['ids_arr'];

        if($op == 'down')//下架
        {
            
            foreach ($ids_arr as $value)
            {
                $exists = $this->getGoodsTrackingTable()->getOne(array('goods_id' => $value , 'status' => 4));
                $goods_info = $this->getGoodsTable()->getOne(array('id' => $value));
                
                if($exists)
                {
                    $this->getGoodsTrackingTable()->updateData(array('timestamp' => $this->getTime()), array('goods_id' => $value , 'status' => 4));
                    
                    $template = new PushTemplateItem();
                    $template->content = '您的商品状态更新请注意查看,您的商品'.$goods_info->goods_sn.'已下架';
                    $template->title = '商品状态更新';
                    
                    $push = new ApiController();
                    $push->pushForController($goods_info->user_id, 1, null, null, $template);
                }
                else 
                {
                    $set['status'] = 4;
                    $set['user_id'] = $goods_info->user_id;
                    $set['goods_id'] = $value;
                    $set['timestamp'] = $this->getTime();
                    
                    $this->getGoodsTrackingTable()->indateKey($set, 2, 4, $goods_info->goods_sn);
                }
            }
            
            $res = $this->getGoodsTable()->updateData(array('status'=>4), array('id'=>$ids_arr));
        }
        elseif($op == 'up') //上架
        {
            foreach ($ids_arr as $value)
            {
                $goods_info = $this->getGoodsTable()->getOne(array('id' => $value));
                
                $template = new PushTemplateItem();
                $template->content = '您的商品状态更新请注意查看,您的商品'.$goods_info->goods_sn.'已重新上架';
                $template->title = '商品状态更新';
                
                $push = new ApiController();
                $push->pushForController($goods_info->user_id, 1, null, null, $template);
            }
            
            $this->getGoodsTrackingTable()->updateData(array('timestamp' => $this->getTime()), array('goods_id' => $ids_arr , 'status' => 3));
            
            $res = $this->getGoodsTable()->updateData(array('status'=>3), array('id'=>$ids_arr));
        }
        if($res)
        {
            die(json_encode(array('code'=>'1')));
        }
        else
        {
            die(json_encode(array('code'=>'0')));
        }
    }
    
    /**
     * add or edit goods information--(操作)
     */
    public function goodsOperateAction()
    {
        $this->checkLogin('goods_add');
        $data = array();
        if(isset($_POST['submit']) || isset($_POST['saveAndRelease']))
        {
            foreach ($_POST as $k=>$v)
            {
                $data[$k] = $v;
            }
        }
        
        if(count($data['image_ids']) <3 || count($data['image_ids']) > 10)
        {
            $this->showMessage("请上传3~10张图片");
        }
        
        $specs = $data['spec'];//商品规格
        $id = $data['id'];//商品id
        isset($data['image_ids']) && $image_ids = $data['image_ids'];
        
        unset($data['submit']);
        unset($data['saveAndRelease']);
        unset($data['spec']);
        unset($data['image_ids']);
        
        //获取插入商品表cash（单价范围）的数据
        $specs_cash_arr = array();
        if($specs)
        {
            $total_number = 0;

            foreach ($specs as $n => $m)
            {
                $specs_cash_arr[$n] = $m['cash'];
                $total_number += $m['number'];
            }

            $specs_cash_max =  $specs_cash_arr[array_search(max($specs_cash_arr), $specs_cash_arr)];
            $specs_cash_min =  $specs_cash_arr[array_search(min($specs_cash_arr), $specs_cash_arr)];

            if($specs_cash_max == $specs_cash_min)
            {
                $data['min_cash'] = number_format($specs_cash_max,1,'.','');
                $data['max_cash'] = number_format($specs_cash_max,1,'.','');
            }
            else
            {
//                 $data['cash'] = number_format($specs_cash_min,1)."-".number_format($specs_cash_max,1);
                $data['min_cash'] = number_format($specs_cash_min,1,'.','');
                $data['max_cash'] = number_format($specs_cash_max,1,'.','');
            }
        }
        else 
        {
            $this->showMessage("请填写商品规格");
        }
        
        
        
        if(!$id)//添加商品
        {
            if($data['salse_type'] != '1')//上架方式不是预售，去掉预售时间
            {
                unset($data['delivery_date']);
            }
            if($data['referrer_type'] != '2')//推荐方式不是促销商品，去掉商品原价
            {
                unset($data['original_price']);
            }
            if(isset($data['secondCategory']) && $data['secondCategory'])
            {
                $data['category_id'] = $data['secondCategory'];
            }
            else 
            {
                echo '<script>alert("商品不能添加到顶级分类中");history.back();</script>';
                die;
            }
            unset($data['firstCategory'],$data['secondCategory']);
            if(!empty($image_ids))
            {
                $data['image'] = implode($image_ids, ',');
            }
            $data['number'] = $total_number;
            $data['timestamp'] = date("Y-m-d H:i:s");
            $data['goods_sn'] = $this->generate();//生成商品编号
            $goods_id = $this->getGoodsTable()->insertData($data);//插入商品表
            
            if($data['user_id'])
            {
                $value['status'] = 1;
                $value['user_id'] = $data['user_id'];
                $value['goods_id'] = $goods_id;
                $value['timestamp'] = $this->getTime();
                
                $this->getGoodsTrackingTable()->indateKey($value, 2, 1, $data['goods_sn']);
            }
        }
        else//更新商品信息
        {

            $goods_info = $this->getGoodsTable()->getOne(array('id'=>$id));
            if($goods_info->status == 2)
            {
                if ($goods_info->user_id){
                    $value['status'] = 3;
                    $value['user_id'] = $goods_info->user_id;
                    $value['goods_id'] = $goods_info->id;
                    $value['timestamp'] = $this->getTime();
                    $this->getGoodsTrackingTable()->indateKey($value, 2, 3, $goods_info->goods_sn);
                }
                $data['status'] = 3;
            }
            
            unset($data['id']);
            if($data['salse_type'] != '1')//上架方式不是预售，去掉预售时间
            {
                unset($data['delivery_date']);
            }
            if($data['referrer_type'] != '2')//推荐方式不是促销商品，去掉商品原价
            {
                unset($data['original_price']);
            }
            if($data['secondCategory'])
            {
                $data['category_id'] = $data['secondCategory'];
            }
            else
            {
                echo '<script>alert("商品不能添加到顶级分类中");history.back();</script>';
                die;
            }
            unset($data['firstCategory'],$data['secondCategory']);
            $data['number'] = $total_number;
            if(!empty($image_ids))
            {
                $data['image'] = implode($image_ids, ',');
            }
            $this->getGoodsTable()->updateData($data, array('id'=>$id));
        }
        
        if(!empty($specs))//插入规格表
        {
            foreach ($specs as $v)
            {
                $spec_id = $v['spec_id'];
                if($spec_id)//更新规格
                {
                    unset($v['spec_id']);
                    $v['timestamp_update'] = date("Y-m-d H:i:s");

                    $this->getGoodsSpecificationTable()->updateData($v , array('id'=>$spec_id));
                }
                else //新增规格
                {
                    unset($v['spec_id']);
                    $v['timestamp'] = date("Y-m-d H:i:s");
                    if($id){//编辑时新增规格
                        $v['goods_id'] = $id;
                    }
                    else //添加商品时新增规格
                    {
                        $v['goods_id'] = $goods_id;
                    }
                    $this->getGoodsSpecificationTable()->insertData($v);
                }
            }
        }
        
        if($goods_id && isset($_POST['submit']))
        {
            $this->redirect()->toRoute('admin-goods',array('action'=>'shelvesManage'));
        }
        elseif($goods_id && isset($_POST['saveAndRelease']))
        {
            $this->redirect()->toRoute('admin-goods',array('action'=>'addGoods' , 'type'=> $data['type']));
        }
        else 
        {
            $this->redirect()->toRoute('admin-goods',array('action'=>'index'));
        }
        
    }
    
    /**
     * 删除商品规格-ajax
     */
    public function delGoodsSpecAction()
    {
        $this->checkLogin();
        $param = $_POST;
        $res = $this->getGoodsSpecificationTable()->deleteData($param['id']);
        if($res)
        {
            die(json_encode(array('code'=>'1')));
        }
        else
        {
            die(json_encode(array('code'=>'0')));
        }
    }
    
    /**
     * 商品编辑-添加的商品分类联动
     */
    public function goodsCategoryLinkageAction()
    {
        $this->checkLogin();
        $param = $_POST;
        $categorys = $this->getGoodsCategoryTable()->fetchAll(array('delete'=>'0','status'=>'1','parent_id'=>$param['pid']));
        if($categorys){
           die(json_encode(array('code'=>'1','categorys'=>$categorys)));
        }else{
           die(json_encode(array('code'=>'0')));
        }
        
    }
    
    /**
     * 选择供应商
     */
    public function selectUserAction()
    {
        $this->checkLogin();
        $param = $_POST;
        $keyword = $param['keyword'];
        $users = $this->getUserTable()->getAll(array('type' => 2, 'delete'=>'0','status'=>'1' , 'register_status' => 3),array('id','company_name'),null,false,null,null,array('company_name'=>$keyword));
        if(!empty($users['list']))
        {
            die(json_encode(array('code'=>'1','users'=>$users['list'])));
        }
        else
        {
            die(json_encode(array('code'=>'0')));
        }
    }
    
    /**
     * 商品分类列表
     * @return \Zend\View\Model\ViewModel
     */
    public function categoryAction()
    {
        $this->checkLogin('goods_category');
        $page = $this->params()->fromRoute('page');
        $type = $this->params()->fromRoute('type');
        $id = $this->params()->fromRoute('id' , 0);
        $keyword = $this->params()->fromRoute('keyword') ? $this->params()->fromRoute('keyword') : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : $keyword;
        
        $status = $this->params()->fromRoute('status') ? $this->params()->fromRoute('status') : '';
        $status = isset($_GET['status']) ? $_GET['status'] : $status;
        
        $params_arr = array('delete' => DELETE_FALSE);
        $params_arr['parent_id'] = $id;
        if($type)
        {
            $params_arr['type'] = $type;
        }
        if($status == '2' || $status == '1')
        {
            $params_arr['status'] = $status;
        }
        
        $goods_category_list = $this->getGoodsCategoryTable()->getAll($params_arr ,null, array('sort' => 'ASC' , 'id' => 'DESC'), true, $page, 10 , array("name"=>$keyword));
        $goods_category_list_new = '';
        if($goods_category_list['list'])
        {
            foreach ($goods_category_list['list'] as $k=>$v)
            {
                if($v['icon']){
                    $image = $this->getImageTable()->getOne(array('id'=>$v['icon']));
                    $v['icon_path'] = $image['path'].$image['filename'];
                }
                else
                {
                    $v['icon_path'] = '';
                }
                $goods_category_list_new[$k] = $v;
            }
        }
        $view=new ViewModel(array(
            'id' => $id,
            'goods_category' => $goods_category_list_new,
            'paginator' => $goods_category_list['paginator'],
            'categoryType' => $this->categoryType(),
            'status' => $status,
            'type' => $type,
            'keyword' => $keyword,
            'condition' => array(
                'action' => 'category',
                'page'   => $page,
                'status' => $status,
                'keyword' => $keyword,
                'type' => $type,
                'id' => $id,
                'where' => array(),
            ),
        ));
        $view->setTemplate('admin/goods/category');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加或编辑商品分类--提交操作
     * @return \Zend\View\Model\ViewModel
     */
    public function categoryOperateAction()
    {
        $this->checkLogin('goods_category_add');
        $data = array();
        if($_POST['submit'])
        {
            foreach ($_POST as $k => $v)
            {
                $data[$k] = $v;
            }
        }
        $id = $data['id'];
        $data['icon'] = $data['image'];
        unset($data['submit']);
        unset($data['Filedata']);
        unset($data['image']);
        if(!$id)//插入
        {
            $data['timestamp'] = date("Y-m-d H:i:s");
            $this->getGoodsCategoryTable()->insertData($data);
        }
        else//跟新
        {
            unset($data[id]);
            $this->getGoodsCategoryTable()->updateData($data, array('id' => $id));
        }
        $this->redirect()->toRoute('admin-goods',array('action' => 'category'));
    }
    
    /**
     * 添加或编辑商品分类--展示
     * @return \Zend\View\Model\ViewModel
     */
    public function addCategoryAction()
    {
        $this->checkLogin('goods_category_detail');
        $id = $this->params()->fromRoute('id');
        $pid = $this->params()->fromRoute('pid');//添加子分类
        
        $type = 1;
        $parent_category = array();
        if($pid)
        {
            $parent_category = $this->getGoodsCategoryTable()->getOne(array('id' => $pid , 'status' => 1 , 'delete'=>DELETE_FALSE));
            $type = $parent_category->type;
        }
       
        $goods_category = '';
        $image = '';
        if($id){//编辑
            $goods_category = $this->getGoodsCategoryTable()->getOne(array('id'=>$id));
            $type = $goods_category->type;
            if($goods_category['icon']){
                $image = $this->getImageTable()->getOne(array('id'=>$goods_category['icon']));
            }
        }
        
        $categorys = $this->getGoodsCategoryTable()->fetchAll(array('delete'=> 0,'parent_id'=>'0','type'=>$type));
        $categoryType = $this->categoryType();
        
        $view=new ViewModel(array(
             'goods_category' => $goods_category,
             'parent_category' => $parent_category,
             'categorys' => $categorys,
             'categoryType' => $categoryType,
             'image' => $image,
             'pid' => $pid,
             'id' => $id,
        ));
        $view->setTemplate('admin/goods/category_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 删除商品分类
     */
    public function delCategoryAction()
    {
        $this->checkLogin('goods_category_delete');
        $id = $this->params()->fromRoute('id');
        if($id)
        {
            $this->getGoodsCategoryTable()->deleteData($id);
        }
        else{
            $this->showMessage("参数错误");
        }
        $this->redirect()->toRoute('admin-goods',array('action' => 'category'));
    }
    
    /**
     * 停用和启用商品分类操作
     */
    public function stopOrStartCategoryAction(){
        $this->checkLogin('goods_category_delete');
        $param = $_POST;
        if($param['op'] == 'stop'){
            $res = $this->getGoodsCategoryTable()->updateData(array('status'=>'2'), array('id'=>$param['id']));
            if($res){
                die(json_encode(array('code'=>'1')));
            }else{
                die(json_encode(array('code'=>'0')));
            }
        }elseif($param['op'] == 'start'){
            $res = $this->getGoodsCategoryTable()->updateData(array('status'=>'1'), array('id'=>$param['id']));
            if($res){
                die(json_encode(array('code'=>'1')));
            }else{
                die(json_encode(array('code'=>'0')));
            }
        }
    }
    
    /**
     * 查询同一分类类型的商品分类--ajax
     */
    public function sameTypeCategoryAction(){
        $this->checkLogin();
        $param = $_POST;
        $categorys = $this->getGoodsCategoryTable()->fetchAll(array('delete'=> 0,'parent_id'=>'0','type'=>$param['type']));
        if($categorys){
           die(json_encode(array('code'=>'1','categorys'=>$categorys)));
        }else{
           die(json_encode(array('code'=>'0')));
        }
    }
    
    /**
     * 商品单位列表
     * @return multitype:
     */
    public function unitAction()
    {
        $this->checkLogin('goods_unit');
        $this->table = $this->getGoodsUnitTable();
        $this->action = 'unit';
        $this->template = array(0=>'goods/unit');
        return $this->getList();
    }
    
    /**
     * 添加或编辑商品单位--展示
     * @return \Zend\View\Model\ViewModel
     */
    public function addUnitAction()
    {
        $this->checkLogin('goods_unit_detail');
        $id = $this->params()->fromRoute('id');
        $goodsUnit = '';
        if($id){
            $goodsUnit = $this->getGoodsUnitTable()->getOne(array('id'=>$id));
        }
        $view=new ViewModel(array(
             'goodsUnit' => $goodsUnit,
        ));
        $view->setTemplate('admin/goods/unit_operate');
        return $this->setMenu($view,1);
    }
    
    /**
     * 添加或编辑商品单位--提交操作
     * @return \Zend\View\Model\ViewModel
     */
    public function operateUnitAction()
    {
        $this->checkLogin('goods_unit_add');
        $data = array();
//         $this->dump($_POST);exit;
        if($_POST['submit'])
        {
            foreach ($_POST as $k=>$v)
            {
                $data[$k] = $v;
            }
        }
        $id = $data['id'];
        unset($data['submit']);
//         $this->dump($data);exit;
        if(!$id)//插入
        {
           $data['timestamp'] = date("Y-m-d H:i:s");
           $this->getGoodsUnitTable()->insertData($data); 
        }
        else//更新
        {
            unset($data['id']);
            $this->getGoodsUnitTable()->updateData($data, array('id'=>$id));
        }
        return $this->redirect()->toRoute('admin-goods',array('action'=>'unit'));
    }
    
    /**
     * 删除商品单位
     * @return \Zend\Http\Response
     */
    public function delUnitAction()
    {
        $this->checkLogin('goods_unit_delete');
        $id = $this->params()->fromRoute('id');
        if($id){
            $this->getGoodsUnitTable()->deleteData($id);
            return $this->redirect()->toRoute('admin-goods',array('action'=>'unit'));
        }else{
            return $this->showMessage("参数错误！");
        }
    }
    
    public function shelvesManageAction()
    {
        $this->checkLogin('goods_list');
        $page = $this->params()->fromRoute('page');
        
        $start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
        $end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
        $keyword = isset($_GET['keyword']) ? $_GET['keyword']:'';
        $status = isset($_GET['status']) ? $_GET['status']:$this->params()->fromRoute('status',0);
        
        $where = new Where();
        $where->equalTo('delete', DELETE_FALSE);
        if($start_time)
        {
            $where->greaterThanOrEqualTo('delivery_date', $start_time);
        }
        if($end_time)
        {
            if($start_time && $start_time > $end_time)
            {
                $end_time = $start_time;
            }
            $where->lessThanOrEqualTo('delivery_date', $end_time);
        }
        
        if($status)
        {
            $where->equalTo('status', $status);
        }
        
        $like = array();
        if($keyword)
        {
            $like['name'] = $keyword;
            $like['goods_sn'] = $keyword;
        }
        
        $goods_list = $this->getGoodsTable()->getAll($where ,null, array('id' => 'DESC' , 'timestamp_update' => 'DESC'), true, $page, 10,$like);
        $goods = $goods_list['list'];
        
        if(!empty($goods))
            {
                $goods_new = '';
                foreach ($goods as $k=>$v)
                {
                    $images = explode(',', $v['image']);
                    $oneImage = $this->getImageTable()->getOne(array('id'=>$images[0]));//取得商品的图片之一
    //                 $this->dump($oneImage);exit;
                    $v['oneImage_path'] = $oneImage['path'].$oneImage['filename'];
                    
                    $spec = $this->getGoodsSpecificationTable()->getOne(array('delete'=>'0','goods_id'=>$v['id']));
                    if($spec)
                    {
                       $v['spec_size'] = $spec->size;
                    }
                    else
                    {
                        $v['spec_size'] = '';
                    }
                    
                    $goods_new[$k] = $v;
                }
            }
        
        $view=new ViewModel(array(
            'goods' => $goods_new,
            'paginator' => $goods_list['paginator'],
            'salse_type' => $this->onSaleType(),
            'goodsStatus' => $this->goodsStatus(),
            'status' => $status,
            'keyword' => $keyword,
            'start_time'   => $start_time,
            'end_time'   => $end_time,
            'condition' => array(
                'action' => 'shelvesManage',
                'page'   => $page,
                'status' => $status,
                'keyword' => $keyword,
                'where' => array(
                    'start_time'   => $start_time,
                    'end_time'   => $end_time,
                ),
            ),
        ));
        $view->setTemplate('admin/goods/shelves_manage');
        return $this->setMenu($view,1);
    }
    
    public function shelvesDetailAction()
    {
        $this->checkLogin('goods_detail');
        $id = $this->params()->fromRoute('id');
        $good = $this->getGoodsTable()->getOne(array('delete'=>'0','id'=>$id));
//         $this->dump($good);exit;
        $images = array();
        $user_name = array();
        //查询供应商
        if($good->user_id)
        {
            $user_name = $this->getUserTable()->getOne(array('delete'=>'0','id'=>$good->user_id),array('company_name'));
        }
        //查询分类
        if($good->category_id)
        {
            $category_name = $this->getGoodsCategoryTable()->getOne(array('delete'=>'0','id'=>$good->category_id),array('name'));
        }
        //查询规格
        $spec = $this->getGoodsSpecificationTable()->getOne(array('delete'=>'0','goods_id'=>$good->id));
        if($good->image)
        {
            $images_ids = explode(',', $good->image);
            $images = $this->getImageTable()->getImages($images_ids);
        }
        $view=new ViewModel(array(
            'images' => $images,
            'good' => $good,
            'user_name'=>$user_name,
            'category_name' => $category_name,
            'spec' => $spec,
            'id' => $id,
            'salse_type' => $this->onSaleType(),
        ));
        $view->setTemplate('admin/goods/shelves_detail');
        return $this->setMenu($view,1);
    }
    
    /**
     * change goods status
     */
    public function changeGoodsStatusAction()
    {
        $this->checkLogin('shelves_manage_review');
        $nopass = $this->params()->fromPost('nopass');
        $pass = $this->params()->fromPost('pass');
        $passAndUp = $this->params()->fromPost('passAndUp');
        $up = $this->params()->fromPost('up');
        $down = $this->params()->fromPost('down');
        $id = $this->params()->fromPost('id','0');
        $reason = $this->params()->fromPost('reason');
        if(!$id)
        {
            $this->showMessage("非法操作！");
        }
        $where = array('id'=>$id);
        $goods_info = $this->getGoodsTable()->getOne($where);
        if($nopass)
        {
            if(!$reason)
            {
                $this->showMessage("请填写审核失败原因");
            }
            
            $this->getGoodsTable()->updateData(array('status'=>6,'reason'=>$reason), $where);
            
            if($goods_info->user_id)
            {
                $value['status'] = 6;
                $value['user_id'] = $goods_info->user_id;
                $value['goods_id'] = $goods_info->id;
                $value['timestamp'] = $this->getTime();
                
                $this->getGoodsTrackingTable()->indateKey($value, 2, 6, $goods_info->goods_sn);
            }
            
            $this->redirect()->toRoute('admin-goods',array('action'=>'shelvesManage'));
        }
        if($pass)
        {
            $this->getGoodsTable()->updateData(array('status'=>2), $where);
            
            if($goods_info->user_id)
            {
                $value['status'] = 2;
                $value['user_id'] = $goods_info->user_id;
                $value['goods_id'] = $goods_info->id;
                $value['timestamp'] = $this->getTime();
                
                $this->getGoodsTrackingTable()->indateKey($value, 2, 2, $goods_info->goods_sn);
            }
            
            $this->redirect()->toRoute('admin-goods',array('action'=>'shelvesManage'));
        }
        if($passAndUp || $up)
        {
            
            /* $exists = $this->getGoodsTrackingTable()->getOne(array('status' => 3 , 'user_id' => $goods_info->user_id , 'goods_id' => $goods_info->id));
            
            if($exists)
            {
                $this->getGoodsTrackingTable()->updateData(array('timestamp' => $this->getTime()), array('status' => 3 , 'user_id' => $goods_info->user_id , 'goods_id' => $goods_info->id));
            }
            else 
            { */
                if($passAndUp)
                {
                    $this->getGoodsTable()->updateData(array('status'=>2), $where);
                    if($goods_info->user_id)
                    {
                        $value['status'] = 2;
                        $value['user_id'] = $goods_info->user_id;
                        $value['goods_id'] = $goods_info->id;
                        $value['timestamp'] = $this->getTime();
                        $this->getGoodsTrackingTable()->indateKey($value, 2, 2, $goods_info->goods_sn);
                        
                        /* $value['status'] = 3;
                        $this->getGoodsTrackingTable()->indateKey($value, 2, 3, $goods_info->goods_sn); */
                    }
                }
                /* else if($up)
                {
                    if($goods_info->user_id)
                    {
                        $value['status'] = 3;
                        $value['user_id'] = $goods_info->user_id;
                        $value['goods_id'] = $goods_info->id;
                        $value['timestamp'] = $this->getTime();
                    
                        $this->getGoodsTrackingTable()->indateKey($value, 2, 3, $goods_info->goods_sn);
                    }
                }
                
            } */

            
            $this->redirect()->toRoute('admin-goods',array('action'=>'addGoods','id'=>$id , 'type' =>$goods_info->type));
        }
        if($down)
        {
            if(!$reason)
            {
                $this->showMessage("请填写商品下架原因");
            }
            
            $exists = $this->getGoodsTrackingTable()->getOne(array('status' => 4 , 'user_id' => $goods_info->user_id , 'goods_id' => $goods_info->id));
            
            if($exists)
            {
                $this->getGoodsTrackingTable()->updateData(array('timestamp' => $this->getTime()), array('status' => 4 , 'user_id' => $goods_info->user_id , 'goods_id' => $goods_info->id));
            }
            else
            {
                $value['status'] = 4;
                $value['user_id'] = $goods_info->user_id;
                $value['goods_id'] = $goods_info->id;
                $value['timestamp'] = $this->getTime();
                
                $this->getGoodsTrackingTable()->indateKey($value, 2, 4, $goods_info->goods_sn);
            }
            
            $this->getGoodsTable()->updateData(array('status'=>4,'reason'=>$reason), $where);
            $this->redirect()->toRoute('admin-goods',array('action'=>'shelvesManage'));
        }
        
    }
    
}
