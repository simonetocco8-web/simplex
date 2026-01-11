<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-ott-2010
 * Time: 18.16.44
 * To change this template use File | Settings | File Templates.
 */


class Simplex_Navigation_Initialize extends Zend_Controller_Plugin_Abstract
{
    public function build(Zend_Controller_Request_Abstract $request, $rules, Zend_Acl $acl)
    {
        $uri = $request->getPathInfo();

        $view = Zend_Layout::getMvcInstance()->getView();

        $view->navigation()->menu()->setUlClass('nav');;

        $user = Zend_Auth::getInstance()->getIdentity();        

        $actionName = $request->getActionName();
        $controllerName = $request->getControllerName();
        $moduleName = $request->getModuleName();

        // 2. creo le pagine con il riferimento a quella a cui sono
        foreach($rules as $rule)
        {
            $action_to_test = ($rule['action']) ?: 'index';
            if(!$acl->isAllowed('user', $rule['controller'], $action_to_test))
            {
                continue;
            }
            
            $page = new Zend_Navigation_Page_Mvc();
            $page->setLabel($rule['label']);
            $page->setAction($rule['action']);
            $page->setController($rule['controller']);
  //          $page->setModule($rule['controller']);

            $class = 'headitem ' . $rule['css_class'];
            if($rule['controller'] == $controllerName)
            {
                $class .= ' active';
            }

            $page->setClass($class);

            if(isset($rule['pages']))
            {
                foreach($rule['pages'] as $subpage)
                {
                    $action_to_test = ($subpage['action']) ?: 'index';
                    if(!$acl->isAllowed('user', $subpage['controller'], $subpage['action']))
                    {
                        continue;
                    }
                    
                    $class = '';
                    if($subpage['controller'] == $controllerName &&
                       $subpage['action'] == $actionName
                    )
                    {
                        $class = 'current';
                    }

                    $page->addPage(array(
                        'label' => $subpage['label'],
//                        'module' => $subpage['controller'],
                        'controller' => $subpage['controller'],
                        'action' => $subpage['action'],
                        'class' => $class
                    ));
                }
            }

            $view->navigation()->addPage($page);
        }

        //$activeNav = $view->navigation()->findByUri($uri)->setActive(true);
    }
}
