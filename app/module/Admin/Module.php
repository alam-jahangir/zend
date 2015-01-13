<?php

namespace Admin;
use Zend\Mvc\MvcEvent;
use Admin\Helper\BgGroup;
use Admin\Helper\PatientStatus;
use Admin\Helper\Religion;

class Module {

    public function onBootstrap($e)
    {
        $app = $e->getApplication();
        $serviceManager = $app->getServiceManager();
        
        $serviceManager->get('viewhelpermanager')->setFactory('getBloogGroup', function($sm) {
            return new BgGroup();
        });
        
        $serviceManager->get('viewhelpermanager')->setFactory('getPatientStatus', function($sm) {
            return new PatientStatus();
        });
        
        $serviceManager->get('viewhelpermanager')->setFactory('getReligion', function($sm) {
            return new Religion();
        });
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
    

    public function getConfig() 
    {
        return include __DIR__ . '/config/module.config.php';
    }
    

}
