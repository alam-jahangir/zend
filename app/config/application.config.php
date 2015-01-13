<?php
return array(
    'modules' => array(
        'Application',
        'Admin'
    ),
    
    'module_listener_options' => array(
        
        
        'module_paths' => array(
            './'.APP_DIR.'/module',
            './'.APP_DIR.'vendor',
        ),
        'config_glob_paths'    => array(
            APP_DIR.'/config/autoload/{,*.}{global,local,message}.php',
        ),
        // Whether or not to enable a configuration cache.
        // If enabled, the merged configuration will be cached and used in
        // subsequent requests.
        //'config_cache_enabled' => true,

        // The key used to create the configuration cache file name.
        //'config_cache_key' => 'module_config_cache',

        // Whether or not to enable a module class map cache.
        // If enabled, creates a module class map cache which will be used
        // by in future requests, to reduce the autoloading process.
        //'module_map_cache_enabled' => true,

        // The key used to create the class map cache file name.
        //'module_map_cache_key' => 'app',

        // The path in which to cache merged configuration.
        'cache_dir' => './var/cache'
    ),
);

