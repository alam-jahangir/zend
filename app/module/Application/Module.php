<?php
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\View\Countries;
use Application\View\Settings;
use Application\View\Navigation;
use Application\View\ParsePageContent;
use Application\View\GetCartSummary;
use Application\View\Activation;

class Module
{
    public function onBootstrap($e)
    {
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();
        
        $config = $app->getServiceManager()->get('Config');
        $phpSettings = $config['phpSettings'];
        if ($phpSettings) {
            foreach($phpSettings as $key => $value) {
                ini_set($key, $value);
            }
        }
        
        $serviceManager->get('viewhelpermanager')->setFactory('authenticate', function($sm) use ($e) {
            $config = $e->getApplication()->getServiceManager()->get('Config');
            $dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');
            return new \Application\View\Authenticate($dbAdapter, $config, $e->getRouteMatch());
        });
        

        $serviceManager->get('viewhelpermanager')->setFactory('getCountries', function($sm) use ($e) {
            $dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');
            return new Countries($dbAdapter);
        });
        
        $serviceManager->get('viewhelpermanager')->setFactory('getCartSummary', function($sm) use ($e) {
            $dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');
            return new GetCartSummary($dbAdapter);
        });
        
        $serviceManager->get('viewhelpermanager')->setFactory('getUserActivation', function($sm) use ($e) {
            $dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');
            return new Activation($dbAdapter);
        });
        
        $serviceManager->get('viewhelpermanager')->setFactory('getSettings', function($sm) use ($e) {
            $dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');
            return new Settings($dbAdapter);
        });
        
        $serviceManager->get('viewhelpermanager')->setFactory('parsePageContent', function($sm) {
            return new ParsePageContent();
        });
        
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
	        'Zend\Loader\ClassMapAutoloader' => array(
	            __DIR__ . '/autoload_classmap.php',
	        ),
 
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/mvc/',
                ),
            ),
        );
    }

}
