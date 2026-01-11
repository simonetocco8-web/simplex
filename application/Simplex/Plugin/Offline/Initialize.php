<?php

class Simplex_Plugin_Offline_Initialize extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $id_user = 0;
        if(Zend_Auth::getInstance()->getIdentity())
        {
            $id_user = Zend_Auth::getInstance()->getIdentity()->user_id;
        }
        if($offline = Maco_Settings_Db::get('offline', 0, $id_user))
        {
            // launch offline page;
            $request->setControllerName('offline');
            $request->setActionName('index');
        }
    }

}
