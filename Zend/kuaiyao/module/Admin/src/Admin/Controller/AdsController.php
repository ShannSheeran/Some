<?php
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Zend\Db\Sql\Where;

class AdsController extends CommonController
{
	
	
	public function indexAction()
	{   
	    $this->checkLogin();
		$data = $this->getAdsTable()->getAll(array('delete'=>0));

		$image_arr = array();
		foreach($data['list'] as $val){
		    if($val['image'])
		    {
		        $image_arr[] =  $val['image'];
		    }
		}
		//print_r();
		if($image_arr)
		{
		    $where = new Where();
		    $where->in('id',$image_arr);
		    $image_arr = array();
		    $image = $this->getImageTable()->fetchAll($where);
		    foreach ($image as $v)
		    {
		        $image_arr[$v->id]= $v->path . $v->filename;
		    }
		}
		
		$view = new ViewModel(array(
             'data' => $data,
		    'image' => $image_arr,
		));

		$view->setTemplate('admin/ads/index');
        return $this->setMenu($view, 1);
	 }
	 
	 public function deladsAction()
	 {     
	     $this->checkLogin();
	     if(isset($_POST['id']) && $_POST['id']){
	           $ads = $this->getAdsTable()->getOne(array('id'=>$_POST['id']));
	           if(!$ads){
	               echo 2;
	               die();
	           }
	           
	           $updata = $this->getAdsTable()->updateData(array('delete'=>1), array('id'=>$_POST['id']));
	           if($updata){
	               echo 1;
	               die();
	           }
	     }else{
	         echo 2;
	         die();
	     }
	 }
	 
	 public function addadsAction()
	 {
	     $this->checkLogin();    
	     $str = ltrim($_POST['link'],"http://");
	     $link = 'https://' . $str;
	    
	     if($_POST){
    	     $data = array(
    	         'name' => isset($_POST['adName']) ? trim($_POST['adName']) :'',
	             'type' => isset($_POST['type']) ? (int) $_POST['type'] : 0,
	             'target' => isset($_POST['target']) ? trim($_POST['target']) : '',
	             'link' => isset($_POST['link']) ? trim($_POST['link']) :'',
	             'image' => isset($_POST['image']) ? trim($_POST['image']) : '',
    	         'timestamp' => $this->getTime(),
    	         'timestamp_update' => $this->getTime(),
    	     );
            
    	     $insert = $this->getAdsTable()->insertData($data);
			/* print_r($insert);die;*/
    	     if($insert){
    	         $this->redirect()->toRoute('admin-ads', array(
    	             'action' => 'index'
    	         ));
    	     }
	     }
	     	     
	     $view = new ViewModel();    
	     $view->setTemplate('admin/ads/addads');
	     return $this->setMenu($view, 1);
	 }
	 
	 
	 public function editadsAction()
	 {
	     $this->checkLogin();	     
	     $id = $this->params()->fromRoute('id');     
	    
	     if($_POST){
	         $data = array(
	             'name' => isset($_POST['adName']) ? trim($_POST['adName']) :'',
	             'type' => isset($_POST['type']) ? (int) $_POST['type'] : 0,
	             'target' => isset($_POST['target']) ? trim($_POST['target']) : '',
	             'link' => isset($_POST['link']) ? trim($_POST['link']) :'',
	             'image' => isset($_POST['image']) ? trim($_POST['image']) : '',
	             'timestamp_update' => $this->getTime(),
	         );
	         
	         $updata = $this->getAdsTable()->updateData($data, array('id'=>$_POST['id']));
	         if($updata){
	             $this->redirect()->toRoute('admin-ads', array(
	                 'action' => 'index'
	             ));
	         }
	     }
	     
	     if(!$id){
	         $this->redirect()->toRoute('admin-ads', array(
	             'action' => 'index'
	         ));
	     }
	     
	     $ads = $this->getAdsTable()->getOne(array('id'=>$id));
	     if($ads){
	         $image = $this->getImageTable()->getOne(array('id'=>$ads['image']));	         
	     }
   
	     $view = new ViewModel(array(
	         'ads' => $ads,
	         'image' => $image['path'] . $image['filename'],
	     ));
	     
	     
	     $view->setTemplate('admin/ads/edit');
	     return $this->setMenu($view, 1);
	 }
}