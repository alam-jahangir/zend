<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Login' => 'Admin\Controller\LoginController',
            'Admin\Controller\Product' => 'Admin\Controller\ProductController',
            'Admin\Controller\Cms' => 'Admin\Controller\CmsController',
            'Admin\Controller\Category' => 'Admin\Controller\CategoryController',
        ),
    ),
    
    'router' => array(
        'routes' => array(
        	
        	'admin' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin[/:action[/:id[/:page]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action' => 'index',
                        'id' => 0,
                        'page' => 1,
                        'roleid' => 0
                    ),
                ),
            ),
            
            'cms' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/cms[/:action[/:id[/:page]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Cms',
                        'id' => 0,
                        'page' => 1,
                        'roleid' => 0
                    ),
                ),
            ),
            
            'category' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/category[/:action[/:id[/:page]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'id' => 0,
                        'page' => 1,
                        'roleid' => 0
                    ),
                ),
            ),
            
            'product' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/product[/:action[/:id[/:page]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Product',
                        'id' => 0,
                        'page' => 1,
                        'roleid' => 0
                    ),
                ),
            ),
            
            'admin_login' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/login',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Login',
                        'action' => 'login',
                    ),
                ),
            ),
            
            'admin_logout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/logout',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Login',
                        'action' => 'logout',
                    ),
                ),
            ),
            
            'admin_forgot_password' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/admin/forgot-password',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Login',
                        'action' => 'forgotPassword',
                    ),
                ),
            )
            
        ),
    ),
   
    'view_manager' => array(
        'template_path_stack' => array(
            'admin' => __DIR__ . '/../view',
        ),
    )
);
