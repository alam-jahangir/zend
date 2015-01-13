<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;
use Application\Model\ConfigCache as Cache;

class Settings extends AbstractHelper
{ 
    
    public function __construct($adapter) 
	{
        $this->adapter = $adapter;
    }
    
    public function __invoke($value = '') {
        $cache = new Cache();
        if (!$result = $cache->getItem('settings_'.$value)) {
        	$settings = new \Admin\Model\Settings($this->adapter);
	        $rowset = $settings->getDataByColumn('code', $value);
	        $result = isset($rowset->value) ? $rowset->value : '';
	        $cache->setItem('settings_'.$value, $result);
	    }
	    return $result;
		    	
	}
    
}