<?php
namespace Account\Model;
 
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;

class Payment extends AbstractTableGateway {
     
    //protected $environment = 'sandbox'; // or 'beta-sandbox' or 'live'
    
    public $response = null;
    
    protected $handler = null;
    
    protected $apiInfo = array();
    
    protected $table = 'users_transaction';
    
    
    public function __construct($apiInfo = array(), Adapter $adapter) {
        $this->apiInfo = $apiInfo;
        
        $this->adapter = $adapter;
        $this->initialize();
    }
    
    public function request($data = array()) {
        
        $expDateMonth = $data['month'];
        // Month must be padded with leading zero
        $padDateMonth = str_pad($expDateMonth, 2, '0', STR_PAD_LEFT);
        
        $expDateYear = $data['year'];
        $cvv2Number = $data['cvc_number'];
        $address1 = $data['street_address'];
        $address2 = $data['options'];
        $city = $data['town'];
        $state = $data['street_address'];
        $zip = $data['postcode'];
        $country = $data['country'];
        $countryCode = $data['ccode'];
        $amount = $data['amount'];
        
        $apiEndpoint = ($this->apiInfo['paypal']['environment'] == 'sandbox') ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
        
        $requestParams = array
					(
					'METHOD' => 'DoDirectPayment', 
					'USER' => $this->apiInfo['paypal']['api_username'], 
					'PWD' => $this->apiInfo['paypal']['api_password'], 
					'SIGNATURE' => $this->apiInfo['paypal']['api_signature'], 
					'VERSION' => $this->apiInfo['paypal']['version'], 
					'PAYMENTACTION' => $this->apiInfo['paypal']['paymentType'], 					
					//'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
					'CREDITCARDTYPE' => $data['cardType'], 
					'ACCT' => $data['field2'], 						
					'EXPDATE' => $padDateMonth.$expDateYear, 			
					'CVV2' => $cvv2Number, 
					'FIRSTNAME' => $data['first_name'], 
					'LASTNAME' => $data['first_name'], 
					'EMAIL' => $data['email_address'], 
					'STREET' => $address1, 
					'CITY' => $city, 
					'STATE' => $state, 					
					'COUNTRYCODE' => $countryCode, 
					'ZIP' => $zip, 
					'AMT' => $amount, 
					'CURRENCYCODE' => 'EUR', // or other currency ('USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD')
					'DESC' => 'Testing Payments Pro' 
					);
                    
   	    if ($this->apiInfo['paypal']['environment'] != 'sandbox') 
            $requestParams['IPADDRESS'] = $_SERVER['REMOTE_ADDR'];
            
        // Loop through $requestParams array to generate the NVP string.
        $nvpString = '';
        foreach($requestParams as $var => $val) {
        	$nvpString .= '&'.$var.'='.urlencode($val);	
        }
        //$nvpString = trim('&', $nvp_string);
        
        //echo $nvpString; exit;
        // Send NVP string to PayPal and store response
        $curl = curl_init();
        		curl_setopt($curl, CURLOPT_VERBOSE, 1);
        		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        		curl_setopt($curl, CURLOPT_URL, $apiEndpoint);
        		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        		curl_setopt($curl, CURLOPT_POSTFIELDS, $nvpString);
        
        $result = curl_exec($curl);
        //echo $result.'<br /><br />';
        //print_r(curl_error($curl)); 
        curl_close($curl);
        
        // Parse the API response
        $this->response = $this->NVPToArray($result);
        /*
        echo '<pre />';
        print_r($result_array);
        die();*/
        //print_r($this->response);
         
        return $this->response;
        
    }
    
    public function response() {
        return $this->response;
    }
    
    // Function to convert NTP string to an array
    private function NVPToArray($NVPString)
    {
    	$proArray = array();
    	while(strlen($NVPString))
    	{
    		// name
    		$keypos= strpos($NVPString,'=');
    		$keyval = substr($NVPString,0,$keypos);
    		// value
    		$valuepos = strpos($NVPString,'&') ? strpos($NVPString,'&'): strlen($NVPString);
    		$valval = substr($NVPString,$keypos+1,$valuepos-$keypos-1);
    		// decoding the respose
    		$proArray[$keyval] = urldecode($valval);
    		$NVPString = substr($NVPString,$valuepos+1,strlen($NVPString));
    	}
    	return $proArray;
    }    
    
    public function setData($id = 0, $data = array()) {

        if ($id == 0) {
            if(isset($data['available_bid'])){
                $available_bid = $data['available_bid'];
                unset($data['available_bid']);
            }
            if(isset($data['price_per_bid'])){
                $price_per_bid = $data['price_per_bid'];
                unset($data['price_per_bid']);
            }
            if ($this->insert($data)) {
                $transaction_id = $this->getLastInsertValue();
                //set again
                $data['available_bid'] = $available_bid;
                $data['price_per_bid'] = $price_per_bid;
                $this->setUserBalance($data);
                return $transaction_id;
            }
            return 0;
        } else {
            throw new \Exception("Failed to save data.");
        }
    }
    
