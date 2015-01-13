<?php
namespace Application\Model;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as SessionStorage;

class Autheticate {
    
    /**
     * Authetication
     * Zend\Authentication\AuthenticationService
     */
    protected $auth;
    
    /**
     * Session Storage Name
     */
    protected $sessionStorageName; 
    
    
    /**
     * Session Config Information
     */ 
    protected $sessionConfig;
    
    
    public function __construct($config = array(), $session_name = 'user') 
    {
        $this->sessionConfig = new \Zend\Session\Config\SessionConfig();
        //$this->sessionConfig->setSavePath($config['session']['save_path']); 
   
        //Initialize Authentication Service
        $this->auth = new AuthenticationService();
        $this->sessionStorageName = $session_name;
    }
    
    
    /**
     * Set cookie
     */
    public function setRememberMe($rememberMe = 0, $time = 2419200) 
    {
         if ($rememberMe) {
             $config = array(
                        'remember_me_seconds' => $time,
                        'use_cookies' => true,
                        'cookie_httponly' => true,
                    );
             $this->_sessionConfig->setOptions($config);
             $sessionManager = new \Zend\Session\SessionManager($this->sessionConfig, null, null);
         } else {
            $config = array(
                        'use_cookies' => false,
                        'cookie_httponly' => false,
                    );
             $this->_sessionConfig->setOptions($config);
             $sessionManager = new \Zend\Session\SessionManager($this->sessionConfig, null, null);
         }
    }
    
       
    /**
     * Read Authentication 
     * @return  object|bool
     */
    public function getIdentity() 
    {
        $this->_session = new SessionStorage($this->sessionStorageName);
        $this->auth->setStorage($this->_session);
        
         if ($this->auth->hasIdentity()) {
            $identity = $this->auth->getIdentity();
            return $identity;
         }
         return false;
    }
    
    
    /**
     * Clear Authentication Information
     * @return  bool
     */
    public function clearIdentity() 
    {
        $this->_session = new SessionStorage($this->sessionStorageName);
        $this->auth->setStorage($this->_session);
        if ($this->auth->hasIdentity()) {
            $identity = $this->auth->clearIdentity();
            return true;
        }
        return false;
         
    }
    
    /**
     * Write Authentcation Information
     * @param   $dbAdapter      Zend\Db\Adapter\Adapter
     * @param   $data           array
     * @param   int
     * @return  object|bool
     */ 
    public function authenticate(\Zend\Db\Adapter\Adapter $dbAdapter, $data = array(), $tablename, $identityColumn, 
                                $credentialColumn, $columnsToStore = array(), $time = 1209600) 
    {
        
        $authAdapter = new AuthAdapter(
            $dbAdapter, 
            $tablename,  //Table Name
            $identityColumn,  //Identity Column
            $credentialColumn, //Credential Column
            //'MD5(?) AND is_active = 1' //$condition
            'MD5(?) AND is_active=1'
        );

        $authAdapter->setIdentity($data[$identityColumn])
                    ->setCredential($data['password']);
        $result = $this->auth->authenticate($authAdapter);
        if ($result->isValid()) {
            
            //Set Remember Me
            if (isset($data['remember_me']) && $data['remember_me']) {
                //$this->setRememberMe($data['remember_me']);
            }
            
            if ($columnsToStore)
                $data = $authAdapter->getResultRowObject($columnsToStore);
            else
                $data = $authAdapter->getResultRowObject();
            
            $this->setStorage($data, $dbAdapter);
            
			return $data;
        }
        return false;
    }
    
    
    /**
     * Store Data in Session
     * @param   $data           array
     * @param   $dbAdapter      Zend\Db\Adapter\Adapter
     * @return  $data           array
     */    
    public function setStorage($data = null, \Zend\Db\Adapter\Adapter $dbAdapter = null) 
    {
       
        $storage = $this->auth->getStorage();
        
        $this->_session = new SessionStorage($this->sessionStorageName);
        $this->auth->setStorage($this->_session);
        
        $storage->write($data);
        
        return $data;
    }
    
}

