<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Autheticate;
use Admin\Model\Validation;
use Application\Image\Resize as ImageResize;


class ProductController extends AbstractActionController 
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
    public function categoryAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $categories = new \Admin\Model\Categories($this->_dbAdapter);
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
        
        $categories = new \Admin\Model\Categories($this->_dbAdapter);
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
        $categoryid = $matches->getParam('id', 0);
        $editCategoryInfo = array();
        if ($categoryid)
            $editCategoryInfo = $categories->getData($categoryid)->toArray();
            
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        //Request Object
        $request = $this->getRequest();
        //Check is Form Post.
        if ($request->isPost()) {
        	
            $data = $request->getPost();
            $data->submenu_item = implode(',', $data->submenu_item);
            $data->identifier = \Application\Model\GenerateIdentifier::formatURL($data->category_name);
            $file = $this->params()->fromFiles('image');
            $filename = $this->_saveImage('category', $file, 35, 35);
            if ($filename) {
				$data->image = $filename;
			}
			
			if (!(int)$data->parent_id) {
				unset($data->parent_id);
				$data->parent_id = null;
			} else {
				$data->parent_id = (int)$data->parent_id;
			}
				
            $message = $this->_saveCategory($data, $categoryid);
            
            if (is_array($message) && isset($message[0]['error'])) {
                $editCategoryInfo[0] = $data->toArray();
                $editCategoryInfo[0]['category_id'] = $categoryid;
            }
            
        }
        
        $model = new \Admin\Model\Models($this->_dbAdapter);
        $modelList = $model->getData(0);
        
        $prentCategories = $categories->getData(0, 0, 0, 1);
        
        return new ViewModel(array(
            'title' => isset($editCategoryInfo[0]['category_id']) ? 'Edit Category- '.$editCategoryInfo[0]['category_name'] : 'Add New Category',
            'userinfo' => $this->userinfo,
            'id' => $categoryid,
            'editCategoryInfo' => $editCategoryInfo,
            'model' => $modelList,
            'prentCategories' => $prentCategories,
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
			'Admin\Controller\Product', 
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
            $categories = new \Admin\Model\Categories($this->_dbAdapter);
            switch($data['action']) {
                case 'delete':
                    foreach ($categoryids as $categoryid) {
                        $categories->deleteData(intval($categoryid));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    foreach ($categoryids as $categoryid) {
                        $saveData['is_active'] =  intval($data['status']);
                        $categoryid = intval($categoryid);
                        if ($categoryid)
                            $categories->setData($categoryid, $saveData);
                    }
                    
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
            }
            
            return $this->redirect()->toRoute('product', array('action' => 'category'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
    }

    
    /**
     * Save Admin User Information
     * @param   $data       array | object
     * @param   $ctegoryid  int
     */
    private function _saveCategory($data = null, $ctegoryid = 0) 
    {
        
        $validation = new Validation();
        if ($validation->isValidCategoryData($data)) {
            $categories = new \Admin\Model\Categories($this->_dbAdapter);
            
            if (!$ctegoryid) {
                $data->created_date = date('Y-m-d h:i:s');
                $data->updated_date = date('Y-m-d h:i:s');
            } else {
                $data->updated_date = date('Y-m-d h:i:s');
            }
            
            if ($categories->setData($ctegoryid, $data->toArray())) {
                $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                return $this->redirect()->toRoute('product', array('action' => 'category'));
            } else {
                $message[0] = array('error' => $this->config['message']['failed_save_data']);
                return $message;
            }
        } else {
            $message[0] = array('error' => $validation->message);
            return $message;
        }
        
    }
    
    private function _saveImage($mediaDir = '', $file, $resizeWidth = 35, $resizeHeight = 35)
    {
    	$filename = '';
		if ($file['name']) {
            $size = new \Zend\Validator\File\Size(array('min'=> 1000, 'max' => 5000000));
            $extension = new \Zend\Validator\File\Extension(array('jpg', 'jpeg', 'png', 'gif'));
            $adapter = new \Zend\File\Transfer\Adapter\Http(); 
            $adapter->setValidators(array($size, $extension), $file['name']);
            if (!$adapter->isValid()){
                $dataError = $adapter->getMessages();
                $error = '';
                foreach($dataError as $key => $row) {
                    $error .= $row.'. ';
                } 
                $this->flashMessenger()->addMessage(array('error' => $error));
            } else {
                $adapter->setDestination(BASE_PATH.DS.'media'.DS.$mediaDir);
                $filename = round(microtime(true) * 1000).str_replace(array(' ', '/', DS, '*', '%', '$', '&'), '', $file['name']);
                $filename = str_replace(
					array(' ','/',DS,'*','%','$','&','+','.','#','@','!','~','?','[',']','{','}',')','(','"',',',':',';',','), 
					'', 
					$filename
				);
                $adapter->addFilter('Rename', $filename);
                if ($adapter->receive($file['name'])) {
                    $imageResize = new ImageResize();
                    $imageResize->setResizeOption(array(
                        'width' => $resizeWidth,
                        'height' => $resizeHeight,
                        'keepRatio' => false,
                    ));
                    $imageResize->filter('media'.DS.$mediaDir.DS.$filename);
                }
            }
        }
        return $filename;
	}
    
    
    /**
     * Show Product List
     */
    public function listAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $products = new \Admin\Model\Products($this->_dbAdapter);
        $productList = $products->getData(0);
    
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($productList, $page, $this->config);
        
        //Get Message From FlashMessenger
        $message = $this->flashMessenger()->getMessages();
        
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'Products');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    /**
     * Add New Category
     */
    public function newProductAction() 
    {
        $this->_initialize();
    
        $products = new \Admin\Model\Products($this->_dbAdapter);        
        $matches = $this->getEvent()->getRouteMatch();
        
        $productid = $matches->getParam('id', 0);
        $productInfo = array();
        if ($productid) {
            $productInfo = $products->getData($productid)->toArray();
        }
         
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            
            $file = $this->params()->fromFiles('image');
            $filename = $this->_saveImage('product', $file, 35, 35);
            if ($filename) {
				$data->image = $filename;
			}
			
            $message = $this->_saveProduct($data, $productid);
            
            if (is_array($message) && isset($message[0]['error'])) {
                $productInfo[0] = $data->toArray();
                $productInfo[0]['id'] = $productid;
            }
            
        }
        
        $model = new \Admin\Model\Models($this->_dbAdapter); 
        $modelData = $model->getData();
        
        $brand = new \Admin\Model\Brands($this->_dbAdapter); 
        $brandData = $brand->getData();
        
        $productOptionGroup = new \Admin\Model\ProductOptionGroup($this->_dbAdapter);
        $optionGroupList = $productOptionGroup->getData(0);
        
        $productPrice = new \Admin\Model\ProductPrice($this->_dbAdapter);
        $productPriceList = $productPrice->getData($productid);
        
        $shape = new \Admin\Model\Shape($this->_dbAdapter);
        $shapeList = $shape->getData();
        
        
        $categories = new \Admin\Model\Categories($this->_dbAdapter);
        $categoryList = $categories->getData(0);
        
        return new ViewModel(array(
            'title' => isset($productInfo[0]['id']) ? 'Edit Product - '.$productInfo[0]['name'] : 'Add New Product',
            'userinfo' => $this->userinfo,
            'editProductInfo' => $productInfo,
            'flashMessages' => $message,
            'brand' => $brandData,
            'model' => $modelData,
            'category' => $categoryList,
            'optionGroup' => $optionGroupList,
            'shapeList' => $shapeList,
            'productPrice' => $productPriceList,
            'productid' => $productid
        ));
        
    }
    
    /**
     * Edit Category Information
     */
    public function editProductAction() 
    {
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch(
			'Admin\Controller\Product', 
			array(
				'action' => 'new-product', 
				'id' => $matches->getParam('id', 0)
			)
		);
    }
    
    /**
     * Mass Status Change of Admin
     * Mass Delete
     * Mass Role Change
     */ 
    public function massProductModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $productids = explode(',', $data['productids']);
            $products = new \Admin\Model\Products($this->_dbAdapter);        
  
		    switch($data['action']) {
                case 'delete':
                    foreach ($productids as $productid) {
                        $products->deleteData(intval($productid));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    if (intval($data['status'])) {
                        foreach ($productids as $productid) {
                            $saveData['status'] =  intval($data['status']);
                            $productid = intval($productid);
                            if ($productid)
                                $products->setData($productid, $saveData, array(), 1);
                        }
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
            }
            return $this->redirect()->toRoute('product', array('action' => 'list'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
    }
    

    
    /**
     * Save Admin User Information
     * @param   $data       array|object
     * @param   $productid  int
     */
    private function _saveProduct($data = null, $productid = 0) 
    {
        $validation = new Validation();
        if ($validation->isValidProductData($data)) {
            $products = new \Admin\Model\Products($this->_dbAdapter);        
             
            if (!(int)$data->brand_id) {
				unset($data->brand_id);
			}
			
			if (!(int)$data->model_id) {
				unset($data->model_id);
			}
		      
            if (!$productid) {
            	$data->created_by = $this->userinfo->user_id;
                $data->created_date = date('Y-m-d h:i:s');
                $data->updated_date = date('Y-m-d h:i:s');
            } else {
            	$data->modified_by = $this->userinfo->user_id;
                $data->updated_date = date('Y-m-d h:i:s');
            }
            
            
            
			$productPrice = array();
            if (isset($data->shape_id)) {
            	
                foreach($data->shape_id as $key => $value) {
                   $productPrice[] = array(
						'shape_id' => $value,
						'group_id' => (isset($data->group_id[$key]) && $data->type == 2) ? (int)$data->group_id[$key] : 1,
						'option_name' => (isset($data->option_name[$key])  && $data->type == 2) ? $data->option_name[$key] : '',
						'price' => isset($data->price[$key]) ? $data->price[$key] : '0.00',
					);
                }
                
                unset($data->shape_id);
                unset($data->group_id);
                unset($data->option_name);
                unset($data->price);
            }
            
			if ($productid = $products->setData($productid, $data->toArray(), $productPrice)) {
                $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                return $this->redirect()->toRoute('product', array('action' => 'list'));
            } else {
                $message[0] = array('error' => $this->config['message']['failed_save_data']);
                return $message;
            }
        } else {
            $message[0] = array('error' => $validation->message);
            return $message;
        }
        
    }
    
}