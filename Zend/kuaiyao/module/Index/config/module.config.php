<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Index\Controller\Common' => 'Index\Controller\CommonController',
            'Index\Controller\Index' => 'Index\Controller\IndexController',
            'Index\Controller\User' => 'Index\Controller\UserController',
            'Index\Controller\Shop' => 'Index\Controller\ShopController',
            'Index\Controller\Seller' => 'Index\Controller\SellerController',
            'Index\Controller\Seckill' => 'Index\Controller\SeckillController',
            'Index\Controller\Lottery' => 'Index\Controller\LotteryController',
            'Index\Controller\News' => 'Index\Controller\NewsController',
        	'Index\Controller\About' => 'Index\Controller\AboutController',
            'Index\Controller\Weixin' => 'Index\Controller\WeixinController',
            'Index\Controller\Company' => 'Index\Controller\CompanyController',
            'Index\Controller\Activity' => 'Index\Controller\ActivityController',
            'Index\Controller\Commodity' => 'Index\Controller\CommodityController',
            'Index\Controller\WeixinCard'=>  'Index\Controller\WeixinCardController',
        )
    ),
    'service_manager' => array(
    
        'factories' => array(
    
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory'
        ),
    
    ),
    
    'translator' => array(
    
        'locale' => 'en_US',
    
        'translation_file_patterns' => array(
    
            array(
    
                'type' => 'gettext',
    
                'base_dir' => __DIR__ . '/../language',
    
                'pattern' => '%s.mo',
    
            ),
    
        ),
    
    ),
    'router' => array(
        'routes' => array(
            'index' => array(
                'type'    => 'segment',
                'options' => array(
//                     'route'    => '/[:controller][/][:action][[/:id][/a:alert][/s:search][/p:page][/l:language]]',
                    'route'    => MODULE_INDEX . '[[:controller][/:action][/:id][/a:alert][/b:between][/t:type][/p:page][/s:search].html]',
					'constraints'=>array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
						'id'=>'[0-9]+',
						'alert'=>'[0-9]+',
						'between'=>'[0-9]+',
						'type'=>'[0-9]+',
						'page'=>'[0-9]+',
						'search'=>'[\w\W]*',
					),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Index\Controller',
                        'controller'    => 'Index',
                         'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
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
            'layout_index/layout' => __DIR__ . '/../view/layout_index/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'layout_index/page' => __DIR__ . '/../view/layout_index/page.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    )
);
