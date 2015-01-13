<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use User\Model\Autheticate;
use User\Model\Validation;

class LoginController extends AbstractActionController {
    
    /**
     * @var Session\Model\Autheticate
     */
    protected $auth;
    
    /**
     * @var Object
     */
    protected $userInfo;
    
    
    private function _isLoggedIn($loggedout = 0) 
    {
        $this->config = $this->getServiceLocator()->get('Config');
        $sessionConfig = $this->getServiceLocator()->get('SessionConfig');
        $this->auth = new Autheticate($sessionConfig, $this->config['session_storage_name']['user']);
        if ($loggedout) {
            $this->auth->clearIdentity();
            $this->flashMessenger()->addMessage(array('success' =>'You are now logged out.'));
            return true;
        }
        
        $this->userInfo = $this->auth->getIdentity();
        //If Logged In Redirect To Welcome Page
        if ($this->userInfo) {
            return true;
        }
    }
    
    public function loginAction() 
    {
        
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('home');
        }
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $validation = new Validation();
            if ($validation->isLoginValid($data)) {
                $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                if ($this->userInfo = $this->auth->authenticate($dbAdapter, $data, 'users', 'email_address', 'password', 
                                              array('username', 'first_name', 'last_name', 'email_address', 'user_id'), 1209600)) {
                                                
                    $user = new \User\Model\User($dbAdapter);
                    if($user->setData($this->userInfo->user_id, array('last_login'=>date('Y-m-d H:i:s')))){
                        return $this->redirect()->toRoute('user');
                    }
                    
                } else {
                    /*$this->flashMessenger()->addMessage(array('error' => 'Username/Password is not correct.'));    
                    return $this->redirect()->toRoute('login');*/
                    $message[] = array('error' => 'Username/Password is not correct.');
                }
            } else {
                /*$this->flashMessenger()->addMessage(array('error' => $validation->message));
                return $this->redirect()->toRoute('login');*/
                $message[] = array('error' => $validation->message);
            }
        }
        
        //$this->layout( 'layout/admin_login' );
        
        return new ViewModel(array(
            'title' => 'Wiijoo :: Login',
            'flashMessages' => $message
        ));
    }
        
    public function logoutAction() 
    {
        if ($this->_isLoggedIn(1)) {
            return $this->redirect()->toRoute('login', 
                    array(
                        'action' =>  'login'
                    )
            );
        } 
        //else {
        //    return $this->redirect()->toRoute('application');
        //}
    }

}