<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Autheticate;

class IndexController extends AbstractActionController 
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
    private function _initialize() {
    	$this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->config = $this->getServiceLocator()->get('Config'); 
	}
     
    /**
     * Logged In Check
     * @param   $isAjax     int
     */
     
    private function _isLoggedIn($loggedout = 0) {
        $this->_initialize();
        $this->_auth = new Autheticate($this->config);
        $this->userinfo = $this->_auth->getIdentity();
        if ($this->userinfo) {
			return true;
		}
		return false;
    }
    
    
    public function indexAction() {
    	$this->_initialize();
    	$request = $this->getRequest();
		$message = $this->flashMessenger()->getMessages();
		$data = array();
        return new ViewModel(array(
            'flashMessages' => $message,
            'data' => $data
        ));
    }
    
    public function paypalSuccessAction() {
    	$this->_isLoggedIn();
    	$request = $this->getRequest();
		if ($request->isPost() || $_POST) {
			
	     	$data = $_POST; 
        	if (isset($data['custom']) && isset($data['payment_status'])) {
        		$custom = array();
      			parse_str($data['custom'], $custom);
      			
        		switch ($custom['type']) {
					case 'membership_subscription':
						$user = new \Admin\Model\Users($this->_dbAdapter);
						$subscriptionData = array(
		        			'is_subscribe' => 1,
		        			'subscribe_date' => date('Y-m-d h:i:s'),
							'transaction_id' => $data['txn_id']
						);
						$user->setData($custom['uid'], $subscriptionData);
						$this->flashMessenger()->addMessage(array('success' => $this->config['message']['subscription_successfully']));
					break;
					
					case 'p1':
					case 'p2':
					case 'p3':
					case 'p4':
						$product_id = intval(str_replace('p', '', $custom['type']));
						$query = 'INSERT IGNORE INTO user_product(user_id,product_id,transaction_id) VALUES (:user_id, :product_id,:transaction_id)';
                    	$this->_dbAdapter->query(
							$query, 
							array(
								'user_id' => $custom['uid'], 
								'product_id' => $product_id,
								'transaction_id' => $data['txn_id']
							)
						);
						$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
					break;
				}
        		
			}
        }
        
    	//$file = fopen("./var/cache/test.txt", "a");
    	//fwrite($file, "=====================================\n");
		//fwrite($file, $_POST);
		//fclose($file);
		
    	exit;
    }
    
    public function checkoutSuccessAction() {
		$this->_initialize();
		$request = $this->getRequest();
		if ($request->isPost() || $_POST) {
			$data = $_POST; 
        	if (isset($data['custom']) && isset($data['payment_status']) && $data['txn_id']) {
        		$custom = array();
      			parse_str($data['custom'], $custom);
      			$cartids = explode(',', $custom['cid']);
        		foreach ($cartids as $cartid) {
					$dealerUpload = new \Admin\Model\DealerUpload($this->_dbAdapter);
        			$dealerUpload->updateDealerCartPriceStatus($cartid, $custom['uid'], $data['txn_id'], 1);
				}
      		}
		}
		exit;
	}
    
    public function paypalCancelAction() {
    	echo '<pre />'; 
    	print_r($_POST);
    	exit;
    }
    
    /** 
	* Create User Account
	*/
	public function createAccountAction() {
		
		if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        
		$request = $this->getRequest();
		$message = $this->flashMessenger()->getMessages();
		$data = array();
		if ($request->isPost()) {
            $data = $this->getRequest()->getPost();
            $data = $data->toArray();
            $validation = new \Application\Model\Validation();
            if ($validation->isValidRegistrationData($data, $this->_dbAdapter)) {
	            $userSession = new \Zend\Session\Container('user_registration');
	            $userSession->user = array(
					'username' => $data['username'],
					'email' => $data['email'],
					'password' => $data['password']
				);
				//echo '</pre />'; print_r($userSession->user); exit;
				return $this->redirect()->toRoute('personal_details');
			} else {
                $message[] = array('error' => $validation->message);
            }
            
        }
		
		return new ViewModel(array(
            'flashMessages' => $message,
            'data' => $data
        ));
        
        
	}
	
	public function personalDetailsAction() {
		if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        
        $message = array();
        $userSession = new \Zend\Session\Container('user_registration');
        if (isset($userSession->user) && $userSession->user) {
        	$request = $this->getRequest();
	        if ($request->isPost()) {
	            $data = $this->getRequest()->getPost();
	            $userData = $userSession->user;
	            
				/** Address Validation **/
				$addresses[] = array(
						'address_type' => 1,
						'name' => $data->private_name,
						'surname' => $data->private_surname,
						'street' => $data->private_street,
						'city' => $data->private_city,
						'state' => $data->private_state,
						'zipcode' => $data->private_zipcode,
						'country' => $data->private_country,
						'phone' => $data->private_phone,
						'email' => $data->private_email
					);
					
				$addresses[] = array(
					'address_type' => 2,
					'name' => $data->business_name,
					'surname' => $data->business_surname,
					'street' => $data->business_street,
					'city' => $data->business_city,
					'state' => $data->business_state,
					'zipcode' => $data->business_zipcode,
					'country' => $data->business_country,
					'phone' => $data->business_phone,
					'email' => $data->business_email
				);
				$validation = new \Application\Model\Validation();
				$addressError = 0;
				foreach ($addresses as $key => $address) {
					if ($validation->isValidAddress($address)) {
        				$addresses[$key] = $address;
					} else {
						$addressError = 1;
		                $message[] = array('error' => $validation->message);
					}
				}
				
				if (!$addressError) {
					$userSession->user = array(
						'username' => $userData['username'],
						'email' => $userData['email'],
						'password' => $userData['password'],
						'addresses' => $addresses
					);
				
					return $this->redirect()->toRoute('upload_document');
				}
			}
	        
        } else {
        	$this->flashMessenger()->addMessage(array('error' => $this->config['message']['step_not_complete']));
        	return $this->redirect()->toRoute('create_account');
		}
        
		return new ViewModel(array(
            'flashMessages' => $message
        ));
	}
	
	public function uploadDocumentAction() {
		if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        
        $message = array();
        $userSession = new \Zend\Session\Container('user_registration');
        if (isset($userSession->user) && $userSession->user) {
        	$request = $this->getRequest();
        	$userData = $userSession->user;
	        if ($request->isPost()) {
				//$data = $this->getRequest()->getPost();
		        
		        $file = $this->params()->fromFiles('passport_image');
		        $file1 = $this->params()->fromFiles('passport_image1');
		        if ($filename = $this->_saveImage('passport_image', $file)) {
		        	$userData['passport_image'] = $filename;
		        } elseif($filename = $this->_saveImage('passport_image', $file1)) {
		        	$userData['passport_image'] = $filename;
				} else {
					$userData['passport_image'] = $userData['passport_image'] ? $userData['passport_image'] : '';
				}
				
				$file = $this->params()->fromFiles('mobile_invoice_image');
		        $file1 = $this->params()->fromFiles('mobile_invoice_image1');
		        if ($filename = $this->_saveImage('mobile_invoice_image', $file)) {
		        	$userData['mobile_invoice_image'] = $filename;
		        } elseif($filename = $this->_saveImage('mobile_invoice_image', $file1)) {
		        	$userData['mobile_invoice_image'] = $filename;
				} else {
					$userData['mobile_invoice_image'] = $userData['mobile_invoice_image'] ? $userData['mobile_invoice_image'] : '';
				}
				
				$file = $this->params()->fromFiles('tax_document');
		        $file1 = $this->params()->fromFiles('tax_document1');
		        if ($filename = $this->_saveImage('tax_document', $file)) {
		        	$userData['tax_document'] = $filename;
		        } elseif($filename = $this->_saveImage('tax_document', $file1)){
		        	$userData['tax_document'] = $filename;
				} else {
					$userData['tax_document'] = $userData['tax_document'] ? $userData['tax_document'] : '';
				}
				
				if($userData['passport_image'] && $userData['tax_document'] && $userData['mobile_invoice_image']) {
					$userData['password'] = \Application\Model\GeneratePassword::generate($userData['password']);
					$userData['is_active'] = 0;
					$addresses = $userData['addresses'];
			        unset($userData['addresses']);
					$user = new \Admin\Model\Users($this->_dbAdapter);
					if ($id = $user->setData(0, $userData)) {
						$this->_saveAddressData($addresses, $id, $userData['email'], $message);
						unset($userSession->user);
						return $this->redirect()->toRoute('account_confirmation');
					} else {
						$message[] = array('error' => $this->config['message']['failed_save_data']);
					}
				} else {
					$imageError = '';
					if ($userData['passport_image'] == '') {
						$imageError = $this->config['message']['passport_image_upload'];
						$message[0] = array('error' => $imageError);
					}
					if ($userData['mobile_invoice_image'] == '') {
						$imageError .= $this->config['message']['mobile_invoice_upload'];
						$message[0] = array('error' => $imageError);
					}
					if ($userData['tax_document'] == '') {
						$imageError .= $this->config['message']['tax_document_upload'];
						$message[0] = array('error' => $imageError);
					}
					$userSession->user = $userData;
				}
			
			}
			
		} else {
        	$this->flashMessenger()->addMessage(array('error' => $this->config['message']['step_not_complete']));
        	return $this->redirect()->toRoute('personal_details');
		}
        
		return new ViewModel(array(
			'data' => $userData,
            'flashMessages' => $message
        ));
	}
	
	/**
	* Save User Registration Data
	*/
	private function _saveAddressData($addresses = array(), $userid, $email, &$message) {
		$address = new \Admin\Model\Address($this->_dbAdapter);
		foreach ($addresses as $data) {
			$data['user_id'] = $userid;
			$address->setData(0, $data);
		}
		
		if ($_SERVER['HTTP_HOST'] == 'localhost') {
			$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
			//$message[] = array('success' => $this->config['message']['saved_successfully']);
		} else { 
			
			$settings = new \Admin\Model\Settings($this->_dbAdapter);
	        $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
	        $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'account_confirmation.html');
	        foreach ($configaration as $key => $value) {
	            $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
	        }
        
	        if (\Application\Model\Email::send($emailTamplate, '', 'Account Confirmation', 
	                                           $configaration['website_name'], $configaration['admin_email_address'], 
	                                           $email)) {
	            $this->flashMessenger()->addMessage(array('success' => $this->config['message']['account_confirmation']));
	            //$message[] = array('success' => $this->config['message']['account_confirmation']);
	        }
		}	
        return true;
	}
	
	
	private function _saveImage($mediaDir = '', $file) {
    	$filename = '';
    	if ($file['name']) {
    		
    		if ( ($file["type"] == "image/gif" || $file["type"] == "image/jpeg"
				|| $file["type"] == "image/jpg" || $file["type"] == "image/pjpeg"
				|| $file["type"] == "image/pdf" || $file["type"] == "image/tiff"
				|| $file["type"] == "image/x-png" || $file["type"] == "image/png")
				&& ($file["size"] < 1048576)) {
					
				$filename = round(microtime(true) * 1000).str_replace(array(' ', '/', DS, '*', '%', '$', '&'), '', $file['name']);
	            $filename = str_replace(
					array(' ','/',DS,'*','%','$','&','+','#','@','!','~','?','[',']','{','}',')','(','"',',',':',';',','), 
					'', 
					$filename
				);
				if (move_uploaded_file($file['tmp_name'], BASE_PATH.DS.'media'.DS.$mediaDir.DS.$filename)) {
					return $filename;
				} 
		  	} else {
            	$error = $this->config['message']['image_invalid_msg'];
                $this->flashMessenger()->addMessage(array('error' => $error));
            }
        }
        return '';
	}
    
    
    public function loginAction() {
        
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        
        $message = array();
		$request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->getRequest()->getPost();
            $validation = new \Application\Model\Validation();
            if ($validation->isLoginValid($data)) {
                $data->username = $data->username;
                if ($this->userInfo = $this->_auth->authenticate($this->_dbAdapter, $data, 'users', 'username', 'password', 
                                              array(
											  	'id', 'username', 'email', 'passport_no', 
											  	'mobile_no', 'passport_image', 'mobile_invoice_image', 
												'tax_number', 'tax_document'
											  ), 1209600)) {
                    
                    $this->flashMessenger()->addMessage(
						array(
							'success' => $this->config['message']['loggedin']
						)
					);
                    
                    return $this->redirect()->toRoute('search');
                    
                } else {
                    $message[] = array(
									'error' => $this->config['message']['login_failed']
								);
                }
            } else {
                $message[] = array('error' => $validation->message);
            }
        }
        
        
        
        return new ViewModel(array(
            'flashMessages' => $message
        ));
        
    }
    
    public function logoutAction() {
		$this->_initialize();
    	$this->_auth = new Autheticate($this->config);
    	$this->_auth->clearIdentity();
        return $this->redirect()->toRoute('login');
    }


    public function forgotPasswordAction() {
        
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        
        $message = $this->flashMessenger()->getMessages();
         
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $settings = new \Admin\Model\Settings($this->_dbAdapter);
            $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
            
            $data->email = trim(strip_tags($data->email));
            $user = new \Admin\Model\Users($this->_dbAdapter);
            $userInfo = $user->getDataByColumn('email', $data->email);
            
            if ($userInfo) {
                
                $configaration['password'] = \Application\Model\GeneratePassword::randomPassword();
                $saveData['password'] = \Application\Model\GeneratePassword::generate($configaration['password'], 'user');
                
                $configaration += (array)$userInfo;
                $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'password_reset_confirmation.html');
                foreach ($configaration as $key => $value) {
                    $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
                }
                
                if ($user->setDataByColumn('id', $userInfo['id'], $saveData)) {
                    if (\Application\Model\Email::send($emailTamplate, '', 'Forgot Password', 
                                                   $configaration['website_name'], $configaration['admin_email_address'], 
                                                   $data->email)) {
                        $message[0] = array('success' => $this->config['message']['email_sent_successfully']);
                    } else {
                        $message[0] = array('error' => $this->config['message']['email_sent_failed']);
                    }
                } else {
                   $message[0] = array('error' => $this->config['message']['reset_password_failed']); 
                }
                
            } else {
                $message[0] = array('error' => $this->config['message']['email_address_not_exist']);
            }
        }
        
		return array(
            'title' => 'Reset your password',
            'flashMessages' => $message
        );
        
    }
    
    
    public function resendActivationAction() {
        
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        $message = $this->flashMessenger()->getMessages();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $settings = new \Admin\Model\Settings($this->_dbAdapter);
            $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
            
            $data->email = trim(strip_tags($data->email));
            $user = new \Admin\Model\Users($this->_dbAdapter);
            $userInfo = $user->getDataByColumn('email', $data->email);
            
            if ($userInfo) {
                
				$saveData['activation_code'] = md5(rand(1, 1000).$userInfo['id'].rand(1, 1000));
                $configaration += (array)$userInfo;
                $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'resend_activation_confirmation.html');
                $configaration['reset_password_url'] = trim($configaration['website_url'], '/').'/account-activation/'.$saveData['activation_code'];
                
                foreach ($configaration as $key => $value) {
                    $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
                }
                
                if ($user->setDataByColumn('id', $userInfo['id'], $saveData)) {
                    if (\Application\Model\Email::send($emailTamplate, '', 'Account Activation Link', 
                                                   $configaration['website_name'], $configaration['admin_email_address'], 
                                                   $data->email)) {
                        $message[0] = array('success' => $this->config['message']['email_sent_successfully']);
                    } else {
                        $message[0] = array('error' => $this->config['message']['email_sent_failed']);
                    }
                } else {
                   $message[0] = array('error' => $this->config['message']['reset_password_failed']); 
                }
                
            } else {
                $message[0] = array('error' => $this->config['message']['email_address_not_exist']);
            }
        }
        
		return array(
            'title' => 'Resend Account Activation',
            'flashMessages' => $message
        );
    }
    
    
    public function accountActivationAction() {
		if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('account');
        }
        
		$message = $this->flashMessenger()->getMessages();
        $matches = $this->getEvent()->getRouteMatch();
    	$code = $matches->getParam('code', 0);

        $user = new \Admin\Model\Users($this->_dbAdapter);
        $userInfo = $user->getDataByColumn('reset_password_key', $code);
		$status = 0;
        if ($userInfo) {
            $saveData['activation_code'] = '';
            $saveData['is_active'] = 0;
            //$saveData['is_confirmed'] = 1;
            if ($user->setDataByColumn('id', $userInfo['id'], $saveData)) {
                $message[0] = array('success' => $this->config['message']['successfully_active_account']);
                $status = 1;
            } else {
               $message[0] = array('error' => $this->config['message']['failed_to_active_account']); 
               $status = 2;
            }
        } else {
            $message[0] = array('error' => $this->config['message']['invalid_code']);
            $status = 3;
        }
  
		return array(
            'title' => 'Account Activation',
            'flashMessages' => $message
        );
        
	}
    
    /**
     * Contact Support
     */
    public function contactUsAction() {
        
        $this->_initialize();
        $message = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
        	
            $data = $request->getPost();
            $data['created_date'] = date('Y-m-d H:i:s');
            $settings = new \Admin\Model\Settings($this->_dbAdapter);
            $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
            $configaration += $data->toArray();
            
            $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'contact_support.html');
            foreach ($configaration as $key => $value) {
                $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
            }
            
            if (\Application\Model\Email::send(
                $emailTamplate, 
				'', 
                'Contact Us -- '.$configaration['website_name'], 
                $configaration['website_name'], 
                $data['name'],
                $data['email_address'],
				$configaration['admin_email_address']
            )) {
                $message[0] = array('success' => $this->config['message']['email_sent_successfully']);
            } else {    
                $message[0] = array('notice' => $this->config['message']['email_sent_failed']);
            }
                       
        }
        
        return new ViewModel(
            array(
                'title' => 'Contact Us',
                'flashMessages' => $message
            )
        );
    }
    
    public function searchAction() {
    	
		if (isset($_GET['s'])) {
			return $this->forward()->dispatch('Application\Controller\Index', array('action' => 'search-result', 's' => $_GET['s']));
		} else {
			$this->_isLoggedIn();
			if (!$this->userinfo) {
				return $this->redirect()->toRoute('login');
			}
			$matches = $this->getEvent()->getRouteMatch();
	    	$message = $this->flashMessenger()->getMessages();
			$paypalPayment = 0;
			$user = new \Admin\Model\Users($this->_dbAdapter);
			$request = $this->getRequest();
			if ($request->isPost()) {
		     	$data = $this->getRequest()->getPost();
	        	if (isset($data->custom) && isset($data->payment_status)) {
	        		if ($data->custom == 'membership_subscription' && strtolower($data->payment_status) == 'completed') {
		        		$message[0] = array('success' => $this->config['message']['subscription_successfully']);
						$paypalPayment = 1;
					}
				}
	        }
			$userDetails = $user->getData($this->userinfo->id)->toArray();
			return new ViewModel(array(
				'uid' => $this->userinfo->id,
	            'userDetails' => $userDetails,
	            'paypalPayment' => $paypalPayment,
	            'flashMessages' => $message
	        ));
		}
	}
	
	public function searchResultAction()
	{
		$this->_isLoggedIn();
		$message = $this->flashMessenger()->getMessages();
		$matches = $this->getEvent()->getRouteMatch();
		
    	$s = strtolower($matches->getParam('s', ''));
    	$dealerUpload = new \Admin\Model\DealerUpload($this->_dbAdapter);
    	$uploadList = $dealerUpload->searchResult($s);
    	
    	$page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($uploadList, $page, $this->config);
        
		return new ViewModel(
            array(
            	'title' => 'Search Result: '.$s,
            	'uid' => $this->userinfo ? $this->userinfo->id : 0,
            	'paginator' => $paginator,
                'flashMessages' => $message
            )
        );
		return $viewModel;
	}
	
	
	public function productAction() {
		$this->_initialize();
    	$matches = $this->getEvent()->getRouteMatch();
    	$id = $matches->getParam('id', 0);
    	$products = new \Admin\Model\Products($this->_dbAdapter);
    	$product = $products->getData($id, 1)->toArray();
    	$viewModel = new ViewModel(
            array(
            	'title' => 'Product Inforation',
                'product' => $product,
             )
        );

        return $viewModel;
	}
	
	public function productlistAction() {
		if (!$this->_isLoggedIn()) {
			$this->redirect()->toRoute('login');
		}

    	$message = $this->flashMessenger()->getMessages();
		$request = $this->getRequest();
		if ($request->isPost() || $_POST) {
			$data = $_POST; 
        	if (isset($data['custom']) && isset($data['payment_status'])) {
        		if (strtolower($data['payment_status']) == 'completed') {
        			//$product_id = intval(str_replace('p', '', $data['custom']));
					//$query = 'INSERT IGNORE INTO user_product(user_id,product_id) VALUES (:user_id, :product_id)';
                    //$this->_dbAdapter->query($query, array('user_id' => $this->userinfo->id, 'product_id' => $product_id));
        			$message[0] = array('success' => $this->config['message']['saved_successfully']);
				}
			}
        }
        
        $sql = 'SELECT product_id FROM user_product WHERE user_id='.$this->userinfo->id;
        $statement = $this->_dbAdapter->query($sql);
		$result = $statement->execute();
		$resultSet = new \Zend\Db\ResultSet\ResultSet();
		$resultSet->initialize($result);
		$userProducts = array();

		foreach ($resultSet as $key => $item) {
			$userProducts[] = $item->product_id;
		}
		
    	return new ViewModel(
            array(
                'title' => 'Products',
                'uid' => $this->userinfo->id,
                'userProducts' => $userProducts,
                'flashMessages' => $message
            )
        );
    	
        
	}
	
	/**
	* Refer To Friend
	*/
	public function referFriendAction() {
        $this->_initialize();
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
        	$data = $this->getRequest()->getPost();
            $settings = new \Admin\Model\Settings($this->_dbAdapter);
            $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
            $data->email = trim(strip_tags($data->email));
            $data->friend_email = trim(strip_tags($data->friend_email));
            $emailTamplate = $data->message.'<p><a href="'.$configaration['website_url'].'">'.$configaration['website_url'].'</a></p>';
                
            if (\Application\Model\Email::send($emailTamplate, '', 'Welcome to '.$configaration['website_name'], 
                                           	   $configaration['website_name'], $data->email, $data->friend_email)) {
                $message[0] = array('success' => $this->config['message']['email_sent_successfully']);
            } else {
                $message[0] = array('error' => $this->config['message']['email_sent_failed']);
            }
       }
        
		return array(
            'title' => 'Refer a Friend',
            'flashMessages' => $message
        );
    }
    
    
    public function accountConfirmationAction() {
		$message = $this->flashMessenger()->getMessages();
		return array(
            'flashMessages' => $message
        );
	}
	
	
	public function detailsAction() {
		$this->_isLoggedIn();
		$message = $this->flashMessenger()->getMessages();
        $matches = $this->getEvent()->getRouteMatch();
    	$id = $matches->getParam('id', 0);
    	
    	$dealerUpload = new \Admin\Model\DealerUpload($this->_dbAdapter);
    	//$product = $dealerUpload->getData($id, 1)->toArray();
    	$product = $dealerUpload->getData($id, 0)->toArray();
    	if (!$product) {
			throw new \Exception($this->config['message']['invalid_request']);
		}
		$dealerComment = new \Admin\Model\DealerComment($this->_dbAdapter);
		
		$request = $this->getRequest();
        if ($request->isPost()) {
        	if (!$this->userinfo) {
				$this->flashMessenger()->addMessage(array('error' => $this->config['message']['not_loggedin']));
				return $this->redirect()->toRoute('login');
			}
			$data = $this->getRequest()->getPost();
        	$saveData['comment'] = trim(strip_tags($data->comment));
        	$saveData['rating'] = intval($data->rating);
        	$saveData['upload_id'] = $id;
        	$saveData['dealer_id'] = $this->userinfo->id;
        	if ($saveData['comment'] == '') {
				$message[] = array('error' => 'Comment is required.');
			}
			if (!$saveData['rating']) {
				$message[] = array('error' => 'Rating is required.');
			}
        	if (!$message) {
				if ($dealerComment->setData($saveData)) {
					$message[0] = array('success' => $this->config['message']['saved_successfully']);
				} else {
					$message[0] = array('error' => $this->config['message']['already_commented']);
				}
			}
        }
        
        $comment = $dealerComment->getRowData($id);
		$productGallery = $dealerUpload->getUploadGallery($id);
    	$productAttr = $dealerUpload->getAttributeInfo($id);
		return new ViewModel(
            array(
                'title' => $product[0]['item_name'],
                'product' => $product,
                'uid' => $this->userinfo->id,
                'comment' => $comment,
                'productGallery' => $productGallery,
                'productAttr' => $productAttr,
                'flashMessages' => $message
            )
        );
		
	}
	
	public function activationAction() {
		if (!$this->_isLoggedIn()) {
			$this->redirect()->toRoute('login');
		}
		$message = $this->flashMessenger()->getMessages();
		$user = new \Admin\Model\Users($this->_dbAdapter);
		$userDetails = $user->getData($this->userinfo->id)->toArray();
		return new ViewModel(array(
			'title' => 'Activation status',
            'userDetails' => $userDetails,
            'flashMessages' => $message
        ));
	}
	
	
	public function locatorAction() {
		$isajax = isset($_GET['isajax']) ? intval($_GET['isajax']) : 0;
		if ( $isajax ) {
			$this->_initialize();
			$lat = isset($_GET['lat']) ? intval($_GET['lat']) : 0;
        	$lng = isset($_GET['lng']) ? intval($_GET['lng']) : 0;
        	$radius = isset($_GET['radius']) ? intval($_GET['radius']) : 0;
        	$users = new \Admin\Model\Users($this->_dbAdapter);
        	$userList = $users->getLocatorUser($lat, $lng, $radius);
        	$viewData = array( 
							'userLst' => $userList,
							'isajax' => $isajax
						);
			$viewModel = new ViewModel($viewData);
		    $viewModel->setTerminal(true);
			return $viewModel;
		} else {
			return new ViewModel(array(
				'title' => 'Dealer Locator',
				'isajax' => $isajax
	        ));
		}
	}
	
	public function cronjobAction() {
		
		$this->_initialize();
		
		$dealerUpload = new \Admin\Model\DealerUpload($this->_dbAdapter);
		$dealerUpload->deleteAllFlagGallery();
		
		$dealerUpload->setPendingStatusForRunningOut();
		
		echo 'success';
		exit;
	}
	
	
	public function geocodeAction(){ 
		$this->_initialize();
		$users = new \Admin\Model\Users($this->_dbAdapter);
		$address = new \Admin\Model\Address($this->_dbAdapter);
		$userList = $users->getUserWithoutLatLang()->toArray();
		//echo '<pre />'; print_r($userList); exit;
		foreach ($userList as $user) {
			$addr = $user['street'].','.$user['city'].','.$user['state'].','.$user['zipcode'].','.$user['country_name'];
	        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($addr)."&sensor=false";
	        $cinit = curl_init();
	        curl_setopt($cinit, CURLOPT_URL, $url);
	        curl_setopt($cinit, CURLOPT_HEADER,0);
	        curl_setopt($cinit, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
	        //curl_setopt($cinit, CURLOPT_FOLLOWLOCATION, 1);
	        curl_setopt($cinit, CURLOPT_RETURNTRANSFER, 1);
	        $response = curl_exec($cinit);
	        curl_close($cinit);
	        $result = json_decode($response, true);
	        if(!empty($result['results'])){
	        	$data['formatted_address'] = $result['results'][0]['formatted_address'];
	        	$data['lat'] = $result['results'][0]['geometry']['location']['lat'];
	        	$data['lng'] = $result['results'][0]['geometry']['location']['lng'];
	        	$data['user_id'] = $user['id'];
	        	$address->setData($user['address_id'], $data);
	        }
		}
		echo 'success';
		exit;
    }
    
}
