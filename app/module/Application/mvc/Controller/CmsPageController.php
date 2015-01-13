<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CmsPageController extends AbstractActionController 
{
   
     
     /**
      * @var Zend\Db\Adapter\Adapter
      */
     private $_dbAdapter;
     
     
     /**
     * Initilize Config, Db Adapter
     * Load User Login Information
     */
    private function _initialize() 
    {
        $this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->config = $this->getServiceLocator()->get('Config');        
    }
     
    
    
    
    /**
     * How to page
     */
    public function indexAction() 
    {
        
        $this->_initialize();
        
        $message = array();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        $identifier = $matches->getParam('identifier', '');
        
        $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
        $cmsPage = $cmsPages->getDataByIdentifier($identifier)->toArray();
        
        
        if (!$cmsPage) {
            $this->getResponse()->setStatusCode(404);
            return; 
        }
        return new ViewModel(
            array(
                'title' => $cmsPage[0]['page_title'],
                //'userinfo' => $this->userinfo,
                'page' => $cmsPage,
                'flashMessages' => $message
            )
        );
    }
    
}
