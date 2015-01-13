<?php

// module/Session/src/Session/Form/LoginForm.php:

namespace Session\Form;

use Zend\Form\Form;

class LoginForm extends Form {
    
    public function init()
    {
        $this->setMethod('post')
             ->loadDefaultDecorators()
             ->addDecorator('FormErrors');

        $this->addElement(
            'text',
            'username',
            array(
                 'filters' => array(
                     array('StringTrim')
                 ),
                 'validators' => array(
                     'EmailAddress'
                 ),
                 'required' => true,
                 'label'    => 'Email'
            )
        );

        $this->addElement(
            'password',
            'password',
            array(
                 'filters' => array(
                     array('StringTrim')
                 ),
                 'validators' => array(
                     array(
                         'StringLength',
                         true,
                         array(
                             6,
                             999
                         )
                     )
                 ),
                 'required' => true,
                 'label'    => 'Password'
            )
        );

        $this->addElement(
            'hash',
            'csrf',
            array(
                 'ignore' => true,
                 'decorators' => array('ViewHelper')
            )
        );

        $this->addElement(
            'submit',
            'login',
            array(
                 'ignore' => true,
                 'label' => 'Login'
            )
        );

    }
    
    /*
    public function __construct($name = null) {
        // we want to ignore the name passed
        parent::__construct('album');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'username',
            'attributes' => array(
                'type' => 'text',
                'class' => 'span12',
                'onfocus' => "if (this.value=='Administrator') this.value='';",
                'onblur' => "if (this.value=='') this.value='Administrator';",
                'value' => 'Administrator'
            )
        ));
        
        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'type' => 'text',
                'class' => 'span12',
                'onfocus' => "if (this.type=='text'){ this.type='password';this.value='';}",
                'onblur' => "if (this.value==''){ this.type='text'; this.value='Password';}",
                'value' => 'Password'
            )
        ));
        
        $this->add(array(
            'name' => 'logged',
            'attributes' => array(
                'id' => 'keepLoged',
                'type' => 'checkbox',
                'class' => 'styled',
                'value' => '1'
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
            ),
        ));
    }
    */
}
