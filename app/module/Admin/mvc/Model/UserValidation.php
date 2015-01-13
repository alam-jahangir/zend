<?php

/**
 * Validation For Login
 */
 
namespace Admin\Model;
use Zend\InputFilter\Factory;
use Application\Model\GeneratePassword;

class UserValidation {
    
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
    public function isLoginValid(&$data = array()) 
    {

        $inputFilter = $this->_factory->createInputFilter(array(
                                    'email_address' => array(
                                         'name' => 'email_address',
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
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter email address'
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
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'encoding' => 'UTF-8',
                                                    'min' => 4,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter email address between 4 to 20 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter email address between 4 to 20 character!'
                                                    )
                                                ),
                                            ),
                                        ),
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
                                             ),
                                             array(
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'min' => 1,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter password between 4 to 20 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter password between 4 to 20 character!'
                                                    )
                                                ),
                                             ),
                                        ),
                                    ),
                                ));

         $inputFilter->setData($data);
         $filterData = $inputFilter->getValues();
         $data->email_address = $filterData['email_address'];
         $data->password = GeneratePassword::generate($filterData['password']);
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
        
        if (isset($data->firstname) || $isNew) {
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
                                'messages' => array (
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
                            ),
                        ),
                    ),
                )
             );
         }
         
         if (isset($data->lastname) || $isNew) {
            
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
                                'messages' => array(
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
                            ),
                        ),
                    ),
                )
            );
        }
        
          
        if (isset($data->email) || $isNew) {
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
                                ), 
                             ),
                             array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 5,
                                    'max' => 255,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter email address between 5 to 255 character!',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter email address between 5 to 255 character!'
                                    )
                                ),
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
                                ),
                            ),
                        ),
                    ),
            );
        }
        if (isset($data->password) || $isNew) {
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
                                    'messages' => array (
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
        $data->firstname = $filterData['firstname'];
        $data->lastname = $filterData['lastname'];
        if ($inputFilter->isValid()) {
            if (isset($data->password) || $isNew) {
               $data->password = \Application\Model\GeneratePassword::generate($filterData['password']);
            }
            
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
    public function isValidForgotPasswordData(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null) 
    {
                          
        if (isset($data->email_address)) {
            $inputFilterFactory = array(
                   'email_address' => array(
                         'name' => 'email_address',
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
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 5,
                                    'max' => 255,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter email address between 5 to 255 character!',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter email address between 5 to 255 character!'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Db\RecordExists',
                                'options' => array(
                                    'table' => 'users',
                                    'field' => 'email_address',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_NO_RECORD_FOUND => 'Email address is not exist',
                                    )
                                ),
                            ),
                        ),
                    ),
            );
        }
         
        $inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
        $inputFilter->setData($data);
        $filterData = $inputFilter->getValues();
        if ($inputFilter->isValid()) {
            if (isset($data->email_address) || $isNew) {
                $data->email_address = $filterData['email_address'];
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
        
        if (isset($data->first_name)) {
            $inputFilterFactory += array(
                'first_name' => array(
                     'name' => 'first_name',
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
                            ),
                        ),
                    ),
                )
             );
         }
         
         if (isset($data->last_name)) {
            
             $inputFilterFactory +=  array(   
                'last_name' => array(
                     'name' => 'last_name',
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
                            ),
                        ),
                    ),
                )
            );
        }
        
        if (isset($data->username)) {
            $inputFilterFactory +=  array(
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
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'User name is required'
                                )
                            )
                         ),
                         array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 4,
                                'max' => 255,
                                'messages' => array (
                                    \Zend\Validator\StringLength::TOO_SHORT => 'Please enter user name between 4 to 255 character!',
                                    \Zend\Validator\StringLength::TOO_LONG => 'Please enter user name between 4 to 255 character!'
                                )
                            ),
                        ),
                        /*
                        array(
                            'name' => 'Db\NoRecordExists',
                            'options' => array(
                                'table' => 'users',
                                'field' => 'username',
                                'adapter' => $dbAdapter,
                                'messages' => array(
                                    \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Username name already exist',
                                )
                            ),
                        ),
                        */
                    ),
                )
            );
        }
          
        if (isset($data->email_address)) {
            $inputFilterFactory += array(
                   'email_address' => array(
                         'name' => 'email_address',
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
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 5,
                                    'max' => 255,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter email address between 5 to 255 character!',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter email address between 5 to 255 character!'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Db\NoRecordExists',
                                'options' => array(
                                    'table' => 'users',
                                    'field' => 'email_address',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email address already exist',
                                    )
                                ),
                            ),
                        ),
                    ),
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
               $data->password = \User\Model\GeneratePassword::generate($filterData['password']);
            }
            if (isset($data->username)) {
                $data->username = $filterData['username'];
            }
            if (isset($data->email_address)) {
                $data->email_address = $filterData['email_address'];
            }
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
    } 
    
    
    /**
     * Email Address Validation
     * @param   $data       
     **/
     public function validateEmailAddress(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null) 
     {
        $inputFilterFactory = array(
                   'email_address' => array(
                         'name' => 'email_address',
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
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 5,
                                    'max' => 255,
                                    'messages' => array(
                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter email address between 5 to 255 character!',
                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter email address between 5 to 255 character!'
                                    )
                                ),
                            ),
                            array(
                                'name' => 'Db\NoRecordExists',
                                'options' => array(
                                    'table' => 'users',
                                    'field' => 'email_address',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email address already exist',
                                    )
                                ),
                            ),
                        ),
                    ),
            );
            
            $inputFilter = $this->_factory->createInputFilter($inputFilterFactory);
            $inputFilter->setData($data);
            $filterData = $inputFilter->getValues();
            if ($inputFilter->isValid()) {
                if (isset($data->email_address)) {
                    $data->email_address = $filterData['email_address'];
                }
                if (isset($data->password)) {
                    $data->password = \User\Model\GeneratePassword::generate($data->password);
                }
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
        foreach ($messages as $error) {
            $message = array_values($error->getMessages());
            $this->message .= $message[0].'. ';
        }
    }

}

