<?php

class Simplex_Acl_Assertion_UserInfo implements Zend_Acl_Assert_Interface
{   
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {   
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');

        $allowedTasks = array(
            'detail',
            'cpw',
            'spw',
            'edit',
            'report'
        );
        $task = $request->getParam('task', false);
        if(in_array($task, $allowedTasks))
        {
            $id = $request->getParam('id', 0);
            return $user_id == $id;
        }

        return false;
    }
}