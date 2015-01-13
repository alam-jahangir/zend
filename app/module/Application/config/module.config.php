<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\CmsPage' => 'Application\Controller\CmsPageController',
            'Application\Controller\Account' => 'Application\Controller\AccountController'
        ),
    ),
    'router' => array(
        'routes' => array(
                        
            'noRoute' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '[/:identifier]',
                    'constraints' => array(
                        'identifier'     => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\CmsPage',
                        'action'     => 'index'
                    )
                )
            ),
            
            'home' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            
            'dashboard' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/dashboard',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index'
                    )
                )
            ),
            
            'details' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/details[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'details',
                        'id' => 0
                    )
                )
            ),
            
            'account' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'index',
                    )
                )
            ),
            
            'checkout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/checkout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'checkout',
                    )
                )
            ),
            
            'checkout_success' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/checkout-success',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'checkoutSuccess',
                    )
                )
            ),
            
            'delete_upload' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/delete-upload[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'deleteUpload',
                        'id' => 0
                    )
                )
            ),
            
            'add_to_favourite' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/add-to-favourite[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'addToFavourite',
                        'id' => 0
                    )
                )
            ),
            
            'item_settins' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/item-settins[/:id[/:page]]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'itemSettins',
                        'id' => 0,
                        'page' => 1
                    )
                )
            ),
            
            'item_rating' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/item-rating[/:id[/:page]]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'itemRating',
                        'id' => 0,
                        'page' => 1
                    )
                )
            ),
            
            'my_uploads' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/my-uploads[/:page]',
                    'constraints' => array(
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'myUploads',
                        'page' => 1,
                    )
                )
            ),
            
            'my_favourites' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/my-favourites[/:page]',
                    'constraints' => array(
                        'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'myFavourites',
                        'page' => 1,
                    )
                )
            ),
            
            'image_upload' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/image-upload',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'imageUpload'
                    )
                )
            ),
            
            'upload_video' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/upload-video',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'uploadVideo'
                    )
                )
            ),
            
            'cost_calculation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/cost-calculation[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'costCalculation',
                        'id' => 0
                    )
                )
            ),
            
            'group_option' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/group-option[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'groupOption',
                        'id' => 0
                    )
                )
            ),
            
            'upload' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/upload[/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'upload',
                        'id' => 0
                    )
                )
            ),
            
            'upload_confirmation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/upload-confirmation',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'uploadConfirmation'
                    )
                )
            ),
            
            'cronjob' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/cronjob',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'cronjob',
                    )
                )
            ),
            
            'geocode' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/geocode',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'geocode',
                    )
                )
            ),
            
            'locator' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/locator',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'locator',
                    )
                )
            ),
            
            'membership_charge' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/membership-charge',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'membershipCharge',
                    )
                )
            ),
            
            'account_edit' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account/edit[/:id]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Account',
                        'action' => 'edit',
                        'id' => '[0-9]+'
                    )
                )
            ),
            
            'refer_friend' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/refer-friend',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'referFriend',
                        'id' => '[0-9]+'
                    )
                )
            ),
            
            
            'login' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'login',
                    )
                )
            ),
            
            'forgot_password' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/forgot-password',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'forgotPassword',
                    )
                )
            ),
            
            'resend_activation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/resend-activation',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'resendActivation',
                    )
                )
            ),
            
            'account_activation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/account-activation[/:code]',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'accountActivation',
                        'code' => '[a-zA-Z][a-zA-Z0-9-]*',
                    )
                )
            ),
            
            'activation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/activation',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'activation'
                    )
                )
            ),
            
            'create_account' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/create-account',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'createAccount'
                    )
                )
            ),
            
            'personal_details' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/personal-details',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'personalDetails'
                    )
                )
            ),
            'paypal_success' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/paypal-success',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'paypalSuccess'
                    )
                )
            ),
            'paypal_cancel' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/paypal-cancel',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'paypalCancel'
                    )
                )
            ),
            
            'account_confirmation' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/confirmation',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'accountConfirmation'
                    )
                )
            ),
            
            'upload_document' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/upload-document',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'uploadDocument'
                    )
                )
            ),
            
            'contact_us' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/contact-us',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'contactUs',
                    )
                )
            ),
            
            'logout' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'logout',
                    )
                )
            ),
            
            'search' => array(
                'type' => 'segment',
                'options' => array(
                    //'route' => '/category[/:identifier[_:id]].html',
                    'route' => '/search[/:page]',
                    'constraints' => array(
                    	'page' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'search',
                        'page' => 1,
                    )
                )
            ),
            
            
            'product_information' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/product[/:identifier[_:id]].html',
                    'constraints' => array(
                    	'identifier' => '[a-zA-Z][a-zA-Z0-9-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'product',
                        'id' => 0,
                    )
                )
            ),
            
            'product_list' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/products.html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'productlist',
                        'id' => 0,
                    )
                )
            )
            
        )
    ),
    
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
