<?php
use Api3\Controller\ActivityDetails;
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\User' => 'Admin\Controller\UserController',
            'Admin\Controller\Card' => 'Admin\Controller\CardController',
            'Admin\Controller\Config' => 'Admin\Controller\ConfigController',
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Invitation' => 'Admin\Controller\InvitationController',
            'Admin\Controller\Common' => 'Admin\Controller\CommonController',
            'Admin\Controller\Device' => 'Admin\Controller\DeviceController',
            'Admin\Controller\Page' => 'Admin\Controller\PageController',
            'Admin\Controller\Ads' => 'Admin\Controller\AdsController',
            'Admin\Controller\Microblog' => 'Admin\Controller\MicroblogController',
            'Admin\Controller\Notification' => 'Admin\Controller\NotificationController',
            'Admin\Controller\Order' => 'Admin\Controller\OrderController',
            'Admin\Controller\Financial' => 'Admin\Controller\FinancialController',
            'Admin\Controller\News' => 'Admin\Controller\NewsController',
            'Admin\Controller\Product' => 'Admin\Controller\ProductController',
            'Admin\Controller\Company' => 'Admin\Controller\CompanyController',
            'Admin\Controller\Activity' => 'Admin\Controller\ActivityController',
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
            
            'admin-invitation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'invitation[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Invitation',
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
            
            'admin-user' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'user[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action' => 'index'
                    )
                )
            ),
            'admin-card' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'card[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Card',
                        'action' => 'index'
                    )
                )
            ),
            
            'admin-config' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'config[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Config',
                        'action' => 'index'
                    )
                )
            ),
            
            'admin-device' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'device[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Device',
                        'action' => 'index'
                    )
                )
            ),
            
            'admin-page' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'page[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Page',
                        'action' => 'index'
                    )
                )
            ),
            'admin-microblog' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'microblog[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Microblog',
                        'action' => 'index'
                    )
                )
            ),
            'admin-other' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'other[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Other',
                        'action' => 'index'
                    )
                )
            ),
            'admin-ads' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'ads[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Ads',
                        'action' => 'index'
                    )
                )
            ),
            'admin-notification' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'notification[a/:action][/i:id][/c:cid][/p:page][/k:keyword][/o:other]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Notification',
                        'action' => 'index'
                    )
                )
            ),
            'admin-order' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'order[a/:action][/i:id][/c:cid][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Order',
                        'action' => 'index'
                    )
                )
            ),
            'admin-financial' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'financial[a/:action][/i:id][/c:cid][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
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
            'admin-news' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'news[a/:action][/i:id][/c:cid][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\News',
                        'action' => 'index'
                    )
                )
            ),
            'admin-product' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'product[a/:action][/i:id][/c:cid][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Product',
                        'action' => 'index'
                    )
                )
            ),
            'admin-company' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'company[a/:action][/i:id][/c:cid][/p:page][/o:other][/k:keyword][t/:top][ci/:city][ca/:category][s/:scale]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'top' => '[0-9]*',
                        'city' => '[0-9]*',
                        'category' => '[0-9]*',
                        'scale' => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\company',
                        'action' => 'index'
                    )
                )
            ),
            'admin-activity' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_ADMIN . 'activity[a/:action][/i:id][/c:cid][/p:page][/o:other][/k:keyword]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                        'cid' => '[0-9]*',
                        'page' => '[0-9]*',
                        'other' => '[0-9]*',
                        'keyword' => '[\w\W]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\activity',
                        'action' => 'index'
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
