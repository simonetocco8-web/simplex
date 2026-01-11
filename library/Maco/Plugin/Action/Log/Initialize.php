<?php

class Maco_Plugin_Action_Log_Initialize  extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        if($auth->hasIdentity())
        {
            if($user = $auth->getIdentity())
            {
                Maco_Logger_File::info('User ' . $user->user_id . ' - ' . $user->username);
                Maco_Logger_File::info('Module: ' .  $request->getModuleName() 
                    . ' - Controller: ' . $request->getControllerName() 
                    . ' - Action: ' . $request->getActionName());
            }
        }
    }
}
