<?php

class Simplex_Acl_Assertion_Orders_Incarico implements Zend_Acl_Assert_Interface
{
    public function assert(Zend_Acl $acl,
                           Zend_Acl_Role_Interface $role = null,
                           Zend_Acl_Resource_Interface $resource = null,
                           $privilege = null)
    {
        if($privilege == 'enti')
        {
            return true;
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $user_id = Zend_Auth::getInstance()->getIdentity()->user_id;
        $db = Zend_Registry::get('dbAdapter');

        $order_id = $request->getParam('id',
            $request->getParam('id_order',
                $request->getParam('order_id', false)));

        if(!$order_id) return false;

        $rcs = $db->fetchAll('select rco, incarico from orders_rcos where id_order = ' . $db->quote($order_id));

        $username = $db->fetchOne('select username from users where user_id = ' . $db->quote($user_id));

        foreach($rcs as $rc)
        {
            $to_check = explode(' - ', $rc['rco']);
            if($to_check[0] == $username)
            {
                if($privilege == 'incarico')
                {
                    return true;
                }

                // controlliamo anche che sia presa in carico
                return $rc['incarico'] == 1;
            }
        }

        return false;
    }
}