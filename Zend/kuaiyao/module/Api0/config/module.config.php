<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Api0\Controller\Index' => 'Api0\Controller\IndexController' ,
             'Api0\Controller\Plan' => 'Api0\Controller\PlanController'
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
            'api0' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => MODULE_API,
                    'defaults' => array(
                        'controller' => 'Api0\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            'api0-index' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_API . '/index[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Api0\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            
            'api0-plan' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => MODULE_API . '/plan[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Api0\Controller\Plan',
                        'action' => 'index'
                    )
                )
            ),
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'admin/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'apiPage' => __DIR__ . '/../view/layout/page.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);