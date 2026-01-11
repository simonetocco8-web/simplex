<?php
/**
* Helper to build a nice looking button (button or link)
*/
class Zend_View_Helper_LoggedUser extends Zend_View_Helper_Abstract
{ 
    protected $_user = false;
	
    /**
    * Loads the textboxlist dependencies
    * 
    * @return string
    */
    public function loggedUser()
    { 
    	if($this->_user === false)
    	{
            $this->_user = Zend_Auth::getInstance()->getIdentity()->user_object;
    	}
        return $this->_user;
    } 
} 