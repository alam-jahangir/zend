<?php
namespace Account\Model;
 

class Payment {
     
    //protected $environment = 'sandbox'; // or 'beta-sandbox' or 'live'
    
    public $response = null;
    
    protected $handler = null;
    
    protected $apiInfo = array();
    
    public function __construct($apiInfo = array()) {
        $this->apiInfo = $apiInfo;
        $this->handler = & ProfileHandler_Array::getInstance(array(
            'username' => $this->apiInfo['paypal']['api_username'],
            'certificateFile' => null,
            'subject' => null,
            'environment' => $this->apiInfo['paypal']['environment']
        ));
    }
    
    public function request($data = array()) {
        
        $pid = ProfileHandler::generateID();
 
        $profile = & new APIProfile($pid, $this->handler);
         
        // Set up your API credentials, PayPal end point, and API version.
        $profile->setAPIUsername($this->apiInfo['paypal']['api_username']);
        $profile->setAPIPassword($this->apiInfo['paypal']['api_password']);
        $profile->setSignature($this->apiInfo['paypal']['api_signature']);
        $profile->setCertificateFile($this->apiInfo['paypal']['cert_file_path']);
        $profile->setEnvironment($this->apiInfo['paypal']['paymentType']);
        //--------------------------------------------------
         
        $dpRequest =& PayPal::getType('DoDirectPaymentRequestType');
        $dpRequest->setVersion("51.0");
         
        // Set request-specific fields.
        $refund_request->setTransactionId('example_transaction_id', 'iso-8859-1');
         
        $paymentType = $this->apiInfo['paypal']['paymentType'];
        // 'Authorization' or 'Sale'
   
        $creditCardType = $data['cardType'];
        $creditCardNumber = $data['field2'];
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
        $country = $data['country'];				// UNITED_STATES or other valid country
        
        $currencyID = 'EUR';						// or other currency ('USD', 'GBP', 'EUR', 'JPY', 'CAD', 'AUD')
         
        // setup the DoDirectPayment Request Payment details.
        $orderTotal =& PayPal::getType('BasicAmountType');
        $orderTotal->setattr('currencyID', $currencyID);
        $orderTotal->setval($data['amount'], 'iso-8859-1');
        
        $shipTo =& PayPal::getType('AddressType');
        $shipTo->setName($data['first_name'].' '.$data['first_name']);
        $shipTo->setStreet1($address1);
        $shipTo->setStreet2($address2);
        $shipTo->setCityName($city);
        $shipTo->setStateOrProvince($state);
        $shipTo->setCountry($country);
        $shipTo->setPostalCode($zip);

         
        $paymentDetails =& PayPal::getType('PaymentDetailsType');
        $paymentDetails->setOrderTotal($OrderTotal);
 
         
        // Set up credit card info.
        $personName =& PayPal::getType('PersonNameType');
        $personName->setFirstName($data['first_name']);
        $personName->setLastName($data['last_name']);
         
        $payer =& PayPal::getType('PayerInfoType');
        $payer->setPayer($data['email_address']);
        $payer->setPayerName($data['username']);
        $payer->setPayerCountry($country);
        $payer->setAddress($shipTo);
        
         
        $cardDetails =& PayPal::getType('CreditCardDetailsType');
        $cardDetails->setCardOwner($payer);
        $cardDetails->setCreditCardType($creditCardType);
        $cardDetails->setCreditCardNumber($creditCardNumber);
        $cardDetails->setExpMonth($padDateMonth);
        $cardDetails->setExpYear($expDateYear);
        $cardDetails->setCVV2($cvv2Number);
         
        $dpDetails =& PayPal::getType('DoDirectPaymentRequestDetailsType');
        $dpDetails->setPaymentDetails($paymentDetails);
        $dpDetails->setCreditCard($cardDetails);
        $dpDetails->setIPAddress($_SERVER['SERVER_ADDR']);
        $dpDetails->setPaymentAction($paymentType);
         
        $dpRequest->setDoDirectPaymentRequestDetails($dpDetails);
         
        $caller =& PayPal::getCallerServices($profile);
         
        // Execute SOAP request.
        $response = $caller->DoDirectPayment($dpRequest);
         
        switch($response->getAck()) {
        	case 'Success':
        	case 'SuccessWithWarning':
                $this->response = array(
                    'transac_id' => $response->getTransactionID(),
                    'avsCode'   => $response->getAVSCode(),
                    'cvv2'  => $response->getCVV2Code(),
                    'amount'   => $response->getAmount()->_value,
                    'currencyId' => $response->getAmount()->getattr('currencyID'),
                    'message'   => $response->getAck()
                );
               break;
        	default:
        		$this->response = array(
                    'message'   => $response->getAck()
                );
                break;
        }
        return $this->response;
    }
    
    public function response() {
        return $this->response;
    }
    
}

