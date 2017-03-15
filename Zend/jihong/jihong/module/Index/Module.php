<?php
namespace Index;

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
        if (false === strpos($controller, __NAMESPACE__)) {
            // not a controller from this module
            return false;
        }

        // Set the layout template
        $viewModel = $mvcEvent->getViewModel();
        $viewModel->setTemplate('layout_index/layout_empty');
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
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}