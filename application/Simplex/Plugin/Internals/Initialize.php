<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 7-ott-2010
 * Time: 11.51.07
 * To change this template use File | Settings | File Templates.
 */

class Simplex_Plugin_Internals_Initialize extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
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
            if(!isset($user->internal))
            {
                $model = new Model_UsersMapper();
                $userDetail = $model->getDetail($user->user_id);
                if(count($userDetail['internals']) == 1)
                {
                    $internal = reset($userDetail['internals']);
                    $user->internal_id = $internal['id_internal'];
                    $user->internal_abbr = $internal['abbr'];
                    $user->internal_name = $internal['full_name'];
                    $user->more_internals = false;
                }
                else
                {
                    $request->setControllerName('login')
                        ->setActionName('internals')
                        ->setParam('internals', $userDetail['internals']);
                }
            }
        }
        else
        {
            $request->setControllerName('login')
                ->setActionName('index');
        }

        return;

        $controller = ($request->getControllerName()) ?: 'index';
        $module = $request->getModuleName();

        if($controller == 'login' || $controller == 'error')
        {
            return;
        }

        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity())
        {
            $user = $auth->getIdentity();

          /*  if(!isset($user->internal))
            {
                $request->setControllerName('login')
                    ->setActionName('internal');
            }*/

            $rulesManager = new Simplex_Acl_RulesManager($user);

            $acl = new Simplex_Acl_Acl($user->role, $rulesManager->getResources(), $rulesManager->getRules());

            /*
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'create') ? '' : 'NOT ') . ' create' . '<br />';
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'read') ? '' : 'NOT ') . ' read' . '<br />';
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'update') ? '' : 'NOT ') . 'update' . '<br />';
            echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'delete') ? '' : 'NOT ') . 'delete' . '<br />';
            */

            $role = $acl->getRoleName($user->role);

            // controlle se la risorsa esiste
            $resource = $controller;
            $action = $request->getActionName();

            if(!$acl->has($resource))
            {
                throw new Exception('denied access');
                $resource = null;
            }

            if (!$acl->isAllowed($role, $resource, $action))
            {
                throw new Exception('denied access');
            }

            $navigationManager = new Simplex_Navigation_Initialize();
            $navigationManager->build($request, $rulesManager->getNestedRules());

            $layout = Zend_Layout::getMvcInstance();
            $layout->user = $auth->getIdentity();

        }
        else
        {
            $request->setControllerName('login')
                ->setActionName('index');
        }
    }
}
