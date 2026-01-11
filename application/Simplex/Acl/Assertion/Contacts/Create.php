<?php

class Simplex_Acl_Assertion_Contacts_Create implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $controller = $request->getParam('controller');
        
        $contact_id = $request->getParam('contact_id', $request->getParam('idc', false));
        
        if($contact_id)
        {
            return false;
        }
        
        return true;
    }
}