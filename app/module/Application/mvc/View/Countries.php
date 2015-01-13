<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;
use Application\Model\ConfigCache as Cache;

class Countries extends AbstractHelper
{ 
    
    public function __construct($adapter) {
        $this->adapter = $adapter;
    }
    
    public function __invoke($name = 'country', $classname = 'inputbox', $columnName = '', $columnValue = '') {
        
        $countries = new \Application\Model\Countries($this->adapter);
        if ($columnName) {
            return $countries->getCountriesByColumn($columnName, $columnValue);
        }
        
        $cache = new Cache();
        if (!$result_html = $cache->getItem('countries_html')) {
            $getAllCountries = $countries->getCountries();
            
            $result_html = '<option value="">Select country</option>';
            foreach($getAllCountries as $country){
                $result_html .= '<option value="'.$country['country_id'].'">'.$country['country'].'</option>';
            }
            
            $cache->setItem('countries_html', $result_html);
        }

        return '<select name="'.$name.'" id="'.$name.'" class="'.$classname.'">'.$result_html.'</select>';
    
    }
    
}