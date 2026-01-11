<?php
/**
* Helper to build a nice looking button (button or link)
*/
class Zend_View_Helper_Acl extends Zend_View_Helper_Abstract
{ 
    protected $_user = false;
	
    /**
    * Loads the textboxlist dependencies
    * 
    * @return string
    */
    public function acl($resource, $action, $onlyAcl = false)
    { 
    	if($this->_user === false)
    	{
            $this->_user = Zend_Auth::getInstance()->getIdentity()->user_object;
    	}

        $registry = Zend_Registry::getInstance();
        $acl = $registry->get('acl');

        $res = $acl->isAllowed('user', $resource, $action);

        if($res || $onlyAcl)
        {
            return $res;
        }

        return $this->_user->has_permission($resource, $action);
    } 
} 