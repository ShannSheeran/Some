<?php
use Api3\Controller\ActivityDetails;
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Common' => 'Admin\Controller\CommonController',
            'Admin\Controller\Order' => 'Admin\Controller\OrderController',
            'Admin\Controller\Goods' => 'Admin\Controller\GoodsController',
            'Admin\Controller\Service' => 'Admin\Controller\ServiceController',
            'Admin\Controller\Financial' => 'Admin\Controller\FinancialController',
            'Admin\Controller\User' => 'Admin\Controller\UserController',
            'Admin\Controller\Information' => 'Admin\Controller\InformationController',
            'Admin\Controller\Config' => 'Admin\Controller\ConfigController',
        )
    ),
    
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory'
        )
    ),
    'translator' => array(
        'locale' => 'zh_CN',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => MODULE_ADMIN,
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            'admin-index' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . '[a/:action][/y:id][/m:cid][/d:other][/s:days][/t:type][/f:year][/h:month]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'other' => '[0-9]*',
                        'days' => '[0-9]*',
                        'type' =>'[0-9]*',
                        'year' =>'[0-9]*',
                        'month' =>'[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            'admin-common' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'index[a/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Common',
                        'action' => 'index'
                    )
                )
            ),
            'admin-order' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'order[a/:action][/i:id][/c:cid][/t:type][/p:page][/o:other][/k:keyword][/s:status][/d:datetime]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*',
                        'status' => '[0-9]*',
                        'datetime' => '[0-9~-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Order',
                        'action' => 'index'
                    )
                )
            ),
            'admin-goods' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'goods[a/:action][/i:id][/c:cid][/pi:pid][/t:type][/p:page][/op:operate][/o:other][/s:status][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'status' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Goods',
                        'action' => 'index'
                    )
                )
            ),
            'admin-service' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'service[a/:action][/i:id][/c:cid][/t:type][/p:page][/o:other][/s:status][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'status' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Service',
                        'action' => 'index'
                    )
                )
            ),
            'admin-financial' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'financial[a/:action][/i:id][/c:cid][/t:type][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Financial',
                        'action' => 'index'
                    )
                )
            ),
            'admin-user' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'user[a/:action][/i:id][/c:cid][/t:type][/p:page][/o:other][/k:keyword][/s:status][/l:level]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*',
                        'status' => '[0-9]*',
                        'level' => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'index'
                    )
                )
            ),
            'admin-information' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'information[a/:action][/i:id][/c:cid][/t:type][/p:page][/o:other][/k:keyword][/s:status]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*',
                        'status' => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Information',
                        'action' => 'platformInformation'
                    )
                )
            ),
            'admin-config' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'config[a/:action][/i:id][/c:cid][/t:type][/p:page][/o:other][/k:keyword][/s:status]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'type' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*',
                        'status' => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Config',
                        'action' => 'adminList'
                    )
                )
            ),
        )
    )
    
    ,
/*     
    'view_manager' => array(
    		'template_path_stack' => array(
    				'sourcing' => __DIR__ . '/../view',
    		),
    ), */

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'admin/login' => __DIR__ . '/../view/index/login.phtml',
            'admin/layout' => __DIR__ . '/../view/layout/layout.phtml',
            
            // 'admin/admin/index'
            // =>
            // __DIR__
            // .
            // '/../view/admin/admin/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'page' => __DIR__ . '/../view/layout/page.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);
