<?php

class Simplex_Acl_Assertion_Companies_ViewExcluded implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_object = Zend_Auth::getInstance()->getIdentity()->user_object;

        if($user_object->has_permission('companies', 'view_excluded'))
        {
            return true;
        }

        $db = Zend_Registry::get('dbAdapter');
        
        $company_id = $request->getParam('id', $request->getParam('company_id', false));

        if(!$company_id) return false;
        
        $excluded = $db->fetchOne('select deleted from companies where company_id = ' . $db->quote($company_id));
        
        return !$excluded;
    }
}