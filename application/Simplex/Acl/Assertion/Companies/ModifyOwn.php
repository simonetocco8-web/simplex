<?php

class Simplex_Acl_Assertion_Companies_ModifyOwn implements Zend_Acl_Assert_Interface
{   
    protected $_can_create = false;
    
    public function __construct($can_create = false)
    {
        $this->_can_create = $can_create;
    }
    
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        $controller = $request->getParam('controller');
        if($controller != 'companies') return true;
        
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');
        
        $company_id = $request->getParam('id', $request->getParam('company_id'), false);
        
        if(!$company_id) return $this->_can_create;
        
        $creator_id = $db->fetchOne('select created_by from companies where company_id = ' . $db->quote($company_id));
        
        return $creator_id == $user_id;
    }
}