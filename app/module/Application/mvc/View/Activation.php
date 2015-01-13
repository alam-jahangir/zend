<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;

class Activation extends AbstractHelper
{ 
    
    public function __construct($adapter) {
        $this->adapter = $adapter;
    }
    
    public function __invoke($dealerId = 0) {
        $user = new \Admin\Model\Users($this->adapter);
		$userDetails = $user->getData($dealerId)->toArray();
		if (isset($userDetails[0]['is_subscribe']) && $userDetails[0]['is_subscribe']) {
			return 1;
		}
		return 0;
    }
}