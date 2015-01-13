<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\Index' => 'User\Controller\IndexController',
            'User\Controller\Login' => 'User\Controller\LoginController',
        ),
    ),
    // The following section is new and should be added to your file
    
    'router' => array(
        'routes' => array(
            'user' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'reset_password' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/reset-password[/:key]',
                    'constraints' => array(
                        'key' => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Index',
                        'action' => 'reset-password',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/login[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Login',
                        'action' => 'login',
                    ),
                ),
            ),
            
            'my_bids' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/my-bids[/:page]',
                    'constraints' => array(
                        'page' => '[0-9]+',
                        'action' => 'my-bids'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Index',
                        'action'    => 'my-bids',
                        'page' => 1
                    ),
                ),
            ),
            
            'my_bid_agents' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/my-bid-agents[/:page]',
                    'constraints' => array(
                        'page' => '[0-9]+',
                        'action' => 'my-bid-agents'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Index',
                        'action'    => 'my-bid-agents',
                        'page' => 1
                    ),
                ),
            ),
            
            'won_auctions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/won-auctions[/:page]',
                    'constraints' => array(
                        'page' => '[0-9]+',
                        'action' => 'won-auctions'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Index',
                        'action'    => 'won-auctions',
                        'page' => 1
                    ),
                ),
            ),
            'buy_won_auction' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/user/buy-won-auction[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'User\Controller\Index',
                        'action'    => 'buy-won-auction',
                        'id' => 0
                    ),
                ),
            )
            
        ),
    ),
    
    
    ////////////////
    'view_manager' => array(
        'template_path_stack' => array(
            'user' => __DIR__ . '/../view',
        ),
    )
);
