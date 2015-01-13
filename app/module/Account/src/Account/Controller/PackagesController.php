<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Users\Model\Users;
use User\Model\Autheticate;
//use User\Model\Validation;

class PackagesController extends AbstractActionController {
    
    /**
     * @var Session\Model\Autheticate
     */
    protected $auth;
    
    /**
     * @var Object
     */
    protected $userInfo;
     
     /**
      * @var Zend\Db\Adapter\Adapter
      */
     private $_dbAdapter;
     
    
    private function _initialize() 
    {
        $this->_dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $this->config = $this->getServiceLocator()->get('Config');
    }    
    
    private function _isLoggedIn($loggedout = 0) 
    {
        $this->_initialize();
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
    
    /**
    * Show packages list
    */
    public function indexAction() 
    {   
        $this->_initialize();
        
        $packagesModel = new \Account\Model\Packages($this->_dbAdapter);
        $packagesList = $packagesModel->getPackagesList();
        
        if ($this->_isLoggedIn()) {
            return new ViewModel(array(
                'title' => 'Wiijoo :: Packages',
                'packagesList' => $packagesList,
                'flashMessages' => $this->flashMessenger()->getMessages()
            ));    
        } else {
            return $this->redirect()->toRoute('login');
        }
    }    
    
    /**
    * Show packages details
    */
    public function buyCreditAction() 
    {   
        $this->_isLoggedIn();
        
        $packagesModel = new \Account\Model\Packages($this->_dbAdapter);
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
        $package_id = $matches->getParam('id', 0);
        
        $message = $this->flashMessenger()->getMessages();
        
        $packageInfo = array();
        if ($package_id) {
            $packageInfo = $packagesModel->getData($package_id)->toArray();
        } else {
            return $this->redirect()->toRoute('packages');
        }
        
        if(empty($packageInfo)){
            return $this->redirect()->toRoute('packages');
        }
        
        // For payment funcion
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            
            $user = new \User\Model\User($this->_dbAdapter);
            
            $userid = $this->userInfo->user_id;
            
            $data2 = array();     
            if ($userid){
                $data2 = $user->getData($userid)->toArray(); 
                $data += array_pop($data2);
            }
            
            if($package_id){
                $data['amount'] = number_format(($packageInfo[0]['price_per_bid']*$packageInfo[0]['available_bid']), 2, '.', '');
                $data['package_id'] = $package_id;
                $data['available_bid'] = $packageInfo[0]['available_bid'];
                $data['price_per_bid'] = $packageInfo[0]['price_per_bid'];
                $data['package_name'] = $packageInfo[0]['package_name'];
            }
        /*echo '<pre>';
            print_r($data);*/
            $message = $this->_savepayment($data);                        
        }        
        
        $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
        $tearmsAndConditionPage = $cmsPages->getDataByIdentifier('terms-and-conditions')->toArray();
        
        if ($this->_isLoggedIn()) {
            return new ViewModel(array(
                'title' => 'Wiijoo :: Buy Credits',
                'packageInfo' => $packageInfo,
                'tearmsAndConditionPage' => $tearmsAndConditionPage,
                'flashMessages' => $message
            ));    
        } else {
            return $this->redirect()->toRoute('login');
        }
    }    
    
    private function _savepayment($data = array())
    {        
        $this->_initialize();
        
        $paypal = new \Account\Model\Payment($this->config, $this->_dbAdapter);
        
        
        $response = $paypal->request($data);
        
        /*echo '<pre>';
        print_r($response);
        exit();*/
        
        if(isset($response['ACK']) && $response['ACK'] == 'Success') {            
            $transaction_arr = array(
            					'user_id' => $data['user_id'], 
            					'package_id' => $data['package_id'], 
            					'payment_method_trasaction_id' => $response['TRANSACTIONID'], 
            					'amount' => $response['AMT'], 
            					'ack' => $response['ACK'], 
            					'cc_number' => substr($data['field2'], -4), 					
            					'transaction_purpose' => $data['available_bid'].' '.$data['package_name'],
            					'price_per_bid' => $data['price_per_bid'],
            					'available_bid' => $data['available_bid'],
            					'created_date' => date('Y-m-d H:i:s')
            					);
            
            if ($paypal->setData(0, $transaction_arr)) {
                $message[0] = array('success' => 'Payment successfully done.');
            } else {
                $message[0] = array('error' => 'Failed to save data. Please try again.');
            }
        
        } else{
            $message[0] = array('error' => 'Payment failed.');
        }
        
        /*echo '<pre>';
        print_r($response);
        die();*/
        
        return $message;
        
    }

}