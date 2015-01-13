<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
return array(
    'db' => array(
        'driver' => 'Pdo',
        'dsn' => 'mysql:dbname=jakato;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
     ),
     
    'pagination' => array(
        'per_page' => 25, 
        'page_range' => 10
    ),
    'session' => array(
        'admin' => 'admin', 
        'user' => 'user',
        'save_path' => './var/session/'
    ),
    
    'phpSettings'   => array(
        'display_startup_errors'        => false,
        'display_errors'                => true,
        'max_execution_time'            => 60,
        'date.timezone'                 => 'Asia/Dhaka',
        'mbstring.internal_encoding'    => 'UTF-8',
    ),
    
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory'
        ),
     ),
);


