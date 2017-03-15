<?php
namespace Admin;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
class Module
{

    public function onBootstrap(MvcEvent $mvcEvent)
    {
        $eventManager = $mvcEvent->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        // Register a dispatch event
        $application = $mvcEvent->getParam('application');
        
        $application->getEventManager()->attach('dispatch', array(
            $this,
            'setLayout'
        ));
    }

    public function setLayout($mvcEvent)
    {
        $matches = $mvcEvent->getRouteMatch();
        $controller = $matches->getParam('controller');
        if (false === strpos($controller, __NAMESPACE__))
        {
            // not a controller from this module
            return false;
        }

        // Set the layout template
        $viewModel = $mvcEvent->getViewModel();
        $viewModel->setTemplate('layout/block');
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'Core\System' => dirname(dirname(__DIR__)) . '/vendor/Core/System'
                )
            ) // 2014.1.22hexin

        );
    }
	
    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
//     					'Admin\Model\AdminTable' =>  function($sm) {
//     						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//     						$table = new AdminTable($dbAdapter);
//     						return $table;
//     					},
//     					'Admin\Model\ImageTable' =>  function($sm) {
//     						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//     						$table = new ImageTable($dbAdapter);
//     						return $table;
//     					},
//     					'Admin\Model\DeviceTable' =>  function($sm) {
//     						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//     						$table = new DeviceTable($dbAdapter);
//     						return $table;
//     					},
//                         'Admin\Model\UserTable' =>  function($sm) {
//                             $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                             $table = new UserTable($dbAdapter);
//                             return $table;
//                         },
//                         'Admin\Model\PageTable' =>  function($sm) {
//                             $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                             $table = new PageTable($dbAdapter);
//                             return $table;
//                         },
//                         'Admin\Model\StatisticsTable' =>  function($sm) {
//                             $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                             $table = new StatisticsTable($dbAdapter);
//                             return $table;
//                         },
//                         'Admin\Model\CarteTable' =>  function($sm) {
//                             $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                             $table = new CarteTable($dbAdapter);
//                             return $table;
//                         },
//                         'Admin\Model\RegionTable' =>  function($sm) {
//                             $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                             $table = new RegionTable($dbAdapter);
//                             return $table;
//                         },
//                         'Admin\Model\LoginTable' =>  function($sm) {
//                             $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
//                             $table = new LoginTable($dbAdapter);
//                             return $table;
//                         },
    			)
    	);
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}