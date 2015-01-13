<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Autheticate;
use Admin\Model\Validation;

class IndexController extends AbstractActionController 
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
     * Dashboard
     */
    public function indexAction() 
    {
        	
        $this->_initialize();
        return $this->forward()->dispatch('Admin\Controller\Index', array('action' => 'admin'));
    }
    
    /**
     * Show Admin User List
     */
    public function adminAction() 
    {
        	
        $this->_initialize();
        $matches = $this->getEvent()->getRouteMatch();
        
	    $adminUser = new \Admin\Model\AdminUsers($this->_dbAdapter);
        $adminList = $adminUser->getData(0);
    
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($adminList, $page, $this->config);
        $message = $this->flashMessenger()->getMessages();
        
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', 'Admin Panel Users');
        $vm->setVariable('userinfo', $this->userinfo);
        
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
    
    
    /**
     * Add New Admin User
     */
    public function newAdminAction() 
    {
    
        $this->_initialize();
        
		$adminUser = new \Admin\Model\AdminUsers($this->_dbAdapter);
        $matches = $this->getEvent()->getRouteMatch();
        
        $userid = $matches->getParam('userid', 0);
        $editUserInfo = array();
        if ($userid)
            $editUserInfo = $adminUser->getData($userid)->toArray();
            
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        
		if ($request->isPost()) {
            $data = $request->getPost();
            if ($editUserInfo) {
                if ($data['password'] == '') {
                    unset($data['password']);
                    unset($data['confirm_password']);
                } 
                if ($data['username'] == $editUserInfo[0]['username']) {
                    unset($data['username']);
                }
                if ($data['email_address'] == $editUserInfo[0]['email_address']) {
                    unset($data['email_address']);
                }   
            }
            $message = $this->_saveAdmin($adminUser, $data, $userid);
            if (is_array($message) && isset($message[0]['error']) && !$userid) {
                $editUserInfo[0] = $data->toArray();
            }
        }
   
   
        return new ViewModel(array(
            'title' => isset($editUserInfo[0]['user_id']) ? 'Edit Administrator - '.$editUserInfo[0]['first_name'].' '.$editUserInfo[0]['last_name'] : 'Add New Administrator',
            'userinfo' => $this->userinfo,
            'editUserInfo' => $editUserInfo,
            'flashMessages' => $message
        ));
        
    }
    
    /**
     * Edit Admin Information
     */
    public function editAdminAction() 
    {
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch(
			'Admin\Controller\Index', 
			array(
				'action' => 'new-admin', 
				'userid' => $matches->getParam('id', 0)
			)
		);
    }
    
    /**
     * Mass Status Change of Admin
     * Mass Delete
     * Mass Role Change
     */ 
    public function massAdminModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $userids = explode(',', $data['userids']);
            $adminUser = new \Admin\Model\AdminUsers($this->_dbAdapter);
            switch($data['action']) {
                case 'delete':
                    foreach ($userids as $userid) {
                        $adminUser->deleteData(intval($userid));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    if (intval($data['status'])) {
                        foreach ($userids as $userid) {
                            $saveData['status'] =  intval($data['status']);
                            $userid = intval($userid);
                            if ($userid)
                                $adminUser->setData($userid, $saveData);
                        }
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
            }
            return $this->redirect()->toRoute('admin', array('action' => 'index'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
    }
    
    /**
     * Save Admin User Information
     * @param   \Admin\Model\AdminUsers $adminUser
     * @param   $data   array
     * @param   $userid int
     */
    private function _saveAdmin(\Admin\Model\AdminUsers $adminUser, $data = array(), $userid = 0) 
    {
        
        $validation = new Validation();
        if ($validation->isValidRegistrationData($data, $this->_dbAdapter, $userid ? 0 : 1)) {
        	
            if (isset($data['confirm_password']))
                unset($data['confirm_password']);
            
			if ($adminUser->setData($userid, $data->toArray())) {
                $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                return $this->redirect()->toRoute('admin', array('action' => 'index'));
            } else {
                $message[0] = array('error' => $this->config['message']['failed_save_data']);
                return $message;
            }
        } else {
            $message[0] = array('error' => $validation->message);
            return $message;
        }
        
    }
    
    /**
	* Users List
	*/
    public function usersAction() 
    {
        $this->_initialize();
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
        $status = isset($_GET['s']) ? intval($_GET['s']) : 0;
        
        $users = new \Admin\Model\Users($this->_dbAdapter);
        $usersList = $users->getData(0, $status);
    
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($usersList, $page, $this->config);
        
        //Get Message From FlashMessenger
        $message = $this->flashMessenger()->getMessages();
        
        $title = 'Dealer list';
        if ($status == 2) {
			$title = 'Rejected Dealer list';
		} else if ($status == 1) {
			$title = 'Accepted Dealer list';
		} else if ($status == 3) {
			$title = 'Pending Dealer list';
		}
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable('paginator', $paginator);
        $vm->setVariable('title', $title);
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('flashMessages', $message);
        return $vm;
    }
      
	/**
	* Add New User From Admin
	*/
	public function newUserAction() 
	{
  
        $this->_initialize();
        $matches = $this->getEvent()->getRouteMatch();
        $message = $this->flashMessenger()->getMessages();
        
        $user = new \Admin\Model\Users($this->_dbAdapter);
        $address = new \Admin\Model\Address($this->_dbAdapter);
        $id = $matches->getParam('id', 0);
        $userDetails = array();
        $userAddress = array();
        if ($id){
            $userDetails = $user->getData($id)->toArray();
            $userAddresses = $address->getData($id)->toArray();
            if ($userAddresses) {
				$userAddress[0] = $userAddresses[0];
			}
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
				'email' => $data->private_email, 'address_id' => intval($data->private_id),
				'country' => intval($data->private_country)
			);
	
			$addresses[] = array(
				'address_type' => 2, 'name' => $data->business_name,
				'surname' => $data->business_surname, 'street' => $data->business_street,
				'city' => $data->business_city, 'state' => $data->business_state,
				'zipcode' => $data->business_zipcode, 'phone' => $data->business_phone,
				'email' => $data->business_email, 'address_id' => intval($data->business_id),
				'country' => intval($data->business_country)
			);
			
			$personalData = array(
				'username' => $data->username, 'password' => $data->password,
				'email' => $data->email, 'is_active' => $data->is_active,
				'passport_no' => $data->passport_no, 'mobile_no' => $data->mobile_no,
				'tax_number' => $data->tax_number
			);
			
			
            $file = $this->params()->fromFiles('passport_image');
            if ($filename = $this->_saveImage('passport_image', $file)) {
            	$personalData['passport_image'] = $filename;
            }
			
        	$file = $this->params()->fromFiles('mobile_invoice_image');
        	if ($filename = $this->_saveImage('mobile_invoice_image', $file)) {
    			$personalData['mobile_invoice_image'] = $filename;
    		}
			
			$file = $this->params()->fromFiles('tax_document');
			if ($filename = $this->_saveImage('tax_document', $file)) {
       			$personalData['tax_document'] = $filename;
        	}				
			
			if ($id) {
                if ($data->email == $userDetails[0]['email']){
                    unset($personalData['email']);
                }
                if ($data->username == $userDetails[0]['username']){
                    unset($personalData['username']);
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
						
						$isStatusChange = $id && ($data->is_active == $userDetails[0]['is_active']) ? 0 : 1;
						$this->_sendEmail($isNew, $isStatusChange, $data->username, $data->email, $data->is_active, $data->password, $data->comment, $message);
						return $this->redirect()->toRoute('admin', array('action' => 'users'));
					} else {
						$message[] = array('error' => $this->config['message']['failed_save_data']);
					}
				}
			} else {
                $message[] = array('error' => $validation->message);
                
                if(!$id){
                    $userInfo[0] = $data->toArray();
                }                
            }
        }
        
        //Send Variable to View
        $vm = new ViewModel();
        $vm->setVariable(
			'title', 
			(isset($userDetails[0]['id']) ? 'Edit user - '.$userDetails[0]['username'] : 'Add new user')
		);
		
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('address', $userAddress);
        $vm->setVariable('userDetails', $userDetails);
        $vm->setVariable('id', $id);
        $vm->setVariable('flashMessages', $message);
        
        return $vm;
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
	
	/**
	* Save User Registration Data
	*/
	private function _sendEmail($isNew, $isStatusChange, $username, $email, $status = 0, $password = '', $notification = '', &$message)
	{
		if ($_SERVER['HTTP_HOST'] == 'localhost') {
			$this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
		} else { 
			$settings = new \Admin\Model\Settings($this->_dbAdapter);
	        $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
	        $configaration['username'] = $username;
			if ($isStatusChange) {
				$emailTamplate = '';
				if ($status == 0 && $isNew) {
					$subject = 'Account Confirmation - '.$configaration['website_name']; 
					$emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'account_confirmation.html');
				} elseif ($status == 1) {
					$subject = 'Status changed notification of your account - '.$configaration['website_name'];
					$emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'account_accepted.html');
				} elseif($status == 2) {
					$subject = 'Status changed notification of your account - '.$configaration['website_name'];
					$emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'account_rejected.html');
				}
				
				foreach ($configaration as $key => $value) {
		            $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
		        }
						        
		        if (\Application\Model\Email::send(
												$emailTamplate, '', 
												$subject, 
		                                        $configaration['website_name'], 
												$configaration['admin_email_address'], 
		                                        $email)) {
		            $this->flashMessenger()->addMessage(array('success' => $this->config['message']['account_confirmation']));
		        }
	        }
	        
	        if ($password) {
	        	$message = 'System administrator changed your account password for security reason.';
				$message .= ' For login, Please use this password:'.$password;
	        	\Application\Model\Email::send(
											   $message, '', 
											   'Password Changed notification - '.$configaration['website_name'], 
	                                           $configaration['website_name'], 
											   $configaration['admin_email_address'], 
	                                           $email
										);
	        }
	        
	        if ($notification) {
	        	\Application\Model\Email::send(
												$notification, '', 
												'Notification - '.$configaration['website_name'], 
	                                            $configaration['website_name'], 
												$configaration['admin_email_address'], 
	                                           	$email
											);
	        }
	        
	        
		}	
		
        return true;
	}
	
	/**
	* Edit New User From Admin
	*/
	public function editUserAction() 
	{
        $matches = $this->getEvent()->getRouteMatch();
        return $this->forward()->dispatch(
			'Admin\Controller\Index', 
			array(
				'action' => 'new-user', 
				'id' => $matches->getParam('id', 0)
			)
		);
    }
      
      
    /**
     * Mass Status Change of Model
     * Mass Delete
     */ 
    public function massUserModificationAction() 
    {
        //Request Object
        $this->_initialize();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $ids = explode(',', $data['ids']);
            $user = new \Admin\Model\Users($this->_dbAdapter);
            switch($data['action']) {
                case 'delete':
                    foreach ($ids as $id) {
                        $user->deleteData(intval($id));
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['deleted_successfully']));
                    break;
                case 'status':
                    foreach ($ids as $id) {
                        $saveData['is_active'] =  intval($data['status']);
                        $id = intval($id);
                        if ($id)
                            $user->setData($id, $saveData);
                    }
                    $this->flashMessenger()->addMessage(array('success' => $this->config['message']['saved_successfully']));
                    break;
                   
            }
            
            return $this->redirect()->toRoute('admin', array('action' => 'users'));
        } else {
            throw new \Exception($this->config['message']['invalid_request']);
        }
    }
    
    public function settingsAction() 
	{
		$this->_initialize();
		$message = $this->flashMessenger()->getMessages();
		$settings = new \Admin\Model\Settings($this->_dbAdapter);
		
		$request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            foreach ($data as $key => $value) {
				$settings->setDataByColumn('code', $key, array('value' => $value));
			}
			$message[0] = array('success' => $this->config['message']['saved_successfully']);
        }
        
        $data = $settings->getData();
        $vm = new ViewModel();
        $vm->setVariable('title', 'System Configaration');
        $vm->setVariable('userinfo', $this->userinfo);
        $vm->setVariable('settings', $data);
        $vm->setVariable('flashMessages', $message);
        return $vm;
	}
    
}