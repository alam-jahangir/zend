<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Autheticate;

class AccountController extends AbstractActionController 
{
	
    /**
     * @var Session\Model\Autheticate
     */
    protected $_auth;
    
    
    /**
     * Logged In User Information
     */
     protected $userinfo;
     
     
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
        $this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->config = $this->getServiceLocator()->get('Config');
        
		$this->_auth = new Autheticate($this->config);
        $this->userinfo = $this->_auth->getIdentity();
        if ($this->userinfo) {
			return true;
		}
		if ($isAjax) {
			return true;
		}
		return $this->redirect()->toRoute('login');        
    }
     
    public function indexAction() 
    {
        
        $this->_initialize();
        
		$user = new \Admin\Model\Users($this->_dbAdapter);
        $address = new \Admin\Model\Address($this->_dbAdapter);
        
        $userDetails = array();
        $userAddress = array();
        
		$userDetails = $user->getData($this->userinfo->id)->toArray();
    	$userAddresses = $address->getData($this->userinfo->id)->toArray();
    	
        if ($userAddresses) {
			$userAddress[0] = $userAddresses[0];
		}
		//echo '<pre />'; print_r($userDetails); exit;
		return new ViewModel(
            array(
                'user' => $userDetails,
                'address' => $userAddress,
                'flashMessages' => $this->flashMessenger()->getMessages()
            )
        );
        
    }
    
    
    public function membershipChargeAction() 
    {
        
        $this->_initialize();
      	return new ViewModel(
            array(
            	'uid' => $this->userinfo->id,
                'flashMessages' => $this->flashMessenger()->getMessages()
            )
        );
        
    }
    
    public function editAction()
    {
    	
		$this->_initialize();
		$message = array();
        
		$user = new \Admin\Model\Users($this->_dbAdapter);
        $address = new \Admin\Model\Address($this->_dbAdapter);
        
		$id = $this->userinfo->id;
        $userAddress = array();
        $userDetails = $user->getData($this->userinfo->id)->toArray();
    	$userAddresses = $address->getData($this->userinfo->id)->toArray();
        if ($userAddresses) {
			$userAddress[0] = $userAddresses[0];
		}
   		
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            
            /** Address Validation **/
			$addresses[] = array(
				'address_type' => 1,'name' => $data->private_name,
				'surname' => $data->private_surname, 'street' => $data->private_street,
				'city' => $data->private_city, 'state' => $data->private_state,
				'zipcode' => $data->private_zipcode, 'phone' => $data->private_phone,
				'email' => $data->private_email, 'address_id' => intval($data->private_id)
			);
	
			$addresses[] = array(
				'address_type' => 2, 'name' => $data->business_name,
				'surname' => $data->business_surname, 'street' => $data->business_street,
				'city' => $data->business_city, 'state' => $data->business_state,
				'zipcode' => $data->business_zipcode, 'phone' => $data->business_phone,
				'email' => $data->business_email, 'address_id' => intval($data->business_id)
			);
			
			$personalData = array(
				'username' => $data->username, 'password' => $data->password,
				'email' => $data->email, 'is_active' => $data->is_active,
				'passport_no' => $data->passport_no, 'mobile_no' => $data->mobile_no,
				'tax_number' => $data->tax_number
			);	
			
			if ($id) {
                if ($data->email == $userDetails[0]['email']){
                    unset($personalData['email']);
                }
                if (isset($data->change_passowrd) && $data->change_passowrd && $data->password != ''){
                	$personalData['password'] = \Application\Model\GeneratePassword::generate($data->password);
                } else {
					unset($personalData['password']);
					$data->password = '';
				}
            } 
            
            $validation = new \Application\Model\Validation();
            $isNew = $id ? 0 : 1;
            if ($validation->isValidRegistrationData($personalData, $this->_dbAdapter , $isNew)) {
        		$addressError = 0;
				foreach ($addresses as $key => $addressData) {
					if ($validation->isValidAddress($addressData)) {
        				$addresses[$key] = $addressData;
					} else {
						$addressError = 1;
		                $message[] = array('error' => $validation->message);
					}
				}
				
				if (!$addressError) {
					if ($id = $user->setData($id, $personalData)) {
						foreach ($addresses as $addressData) {
							$addressData['user_id'] = $id;
							$address->setData($addressData['address_id'], $addressData);
						}
						$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
						return $this->redirect()->toRoute('account');
					} else {
						$this->flashMessenger()->addMessage(array('error' => $this->config['message']['failed_save_data']));
					}
				}
				
			} else {
                $message[] = array('error' => $validation->message);
                $this->flashMessenger()->addMessage(array('error' => $this->config['message']['failed_save_data']));
                $userDetails[0] = $personalData;               
            }
        }
        
		
        
		return new ViewModel(
            array(
            	'title' => 'Edit user - '.$userDetails[0]['username'],
                'userDetails' => $userDetails,
                'address' => $userAddress,
                'flashMessages' => $message
            )
        );
        
	}
	
	public function myUploadsAction() {
		$this->_initialize();
		
		$message = $this->flashMessenger()->getMessages();
		$matches = $this->getEvent()->getRouteMatch();
		
		$sortKey = isset($_GET['sort']) ? intval($_GET['sort']) : '';
        $sort = 'd.recommanded_price DESC';
        $cond = '';
        if ($sortKey == 1) {
			$sort = 'd.recommanded_price DESC';
		} elseif ($sortKey == 2) {
			$sort = 'd.recommanded_price ASC';
		} elseif ($sortKey == 3) {
			$sort = 'd.id DESC';
		} elseif ($sortKey == 4) {
			$sort = 'd.id ASC';
		} elseif ($sortKey == 5) {
			$cond = 'DATE_ADD( d.renew_date, INTERVAL 30 DAY )<=now( )';
			$sort = 'd.id DESC';
		}
		
		$dealerUpload = new \Admin\Model\DealerUpload($this->_dbAdapter);
        $uploadList = $dealerUpload->getDataByUserId($this->userinfo->id, $sort, $cond);
        
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($uploadList, $page, $this->config);
        
		return new ViewModel(
            array(
            	'title' => 'My Uploads',
            	'paginator' => $paginator,
                'flashMessages' => $message
            )
        );
	}
	
	public function uploadAction() {
		
		$userSession = new \Zend\Session\Container('dealer_upload_image');
		unset($userSession->filelist);
		
		$this->_initialize();
		
		$message = $this->flashMessenger()->getMessages();
		$matches = $this->getEvent()->getRouteMatch();
        $id = $matches->getParam('id', 0);
        
		$category = new \Admin\Model\Category($this->_dbAdapter);
        $categoryList = $category->getData(0, 1);
        
		$dealerUpload = new \Admin\Model\DealerUpload($this->_dbAdapter);
        $dealerUploadInfo = $dealerUpload->getData($id);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            
			$saveData = array();
            $group = explode('_', $data['option_id']);
			$saveData['user_id'] = $this->userinfo->id;
			$saveData['category_id'] = intval($data['category_id']);
			$saveData['group_id'] = intval($group[0]);
			$saveData['group_option_index'] = intval($group[1]);
			$saveData['year'] = $data['item_year'];
			$saveData['price_option'] = $data['price_option'];
			$saveData['currency'] = $data['currency'];
			$saveData['recommanded_price'] = intval($data['recommanded_price']);
			$saveData['dealer_price'] = intval($data['dealer_price']);
			$saveData['renew_date'] = date('Y-m-d H:i:s');
			if (!$id)
				$saveData['is_active'] = 1;
			
        	$file = $this->params()->fromFiles('video_filename');
	        if ($filename = $this->_saveImage('product', $file)) {
	        	$saveData['video_filename'] = $filename;
	        } elseif($filename = $this->_saveImage('product', $file1)) {
	        	$saveData['image1'] = $filename;
			}
			
			$file = $this->params()->fromFiles('image2');
	        $file1 = $this->params()->fromFiles('m_image2');
	        if ($filename = $this->_saveImage('product', $file)) {
	        	$saveData['image2'] = $filename;
	        } elseif($filename = $this->_saveImage('product', $file1)) {
	        	$saveData['image2'] = $filename;
			}
			
			$file = $this->params()->fromFiles('image3');
	        $file1 = $this->params()->fromFiles('m_image3');
	        if ($filename = $this->_saveImage('product', $file)) {
	        	$saveData['image3'] = $filename;
	        } elseif($filename = $this->_saveImage('product', $file1)){
	        	$saveData['image3'] = $filename;
			}
			
			$validation = new \Application\Model\Validation();
            if ($validation->isDealerUploadValid($saveData)) {
            	if ($id = $dealerUpload->setData($id, $saveData)) {
            		
            		// Save Dealer Upload product Attribute
            		$attributes = $data['attribute'];
            		$subitems = isset($data['subitem']) ? $data['subitem'] : array();
            		$uploadAttribute['upload_id'] = $id;
            		foreach ($attributes as $attribute) {
            			$optionInfo = explode('_', $attribute);
						$uploadAttribute['attribute_id'] = intval($optionInfo[0]);
	            		$uploadAttribute['attribute_option_index'] = intval($optionInfo[1]);
	            		$uploadAttribute['attribute_option_subitem'] = isset($subitems[$uploadAttribute['attribute_id']][$uploadAttribute['attribute_option_index']]) ? $subitems[$uploadAttribute['attribute_id']][$uploadAttribute['attribute_option_index']] : '';
	            		$dealerUpload->setDealerUploadAttribute($uploadAttribute);
					}
					
            		// Save Dealer Upload product Cart Price
            		$cartPrice = array(
						'upload_id' => $id,
						'upload_price' => $saveData['recommanded_price'],
						'cart_price' => round(($saveData['recommanded_price']*2)/100, 2),
						'cart_status' => 0,
						'updated_date' => date('Y-m-d h:i:s')
					);
					$dealerUpload->setDealerCartPrice($cartPrice);
					
					$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    return $this->redirect()->toRoute('my_uploads');
				} else {
					$message[] = array('error' => $this->config['message']['failed_save_data']);
				}
				
            } else {
                $message[] = array('error' => $validation->message);
            }
        }
        
        return new ViewModel(
            array(
            	'title' => 'Upload',
                'category' => $categoryList,
                'flashMessages' => $message
            )
        );
        
	}
	
	public function imageUploadAction() {
		$this->_initialize(1);
		$error = '';
		$html = '';
		$msg = '';
		$fileList = array();
	    $request = $this->getRequest();
        if ($request->isPost()) {
        	$data = $request->getPost();
        	$userSession = new \Zend\Session\Container('dealer_upload_image');
        	if (isset($userSession->filelist)) {
				$fileList = $userSession->filelist;
			}
			$directory = BASE_PATH.DS.'media'.DS.'product'.DS;
			
			if (intval($data['remove'])) {
				foreach($fileList as $key => $val) {
					if ($val == strval($data['val'])) {
						unset($fileList[$key]);
						@unlink($directory.$val);
					}
				}
				$userSession->filelist = $fileList;
			} else {
				if (count($fileList) < 10) {
					$directory .= 'user_'.$this->userinfo->id;	
					$file = $this->params()->fromFiles($data['id']);
					if (!file_exists($directory)) {
						@mkdir($directory, 0777);
					}
			        if ($filename = $this->_saveImage($directory, $file)) {
			        	$fileList[] = 'user_'.$this->userinfo->id.'/'.$filename;
			        	$userSession->filelist = $fileList;
			        } else {
						$message = $this->flashMessenger()->getMessages();
						$error = $this->config['message']['image_invalid_msg'];
					}
			    } else {
					$error = ' you can not upload image more than 10';
				} 
			}
			 
	        $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
            $url = $renderer->getSettings('website_url');
            if ($_SERVER['HTTP_HOST'] == 'localhost') {
				$url .= '/jakato';
			}
  			foreach ($fileList as $key => $file) {
				$html .= '<div class="image_list"><img src="'.$url.'/media/product/'.$file.'" alt="" /><span><input type="checkbox" value="'.$file.'" name="delete['.$key.']" />Delete</span></div>';
			}
			
			echo "{";
			echo				"error: '" . $error . "',\n";
			echo				"count: '" . count($fileList) . "',\n";
			echo 				"html: '". $html."'\n";
			echo "}";
			exit;
		} else {
			throw new \Exception($this->config['message']['invalid_request']);
		}
	}
	
	private function _saveImage($mediaDir = '', $file)
    {
    	$filename = '';
    	if ($file['name']) {
    		
    		if ( ($file["type"] == "image/gif" || $file["type"] == "image/jpeg"
				|| $file["type"] == "image/jpg" || $file["type"] == "image/pjpeg"
				|| $file["type"] == "image/x-png" || $file["type"] == "image/png")
				&& ($file["size"] < 1048576)) {
					
				$filename = round(microtime(true) * 1000).str_replace(array(' ', '/', DS, '*', '%', '$', '&'), '', $file['name']);
	            $filename = str_replace(
					array(' ','/',DS,'*','%','$','&','+','#','@','!','~','?','[',']','{','}',')','(','"',',',':',';',','), 
					'', 
					$filename
				);
				if (move_uploaded_file($file['tmp_name'], $mediaDir.DS.$filename)) {
					return $filename;
				} 
		  	} else {
            	$error = $this->config['message']['image_invalid_msg'];
                $this->flashMessenger()->addMessage(array('error' => $error));
            }
        }
        return '';
	}
	
	public function groupOptionAction()
	{
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			$this->_initialize(1);
			
			$viewData = array();		
			$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;
	        $type = isset($_GET['type']) ? strval($_GET['type']) : '';
	        $viewData['type'] = $type;
	        if ($type == 'c') {
		        $attribute = new \Admin\Model\AttributeGroup($this->_dbAdapter);
		        $cid = intval($cid);
		        $option = $attribute->getDataByCategoryId($cid);
		        $viewData['option'] = $option;
			} elseif ($type == 'a') {
				$attribute = new \Admin\Model\Attribute($this->_dbAdapter);
				$cidList = explode('_', $cid);
		        $option = $attribute->getDataByGroupId(intval($cidList[0]), intval($cidList[1]), 1);
		        
		        $groupWiseOption = array();
				if ($option) {
			        //$currentId = $option[0]['id']
					foreach($option as $item) {
						//if ($currentId != $item['id']) {
						//	$currentId
						//}
						$groupWiseOption[$item['id']][] = $item;
					}
				}
				$viewData['option'] = $groupWiseOption;
			}
			
			$viewModel = new ViewModel($viewData);
	        $viewModel->setTerminal(true);
	        return $viewModel;
		} else {
			throw new \Exception($this->config['message']['invalid_request']);
		}
	}
	
	public function costCalculationAction() {
		
		if ( $this->getRequest()->isXmlHttpRequest() ) {
			
			$this->_initialize(1);
			$fileList = array();
			$userSession = new \Zend\Session\Container('dealer_upload_image');
	    	if (isset($userSession->filelist)) {
				$fileList = $userSession->filelist;
			}
			
			$recomendedPrice = 0;
			$video = 0;
			$request = $this->getRequest();
			if ($request->isPost()) {
        		$data = $request->getPost();
        		$recomendedPrice = intval($data['rprice']);
        		$video = intval($data['video']);
        	}
        	
			$cost = \Application\Model\CostCalculation::get($recomendedPrice, count($fileList), $video);
			
			$jsonArray = array('cost' => $cost, 'rprice' => $recomendedPrice, 'image_total' => count($fileList));
			echo json_encode($jsonArray); exit;
			
		} else {
			throw new \Exception($this->config['message']['invalid_request']);
		}
	}
	 
}
