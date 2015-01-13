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
    private function _initialize() 
    {
    	$this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->config = $this->getServiceLocator()->get('Config'); 
	}
     
    /**
     * Logged In Check
     * @param   $isAjax     int
     */
     
    private function _isLoggedIn($loggedout = 0) 
    {
        $this->_initialize();
        $this->_auth = new Autheticate($this->config);
        $this->userinfo = $this->_auth->getIdentity();
        if ($this->userinfo) {
			return true;
		}
		return false;
    }
    
    
    public function indexAction() 
    {
        $this->_initialize();
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
				return $this->redirect()->toRoute('create_account');
			} else {
                $message[] = array('error' => $validation->message);
            }
            
        }
		
		return new ViewModel(array(
            'flashMessages' => $message,
            'data' => $data
        ));
    }
    
    /** 
	* Create User Account
	*/
	public function createAccountAction()
  	{
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
	            
	            $userData['passport_no'] = $data->passport_no != '' ? $data->passport_no : $data->passport_no1;
	            if ($data->passport_no != '') {
	            	$file = $this->params()->fromFiles('passport_image');
	            	$userData['passport_image'] = $this->_saveImage('passport_image', $file);
				} elseif ($data->passport_no1 != '') {
					$file = $this->params()->fromFiles('passport_image1');
            		$userData['passport_image'] = $this->_saveImage('passport_image', $file);
				}
				
	            $userData['mobile_no'] = $data->mobile_no != '' ? $data->mobile_no : $data->mobile_no1;
	            if ($data->mobile_no != '') {
	            	$file = $this->params()->fromFiles('mobile_invoice_image');
            		$userData['mobile_invoice_image'] = $this->_saveImage('mobile_invoice_image', $file);
				} elseif ($data->mobile_no1 != '') {
					$file = $this->params()->fromFiles('mobile_invoice_image1');
            		$userData['mobile_invoice_image'] = $this->_saveImage('mobile_invoice_image', $file);
				}
				
	            $userData['tax_number'] = $data->tax_number != '' ? $data->tax_number : $data->tax_number1;
	            if ($data->tax_number != '') {
					$file = $this->params()->fromFiles('tax_document');
            		$userData['tax_document'] = $this->_saveImage('tax_document', $file);
				} elseif ($data->tax_number1 != '') {
					$file = $this->params()->fromFiles('tax_document1');
            		$userData['tax_document'] = $this->_saveImage('tax_document', $file);
				}
				
				$userData['password'] = \Application\Model\GeneratePassword::generate($userData['password']);
				$userData['is_active'] = 0;
				
				
				
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
					$user = new \Admin\Model\Users($this->_dbAdapter);
					if ($id = $user->setData(0, $userData)) {
						$this->_saveAddressData($addresses, $id, $userData['email'], $message);
						return $this->redirect()->toRoute('account_confirmation');
					} else {
						$message[] = array('error' => $this->config['message']['failed_save_data']);
					}
				}
			}
	        
        } else {
        	$this->flashMessenger()->addMessage(array('error' => $this->config['message']['step_not_complete']));
        	return $this->redirect()->toRoute('home');
		}
        
		return new ViewModel(array(
            'flashMessages' => $message
        ));
        
	}
	
	public function uploadDocumentAction()
	{
		
	}
	
	/**
	* Save User Registration Data
	*/
	private function _saveAddressData($addresses = array(), $userid, $email, &$message)
	{
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
				if (move_uploaded_file($file['tmp_name'], BASE_PATH.DS.'media'.DS.$mediaDir.DS.$filename)) {
					return $filename;
				} 
		  	} else {
            	$error = 'Supported extension(jpeg, jpg, gif, png).Maximum file Size:1MB';
                $this->flashMessenger()->addMessage(array('error' => $error));
            }
        }
        return '';
	}
    
    
    public function loginAction() 
    {
        
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
                    
                    return $this->redirect()->toRoute('account');
                    
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
    
    public function logoutAction() 
	{
		$this->_initialize();
    	$this->_auth = new Autheticate($this->config);
    	$this->_auth->clearIdentity();
        return $this->redirect()->toRoute('login');
    }


    public function forgotPasswordAction() 
    {
        
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
    
    
    public function resendActivationAction() 
    {
        
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
    
    
    public function accountActivationAction()
    {
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
    public function contactUsAction() 
    {
        
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
    
    public function searchAction()
    {
    	$this->_initialize();
    	$matches = $this->getEvent()->getRouteMatch();
    	//$categoryid = $matches->getParam('catid', 0);
    	//$categories = new \Admin\Model\Categories($this->_dbAdapter);
    	//$category = $categories->getData($categoryid)->toArray();
		    	
		return new ViewModel(array(
            //'category' => $category,
            //'subCategories' => $subCategories,
            'flashMessages' => $this->flashMessenger()->getMessages()
        ));
	
	}
	
	
	public function productAction()
	{
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
	
	public function productlistAction()
	{
		$this->_initialize();
		$message = array();
    	return new ViewModel(
            array(
                'title' => 'Products',
                'flashMessages' => $message
            )
        );
    	
        
	}
	
	/**
	* Refer To Friend
	*/
	public function referFriendAction() 
    {
        
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
    
    
    public function accountConfirmationAction()
    {
		
	}
    
}
