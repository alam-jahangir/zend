<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Model\Users;
use User\Model\Autheticate;
use User\Model\Validation;

class IndexController extends AbstractActionController {
    
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
        $this->config['pagination']['per_page'] = 20;
        $this->config['pagination']['page_range'] = 8;
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
    * Show My Account
    */
    public function indexAction() 
    {        
        if ($this->_isLoggedIn()) {
            //$this->layout( 'layout/admin_layout' );
            return new ViewModel(array(
                'title' => 'Wiijoo :: My Account',
                'flashMessages' => $this->flashMessenger()->getMessages()
            ));    
        } else {
            return $this->redirect()->toRoute('login');
        }
    }
    
    public function signupAction() 
    {
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('application');
        }
        
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        $request = $this->getRequest();        
        //Check is Form Post.
        if ($request->isPost()) {
            $data = $request->getPost();
            if(isset($data['passkey']) && $data['passkey'] != ''){
                $data['invite_user_id'] = base64_decode($data['passkey']);
            }
            unset($data['passkey']);
            
            $data->password = $this->generatePassword(8);
            $data->confirm_password = $data->password;
            
            $message = $this->_saveUser($data);
        }        
        //$this->layout( 'layout/login' );
        return new ViewModel(array(
            'title' => 'Wiijoo :: Sign Up',
            'flashMessages' => $message
        ));
    }
    
    // auto generated password
    public function generatePassword($length = 8) 
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);
    
        for ($i = 0, $result = ''; $i < $length; $i++) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }
    
        return $result;
    }
    
    /**
     * Save Frontend User Information
     * @param   $data   array
     */
    private function _saveUser($data = array()) 
    {
        
        $validation = new Validation();
        if ($validation->isValidRegistrationData($data, $this->_dbAdapter)) {
            $message = $this->_setUserData($data);
        } else {
            $message[0] = array('error' => $validation->message);
            return $message;
        }        
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
    
    public function resetPasswordAction() {
        
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('user');  
        } 
        
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        $request = $this->getRequest();               
        $user = new \User\Model\User($this->_dbAdapter);
        
        
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
	    $resetkey = $matches->getParam('key', 0);
        
        $userInfo = $user->getDataByColumn('reset_password_key', $resetkey);
        if ($userInfo) {                
            //Check is Form Post.
            if ($request->isPost()) {
                $data = $request->getPost();
                if ($data->password == $data->confirm_password) {
                    unset($data->confirm_password);
                    $data->reset_password_key = '';
                    $data->password = \User\Model\GeneratePassword::generate($data->password);
                    //echo '<Pre />';
                    //print_r($data); exit;
                    if ($user->setDataByColumn('user_id', $userInfo->user_id, $data->toArray())) {
                        $this->flashMessenger()->addMessage(array('success' => 'Successfully changed your password.'));
                        return $this->redirect()->toRoute('login', array('action' =>  'login'));
                    } else {
                        $message[0] = array('error' => 'Failed to change your password.');
                    }
                } else {
                    $message[0] = array('error' => 'Password and Confirm Password is not same.');
                }
            }
        } else {
            $message[0] = array('error' => 'Activation key is expired');
        }
        return array(
            'title' => 'Wiijoo :: Change Your Password',
            'flashMessages' => $message,
            'userInfo' => $userInfo
        );
    }

    public function forgotAction() 
    {
        
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('user');  
        } 
        
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        $request = $this->getRequest();               
        
        //Check is Form Post.
        if ($request->isPost()) {
            $data = $request->getPost();        
            $validation = new Validation();
            if ($validation->isValidForgotPasswordData($data, $this->_dbAdapter)) {
                
                $data->email_address = trim(strip_tags($data->email_address));
                
                $settings = new \Admin\Model\Settings($this->_dbAdapter);
                $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
                
                $configaration += $data->toArray();
                $hashKey = md5(microtime());
                
                $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
                $configaration['reset_password_url'] = $renderer->serverUrl().$renderer->url('reset_password', array('action' => 'reset-password', 'key' => $hashKey));
                
                $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'account_password_reset_confirmation.html');
                foreach ($configaration as $key => $value) {
                    $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
                }
                
                $user = new \User\Model\User($this->_dbAdapter);
                $saveData['reset_password_key'] = $hashKey;
                if ($user->setDataByColumn('email_address', $configaration['email_address'], $saveData)) {
                    
                    if (\Application\Model\ElasticEmail::send($emailTamplate, '', 'Forgot Password', 
                                                       $configaration['website_name'], $configaration['admin_email_address'], 
                                                       $configaration['email_address'])) {
                        //$this->flashMessenger()->addMessage(array('success' => 'Email sent successfully.'));
                        $message[0] = array('success' => 'Email sent successfully.');
                    } else {
                        //$this->flashMessenger()->addMessage(array('error' => 'Failed to sent email. Please try again.'));
                        $message[0] = array('error' => 'Failed to sent email. Please try again.');
                    }
                } else {
                    $message[0] = array('error' => 'Email address is not exist.');
                }
                               
            } else {
                $message[0] = array('error' => $validation->message);
            }    
        }      
        
        //$this->layout( 'layout/login' );
        return array(
            'title' => 'Wiijoo :: Reset Your Password',
            'flashMessages' => $message
        );
    }
    
    /**
    * Show User Account Info
    */
    public function accountAction() 
    {
        if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }

        $user = new \User\Model\User($this->_dbAdapter);
        
        $userid = $this->userInfo->user_id;
                
        $editUserInfo = array();
        if ($userid)
            $editUserInfo = $user->getData($userid)->toArray();
                    
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        //$this->layout( 'layout/login' );
        return new ViewModel(array(
            'title' => $editUserInfo ? 'Wiijoo :: My Account- '.$editUserInfo[0]['first_name'].' '.$editUserInfo[0]['last_name']: 'Wiijoo :: My Account',
            'userinfo' => $this->userInfo,
            'editUserInfo' => $editUserInfo,
            'flashMessages' => $message
        ));
        
    }
    
    /**
     * Save Frontend User Information
     * @param   $data   array
     */
    public function updateAction() 
    {
        
        if ($this->_isLoggedIn()) {
            $userid = $this->userInfo->user_id; 
            
            $result = array('error' => 0, 'msg' => '');
            
            $validation = new Validation();
            
            $request = $this->getRequest();        
            //Check is Form Post.
            if ($request->isPost()) {
                $data = $request->getPost();       
                if(isset($data['address_id'])){ 
                    
                    $data['user_id'] = $userid;
                    //print_r($data->toArray());
                    $Address = new \User\Model\Address($this->_dbAdapter);
                                    
                    if ($get_address_id = $Address->setAddressData($userid, $data['address_id'], $data->toArray())) {
                        $result['msg'] = 'Successfully Updated';
                        $result['address_id'] = $get_address_id;
                    } else {
                        $result['msg'] = 'Failed to save data. Please try again.';
                        $result['error'] = 1;
                    }
                } elseif(isset($data['facebook_url'])){
                    
                    $data['is_social_updated'] = 1;    
                    $message = $this->_setUserData($data, $userid);
                    $result['msg'] = 'Social profiles data successfully updated';
                    
                } elseif(isset($data['username'])){
                    
                    if ($validation->isAccountUpdateValidData($data, $this->_dbAdapter)) 
                    {
                        $data['is_username_updated'] = 1;
                        $this->_setUserData($data, $userid);
                        $result['msg'] = 'Username successfully updated';                               
                    } else {
                        $result['msg'] = $validation->message;
                        $result['error'] = 1;
                    }    
                    
                } elseif(isset($data['password']) && isset($data['confirm_password'])){
                    
                    if ($validation->isAccountUpdateValidData($data, $this->_dbAdapter)) 
                    {                        
                        if (isset($data['confirm_password']))
                            unset($data['confirm_password']);
                            
                        $data['is_password_updated'] = 1;    
                        $this->_setUserData($data, $userid);
                        $result['msg'] = 'Password Successfully Changed';                               
                    } else {
                        $result['msg'] = $validation->message;
                        $result['error'] = 1;
                    }    
                    
                } elseif(isset($data['email_address'])){
                    
                    $data['date_of_birth'] = $data['dob_year'].'-'.$data['dob_month'].'-'.$data['dob_date'];
                    
                    $useremail_address = $this->userInfo->email_address; 
                    
                    $this->userInfo->email_address = trim($data['email_address']);
                    
                    if(trim($useremail_address) == trim($data['email_address'])){
                        unset($data['email_address']);
                        $this->userInfo->email_address = $useremail_address;
                    }
                    
                    if ($validation->isAccountUpdateValidData($data, $this->_dbAdapter)) 
                    {                        
                        if (isset($data['dob_year']) || isset($data['dob_month']) || isset($data['dob_date']))
                            unset($data['dob_year']);unset($data['dob_month']);unset($data['dob_date']);
                            
                        $this->_setUserData($data, $userid);
                        
                        $this->userInfo->first_name = $data['first_name'];
                        $this->userInfo->last_name = $data['last_name'];
                        $this->userInfo->last_name = $data['last_name'];
                        
                        $this->auth->clearIdentity();
                        $this->auth->setStorage($this->userInfo);
                        
                        $result['msg'] = 'Profile details updated';                               
                    } else {
                        $result['msg'] = $validation->message;
                        $result['error'] = 1;
                    }                        
                } elseif(isset($data['is_subscribe'])){
                    
                    $this->_setUserData($data, $userid);
                    $result['msg'] = 'Newsletter subscription updated';
                    
                } else {
                    $result['error'] = 1;
                    $result['msg'] = 'Submitted data error';
                }  
            } else {
                $result['error'] = 1;
                $result['msg'] = 'No data submitted';
            } // is post closing        
        } else {
            $result['error'] = 1;
            $result['msg'] = 'session_expired';
        }// is login checking
        
        echo json_encode($result);
        exit(); 
        
        /*$viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;*/        
    }
    
    
    public function shortcutSignupAction() {
        if ($this->_isLoggedIn()) {
            return $this->redirect()->toRoute('application');
        }
        
        $request = $this->getRequest();        
        //Check is Form Post.
        if ($request->isPost()) {
            $data = $request->getPost();
            if (isset($data['passkey']) && $data['passkey'] != ''){
                $data['invite_user_id'] = base64_decode($data['passkey']);
                unset($data['passkey']);
            }
            
            $data->password = $this->generatePassword(rand(8, 12));
            $data->confirm_password = $data->password;
            $data->username = '';
            $data->first_name = '';
            $data->last_name = '';
            
            $validation = new Validation();
            if ($validation->validateEmailAddress($data, $this->_dbAdapter)) {
                $message = $this->_setUserData($data);
                return $this->redirect()->toRoute('user', array('action' => 'account'));
            } else {
                $this->flashMessenger()->addMessage(array('error' => $validation->message));
                return $this->redirect()->toRoute('user', array('action' => 'forgot'));  
            }   
        }
        return $this->redirect()->toRoute('user', array('action' => 'forgot'));         
         
    }
    
    /**
     * Save User Information
     * @param   $data       array
     * @param   $userid     int
     */
    private function _setUserData($data = array(), $userid = 0)
    {
        
        $user = new \User\Model\User($this->_dbAdapter);
        
        $data['updated_date'] = date('Y-m-d H:i:s');

        if($userid == 0){
            $data['created_date'] = date('Y-m-d H:i:s');
            unset($data['updated_date']);
        }
        
        $confirm_password_value = '';
        if (isset($data['confirm_password'])){
            $confirm_password_value = $data['confirm_password']; 
            unset($data['confirm_password']);
        }
                                
        if ( $lastInsertId = $user->setData($userid, $data->toArray())) {
            
            if($userid == 0 && $lastInsertId != 0){
                
                $packagesModel = new \Account\Model\Packages($this->_dbAdapter);
                               
                // for invite user promotion                
                $result = $data->toArray();                
                if(isset($result['invite_user_id']) && intval($result['invite_user_id']) ) {               
                    $packageInfo2 = $packagesModel->getData(8, 2)->toArray();
                    $users_promotion_data = array(
                        'user_id' => $result['invite_user_id'],
                        'package_id' => $packageInfo2[0]['package_id'],
                        'price_per_bid' => $packageInfo2[0]['price_per_bid'],
                        'bid_left' => $packageInfo2[0]['available_bid'],
                        'created_date' => date('Y-m-d H:i:s')
                    );
                    $insert2 = $sql->insert('users_balance')
                                  ->values($users_promotion_data);
                    $statement = $sql->prepareStatementForSqlObject($insert2);
                    $statement->execute(); 
                }
                
            }
            
            if ($userid == 0){
                $settings = new \Admin\Model\Settings($this->_dbAdapter);
                $configaration = $settings->getCacheData(array('admin_email_address', 'website_url', 'logo_url', 'website_name'));
                $data->confirm_password = $confirm_password_value;
                $configaration += $data->toArray();
                $emailTamplate = @file_get_contents(BASE_PATH.DS.'app'.DS.'email_tamplate'.DS.'account_new.html');
                foreach ($configaration as $key => $value) {
                    $emailTamplate = str_replace('{{'.$key.'}}', $value, $emailTamplate);
                }
                if (\Application\Model\ElasticEmail::send(
                    $emailTamplate, '', 
                    'Welcome to wiijoo.com - your user details', 
                    $configaration['website_name'], $configaration['admin_email_address'], 
                    $configaration['email_address']
                )) {
                    $this->flashMessenger()->addMessage(array('success' => 'Registration complete. Email sent successfully.'));
                } else {
                    $this->flashMessenger()->addMessage(array('success' => 'Registration complete. Failed to send email.'));    
                }
                
                //return $this->redirect()->toRoute('home');
                if ($this->userInfo = $this->auth->authenticate($this->_dbAdapter, $data, 'users', 'email_address', 'password', 
                                              array('username', 'first_name', 'last_name', 'email_address', 'user_id'), 1209600)) {
                    
                    $user->setData($this->userInfo->user_id, array('last_login' => date('Y-m-d H:i:s')));
                    return $this->redirect()->toRoute('user', array('action' => 'account'));   
                }
            }
            
        } else {
            $message[0] = array('error' => 'Failed to save data. Please try again.');
            return $message;
        }
    }
    
    
    public function myBidsAction() {
        if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }

        $bidLog = new \Application\Model\UserBidLog($this->_dbAdapter);
        
        $userid = $this->userInfo->user_id;
        $bidLogInfo = $bidLog->getUserBidLog($userid);
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        $page = $matches->getParam('page', 1);
        
        $paginator = \Application\Model\Pagination::loadPaginator($bidLogInfo, $page, $this->config);
        
        $userBalance = new \Application\Model\UserBalance($this->_dbAdapter);
        $userBalanceInfo = $userBalance->getUserBalanceLeft($userid)->toArray();
        
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        //$this->layout( 'layout/login' );
        return new ViewModel(array(
            'title' => 'Wiijoo :: My Bids',
            'userinfo' => $this->userInfo,
            'paginator' => $paginator,
            'userBalanceInfo' => $userBalanceInfo,
            'flashMessages' => $message
        ));
        
    }
    
    
    public function wonAuctionsAction() {
       if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }

        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        
        $userid = $this->userInfo->user_id;
        $products = new \Admin\Model\Products($this->_dbAdapter);
        $productList = $products->getWinProductByUser($userid); 
        
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        $page = $matches->getParam('page', 1);
        $paginator = \Application\Model\Pagination::loadPaginator($productList, $page, $this->config);
        
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        return new ViewModel(
            array(
                'title' => 'Wiijoo :: My Won Auctions',
                'paginator' => $paginator,
                'userinfo' => $this->userInfo,
                'flashMessages' => $message
            )
        );
        
    }
    
    public function myBidAgentsAction() {
        if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }

        $userBidAgent = new \Application\Model\UserBidAgent($this->_dbAdapter);
        
        $userid = $this->userInfo->user_id;
        
        //Get Url Routing Information
        $matches = $this->getEvent()->getRouteMatch();
        $page = $matches->getParam('page', 1);
        
        $userAgentInfo = $userBidAgent->getUserAgentData($userid);
        $paginator = \Application\Model\Pagination::loadPaginator($userAgentInfo, $page, $this->config);
                    
        //Get Flash Message
        $message = $this->flashMessenger()->getMessages();
        
        //$this->layout( 'layout/login' );
        return new ViewModel(array(
            'title' => 'Wiijoo :: My Bid Agents',
            'userinfo' => $this->userInfo,
            'paginator' => $paginator,
            'flashMessages' => $message
        ));
        
    }
    
    public function deleteAgentAction() {
        if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }

        $userBidAgent = new \Application\Model\UserBidAgent($this->_dbAdapter);
        $matches = $this->getEvent()->getRouteMatch();
        $agentId = $matches->getParam('id', 0);
        $data['status'] = 2;
        if ($userBidAgent->setData($agentId, $data)) {
            $this->flashMessenger()->addMessage(array('success' => 'Deleted successfully'));
        } else {
            $this->flashMessenger()->addMessage(array('error' => 'Failed to delete data. Please try  again.'));
        }
        return $this->redirect()->toRoute('user', array('action' =>  'my-bid-agents'));
    }
    
    
    
    /**
     * Newest User List
     */
    public function newestUserListAction() 
    {        
        $this->_initialize();
        
        $result = array('html' => '-- No User Found --');
        
        $userModel = new \User\Model\User($this->_dbAdapter);
        $newestUserList = $userModel->getNewestUser()->toArray();

        if(count($newestUserList) > 0)
        {
            $html = ''; 
            foreach($newestUserList as $user){
                $html .= '<li>
                            '.$user['username'].' ( '.$user['email_address'].' )
                        </li>';
            }
            $result['html'] = $html;
        }
        
        echo json_encode($result);
        exit(); 
    }
    
    /**
     * Buy Won Auctions action
     */
    public function buyWonAuctionAction()  
    {        
        if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }
        
        $message = array();
        $data = array();
        
        $matches = $this->getEvent()->getRouteMatch();
        
        //Get Product Id
        $productid = intval($matches->getParam('id', 0));
        $data['product_id'] = $productid; 
                
        //Get Logged In Userid
        $userid = isset($this->userInfo->user_id) ? $this->userInfo->user_id : 0;

        //Product Details
        $products = new \Admin\Model\Products($this->_dbAdapter);
        $productDetails = $products->getData($productid)->toArray(); 
        if (!$productDetails) {
            throw new \Exception('Product Not Found');
        }
        if($productDetails[0]['status'] != 5 ){
            $this->redirect()->toRoute('won_auctions', array('action' => 'won-auctions', 'id' => $productid));
        }
        // get won product bid price
        $wonProductHistory = $products->getWinProductById($userid, $productid)->toArray();
        if (empty($wonProductHistory) || ($wonProductHistory[0]['transaction_id'] > 0)) {
            $this->redirect()->toRoute('won_auctions', array('action' => 'won-auctions', 'id' => $productid));
        }
        
        //Shipping Charge
        $shippingCharge = array();
        if ($userid) {
            $shippingModel = new \Admin\Model\Shipping($this->_dbAdapter);
            $shippingCharge = $shippingModel->getShippingChargeFrontend($userid)->toArray();
        } 
                
        // get Terms and condition page content
        $cmsPages = new \Admin\Model\CmsPages($this->_dbAdapter);
        $tearmsAndConditionPage = $cmsPages->getDataByIdentifier('terms-and-conditions')->toArray();
        
        // For an auction payment
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            if ($userid) {
                
                $data['product_name'] = $productDetails[0]['name'];
                
                $shippingCost = isset($shippingCharge[0]['shipping_cost'])  ? $shippingCharge[0]['shipping_cost'] : 0;
                
                // Load helper
                $renderer = $this->serviceLocator->get('Zend\View\Renderer\RendererInterface');
                $price = $renderer->pricePerBid(intval($wonProductHistory[0]['total_bid']), $productDetails[0]['actual_price']);
                $data['amount'] = number_format(($price['total_price']+$shippingCost), 2, '.', '');  
                $data['shipping_charge'] = number_format($shippingCost , 2, '.', '');
                
                $data += $request->getPost()->toArray();
                
                $user = new \User\Model\User($this->_dbAdapter);
                            
                $data2 = array();     
                if ($userid){
                    $data2 = $user->getData($userid)->toArray(); 
                    $data += array_pop($data2);
                }
                
                /*echo '<pre>';
                print_R($data);
                die();*/
                
                $message = $this->_saveWinAuctionPayment($data);

                if(isset($message['success'])){
                    $this->flashMessenger()->addMessage($message);
                    $this->redirect()->toRoute('won_auctions', array('action' => 'won-auctions', 'id' => $productid));
                }
                
            } else {
                $this->redirect()->toRoute('login', array('action' =>  'login'));        
            }                
        }    
              
        return new ViewModel(
            array(
                'title' => 'Wiijoo :: '.$productDetails[0]['name'],
                'productData' => $productDetails,
                'shippingCharge' => $shippingCharge,
                'tearmsAndConditionPage' => $tearmsAndConditionPage,
                'wonProductHistory' => $wonProductHistory,
                'userInfo' => $this->userInfo,
                'flashMessages' => $message
            )
        );
        
    }
    
    private function _saveWinAuctionPayment($data = array())
    {        
        $this->_initialize();
        
        $paypal = new \Account\Model\Payment($this->config, $this->_dbAdapter);
        
        $response = $paypal->request($data);
        
        /*echo '<pre>';
        print_r($response);
        exit();*/
        
        if( isset($response) && $response['ACK'] == 'Success')
        {            
            $transaction_arr = array(
            					'user_id' => $data['user_id'], 
            					'product_id' => $data['product_id'], 
            					'payment_method_trasaction_id' => $response['TRANSACTIONID'], 
            					'amount' => $response['AMT'], 
            					'shipping_charge' => $data['shipping_charge'], 
            					'ack' => $response['ACK'], 
            					'cc_number' => substr($data['field2'], -4), 					
            					'transaction_purpose' => 'Win & Buy this product "'.$data['product_name'].'" ',
            					'created_date' => date('Y-m-d H:i:s')
            					);
            
            if ($paypal->setWonAuctionPaymentData(0, $transaction_arr)) {
                $message = array('success' => 'Win auction payment successfully done.');
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
    
    
    /**
    * User Account Promotion Function
    */
    public function promotionAction() 
    {
        if (!$this->_isLoggedIn()) {
            return $this->redirect()->toRoute('login', array('action' =>  'login'));
        }
        
        $message = array();
                        
        $userid = $this->userInfo->user_id;
       
        // For an auction payment
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $data = $request->getPost()->toArray(); 
            
    	    $promotionModel = new \Admin\Model\Promotions($this->_dbAdapter);
            $message[] = $promotionModel->submitPromotionCode($userid, $data);
            
            $this->flashMessenger()->addMessage($message);
            
        }  
        
        //$this->layout( 'layout/login' );
        return new ViewModel(array(
            'title' => 'Wiijoo :: Promotion',
            'userinfo' => $this->userInfo,
            //'editUserInfo' => $editUserInfo,
            'flashMessages' => $message
        ));
        
    }

}