<?php

class Simplex_Acl_Assertion_Tasks_Own implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');
        
        $task_id = $request->getParam('id', $request->getParam('task_id', false));
        
        if(!$task_id) return false;
        
        $who_id = $db->fetchOne('select id_who from tasks where task_id = ' . $db->quote($task_id));
        
        return $who_id == $user_id;
    }
}