<?php

/**
 * Validation For Login
 */
 
namespace Application\Model;
use Zend\InputFilter\Factory;
use Application\Model\GeneratePassword;

class Validation {
    
    /**
     * @var Object
     */
    protected $inputFilter;
    
    /**
     * @var string
     */
    public $message;
    
    /**
     * @var \Zend\InputFilter\Factory
     */
    protected $_factory;
    
    public function __construct() {
        $this->_factory = new Factory();
    }
    
    
    /**
     * Filter & Validation Check of Login
     * @param   $data   array
     * @return  string
     */
    public function isLoginValid(&$data = array()) {

        $inputFilter = $this->_factory->createInputFilter(array(
                                    'username' => array(
                                         'name' => 'username',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'StripTags'),
                                            array('name' => 'StringTrim')
                                          ),
                                         'validators' => array(
				                             array(
				                                'name' => 'NotEmpty',
				                                'options' => array (
				                                    'messages' => array (
				                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Username is required'
				                                    )
				                                )
				                             )
				                        )
                                    ),
                                    
                                    'password' => array(
                                         'name' => 'password',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'StripTags'),
                                            array('name' => 'StringTrim'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array(
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter password'
                                                    )
                                            	)
                                            )
                                        )
                                    )
                                ));

         $inputFilter->setData($data);
         $filterData = $inputFilter->getValues();
         $data->username = $filterData['username'];
         $data->password = GeneratePassword::processPassword($filterData['password'], 'user');
         if ($inputFilter->isValid()) {
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
    }
    
    /**
     * Filter & Validation Check of Registration Data 
     * @param   $data   array
     * @return  string
     */
    public function isValidRegistrationData(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null, $isNew = 1) 
    {
        
        $inputFilterFactory = array();
        
        if (isset($data['username']) || $isNew) {
        	
            $inputFilterFactory += array(
                'username' => array(
                     'name' => 'username',
                     'required' => true,
                     'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                      ),
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Username is required'
                                )
                            )
                         ),
                         array(
                            'name' => 'Db\NoRecordExists',
                            'options' => array(
                                'table' => 'users',
                                'field' => 'username',
                                'adapter' => $dbAdapter,
                                'messages' => array(
                                    \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Username already exists. please choose another username',
                                )
                            )
                        )
                    )
                )
                
             );
             
         }
         
        if (isset($data['email']) || $isNew) {
            $inputFilterFactory += array(
                   'email' => array(
                         'name' => 'email',
                         'required' => true,
                         'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                          ),
                         'validators' => array(
                             array(
                                'name' => 'NotEmpty',
                                'options' => array (
                                    'messages' => array (
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Email address is required'
                                    )
                                )
                             ),
                             array(
                                'name' => 'EmailAddress',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min'      => 5,
                                    'max'      => 255,
                                    'messages' => array(
                                        \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                                    )
                                )
                             ),
                            array(
                                'name' => 'Db\NoRecordExists',
                                'options' => array(
                                    'table' => 'users',
                                    'field' => 'email',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email address already exist',
                                    )
                                )
                            )
                        )
                    )
            	);
        }
        
        if (isset($data['password']) || $isNew) {
             $inputFilterFactory +=  array(
                     'password' => array(
                         'name' => 'password',
                         'required' => true,
                         'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                          ),
                         'validators' => array(
                             array(
                                'name' => 'NotEmpty',
                                'options' => array (
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Password is required'
                                    )
                                )
                             )
                        )
                	)
             	);
        }
         
        $inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
        $inputFilter->setData($data);
        $filterData = $inputFilter->getValues();
        if ($inputFilter->isValid()) {
        	
            if (isset($data['email']) || $isNew) {
                $data['email'] = $filterData['email'];
            }
            
            if (isset($data['username']) || $isNew) {
                $data['username'] = $filterData['username'];
            }
            
            if (isset($data['password']) || $isNew) {
                $data['password'] = $filterData['password'];
            }
            
            return true;
         } else {
         	$this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
    }
    
    /**
     * Filter & Validation Check of Registration Data 
     * @param   $data   array
     * @return  string
     */
    public function isValidForgotPasswordData(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null) 
    {
                          
        if (isset($data->email)) {
            $inputFilterFactory = array(
                   'email' => array(
                         'name' => 'email',
                         'required' => true,
                         'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                          ),
                         'validators' => array(
                             array(
                                'name' => 'NotEmpty',
                                'options' => array (
                                    'messages' => array (
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Email address is required'
                                    )
                                )
                             ),
                             array(
                                'name' => 'EmailAddress',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min'      => 5,
                                    'max'      => 255,
                                    'messages' => array(
                                        \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                                    )
                                ), 
                             ),
                             array(
                                'name' => 'Db\RecordExists',
                                'options' => array(
                                    'table' => 'users',
                                    'field' => 'email',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND => 'Email address is not exist',
                                    )
                                )
                            )
                        )
                    )
            );
        }
         
        $inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
        $inputFilter->setData($data);
        $filterData = $inputFilter->getValues();
        if ($inputFilter->isValid()) {
            if (isset($data->email) || $isNew) {
                $data->email = $filterData['email'];
            }
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
    }
    
    /**
     * Filter & Validation Check of Registration Data 
     * @param   $data   array
     * @return  string
     */
    public function isAccountUpdateValidData(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null) 
    {
        
        $inputFilterFactory = array();
        
        if (isset($data->firstname)) {
            $inputFilterFactory += array(
                'firstname' => array(
                     'name' => 'firstname',
                     'required' => true,
                     'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                      ),
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array(
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter first name'
                                )
                            )
                         ),
                         array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 2,
                                'max' => 255,
                                'messages' => array(
                                    \Zend\Validator\StringLength::TOO_SHORT => 'Please enter first name between 2 to 255 character!',
                                    \Zend\Validator\StringLength::TOO_LONG => 'Please enter first name between 2 to 255 character!'
                                )
                            )
                        )
                    )
                )
             );
         }
         
         if (isset($data->lastname)) {
            
             $inputFilterFactory +=  array(   
                'lastname' => array(
                     'name' => 'lastname',
                     'required' => true,
                     'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                      ),
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter last name'
                                )
                            )
                         ),
                         array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 2,
                                'max' => 255,
                                'messages' => array(
                                    \Zend\Validator\StringLength::TOO_SHORT => 'Please enter lase name between 2 to 255 character!',
                                    \Zend\Validator\StringLength::TOO_LONG => 'Please enter last name between 2 to 255 character!'
                                )
                            )
                        )
                    )
                )
            );
        }
        
        
          
        if (isset($data->email)) {
            $inputFilterFactory += array(
                   'email' => array(
                         'name' => 'email',
                         'required' => true,
                         'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                          ),
                         'validators' => array(
                             array(
                                'name' => 'NotEmpty',
                                'options' => array (
                                    'messages' => array (
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Email address is required'
                                    )
                                )
                             ),
                             array(
                                'name' => 'EmailAddress',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min'      => 5,
                                    'max'      => 255,
                                    'messages' => array(
                                        \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                                    )
                                ) 
                             ),
                             array(
                                'name' => 'Db\NoRecordExists',
                                'options' => array(
                                    'table' => 'users',
                                    'field' => 'email',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email address already exist',
                                    )
                                )
                            )
                        )
                    )
            );
        }
        if (isset($data->password)) {
             $inputFilterFactory +=  array(
                         'password' => array(
                         'name' => 'password',
                         'required' => true,
                         'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                          ),
                         'validators' => array(
                             array(
                                'name' => 'NotEmpty',
                                'options' => array (
                                    'messages' => array (
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Password is required'
                                    )
                                )
                             ),
                             array(
                                'name' => 'StringLength',
                                'options' => array(
                                    //'encoding' => 'UTF-8',
                                    'min' => 4,
                                    'max' => 255,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter password between 4 to 255 character!',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter password between 4 to 255 character!'
                                    )
                                ),
                            ),
                        ),
                    ),
                    
                    'confirm_password' => array(
                         'name' => 'confirm_password',
                         'required' => true,
                         'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                          ),
                         'validators' => array(
                             array(
                                'name' => 'NotEmpty',
                                'options' => array (
                                    'messages' => array(
                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Confirm password is required'
                                    )
                                )
                             ),
                             array(
                                'name' => 'identical',
                                'options' => array(
                                    'token' => 'password',
                                    'messages' => array(
                                        \Zend\Validator\Identical::NOT_SAME => 'Confirm password and Password is not same'
                                    ) 
                                )
                             ), 
                             array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'min' => 4,
                                    'max' => 255,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter confirm password between 4 to 255 character!',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter confirm password between 4 to 255 character!'
                                    )
                                ),
                            ),
                        ),
                    )
             );
                            
         }
         
        $inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
        $inputFilter->setData($data);
        $filterData = $inputFilter->getValues();
        if ($inputFilter->isValid()) {
            if (isset($data->password)) {
               $data->password = GeneratePassword::generate($filterData['password']);
            }
            if (isset($data->email)) {
                $data->email = $filterData['email'];
            }
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
    }
	
	
	/**
	* Validate Address
	*/
	public function isValidAddress(&$data = array()) 
    {
        
        $inputFilterFactory = array();
        $inputFilterFactory += array(
        
            'name' => array(
                 'name' => 'user_id',
                 'required' => true,
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Name is required'
                            )
                        )
                     )
                )
            ),
            
            'surname' => array(
                 'name' => 'surname',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Surname is required'
                            )
                        )
                     )
                )
            ),
            
            'street' => array(
                 'name' => 'street',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Street is required'
                            )
                        )
                     )
                )
            ),
            
            'city' => array(
                 'name' => 'city',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'City is required'
                            )
                        )
                     )
                )
            ),
            
            'state' => array(
                 'name' => 'state',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'State is required'
                            )
                        )
                     )
                )
            ),
            
            'zipcode' => array(
                 'name' => 'zipcode',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Zipcode is required'
                            )
                        )
                     )
                )
            ),
            
            'phone' => array(
                 'name' => 'phone',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Phone Number is required'
                            )
                        )
                     )
                )
            ),
            
            'email' => array(
                 'name' => 'email',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array (
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Email address is required'
                            )
                        )
                     ),
                     array(
                        'name' => 'EmailAddress',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 255,
                            'messages' => array(
                                \Zend\Validator\EmailAddress::INVALID_FORMAT => 'Email address format is invalid'
                            )
                        )
                     )
                )
            )
                
        );
             
    	$inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
        $inputFilter->setData($data);
        $filterData = $inputFilter->getValues();
        if ($inputFilter->isValid()) {
        	$data['name'] = $filterData['name'];
            $data['surname'] = $filterData['surname'];
            $data['street'] = $filterData['street'];
            $data['city'] = $filterData['city'];
            $data['state'] = $filterData['state'];
            $data['zipcode'] = $filterData['zipcode'];
            $data['phone'] = $filterData['phone'];
            $data['email'] = $filterData['email'];
            return true;
         } else {
         	$this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
    } 
    
    
    public function isDealerUploadValid(&$data = array())
    {
    	$inputFilterFactory = array(
        
            	'category_id' => array(
                     'name' => 'category_id',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select item'
                                )
                            )
                         )
                    )
                ),
                
                'group_id' => array(
                     'name' => 'group_id',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select option from second dropdawn'
                                )
                            )
                         )
                    )
                ),
                
                'group_option_index' => array(
                     'name' => 'group_option_index',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select option from second dropdawn'
                                )
                            )
                         )
                    )
                ),
                
                'year' => array(
                     'name' => 'year',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter year'
                                )
                            )
                         )
                    )
                ),
                
                'price_option' => array(
                     'name' => 'price_option',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select price option'
                                )
                            )
                         )
                    )
                ),
                
                'currency' => array(
                     'name' => 'currency',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select currency'
                                )
                            )
                         )
                    )
                ),
                
                'recommanded_price' => array(
                     'name' => 'recommanded_price',
                     'required' => true,
                     'validators' => array(
                         array(
                            'name' => 'NotEmpty',
                            'options' => array (
                                'messages' => array (
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter recommanded price'
                                )
                            )
                         )
                    )
                )
            );
            
            $inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
	        $inputFilter->setData($data);
	        $filterData = $inputFilter->getValues();
	        if ($inputFilter->isValid()) {
	            return true;
	         } else {
	         	$this->_processMessage($inputFilter->getInvalidInput());
	            return false;
	         }
	}
    
     
    /**
     * Process InputFilter Messages
     * @param   $messages object
     */
    protected function _processMessage($messages = null) 
    {
    	$total = count($messages);
        foreach ($messages as $key => $error) {
            $message = array_values($error->getMessages());
            $this->message .= $message[0].'.';
            if ($total > 1 && $key+1 <= $total) {
				$this->message .= '<br />';
			}
        }
    }

}

