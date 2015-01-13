<?php
namespace Application\View;
use Zend\View\Helper\AbstractHelper;
use Application\Model\Autheticate;

class Authenticate  extends AbstractHelper
{ 
    
    protected $dbAdapter;
    
    protected $sessionConfig;
    
    protected $route;
    
    
    /**
     * @param   $dbAdapter      \Zend\Db\Adapter\Adapter 
     * @param   $sessionConfig  \Zend\Session\Config\SessionConfig
     */
    public function __construct($dbAdapter, $sessionConfig, $route) 
	{
       $this->dbAdapter = $dbAdapter;
       $this->sessionConfig = $sessionConfig;
       $this->route = $route;
    }
    
    public function __invoke($storageName = 'user') 
	{
        $auth = new Autheticate($this->sessionConfig, $storageName);
        $userinfo = $auth->getIdentity();
        if ($userinfo) {
			return $userinfo;
		}
        return null;
    }
    
}