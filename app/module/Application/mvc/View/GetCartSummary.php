<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;

class GetCartSummary extends AbstractHelper
{ 
    
    public function __construct($adapter) {
        $this->adapter = $adapter;
    }
    
    public function __invoke($dealerId = 0) {
        $dealerUpload = new \Admin\Model\DealerUpload($this->adapter);
        return $dealerUpload->getCartSummary($dealerId);
    }
    
}