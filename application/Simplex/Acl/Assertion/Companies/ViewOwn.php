<?php

class Simplex_Acl_Assertion_Companies_ViewOwn implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');
        
        $company_id = $request->getParam('id', $request->getParam('company_id', false));

        if(!$company_id) return false;
        
        $creator_id = $db->fetchOne('select created_by from companies where company_id = ' . $db->quote($company_id));

        if(!$creator_id == $user_id)
        {
            return false;
        }

        $user_object = Zend_Auth::getInstance()->getIdentity()->user_object;

        if($user_object->has_permission('companies', 'view_excluded'))
        {
            return true;
        }

        $excluded = $db->fetchOne('select deleted from companies where company_id = ' . $db->quote($company_id));

        return !$excluded;
    }
}