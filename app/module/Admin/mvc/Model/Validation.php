<?php

/**
 * Validation For Login
 */
 
namespace Admin\Model;
use Zend\InputFilter\Factory;
use Application\Model\GeneratePassword;

class Validation {
    
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
                                            array('name' => 'StringTrim'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                 'options' => array (
                                                     'messages' => array(
                                                         \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter user name'
                                                     )
                                                 )
                                             ),
                                             array(
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'encoding' => 'UTF-8',
                                                    'min' => 4,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter user name between 4 to 20 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter user name between 4 to 20 character!'
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
         $data->username = $filterData['username'];
         $data->password = GeneratePassword::processPassword($filterData['password'], 'admin');
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
    public function isValidRegistrationData(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null, $isNew = 1) {
        
        $inputFilterFactory = array(
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
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                \Zend\Validator\StringLength::TOO_SHORT => 'Please enter first name between 1 to 255 character!',
                                \Zend\Validator\StringLength::TOO_LONG => 'Please enter first name between 1 to 255 character!'
                            )
                        ),
                    ),
                ),
            ),
            
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
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter last name'
                            )
                        )
                     ),
                     array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255,
                            'messages' => array(
                                \Zend\Validator\StringLength::TOO_SHORT => 'Please enter lase name between 1 to 255 character!',
                                \Zend\Validator\StringLength::TOO_LONG => 'Please enter last name between 1 to 255 character!'
                            )
                        ),
                    ),
                ),
            ),
            
            
            'is_active' => array(
                 'name' => 'is_active',
                 'required' => true,
                 'filters' => array(
                    array('name' => 'Digits')
                  ),
                 'validators' => array(
                     array(
                        'name' => 'NotEmpty',
                        'options' => array (
                            'messages' => array(
                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select status'
                            )
                        )
                     )
                ),
            )
        );
        
        if (isset($data->profile_name) || $isNew) {
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
                                'messages' => array(
                                    \Zend\Validator\NotEmpty::IS_EMPTY => 'Username is required'
                                )
                            )
                         ),
                         array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 4,
                                'max' => 255,
                                'messages' => array(
                                    \Zend\Validator\StringLength::TOO_SHORT => 'Please enter username between 4 to 255 character!',
                                    \Zend\Validator\StringLength::TOO_LONG => 'Please enter username between 4 to 255 character!'
                                )
                            ),
                        ),
                        array(
                            'name' => 'Db\NoRecordExists',
                            'options' => array(
                                'table' => 'admin_users',
                                'field' => 'username',
                                'adapter' => $dbAdapter,
                                'messages' => array(
                                    \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Username exist',
                                )
                            ),
                        ),
                    ),
                )
            );
        }
          
        if (isset($data->email_address) || $isNew) {
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
                                    'messages' => array(
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
                                    'table' => 'admin_users',
                                    'field' => 'email_address',
                                    'adapter' => $dbAdapter,
                                    'messages' => array(
                                        \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Email address exist',
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
        $data->first_name = $filterData['first_name'];
        $data->last_name = $filterData['last_name'];
        if ($inputFilter->isValid()) {
            if (isset($data->password) || $isNew) {
               $data->password = GeneratePassword::generate($filterData['password'], 'admin');
            }
            if (isset($data->username) || $isNew) {
                $data->username = $filterData['username'];
            }
             
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
     * Filter & Validate check for Post data of Category 
     */
     public function isValidCategoryData(&$data = array()) {
        $inputFilter = $this->_factory->createInputFilter(array(
                                    'category_name' => array(
                                         'name' => 'category_name',
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
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter category name'
                                                    )
                                                )
                                             ),
                                             array(
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'encoding' => 'UTF-8',
                                                    'min' => 1,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter category name between 1 to 255 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter category name between 1 to 255 character!'
                                                    )
                                                ),
                                            ),
                                        ),
                                    ),
                                    
                                    'category_description' => array(
                                         'name' => 'category_description',
                                         'required' => true,
                                         'filters' => array(
                                            //array('name' => 'StripTags'),
                                            array('name' => 'StringTrim'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array (
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter description'
                                                    )
                                                )
                                             ),
                                        ),
                                    ),
                                    'is_active' => array(
                                         'name' => 'is_active',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'Digits'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array (
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select status'
                                                    )
                                                )
                                             ),
                                        ),
                                    )
                                    
                                ));

         $inputFilter->setData($data);
         $filterData = $inputFilter->getValues();
         $data->category_name = $filterData['category_name'];
         $data->category_description = $filterData['category_description'];
         if ($inputFilter->isValid()) {
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
        
     }
     
     
     
     /**
     * Filter & Validate check for Post data of Category 
     */
     public function isValidProductData(&$data = array()) {
        $inputFilter = $this->_factory->createInputFilter(array(
                                    'name' => array(
                                         'name' => 'name',
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
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter product name'
                                                    )
                                                )
                                             ),
                                             array(
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'encoding' => 'UTF-8',
                                                    'min' => 1,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter product name between 1 to 255 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter product name between 1 to 255 character!'
                                                    )
                                                ),
                                            ),
                                        ),
                                    ),
                                    /*
                                    'model_id' => array(
                                         'name' => 'model_id',
                                         'required' => true,
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array(
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select model'
                                                    )
                                                )
                                             ),
                                        ),
                                    ),
									*/
                                    'status' => array(
                                         'name' => 'status',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'Digits'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array(
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select status'
                                                    )
                                                )
                                             ),
                                        ),
                                    ),
                                    
                                    'type' => array(
                                         'name' => 'type',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'Digits'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array(
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select product type'
                                                    )
                                                )
                                             ),
                                        ),
                                    )
                                    
                                    
                                ));

         $inputFilter->setData($data);
         $filterData = $inputFilter->getValues();
         $data->name = $filterData['name'];
         if ($inputFilter->isValid()) {
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
        
     }
     
     /**
     * Filter & Validate check for Post data of CMS Page 
     */
     public function isValidPageData(&$data = array()) {
        $inputFilter = $this->_factory->createInputFilter(array(
                                    'page_title' => array(
                                         'name' => 'page_title',
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
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter page title'
                                                    )
                                                )
                                             ),
                                             array(
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'encoding' => 'UTF-8',
                                                    'min' => 1,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter page title between 1 to 255 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter page title between 1 to 255 character!'
                                                    )
                                                ),
                                            ),
                                        ),
                                    ),
                                    
                                    'page_content' => array(
                                         'name' => 'page_content',
                                         'required' => true,
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array (
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter content'
                                                    )
                                                )
                                             ),
                                        ),
                                    ),
                                    'is_active' => array(
                                         'name' => 'is_active',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'Digits'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array (
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select status'
                                                    )
                                                )
                                             ),
                                        ),
                                    )
                                    
                                ));

         $inputFilter->setData($data);
         $filterData = $inputFilter->getValues();
         $data->page_title = $filterData['page_title'];
         if ($inputFilter->isValid()) {
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
        
     }
     
     
      /**
     * Filter & Validate check for Post data of CMS Block 
     */
     public function isValidBlockData(&$data = array()) {
        $inputFilter = $this->_factory->createInputFilter(array(
                                    'block_title' => array(
                                         'name' => 'page_title',
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
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter page title'
                                                    )
                                                )
                                             ),
                                             array(
                                                'name' => 'StringLength',
                                                'options' => array(
                                                    'encoding' => 'UTF-8',
                                                    'min' => 1,
                                                    'max' => 255,
                                                    'messages' => array(
                                                        \Zend\Validator\StringLength::TOO_SHORT => 'Please enter block title between 1 to 255 character!',
                                                        \Zend\Validator\StringLength::TOO_LONG => 'Please enter block title between 1 to 255 character!'
                                                    )
                                                ),
                                            ),
                                        ),
                                    ),
                                    
                                    'block_content' => array(
                                         'name' => 'page_content',
                                         'required' => true,
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array (
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter content'
                                                    )
                                                )
                                             ),
                                        ),
                                    ),
                                    'block_status' => array(
                                         'name' => 'block_status',
                                         'required' => true,
                                         'filters' => array(
                                            array('name' => 'Digits'),
                                          ),
                                         'validators' => array(
                                             array(
                                                'name' => 'NotEmpty',
                                                'options' => array (
                                                    'messages' => array(
                                                        \Zend\Validator\NotEmpty::IS_EMPTY => 'Please select status'
                                                    )
                                                )
                                             ),
                                        ),
                                    )
                                    
                                ));

         $inputFilter->setData($data);
         $filterData = $inputFilter->getValues();
         $data->block_title = $filterData['block_title'];
         if ($inputFilter->isValid()) {
            return true;
         } else {
            $this->_processMessage($inputFilter->getInvalidInput());
            return false;
         }
        
     }
    
    
    
    /**
     * Filter & Validate check for Post data of CMS Block 
     */
     public function isValidPromotionData(&$data = array(), \Zend\Db\Adapter\Adapter $dbAdapter = null) {
        $inputFilterFactory = array(
                            'promotion_title' => array(
                                 'name' => 'promotion_title',
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
                                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter promotion title'
                                            )
                                        )
                                     ),
                                     array(
                                        'name' => 'StringLength',
                                        'options' => array(
                                            'encoding' => 'UTF-8',
                                            'min' => 1,
                                            'max' => 255,
                                            'messages' => array(
                                                \Zend\Validator\StringLength::TOO_SHORT => 'Please enter promotion title between 1 to 255 character!',
                                                \Zend\Validator\StringLength::TOO_LONG => 'Please enter promotion title between 1 to 255 character!'
                                            )
                                        ),
                                    ),
                                ),
                            ),
                            
                            'promotion_description' => array(
                                 'name' => 'promotion_description',
                                 'required' => true,
                                 'validators' => array(
                                     array(
                                        'name' => 'NotEmpty',
                                        'options' => array (
                                            'messages' => array (
                                                \Zend\Validator\NotEmpty::IS_EMPTY => 'Please enter promotion description'
                                            )
                                        )
                                     ),
                                ),
                            )                                    
                        );
                                
         if (isset($data->promotion_code)) {
            $inputFilterFactory +=  array(
                'promotion_code' => array(
                     'name' => 'promotion_code',
                     'required' => true,
                     'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                      ),
                     'validators' => array(
                        array(
                            'name' => 'Db\NoRecordExists',
                            'options' => array(
                                'table' => 'users_promotion',
                                'field' => 'promotion_code',
                                'adapter' => $dbAdapter,
                                'messages' => array(
                                    \Zend\Validator\Db\NoRecordExists::ERROR_RECORD_FOUND => 'Promotion code alreay exist',
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
         $data->promotion_title = $filterData['promotion_title'];
         if (isset($data->promotion_code)) {
            $data->promotion_code = $filterData['promotion_code'];
         }         
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
    protected function _processMessage($messages = null) {
        foreach ($messages as $error) {
            $message = array_values($error->getMessages());
            $this->message .= $message[0].'. ';
        }
    }

}

