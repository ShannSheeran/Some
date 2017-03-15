<?php
namespace Index\Controller;
use Zend\View\Model\ViewModel;
use Index\Controller\CommonController;
class AboutController extends CommonController

{
    /**
     * 关于我们
     */
    public function indexAction(){

        $view = new ViewModel();
        $view->setTemplate('index/About/index');
        //return $view;
        return $this->setMenu($view, 1);
    }
    
    
    /**
     * 联系我们
     */
    public function connectionAction(){

        $view = new ViewModel();
        $view->setTemplate('index/About/connection');
        //return $view;
        return $this->setMenu($view, 1);
    }
    
    
    /**
     * 项目介绍
     */
    public function accountAction(){

        $view = new ViewModel();
        $view->setTemplate('index/About/account');
        //return $view;
        return $this->setMenu($view, 1);
    }
    
    /**
     * 项目介绍
     */
    public function happyAction(){
    
        $view = new ViewModel();
        $view->setTemplate('index/About/happy');
        //return $view;
        return $this->setMenu($view, 1);
    }
    
   
}
