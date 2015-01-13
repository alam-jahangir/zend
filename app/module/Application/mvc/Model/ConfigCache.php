<?php

namespace Application\Model;

/**
 * Data Cache Class
 * ConfigCache
 */
class ConfigCache {
    
    private $_cache;
    
    public function __construct() {
        $this->_cache = \Zend\Cache\StorageFactory::factory(array(
	            'adapter' => array(
                    'name'    => 'filesystem'
                ),
	            'plugins' => array(
	                'exception_handler' => array('throw_exceptions' => false),
	                'serializer'
	            )
         ));
	    $this->_cache->setOptions(array(
            'cache_dir' => './var/cache',
            'dir_level' => 2
        ));
        $this->_cache->getOptions()->setNamespace('cache');
    }
    
    public function setItem($key = '', $data = null) {
        $this->_cache->setItem($key, $data);
    }
    
    public function removeItem($key = '') {
        try {
            $this->_cache->removeItem($key);
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getItem($key = '') {
        $result = $this->_cache->getItem($key, $success);
        if (!$success) {
            return array();
        } 
        return $result;
    }
    
    
    public function removeItems($key = array()) {
        try {
            $this->_cache->removeItems(array($key));     
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
    }
}