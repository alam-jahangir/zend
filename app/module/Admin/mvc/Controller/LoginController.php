<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Model\Autheticate;
use Admin\Model\Validation;

class LoginController extends AbstractActionController {
    
    /**
     * @var Admin\Model\Autheticate
     */
    protected $auth;
    
    /**
     * @var Object
     */
    protected $userinfo;
    
    /**
    * @var Object
    */
    protected $config;
    
    
    private function _isLoggedIn($loggedout = 0) 
    {
        $this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->config = $this->getServiceLocator()->get('Config');
        $this->auth = new Autheticate($this->config, $this->config['session']['admin']);
        
        if ($loggedout) {
            $this->auth->clearIdentity();
            $this->flashMessenger()->addMessage(
				array(
					'success' => $this->config['message']['logout']
				)
			);
            return true;
        }
        
        $this->userinfo = $this->auth->getIdentity();
        
        //If Logged In Redirect To Welcome Page
        if ($this->userinfo) {
        	return true;
        }
    }
    
    public function loginAction() 
    {
       	
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('admin');
        }
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
         
        if ($request->isPost()) {
            $data = $this->getRequest()->getPost();
            $validation = new Validation();
            if ($validation->isLoginValid($data)) {
                $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                $data->profile_name = $data->username;
                if ($this->userinfo = $this->auth->authenticate($dbAdapter, $data, 'admin_users', 'username', 'password', 
                                              array('username', 'first_name', 'last_name', 'user_id', 'avatar'), 1209600)) {
                    
                    $this->flashMessenger()->addMessage(
						array(
							'success' => $this->config['message']['loggedin']
						)
					);
                    
                    return $this->redirect()->toRoute('admin');
                    
                } else {
                    $message[] = array(
									'error' => $this->config['message']['login_failed']
								);
                }
            } else {
                $message[] = array('error' => $validation->message);
            }
        }
        
        $this->layout( 'layout/admin_login' );
       
        return new ViewModel(array(
            'flashMessages' => $message
        ));
    }
    
    
    
    public function logoutAction() {
        if ($this->_isLoggedIn(1)) {
            return $this->redirect()->toRoute('admin_login');
        } 
    }


    public function forgotPasswordAction() 
    {
        
        $this->_isLoggedIn();
        $message =$this->flashMessenger()->getMessages();
         
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $this->getRequest()->getPost();
            
            $settings = new \Admin\Model\Settings($this->_dbAdapter);
            $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
            
            $data->email_address = trim(strip_tags($data->email_address));
            $adminUsers = new \Admin\Model\AdminUsers($this->_dbAdapter);
            $adminUserInfo = $adminUsers->getDataByColumn('email_address', $data->email_address);
            
            if ($adminUserInfo) {
                
                $configaration['password'] = \User\Model\GeneratePassword::randomPassword();
                $saveData['password'] = \User\Model\GeneratePassword::generate($configaration['password'], 'admin');
                
                $configaration += (array)$adminUserInfo;
                $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'admin_password_reset_confirmation.html');
                foreach ($configaration as $key => $value) {
                    $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
                }
                
                if ($adminUsers->setDataByColumn('user_id', $adminUserInfo['user_id'], $saveData)) {
                    if (\Application\Model\ElasticEmail::send(
													$emailTamplate, 
													'', 
													'Forgot Password', 
                                                   	$configaration['website_name'], 
													$configaration['admin_email_address'], 
                                                   	$data->email_address)) {
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
        
        $this->layout( 'layout/admin_login' );
        return array(
            'title' => 'Retrieve your password',
            'flashMessages' => $message
        );
    }

}