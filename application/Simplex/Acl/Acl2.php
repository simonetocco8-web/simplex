<?php

class Simplex_Acl_Acl2 extends Zend_Acl
{
    public function __construct(Model_User $user)
    {
        // visto che non abbiamo ruoli carichiamo un ruolo fittizio
        $this->addRole('user');
        
        // carico i permessi
        $permissionsModel = new Model_Permissions();
        $permissions = $permissionsModel->getPermissions();
        
        // carico le risorse
        $this->add(new Zend_Acl_Resource('addresses'));
        $this->add(new Zend_Acl_Resource('admin'));
        $this->add(new Zend_Acl_Resource('administration'));
        $this->add(new Zend_Acl_Resource('chat'));
        $this->add(new Zend_Acl_Resource('common'));
        $this->add(new Zend_Acl_Resource('companies'));
        $this->add(new Zend_Acl_Resource('contacts'));
        $this->add(new Zend_Acl_Resource('dashboard'));
        $this->add(new Zend_Acl_Resource('messages'));
        $this->add(new Zend_Acl_Resource('export'));
        $this->add(new Zend_Acl_Resource('error'));
        $this->add(new Zend_Acl_Resource('files'));
        $this->add(new Zend_Acl_Resource('index'));
        $this->add(new Zend_Acl_Resource('login'));
        $this->add(new Zend_Acl_Resource('offers'));
        $this->add(new Zend_Acl_Resource('orders'));
        $this->add(new Zend_Acl_Resource('sdm'));
        $this->add(new Zend_Acl_Resource('tasks'));
        $this->add(new Zend_Acl_Resource('search'));
        $this->add(new Zend_Acl_Resource('users'));

        // azioni che possono fare tutti
        $this->allow(null, 'login');
        
        $this->allow('user', 'index');
        $this->allow('user', 'dashboard');
        $this->allow('user', 'error');
        $this->allow('user', 'chat');
        $this->allow('user', 'common');
        $this->allow('user', 'messages');
        $this->allow('user', 'files');
        $this->allow('user', 'search');
        $this->allow('user', 'users');

        $this->allow('user', 'export');

        $this->allow('user', 'companies', array(
            'tbl',
            'percentforpartner',
            'setint',
            'acaddresses',
        ));
        $this->allow('user', 'contacts', array(
            'contactsbycompanyforselect',
            'tbl',
            'numbersfor',
            'mailsfor',
            'addressesfor',
        ));
        $this->allow('user', 'admin', array(
            'tbl',
            'subservicesbyserviceforselect',
        ));
        
        // admin controller
        if($user->has_permission('admin'))
        {
            if($user->has_permission('admin', 'view') || $user->has_permission('admin', 'modify'))
            {
                $this->allow('user', 'admin');
            }
        }
        else
        {
            $this->allow('user', 'admin', array(
                'users'
            ), new Simplex_Acl_Assertion_UserInfo());
        }
        
        // administration controller
        if($user->has_permission('administration'))
        {
            if($user->has_permission('administration', 'view'))
            {
                $this->allow('user', 'administration', array(
                    'index',
                    'production',
                    'tobe',
                    'open',
                    'closed',
                    'draft',
                    'invoices',
                    'invoice',
                    'export',
                    'tranches',
                    'tranche',
                ));
            }
            
            // edit or modify???
            if($user->has_permission('administration', 'edit'))
            {
                $this->allow('user', 'administration', array(
                    'dofattura',
                    'close',
                    'np',
                    'nps',
                    'nc',
                    'ncs',
                    'pc',
                    'pcs',
                ));
            }
        }
        
        // companies controller
        if($user->has_permission('companies'))
        {
            if($user->has_permission('companies', 'view'))
            {
                $this->allow('user', 'companies', array(
                    'index',
                    'list',
                    'export',
                ));

                $this->allow('user', 'companies', array(
                    'detail',
                    'export',
                ), new Simplex_Acl_Assertion_Companies_ViewExcluded());
            }
            elseif($user->has_permission('companies', 'view_own'))
            {
                $this->allow('user', 'companies', array(
                    'index',
                    'list',
                ));
                $this->allow('user', 'companies', array(
                    'detail',
                    'export',
                ), new Simplex_Acl_Assertion_Companies_ViewOwn());
            }

            $can_create = $user->has_permission('companies', 'create');
            if($can_create && $user->has_permission('companies', 'modify'))
            {
                $this->allow('user', 'companies', array('edit', 'save'));
            }
            else
            {
                if($can_create)
                {
                    $this->allow('user', 'companies', array('edit', 'save'), new Simplex_Acl_Assertion_Companies_Create());
                }
                else
                {
                    if($user->has_permission('companies', 'modify'))
                    {
                        $this->allow('user', 'companies', array('edit', 'save'), new Simplex_Acl_Assertion_Companies_Modify());
                    }
                }
            }
            if(!$user->has_permission('companies', 'modify') && $user->has_permission('companies', 'modify_own'))
            {
                $this->allow('user', 'companies', array('edit', 'save'), new Simplex_Acl_Assertion_Companies_ModifyOwn($can_create));
            }
            if($user->has_permission('companies', 'exclude'))
            {
                $this->allow('user', 'companies', 'exclude');
            }
            if($user->has_permission('companies', 'link_office'))
            {
                $this->allow('user', 'companies', array(
                    'lo',
                ));
            }
        }
        
        // contacts controller
        if($user->has_permission('contacts'))
        {
            if($user->has_permission('contacts', 'view'))
            {
                $this->allow('user', 'companies', array(
                    'detailcontact',
                ));
                $this->allow('user', 'contacts', array(
                    'index',
                    'list',
                    'detail',
                ));
            }
            
            if($user->has_permission('contacts', 'create') && $user->has_permission('contacts', 'modify'))
            {
                $this->allow('user', 'companies', 'deletecontact');
                $this->allow('user', 'companies', 'editcontact');
                $this->allow('user', 'companies', 'savecontact');
                $this->allow('user', 'contacts', 'edit');
                $this->allow('user', 'contacts', 'save');
            }
            else
            {
                if($user->has_permission('contacts', 'create'))
                {
                    $this->allow('user', 'companies', 'deletecontact', new Simplex_Acl_Assertion_Contacts_Create());
                    $this->allow('user', 'companies', 'editcontact', new Simplex_Acl_Assertion_Contacts_Create());
                    $this->allow('user', 'companies', 'savecontact', new Simplex_Acl_Assertion_Contacts_Create());
                    $this->allow('user', 'contacts', 'edit', new Simplex_Acl_Assertion_Contacts_Create());
                    $this->allow('user', 'contacts', 'save', new Simplex_Acl_Assertion_Contacts_Create());
                }
                else
                {
                    if($user->has_permission('contacts', 'modify'))
                    {
                        $this->allow('user', 'companies', 'deletecontact', new Simplex_Acl_Assertion_Contacts_Modify());
                        $this->allow('user', 'companies', 'editcontact', new Simplex_Acl_Assertion_Contacts_Modify());
                        $this->allow('user', 'companies', 'savecontact', new Simplex_Acl_Assertion_Contacts_Modify());
                        $this->allow('user', 'contacts', 'edit', new Simplex_Acl_Assertion_Contacts_Modify());
                        $this->allow('user', 'contacts', 'save', new Simplex_Acl_Assertion_Contacts_Modify());
                    }
                }
            }
            if( !$user->has_permission('contacts', 'modify') && $user->has_permission('contacts', 'modify_own'))
            {
                $this->allow('user', 'companies', 'deletecontact', new Simplex_Acl_Assertion_Contacts_ModifyOwn());
                $this->allow('user', 'companies', 'editcontact', new Simplex_Acl_Assertion_Contacts_ModifyOwn());
                $this->allow('user', 'companies', 'savecontact', new Simplex_Acl_Assertion_Contacts_ModifyOwn());
                $this->allow('user', 'contacts', 'edit', new Simplex_Acl_Assertion_Contacts_ModifyOwn());
                $this->allow('user', 'contacts', 'save', new Simplex_Acl_Assertion_Contacts_ModifyOwn());
            }
        }
        
        // offers controller
        if($user->has_permission('offers'))
        {
            if($user->has_permission('offers', 'view'))
            {
                $this->allow('user', 'offers', array(
                    'index',
                    'list',
                    'detail',
                    'top',
                    'export',
                    'export2',
                    'companies',
                ));
            }
            elseif($user->has_permission('offers', 'view_own'))
            {
                $this->allow('user', 'offers', array(
                    'index',
                    'list',
                ));
                $this->allow('user', 'offers', 'detail', new Simplex_Acl_Assertion_Offers_ViewOwn());
                $this->allow('user', 'offers', 'top', new Simplex_Acl_Assertion_Offers_ViewOwn());
                $this->allow('user', 'offers', 'export', new Simplex_Acl_Assertion_Offers_ViewOwn());
            }

            if($user->has_permission('offers', 'create') && $user->has_permission('offers', 'modify') && $user->has_permission('offers', 'modify_own'))
            {
                $this->allow('user', 'offers', 'edit');
                $this->allow('user', 'offers', 'cr');
                $this->allow('user', 'offers', 'cs');
                $this->allow('user', 'offers', 'css');
            }
            elseif($user->has_permission('offers', 'create') && $user->has_permission('offers', 'modify'))
            {
                $this->allow('user', 'offers', 'edit');
                $this->allow('user', 'offers', 'cr');
                $this->allow('user', 'offers', 'cs');
                $this->allow('user', 'offers', 'css');
            }
            elseif($user->has_permission('offers', 'create') && $user->has_permission('offers', 'modify_own'))
            {
                $this->allow('user', 'offers', 'edit', new Simplex_Acl_Assertion_Offers_CreateModifyOwn());
                $this->allow('user', 'offers', 'cr', new Simplex_Acl_Assertion_Offers_CreateModifyOwn());
                $this->allow('user', 'offers', 'cs', new Simplex_Acl_Assertion_Offers_CreateModifyOwn());
                $this->allow('user', 'offers', 'css', new Simplex_Acl_Assertion_Offers_CreateModifyOwn());
            }
            elseif($user->has_permission('offers', 'modify_own'))
            {
                $this->allow('user', 'contacts', 'edit', new Simplex_Acl_Assertion_Offers_ModifyOwn());
                $this->allow('user', 'contacts', 'cr', new Simplex_Acl_Assertion_Offers_ModifyOwn());
                $this->allow('user', 'contacts', 'cs', new Simplex_Acl_Assertion_Offers_ModifyOwn());
                $this->allow('user', 'contacts', 'css', new Simplex_Acl_Assertion_Offers_ModifyOwn());
            }
            elseif($user->has_permission('offers', 'create'))
            {
                $this->allow('user', 'offers', 'edit', new Simplex_Acl_Assertion_Offers_Create());
                $this->allow('user', 'offers', 'cr', new Simplex_Acl_Assertion_Offers_Create());
                $this->allow('user', 'offers', 'cs', new Simplex_Acl_Assertion_Offers_Create());
                $this->allow('user', 'offers', 'css', new Simplex_Acl_Assertion_Offers_Create());
            }
            elseif($user->has_permission('offers', 'modify'))
            {
                $this->allow('user', 'offers', 'edit', new Simplex_Acl_Assertion_Offers_Modify());
                $this->allow('user', 'offers', 'cr', new Simplex_Acl_Assertion_Offers_Modify());
                $this->allow('user', 'offers', 'cs', new Simplex_Acl_Assertion_Offers_Modify());
                $this->allow('user', 'offers', 'css', new Simplex_Acl_Assertion_Offers_Modify());
            }


            /*
            if($user->has_permission('offers', 'create') && $user->has_permission('offers', 'modify'))
            {
                $this->allow('user', 'offers', 'edit');
                $this->allow('user', 'offers', 'cr');
                $this->allow('user', 'offers', 'cs');
                $this->allow('user', 'offers', 'css');
            }
            else
            {
                if($user->has_permission('offers', 'create'))
                {
                    $this->allow('user', 'offers', 'edit', new Simplex_Acl_Assertion_Offers_Create());
                }
                else
                {
                    if($user->has_permission('offers', 'modify'))
                    {
                        $this->allow('user', 'offers', 'edit', new Simplex_Acl_Assertion_Offers_Modify());
                        $this->allow('user', 'offers', 'cr', new Simplex_Acl_Assertion_Offers_Modify());
                        $this->allow('user', 'offers', 'cs', new Simplex_Acl_Assertion_Offers_Modify());
                        $this->allow('user', 'offers', 'css', new Simplex_Acl_Assertion_Offers_Modify());
                    }
                }
            }
            if($user->has_permission('offers', 'modify_own'))
            {
                $this->allow('user', 'contacts', 'edit', new Simplex_Acl_Assertion_Offers_ModifyOwn());
                $this->allow('user', 'contacts', 'cr', new Simplex_Acl_Assertion_Offers_ModifyOwn());
                $this->allow('user', 'contacts', 'cs', new Simplex_Acl_Assertion_Offers_ModifyOwn());
                $this->allow('user', 'contacts', 'css', new Simplex_Acl_Assertion_Offers_ModifyOwn());
            }
            */
        }
        
        // orders controller
        if($user->has_permission('orders'))
        {
            if($user->has_permission('orders', 'view'))
            {
                $this->allow('user', 'orders', array(
                    'index',
                    'list',
                    'exportrali',
                    'up',
                    'uc',
                    'ucm',
                    'ucg',
                    'detail',
                    'companies',
                ));
            }
            elseif($user->has_permission('orders', 'view_own'))
            {
                $this->allow('user', 'orders', array(
                    'index',
                    'list',
                    'companies',
                ));
                $this->allow('user', 'orders', array(
                    'exportrali', // id
                    'up', 
                    'uc',
                    'ucm', // id_order
                    'ucg', // id_order
                    'detail', // id
                ), new Simplex_Acl_Assertion_Orders_ViewOwn());

            }
            if($user->has_permission('orders', 'create'))
            {
                $this->allow('user', 'offers', array(
                    'cco', 'ccos'
                ));
            }

            if($user->has_permission('orders', 'assign'))
            {
                $this->allow('user', 'orders', array(
                    'mpm', 'mpms', 'mp', 'mps', 'mcomm', 'mcomms', /*'cancel',*/
                ));
            }

            if($user->has_permission('orders', 'modify_always'))
            {
                $this->allow('user', 'orders', array(
                    'mcm', 'mcms', 'mc', 'mcs', 'closefase', 'closefases', 'enti', 'incarico', 'work' /*'cancel',*/
                ));
            }
            elseif($user->has_permission('orders', 'incarico'))
            {
                $this->allow('user', 'orders', array(
                    'mcm', 'mcms', 'mc', 'mcs', 'closefase', 'closefases', 'enti', 'incarico', 'work' /*'cancel',*/
                ), new Simplex_Acl_Assertion_Orders_Incarico());
            }


           // if($user->has_permission('orders', 'modify_plan'))
            //{
            //    $this->allow('user', 'orders', array(
            //        'mpm', 'mpms', 'mp', 'mps', 'mcomm', 'mcomms', 'suspend', /*'cancel',*/ 'resume'
            //    ));
            //}


            //if($user->has_permission('orders', 'modify_cons'))
            //{
            //    $this->allow('user', 'orders', array(
            //        'mcm', 'mcms', 'mc', 'mcs', 'mcomm', 'mcomms', 'closefase', 'closefases', 'enti', 'suspend', /*'cancel',*/ 'resume'
            //    ));
           // }

            if($user->has_permission('orders', 'cancel'))
            {
                $this->allow('user', 'orders', array(
                    'cancel',
                    'suspend',
                    'resume'
                ));
            }
        }
        
        // sdm controller
        if($user->has_permission('sdm'))
        {
            if($user->has_permission('sdm', 'view'))
            {
                $this->allow('user', 'sdm', array(
                    'index',
                    'list',
                    'detail',
                    'export'
                ));
            }
            elseif($user->has_permission('sdm', 'view_own'))
            {
                $this->allow('user', 'sdm', array(
                    'index',
                    'list',
                ));
                $this->allow('user', 'sdm', array(
                    'detail',
                    'export',
                ), new Simplex_Acl_Assertion_Sdm_ViewOwn());
            }

            
            if($user->has_permission('sdm', 'create'))
            {
                $this->allow('user', 'sdm', array('edit', 'mrv', 'save', 'v', 'mr'));
            }
            
            if($user->has_permission('sdm', 'responsabile'))
            {
                $this->allow('user', 'sdm', array('mssb', 'msr', 'mnv', 'ms', 'mv', 'mpv', 'mpnv'));
            }
            
            if($user->has_permission('sdm', 'risolutore'))
            {
                $this->allow('user', 'sdm', 'mt');
            }

            if($user->has_permission('sdm', 'preventer'))
            {
                $this->allow('user', 'sdm', 'mpa');
            }
        }
        
        // tasks controller
        if($user->has_permission('tasks'))
        {
            if($user->has_permission('tasks', 'view'))
            {
                $this->allow('user', 'tasks', array(
                    'index',
                    'list',
                    'agenda',
                    'table',
                    'detail',
                    'where',
                    'calendar',
                ));
            }
            elseif($user->has_permission('tasks', 'view_own'))
            {
                $this->allow('user', 'tasks', array(
                    'index',
                    'list',
                    'agenda',
                    'table',
                    'calendar',
                ));
                $this->allow('user', 'tasks', 'detail', new Simplex_Acl_Assertion_Tasks_Own());
                $this->allow('user', 'tasks', 'where', new Simplex_Acl_Assertion_Tasks_Own());
            }
            
            if($user->has_permission('tasks', 'create'))
            {
                $this->allow('user', 'tasks', array(
                    'edit',
                    'save',
                ));
            }
            elseif($user->has_permission('tasks', 'create_own'))
            {
                $this->allow('user', 'tasks', 'edit', new Simplex_Acl_Assertion_Tasks_Own());
                $this->allow('user', 'tasks', 'save', new Simplex_Acl_Assertion_Tasks_Own());
            }
            
            if($user->has_permission('tasks', 'modify'))
            {
                $this->allow('user', 'tasks', array('done', 'postpone', 'edit'));
            }
            elseif($user->has_permission('tasks', 'modify_own'))
            {
                $this->allow('user', 'tasks', array('done', 'postpone', 'edit'), new Simplex_Acl_Assertion_Tasks_Own());
            }
        }
    }
    
    /**
    * Returns the number of the resources
    * 
    * @return array
    */
    public function getResourcesCount()
    {
        return count($this->_resources);
    }
    
    /**
    * Returns the resource at the given index.
    * 
    * @param int $index
    * @return Zend_Acl_Resource
    */
    public function getResource(int $index)
    {
        if($index < 0 || $index >= $this->getResourcesCount())
        {
            return null;
        }
        return $this->_resources[$index];
    }
    
    /**
    * Returns all the resources as array
    * 
    * @return array
    */
    public function getResources()
    {
        return $this->_resources;
    }

    public function getRoles()
    {
        return $this->_roles;
    }

    public function getRoleName($value)
    {
        if(array_key_exists($value, $this->_roles))
        {
            return $this->_roles[$value];
        }

        throw new Exception('no role for the given key "' . $value . '"!');
    }

    public function getRoleValue($name)
    {
        if($value = array_search($name, $this->_roles))
        {
            return $value;
        }

        throw new Exception('no role for the given key "' . $name . '"!');
    }
}


?>
