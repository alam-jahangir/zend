<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Autheticate;
use Admin\Model\Validation;
use Application\Image\Resize as ImageResize;


class CategoryController extends AbstractActionController 
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
     * Show Category List
     */
    public function indexAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $categories = new \Admin\Model\Category($this->_dbAdapter);
        $categoryList = $categories->getData(0);
    
        $this->layout( 'layout/admin_layout' );
        
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($categoryList, $page, $this->config);
        
        //Get Message From FlashMessenger
        $message = $this->flashMessenger()->getMessages();
        
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'Categories');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    /**
     * Add New Category
     */
    public function newCategoryAction() 
    {
        $this->_initialize();
        
        $category = new \Admin\Model\Category($this->_dbAdapter);
        $matches = $this->getEvent()->getRouteMatch();
        
        $categoryid = $matches->getParam('id', 0);
        $editCategoryInfo = array();
        if ($categoryid)
            $editCategoryInfo = $category->getData($categoryid)->toArray();
            
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
        	$data = $request->getPost();
            $data->name = trim(strip_tags($data->name));
            if ($data->name != '') {
	            if (!$categoryid) {
	                $data->created_date = date('Y-m-d h:i:s');
	                $data->updated_date = date('Y-m-d h:i:s');
	            } else {
	                $data->updated_date = date('Y-m-d h:i:s');
	            }
	            
	            if ($category->setData($categoryid, $data->toArray())) {
	                $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
	                return $this->redirect()->toRoute('category', array('action' => 'index'));
	            } else {
	                $message[0] = array('error' => $this->config['message']['failed_save_data']);
	            }
	        } else {
	            $message[0] = array('error' => 'Category name is required');
	        }
	        
            if (is_array($message) && isset($message[0]['error'])) {
                $editCategoryInfo[0] = $data->toArray();
                $editCategoryInfo[0]['id'] = $categoryid;
            }
        }
        
        return new ViewModel(array(
            'title' => isset($editCategoryInfo[0]['id']) ? 'Edit Category- '.$editCategoryInfo[0]['name'] : 'Add New Category',
            'userinfo' => $this->userinfo,
            'id' => $categoryid,
            'category' => $editCategoryInfo,
            'flashMessages' => $message
        ));
        
    }
    
    /**
     * Edit Category Information
     */
    public function editCategoryAction() 
    {

        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch(
			'Admin\Controller\Category', 
			array(
				'action' => 'new-category', 
				'id' => $matches->getParam('id', 0)
			)
		);
		
    }
    
    /**
     * Mass Status Change of Admin
     * Mass Delete
     * Mass Role Change
     */ 
    public function massCategoryModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $categoryids = explode(',', $data['categoryids']);
            $category = new \Admin\Model\Category($this->_dbAdapter);
            switch($data['action']) {
                case 'delete':
                    foreach ($categoryids as $categoryid) {
                        $category->deleteData(intval($categoryid));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    foreach ($categoryids as $categoryid) {
                        $saveData['is_active'] =  intval($data['status']);
                        $categoryid = intval($categoryid);
                        if ($categoryid)
                            $category->setData($categoryid, $saveData);
                    }
                    
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
            }
            
            return $this->redirect()->toRoute('category', array('action' => 'index'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
        
    }

    
    /**
     * Show Attribute List
     */
    public function attributeAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $attribute = new \Admin\Model\Attribute($this->_dbAdapter);
        $attributeList = $attribute->getData(0);
    
        $this->layout( 'layout/admin_layout' );
        
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($attributeList, $page, $this->config);
        
        //Get Message From FlashMessenger
        $message = $this->flashMessenger()->getMessages();
        
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'Attribute List');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    /**
     * Add New Attribute
     */
    public function newAttributeAction() 
    {
        $this->_initialize();
    
        $attribute = new \Admin\Model\Attribute($this->_dbAdapter);
		 
        $attributeGroup = new \Admin\Model\AttributeGroup($this->_dbAdapter);
        $groupList = $attributeGroup->getData(0);
        
		$matches = $this->getEvent()->getRouteMatch();
        $id = $matches->getParam('id', 0);
        
		$attributeInfo = array();
		$optionData = array();
		if ($id) {
            $attributeInfo = $attribute->getData($id)->toArray();
            $optionData = $attribute->getOptionData($id);
        }
         
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $optionList = $data->option_name;
            $subItemList = $data->subitem;
            
            unset($data->option_name);
            unset($data->subitem);
            
            $data->name = trim(strip_tags($data->name));
            if ($data->name != '') {
	            if (!$id) {
	                $data->created_date = date('Y-m-d h:i:s');
	                $data->updated_date = date('Y-m-d h:i:s');
	            } else {
	                $data->updated_date = date('Y-m-d h:i:s');
	            }
	  
	            if ($id = $attribute->setData($id, $data->toArray())) {
	            	$attribute->deleteAttributeOption($id);
	            	if ($data->input_type == 1) {
		            	foreach ($optionList as $key => $option) {
							$optionSaveData['attribute_id'] = $id;
							$optionSaveData['option_name'] = trim(strip_tags($option));
							$optionSaveData['option_index'] = $key+1;
							$optionSaveData['subitem'] = $subItemList[$key];
							if ($optionSaveData['option_name'] != '') {
								$attribute->setAttributeOption($optionSaveData);
							}
						}
					}
	            	$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
	                return $this->redirect()->toRoute('category', array('action' => 'attribute'));
	            } else {
	                $message[0] = array('error' => $this->config['message']['failed_save_data']);
	            }
	        } else {
	            $message[0] = array('error' => 'Attribute name is required');
	        }
            
            if (is_array($message) && isset($message[0]['error'])) {
            	$data->sub_level = unserialize($data->subitem);
                $attributeInfo[0] = $data->toArray();
                $attributeInfo[0]['id'] = $id;
            }
            
        }
        
        return new ViewModel(array(
            'title' => isset($attributeInfo[0]['id']) ? 'Edit Attribute - '.$attributeInfo[0]['name'] : 'Add New Attribute',
            'userinfo' => $this->userinfo,
            'attribute' => $attributeInfo,
            'flashMessages' => $message,
            'groupList' => $groupList,
            'optionData' => $optionData
        ));
        
    }
    
    /**
     * Edit Attribute Information
     */
    public function editAttributeAction() 
    {
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch(
			'Admin\Controller\Category', 
			array(
				'action' => 'new-attribute', 
				'id' => $matches->getParam('id', 0)
			)
		);
    }
    
    /**
     * Mass Status Change of Admin
     * Mass Delete
     * Mass Role Change
     */ 
    public function massAttributeModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $attributeids = explode(',', $data['attributeids']);
            $attribute = new \Admin\Model\Attribute($this->_dbAdapter);        
  
		    switch($data['action']) {
                case 'delete':
                    foreach ($attributeids as $attributeid) {
                        $attribute->deleteData(intval($attributeid));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    //if (intval($data['status'])) {
                    foreach ($attributeids as $attributeid) {
                        $saveData['is_active'] =  intval($data['status']);
                        $attributeid = intval($attributeid);
                        if ($attributeid)
                            $attribute->setData($attributeid, $saveData);
                    }
                    //}
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
            }
            return $this->redirect()->toRoute('category', array('action' => 'attribute'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
    }
    
    
    /**
     * Show Attribute List
     */
    public function attributeGroupAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $attribute = new \Admin\Model\AttributeGroup($this->_dbAdapter);
        $attributeList = $attribute->getData(0);
    
        $this->layout( 'layout/admin_layout' );
        
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($attributeList, $page, $this->config);
        
        //Get Message From FlashMessenger
        $message = $this->flashMessenger()->getMessages();
        
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'Attribute Group List');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    /**
     * Add New Attribute
     */
    public function newAttributeGroupAction() 
    {
        $this->_initialize();
    
        $attribute = new \Admin\Model\AttributeGroup($this->_dbAdapter);
		$category = new \Admin\Model\Category($this->_dbAdapter);      
		$categoryList = $category->getData(0)->toArray();
		        
        $matches = $this->getEvent()->getRouteMatch();
        
        $id = $matches->getParam('id', 0);
        $attributeInfo = array();
        $groupOptions = array();
        if ($id) {
            $attributeInfo = $attribute->getData($id)->toArray();
            $attributeGroupOption = $attribute->getAttributeGroupOption($id);
            foreach ($attributeGroupOption as $option) {
				$groupOptions[$option['option_index']] = $option['option_name'];
			}
        }
         
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $optionList = $data->option_name;
            unset($data->option_name);
            $data->name = trim(strip_tags($data->name));
            if ($data->name != '') {
	            if (!$id) {
	                $data->created_date = date('Y-m-d h:i:s');
	                $data->updated_date = date('Y-m-d h:i:s');
	            } else {
	                $data->updated_date = date('Y-m-d h:i:s');
	            }
	  
	            if ($id = $attribute->setData($id, $data->toArray())) {
	            	$attribute->deleteAttributeGroupOption($id);
	            	foreach ($optionList as $key => $option) {
						$optionData['group_id'] = $id;
						$optionData['option_index'] = $key+1;
						$optionData['option_name'] = trim(strip_tags($option));
						if ($optionData['option_name'] != '') {
							$attribute->setAttributeGroupOption($optionData);
						}
					}
	            	$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
	                return $this->redirect()->toRoute('category', array('action' => 'attribute-group'));
	            } else {
	                $message[0] = array('error' => $this->config['message']['failed_save_data']);
	            }
	        } else {
	            $message[0] = array('error' => 'Attribute name is required');
	        }
            
            if (is_array($message) && isset($message[0]['error'])) {
            	$attributeInfo[0] = $data->toArray();
                $attributeInfo[0]['id'] = $id;
                $groupOptions = $optionList;
            }
            
        }
        
        return new ViewModel(array(
            'title' => isset($attributeInfo[0]['id']) ? 'Edit Attribute Group - '.$attributeInfo[0]['name'] : 'Add New Attribute Group',
            'userinfo' => $this->userinfo,
            'attribute' => $attributeInfo,
            'groupOptions' => $groupOptions,
            'categoryList' => $categoryList,
            'flashMessages' => $message
        ));
        
    }
    
    /**
     * Edit Attribute Information
     */
    public function editAttributeGroupAction() 
    {
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch(
			'Admin\Controller\Category', 
			array(
				'action' => 'new-attribute-group', 
				'id' => $matches->getParam('id', 0)
			)
		);
    }
    
    /**
     * Mass Status Change of Admin
     * Mass Delete
     * Mass Role Change
     */ 
    public function massAttributeGroupModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $attributeids = explode(',', $data['attributeids']);
            $attribute = new \Admin\Model\AttributeGroup($this->_dbAdapter);        
  
		    switch($data['action']) {
                case 'delete':
                    foreach ($attributeids as $attributeid) {
                        $attribute->deleteData(intval($attributeid));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    //if (intval($data['status'])) {
                    foreach ($attributeids as $attributeid) {
                        $saveData['is_active'] =  intval($data['status']);
                        $attributeid = intval($attributeid);
                        if ($attributeid)
                            $attribute->setData($attributeid, $saveData);
                    }
                    //}
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
            }
            return $this->redirect()->toRoute('category', array('action' => 'attribute-group'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
    }
    
    public function attributeGroupOptionAction()
    {
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$this->_initialize(1);
			$id = isset($_GET['gid']) ? intval($_GET['gid']) : 0;
	        $oid = isset($_GET['oid']) ? intval($_GET['oid']) : 0;
	        $attribute = new \Admin\Model\AttributeGroup($this->_dbAdapter);
	        $option = $attribute->getAttributeGroupOption($id);
	        $viewModel = new ViewModel(
	            array(
	                'option' => $option,
	                'oid' => $oid
	            )
	        );
	
	        $viewModel->setTerminal(true);
	        return $viewModel;
		} else {
			throw new \Exception($this->config['message']['invalid_request']);
		}
	}
    

}