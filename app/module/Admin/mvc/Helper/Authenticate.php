<?php
namespace Admin\Helper;
use Zend\View\Helper\AbstractHelper;
use Admin\Acl\Acl;

class Authenticate  extends AbstractHelper
{ 
    
    private $_sm;
    
    const AUTHORIZE = 200;
    
    const NOT_AUTHORIZE = 404;
    
    const NOT_LOGGEDIN = 302;
    
    
    public function __construct($app) {
       $this->_sm = $app->getServiceManager();
    }
    
    public function __invoke($userInfo) {
        
        $router = $this->_sm->get('router');
        $request = $this->_sm->get('request');
        $routeMatch = $router->match($request);
        $routerName = $routeMatch->getMatchedRouteName();
        
        if ($userInfo) {
            if (!Acl::doAuthorization(
                $userInfo->role_id,  
                $this->_sm->get('Zend\Db\Adapter\Adapter'), //Db Adapter
                $routerName, //router name
                $routeMatch->getParam('action', 'index'),
                $userInfo->role_code
            )) {
                throw new \Exception('Page not found', self::NOT_AUTHORIZE);
            }
        } else {
            return false;
        }
        return true;
    }
    
}