    public function setUserBalance($data = array()) 
    {
        $sql = new Sql($this->adapter);
        /*$select = $sql->select();
        $select->from('users_balance')
              ->where(array('user_id' => $data['user_id']));
              
        /*$selectString = $sql->getSqlStringForSqlObject($select);
        die($selectString);*/
        
        /*$statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $result_data = $resultSet->toArray();
        $total_data = count($result_data);*/
        
        /*if($total_data > 0){
            $users_balance_data = array(
                'user_balance' => $result_data[0]['user_balance']+$data['amount'],
                'bid_left' => $result_data[0]['bid_left']+$data['available_bid']
            );  
            $update = $sql->update('users_balance')
                      ->set($users_balance_data)
                      ->where(array('user_id' => $data['user_id']));
            $statement = $sql->prepareStatementForSqlObject($update);
            $statement->execute();       
        }
        else
        {*/
            $users_balance_data = array(
                'user_id' => $data['user_id'],
                'package_id' => $data['package_id'],
                'price_per_bid' => $data['price_per_bid'],
                'bid_left' => $data['available_bid'],
                'created_date' => $data['created_date']
            );
            $insert = $sql->insert('users_balance')
                          ->values($users_balance_data);
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();                           
        //}
    }   
    
    // BUY PRODUCT PAYMENT FUNCTION
    public function setAuctionData($id = 0, $data = array()) {

        if ($id == 0) {
            if ($this->insert($data)) {
                $transaction_id = $this->getLastInsertValue();
                $this->updateProductAndUserData($data);
                return $transaction_id;
            }
            return 0;
        } else {
            throw new \Exception("Failed to save data.");
        }
    } 
    
    // BUY PRODUCT PAYMENT FUNCTION
    public function updateProductAndUserData($data = array()) 
    {
        $sql = new Sql($this->adapter);
        
        // update product status
        $product_data = array(
            'status' => 4,
            'updated_date' => $data['created_date']
        );  
        $update_product = $sql->update('products')
                  ->set($product_data)
                  ->where(array('product_id' => $data['product_id']));
        $statement = $sql->prepareStatementForSqlObject($update_product);
        $statement->execute();  
        
        // get all refunded bid for this product
        $select = $sql->select();
        $select->from(array('u_b_l' => 'users_bid_log'))
              ->where(array('u_b_l.user_id' => $data['user_id'], 'u_b_l.product_id' => $data['product_id'], 'u_b_l.is_refund' => 2));
                      
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
        $result_data = $resultSet->toArray();
        $total_data = count($result_data);

        if($total_data > 0){
            
            foreach($result_data as $singleRow){
            
                // get latest bid_left value
                $select2 = $sql->select();
                $select2->from(array('u_b' => 'users_balance'))
                        ->where(array('u_b.user_id' => $data['user_id'], 'u_b.balance_id' => $singleRow['balance_id']));
                              
                $statement2 = $sql->prepareStatementForSqlObject($select2);
                $result2 = $statement2->execute();
                
                $resultSet2 = new ResultSet;
                $resultSet2->initialize($result2);
                $bid_left_result_data = $resultSet2->toArray();
            
                // update user_balance data
                $users_balance_data = array(
                    'bid_left' => $bid_left_result_data[0]['bid_left']+1
                );  
                $update = $sql->update('users_balance')
                          ->set($users_balance_data)
                          ->where(array('balance_id' => $singleRow['balance_id']));
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute(); 
                                
            }
            
            // update users_bid_log row
            $users_bid_log_data = array(
                'is_refund' => 1
            );  
            $update_bid_log = $sql->update('users_bid_log')
                      ->set($users_bid_log_data)
              ->where(array('user_id' => $data['user_id'], 'product_id' => $data['product_id']));
            $statement = $sql->prepareStatementForSqlObject($update_bid_log);
            $statement->execute();  
                           
        }
        
        // update lastbid_log table    
            
        $select1 = $sql->select();
        $select1->from(array('lb_l' => 'lastbid_log'))
          ->where(array('lb_l.product_id' => $data['product_id']));
          
        $statement1 = $sql->prepareStatementForSqlObject($select1);
        $result1 = $statement1->execute();
        
        $resultSet1 = new ResultSet;
        $resultSet1->initialize($result1);
        $last_bid_data = $resultSet1->toArray();
        $exists_data = count($last_bid_data);
        
        if($exists_data > 0){
             
            $userBidLog = new \Application\Model\UserBidLog($this->adapter);
            $bidInfo = $userBidLog->getProductTotalBid($data['product_id']);
               
            $users_bid_log_data = array(
                'total_bid' => $bidInfo[0]
            );  
            $update_bid_log = $sql->update('lastbid_log')
                      ->set($users_bid_log_data)
              ->where(array('product_id' => $data['product_id']));
            $statement = $sql->prepareStatementForSqlObject($update_bid_log);
            $statement->execute();  
        }        
        
        /*$selectString = $sql->getSqlStringForSqlObject($select);
        die($selectString);*/
    } 
    
    // WON AUCTION PAYMENT FUNCTION
    public function setWonAuctionPaymentData($id = 0, $data = array()) {

        if ($id == 0) {
            if ($this->insert($data)) {
                $transaction_id = $this->getLastInsertValue();
                //$this->updateProductAndUserData($data);
                return $transaction_id;
            }
            return 0;
        } else {
            throw new \Exception("Failed to save data.");
        }
    } 
}

