<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Account\Controller\Packages' => 'Account\Controller\PackagesController',
            'Account\Controller\Transactions' => 'Account\Controller\TransactionsController'
        ),
    ),
    // The following section is new and should be added to your file
    
    'router' => array(
        'routes' => array(
            'packages' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/packages[/:action[/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Account\Controller\Packages',
                        'action' => 'index',
                        'id' => 0,
                    ),
                ),
            ),
            'transactions' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/transactions[/:action[/:id]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Account\Controller\Transactions',
                        'action' => 'index',
                        'id' => 0,
                    ),
                ),
            ),
        ),
    ),
    
    
    ////////////////
    'view_manager' => array(
        'template_path_stack' => array(
            'packages' => __DIR__ . '/../view',
        ),
    )
);
