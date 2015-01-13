<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));

define('DS', DIRECTORY_SEPARATOR);

define('PS', PATH_SEPARATOR);

define('APP_DIR', 'app');

// Setup autoloading
require 'init_autoloader.php';



// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
