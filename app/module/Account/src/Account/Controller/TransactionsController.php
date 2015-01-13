<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
//use Users\Model\Users;
use User\Model\Autheticate;
//use User\Model\Validation;

class TransactionsController extends AbstractActionController {
    
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
        $this->_isLoggedIn();
        
        $userid = $this->userInfo->user_id;
            
        $TransactionsModel = new \Account\Model\Transactions($this->_dbAdapter);
        $transactionsList = $TransactionsModel->getTransactionList($userid);
        
        if ($this->_isLoggedIn()) {
            return new ViewModel(array(
                'title' => 'Wiijoo :: My Transactions',
                'transactionsList' => $transactionsList,
                'flashMessages' => $this->flashMessenger()->getMessages()
            ));    
        } else {
            return $this->redirect()->toRoute('login');
        }
    }    
    
        
    /**
     * Contact Support
     */
    public function sendInvoiceAction() 
    {
        $this->_initialize();
        $message = array();
        
        $result = array('error' => 0, 'msg' => '');
            
        if ($this->_isLoggedIn()) {                                                    
            $request = $this->getRequest();
            if ($request->isPost()) {
                $data = $request->getPost();
                
                $TransactionsModel = new \Account\Model\Transactions($this->_dbAdapter);
                $transactionsDetails = $TransactionsModel->getData(base64_decode($data['tid']))->toArray();
                
                $transactionsDetails[0]['created_date'] = date( 'M jS, Y H:i:s', strtotime($transactionsDetails[0]['created_date']));
                $transactionsDetails[0]['amount'] = number_format($transactionsDetails[0]['amount'], 2, ',', ' ');
                $invoiceTitle = ''.$transactionsDetails[0]['transaction_id'];
                
                $userModel = new \User\Model\User($this->_dbAdapter);
                $userInfo = $userModel->getData($transactionsDetails[0]['user_id'])->toArray();
                $transactionsDetails[0]['first_name'] = $userInfo[0]['first_name'];
                $transactionsDetails[0]['last_name'] = $userInfo[0]['last_name'];
                $transactionsDetails[0]['email_address'] = $userInfo[0]['email_address'];
                                        
                $settings = new \Admin\Model\Settings($this->_dbAdapter);
                $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
                $configaration += array_pop($transactionsDetails);
    
                $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'invoice_email_template.html');
                foreach ($configaration as $key => $value) {
                    $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
                }
                if (\Application\Model\ElasticEmail::send(
                    $emailTamplate, '', 
                    '[Purchases Invoice - #'.str_pad($invoiceTitle, (11-strlen($invoiceTitle)), '0', STR_PAD_LEFT).']', 
                    $configaration['website_name'], 
                    $configaration['admin_email_address'], 
                    $configaration['email_address']
                )) {
                    $result['error'] = 0;
                    $result['msg'] = 'Email sent';
                } else {    
                    $result['error'] = 1;
                    $result['msg'] = 'Sending failed';
                }
                            
            } else {
                $result['error'] = 1;
                $result['error'] = 'Sending failed';
            }
        } else {
            $result['error'] = 1;
            $result['msg'] = 'session_expired';
        }                 
        
        echo json_encode($result);
        exit();
    }        

}