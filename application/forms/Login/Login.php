<?php

class Form_Login_Login extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');

        $this->setAttrib('id', 'login-form');

        $this->addElement(
            'text',
            'uname',
            array(
                'label' => 'username',
                'required' => true,
                'filters' => array('StringTrim'),
                'class' => 'input-medium required'
            )
        );

        $this->addElement(
            'password',
            'pword',
            array(
                'label' => 'password',
                'required' => true,
                'class' => 'input-medium required'
            )
        );

        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Login',
            'class' => 'button'
        ));
    }
}