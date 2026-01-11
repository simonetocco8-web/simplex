<?php

class Simplex_Acl_Assertion_Sdm_Modify implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        
        if($request->getParam('id', $request->getParam('sdm_id', false)))
        {
            return true;
        }
        
        return false;
    }
}