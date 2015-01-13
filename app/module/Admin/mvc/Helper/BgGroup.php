<?php
namespace Admin\Helper;
use Zend\View\Helper\AbstractHelper;

class BgGroup extends AbstractHelper
{ 
    private $_bgGroup = array(
        '1' => 'O<sub>+</sub>', 
        '2' => 'A<sub>+</sub>',
        '3' => 'B<sub>+</sub>',
        '4' => 'AB<sub>+</sub>',
        '5' => 'O<sub>-</sub>',
        '6' => 'A<sub>-</sub>',
        '7' => 'B<sub>-</sub>',
        '8' => 'AB<sub>-</sub>'
    ); 
    
    public function __invoke($bg = 0) 
    {
        if (!$bg) {
            return $this->_bgGroup;
        }
        return isset($this->_bgGroup[$bg]) ? $this->_bgGroup[$bg] : '';
    }
    
}