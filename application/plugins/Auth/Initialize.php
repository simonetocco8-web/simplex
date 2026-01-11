<?php

class Auth_Plugin_Initialize extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$controller = ($request->getControllerName()) ?: 'index';
		$module = $request->getModuleName();
		if($controller == 'login' || $controller == 'error')
		{
			return;
		}

		$auth = Zend_Auth::getInstance();

		if ($auth->hasIdentity())
		{
			// DO SOMETHING
			$session = new Zend_Session_Namespace('simplex_acl');
			if(!isset($session->acl))
			{
				$db = Zend_Registry::get('dbAdapter');
				$acl = new Auth_Model_AclModel($db, (int) $auth->getIdentity()->id);
				$session->acl = $acl;
			}
			{
				$acl = $session->acl;
			}

			/*
			 echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'create') ? '' : 'NOT ') . ' create' . '<br />';
			 echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'read') ? '' : 'NOT ') . ' read' . '<br />';
			 echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'update') ? '' : 'NOT ') . 'update' . '<br />';
			 echo 'admin can ' .  ($acl->isAllowed('admin', 'dashboard', 'delete') ? '' : 'NOT ') . 'delete' . '<br />';
			 */


			// controlle se la risorsa esiste
			$resource = $module . '_' . $controller;
			$action = $request->getActionName();

			if(!$acl->has($resource))
			{
				$resource = null;
			}

			if (!$acl->isAllowed($acl->getLoggedUserRole(), $resource, $action))
			{
				throw new Exception('denied access');

				/*
				 $request->setModuleName('auth')
				 ->setControllerName('error')
				 ->setActionName('acl');
				 */
			}

			$layout = Zend_Layout::getMvcInstance();
			$layout->user = $auth->getIdentity()->username;
			$layout->userId = $auth->getIdentity()->id;

			$view = Zend_Layout::getMvcInstance()->getView();

			$pages = $view->navigation()->getPages();

			foreach($pages as $page)
			{
				$resource = $page->getModule() . '_' . (($page->getController()) ?: 'index');
				if(!$acl->has($resource))
				{
					$resource = null;
				}

				$action = $page->getAction() ?: 'index';

				if (!$acl->isAllowed($acl->getLoggedUserRole(), $resource, $action))
				{
					$view->navigation()->removePage($page);
					continue;
				}
				else
				{
					$subpages = $page->getPages();
					if(!empty($subpages))
					{
						foreach ($subpages as $subpage)
						{
							$resource = $subpage->getModule() . '_' . (($page->getController()) ?: 'index');
							if(!$acl->has($resource))
							{
								$resource = null;
							}

							$action = $subpage->getAction() ?: 'index';

							if (!$acl->isAllowed($acl->getLoggedUserRole(), $resource, $subpage->getAction()))
							{
								$page->removePage($subpage);
								continue;
							}
						}
					}
				}
			}
		}
		else
		{
			$request->setModuleName('auth')
			->setControllerName('login')
			->setActionName('index');
		}
	}
}
