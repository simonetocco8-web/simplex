<?php
  
class Simplex_Plugin_Auth_Initialize extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $controller = ($request->getControllerName()) ?: 'index';
        
        if($controller == 'login' || $controller == 'error')
        {
            return;
        } 
        
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity())
        {
            $user = $auth->getIdentity();
            
            if(!isset($user->internal_id))
            {
                $request->setControllerName('login')
                    ->setActionName('index');
            }
            
            $rulesManager = new Simplex_Acl_RulesManager($user);
        
            
            $user_object = @$user->user_object;

            if(!$user_object)
            {
                $auth->clearIdentity();
                $request->setControllerName('login')
                    ->setActionName('index');
                return;
            }
            
            $acl = new Simplex_Acl_Acl2($user_object);

            $registry = Zend_Registry::getInstance();
            $registry->set('acl', $acl);

            $resource = $controller;
            $action = $request->getActionName();
  
            // creiamo prima la navigazione
            $navigationManager = new Simplex_Navigation_Initialize();
            $navigationManager->build($request, $rulesManager->getNestedRules(), $acl);

            $layout = Zend_Layout::getMvcInstance();
            $layout->user = $auth->getIdentity();
            
            if (!$acl->isAllowed('user', $resource, $action)) 
            {
                $request->setControllerName('error')
                    ->setActionName('denied');
                return;
                throw new Zend_Controller_Action_Exception('Accesso Negato', 403);// Exception('denied access at resource ' . $resource . ', action ' . $action);
            }   
            
            // todo acl
            //$acl = new Simplex_Acl_Acl($user->id_role, $rulesManager->getResources(), $rulesManager->getRules(), $rulesManager->getRoles());

            /*
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'create') ? '' : 'NOT ') . ' create' . '<br />';
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'read') ? '' : 'NOT ') . ' read' . '<br />';
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'update') ? '' : 'NOT ') . 'update' . '<br />';
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'delete') ? '' : 'NOT ') . 'delete' . '<br />';
            */
    // todo restore
           /*
            $role = $acl->getRoleName($user->id_role);

            // controlle se la risorsa esiste
            $resource = $controller;
            $action = $request->getActionName();
*/

            //todo restore
/*
            if(!$acl->has($resource))
            {
                // todo: reenable acl control
            //    throw new Exception('denied access at resource ' . $resource);
                $resource = null;
            }

            if (!$acl->isAllowed($role, $resource, $action)) 
            {
                // todo: reenable acl control
           //     throw new Exception('denied access at resource ' . $resource . ', action ' . $action);
            }   
            */
        }
        else
        {
            $request->setControllerName('login')
                ->setActionName('index');
        }
    }
}
