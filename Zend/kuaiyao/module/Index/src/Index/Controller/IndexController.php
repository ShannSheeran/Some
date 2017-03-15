<?php
namespace Index\Controller;
use Zend\View\Model\ViewModel;
use Admin\Controller\CommonController as AdminController;
class IndexController extends CommonController
{

    /**
     * 首页
     */
    public function indexAction()
    {
        $this->getAdminController()->statOporation(5,1);
        $view = new ViewModel();
        $view->setTemplate('index/index/index');
        return $this->setMenu($view, 1);
    }
    
   public function indexaAction()
    {
		
        $view = new ViewModel();
        $view->setTemplate('index/index/indexa');
        return $this->setMenu($view, 1);
    }
    
	public function indexbAction()
    {
		
        $view = new ViewModel();
        $view->setTemplate('index/index/indexb');
        return $this->setMenu($view, 1);
    }
	
	public function lineAction(){
		$view = new ViewModel();
        $view->setTemplate('index/index/line');
        return $this->setMenu($view, 1);
	}
}
