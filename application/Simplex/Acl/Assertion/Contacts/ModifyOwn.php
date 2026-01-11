<?php

class Simplex_Acl_Assertion_Contacts_ModifyOwn implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');
        
        $contact_id = $request->getParam('contact_id', $request->getParam('idc', $request->getParam('id', false)));
        
        if(!$contact_id) return false;
        
        $creator_id = $db->fetchOne('select created_by from contacts where contact_id = ' . $db->quote($contact_id));
        
        return $creator_id == $user_id;
    }
}