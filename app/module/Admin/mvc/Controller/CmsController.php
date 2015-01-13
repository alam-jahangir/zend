<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Autheticate;
use Admin\Model\Validation;


class CmsController extends AbstractActionController 
{
    
    /**
     * @var Object
     */
    protected $userinfo;
    
    /**
     * @var array
     */
     
     protected $config;  
     
     /**
      * @var Zend\Db\Adapter\Adapter
      */
     private $_dbAdapter;
    
    /**
     * Initilize Config, Db Adapter
     * Load User Login Information
     */
    private function _initialize($isAjax = 0) 
    {
        $this->config = $this->getServiceLocator()->get('Config');
        $this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $auth = new Autheticate($this->config, $this->config['session']['admin']);
        $this->userinfo = $auth->getIdentity();
        if ($this->userinfo) {
        	$this->layout('layout/admin_layout' );
            return true;
        } else {
            if ($isAjax)
                return '';
             
			 return $this->redirect()->toRoute('admin_login');
        }
    }
    
   
    
    /**
     * Show Cms Page List
     */
    public function pageAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
        $cmsPagesList = $cmsPages->getData(0);
        
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($cmsPagesList, $page, $this->config);
        
        //Get Message From FlashMessenger
        $message = $this->flashMessenger()->getMessages();
        
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'Cms Page');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    /**
     * Add New Cms Page
     */
    public function newPageAction() 
    {
        $this->_initialize();
        
        $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        $id = $matches->getParam('id', 0);
        $pageInfo = array();
        if ($id)
            $pageInfo = $cmsPages->getData($id)->toArray();

        $message = $this->flashMessenger()->getMessages();
         $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $message = $this->_savePage($data, $id);
            
            if (is_array($message) && isset($message[0]['error'])) {
                $pageInfo[0] = $data->toArray();
                $pageInfo[0]['id'] = $id;
            }
            
        }
        return new ViewModel(array(
            'title' => isset($pageInfo[0]['page_id']) ? 'Edit Page - '.$pageInfo[0]['page_title']: 'Add New Page',
            'userinfo' => $this->userinfo,
            'editPageInfo' => $pageInfo,
            'flashMessages' => $message
        ));
        
    }
    
    /**
     * Edit Cms Page Information
     */
    public function editPageAction() 
    {
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch('Admin\Controller\Cms', array('action' => 'new-page', 'id' => $matches->getParam('id', 0)));
    }
    
    /**
     * Mass Status Change of Cms Page
     * Mass Delete
     * Mass Role Change
     */ 
    public function massPageModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $cmsPageIds = explode(',', $data['ids']);
            $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
            switch($data['action']) {
                case 'delete':
                    foreach ($cmsPageIds as $cmsPageId) {
                        $cmsPages->deleteData(intval($cmsPageId));
                    }
                    $this->flashMessenger()->addMessage(array('success' => 'Deleted successfully'));
                    break;
                case 'status':
                    foreach ($cmsPageIds as $cmsPageId) {
                        $saveData['is_active'] =  intval($data['status']);
                        $cmsPageId = intval($cmsPageId);
                        if ($cmsPageId)
                            $cmsPages->setData($cmsPageId, $saveData);
                    }
                    $this->flashMessenger()->addMessage(array('success' => 'Saved successfully'));
                    break;
            }
            
            return $this->redirect()->toRoute('cms', array('action' => 'page'));
        } else {
            throw new \Exception('Invalid request');
        }
    }

    
    /**
     * Save Cms Page Information
     * @param   $data       array|object
     * @param   $cmsPageId  int
     */
    private function _savePage($data = null, $cmsPageId = 0) 
    {
        
        $validation = new Validation();
        if ($validation->isValidPageData($data)) {
            $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
            
            if (!$cmsPageId) {
                $data->created_date = date('Y-m-d h:i:s');
                $data->updated_date = date('Y-m-d h:i:s');
            } else {
                $data->updated_date = date('Y-m-d h:i:s');
            }
            $data->page_identifier = str_replace(' ', '-', strtolower($data->page_identifier));
            
            if ($cmsPages->setData($cmsPageId, $data->toArray())) {
                $this->flashMessenger()->addMessage(array('success' => 'Successfully saved'));
                return $this->redirect()->toRoute('cms', array('action' => 'page'));
            } else {
                $message[0] = array('error' => 'Failed to save data. Please try again.');
                return $message;
            }
        } else {
            $message[0] = array('error' => $validation->message);
            return $message;
        }
        
    }
    
    
    
    /**
     * Show Cms Block List
     */
    public function blockAction() 
    {
        $this->_initialize();
        $matches = $this->getEvent()->getRouteMatch();
        
	    $cmsBlocks = new \Admin\Model\CmsBlock($this->_dbAdapter);
        $cmsBlocksList = $cmsBlocks->getData(0);
    	$page = $matches->getParam('page', 1);
    	
        $paginator = \Application\Model\Pagination::loadPaginator($cmsBlocksList, $page, $this->config);
        $message = $this->flashMessenger()->getMessages();
        
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'CMS Block');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    /**
     * Add New Cms Block
     */
    public function newBlockAction() 
    {
        $this->_initialize();
        
		$cmsBlocks = new \Admin\Model\CmsBlock($this->_dbAdapter);
        $matches = $this->getEvent()->getRouteMatch();
        
        $blockid = $matches->getParam('id', 0);
        $editBlockInfo = array();
        if ($blockid) 
            $editBlockInfo = $cmsBlocks->getData($blockid)->toArray();
            
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $message = $this->_saveBlock($data, $blockid);
            if (is_array($message) && isset($message[0]['error'])) {
                $editBlockInfo[0] = $data->toArray();
                $editBlockInfo[0]['block_id'] = $blockid;
            }
            
        }
        
        return new ViewModel(array(
            'title' => isset($editBlockInfo[0]['block_id']) ? 'Edit CMS Block- '.$editBlockInfo[0]['block_title']: 'Add New Block',
            'userinfo' => $this->userinfo,
            'editBlockInfo' => $editBlockInfo,
            'flashMessages' => $message
        ));
        
    }
    
    /**
     * Edit Cms Block Information
     */
    public function editBlockAction() 
    {
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch('Admin\Controller\Cms', array('action' => 'new-block', 'id' => $matches->getParam('id', 0)));
    }
    
    /**
     * Mass Status Change of Cms Block
     * Mass Delete
     * Mass Role Change
     */ 
    public function massBlockModificationAction() 
    {
    	
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $cmsBlockIds = explode(',', $data['blockids']);
            $cmsBlocks = new \Admin\Model\CmsBlock($this->_dbAdapter);
            switch($data['action']) {
                case 'delete':
                    foreach ($cmsBlockIds as $cmsBlockId) {
                        $cmsBlocks->deleteData(intval($cmsBlockId));
                    }
                    $this->flashMessenger()->addMessage(array('success' => 'Deleted successfully'));
                    break;
                case 'status':
                	foreach ($cmsBlockIds as $cmsBlockId) {
                        $saveData['block_status'] =  intval($data['status']);
                        $cmsBlockId = intval($cmsBlockId);
                        if ($cmsBlockId)
                            $cmsBlocks->setData($cmsBlockId, $saveData);
                    }
                    $this->flashMessenger()->addMessage(array('success' => 'Saved successfully'));
                    break;
            }
            return $this->redirect()->toRoute('cms', array('action' => 'block'));
        } else {
            throw new \Exception('Invalid request');
        }
    }

    
    /**
     * Save Cms Block Information
     * @param   $data       array|object
     * @param   $cmsBlockId int
     */
    private function _saveBlock($data = null, $cmsBlockId = 0) 
    {
        
        $validation = new Validation();
        if ($validation->isValidBlockData($data)) {
            $cmsBlocks = new \Admin\Model\CmsBlock($this->_dbAdapter);
            
            if (!$cmsBlockId) {
                $data->created_date = date('Y-m-d h:i:s');
                $data->updated_date = date('Y-m-d h:i:s');
            } else {
                $data->updated_date = date('Y-m-d h:i:s');
            }
            
            if ($cmsBlockId = $cmsBlocks->setData($cmsBlockId, $data->toArray())) {
                $this->flashMessenger()->addMessage(array('success' => 'Saved successfully'));
                return $this->redirect()->toRoute('cms', array('action' => 'block'));
            } else {
                $message[0] = array('error' => 'Failed to save data. Please try again.');
                return $message;
            }
        } else {
            $message[0] = array('error' => $validation->message);
            return $message;
        }
        
    }
    
}