<?php

class Simplex_Plugin_FlashMessage extends Zend_Controller_Plugin_Abstract
{
    
    public function  preDispatch(Zend_Controller_Request_Abstract $request) 
    {
        //$view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        //$mess = $this->_helper->getHelper('FlashMessenger');
        //$view->flashMessages = $this->_helper->getHelper('FlashMessenger')->getMessages();
        //$view->messages = $this->messenger->getMessages();
//        Zend_Debug::dump($this);exit;
        $messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $layout = Zend_Layout::getMvcInstance();
        $layout->flashMessages = $messenger->getMessages();
    }
}