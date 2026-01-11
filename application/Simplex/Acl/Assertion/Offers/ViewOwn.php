<?php

class Simplex_Acl_Assertion_Offers_ViewOwn implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');
        
        $offer_id = $request->getParam('id', $request->getParam('offer_id', false));
        
        if(!$offer_id) return false;
        
        $creator_id = $db->fetchOne('select created_by from offers where offer_id = ' . $db->quote($offer_id));
        
        return $creator_id == $user_id;
    }
}