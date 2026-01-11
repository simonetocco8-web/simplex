<?php

class Model_UsersMapper
{
	protected $_data = array(
        'id' => '',
        'active' => '',
        'username' => '',
        'id_role' => '',
        'id_contact' => '',
        'internals' => array(
            'id_internal' => '',
        )
    );

    public function __construct()
    {

    }
    
    public function getUserWithContactData($user_id)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();

        $select->from('users', array('username', 'active', 'user_id'))
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', '*')
            ->where('users.user_id = ?', $user_id);

        $userData = $db->fetchRow($select);
        return $userData;
    }
    
    public function getDtgs()
    {
		$db = $this->_getDbAdapter();
        
        $select = $db->select();
        
        $select->from('users', array('user_id', 'username'))
            ->where('id_role = 3')
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', array('nome', 'cognome'))
            ->order('username ASC'); 
        // id ruolo DTG = 3
        // TODO: ora � statico a 3. potrebbe essere dinamico??? Al momento non c'è...
        
        return $db->fetchAll($select);
	}
    
    public function getRcos($options = null)
    {
        $db = $this->_getDbAdapter();
        
        $select = $db->select();
        
        $select->from('users', array('user_id', 'username'))
            ->where('id_role = 2')
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', array('nome', 'cognome'))
            ->order('username ASC'); 
        // id ruolo RCO = 2
        // TODO: ora � statico a 2. potrebbe essere dinamico???
        
        return $db->fetchAll($select);
    }
    
    public function getAllUsers($options = null)
    {
        $db = $this->_getDbAdapter();
        
        $select = $db->select();
        
        $select->from('users', array('user_id', 'username'))
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', array('nome', 'cognome'))
            ->order('username ASC'); 
        
        return $db->fetchAll($select);
    }

    public function getAllActiveUsers($options = null)
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $user = Zend_Auth::getInstance()->getIdentity();

        $select->from('users', array('user_id', 'username'))
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', array('nome', 'cognome'))

            ->joinLeft('users_internals', 'users_internals.id_user = user_id', array())
            ->where('users_internals.id_internal = ?', $user->internal_id)

            ->where('users.deleted = 0 and active = 1')
            ->order('username ASC');

        return $db->fetchAll($select);
    }


    /**
     * Returns user role as array: id_role => X, role_name => Y
     *
     * @param int $id
     * @return array
     */
    public function getUserRole($id)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        $select->from('acl_users_roles', array('role_id' => 'id_role'))
        ->joinLeft('acl_roles', 'id_role = id', array('role_name' => 'name'))
        ->where('id_user = ?', $id);

        return $db->fetchRow($select);
    }

    public function getUsersList($sort = null, $dir = 'ASC', $search = array(), $searchOperand = 'AND')
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('users', array('id', 'username', 'active'))
        ->joinLeft('contacts_users', 'id_user = users.id', array())
        ->joinLeft('contacts', 'contacts.id = id_contact', array('nome', 'cognome'))
        ->joinLeft('acl_users_roles', 'acl_users_roles.id_user = users.id', array())
        ->joinLeft('acl_roles', 'acl_roles.id = id_role', array('role_name' => 'acl_roles.name', 'role_description' => 'acl_roles.description'))
        ->joinLeft('users_internals', 'users_internals.id_user = users.id', array())
        ->joinLeft('internals', 'internals.id = users_internals.id_internal', array('iname' => 'name', 'iabbr' => 'abbr'));

        /*
        $lnk = new Maco_Db_LinkerNN($db);
        
        $lnk->execute('users_roles', 
                      array('field' => 'id_user', 'value' => 1), 
                      array('field' => 'id_role', 'value' => 2), 
                      array('description' => 'mario'));
        */
        
        if($sort)
        {
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('username ASC');
        }

        if(!empty($search))
        {
            foreach($search as $k => $s)
            {
                if($s != '' && $k != 'page' && $k != 'format' && $k != 'perpage' && $k != 'sdl' && $k != 'sfl' && $k != '_s' && $k != '_d')
                {
                    $k = str_replace('|', '.', $k);
                    
                    $method = ($searchOperand == 'AND') ? 'where' : 'orWhere';
                    
                    $select->$method($k . ' like ' . $db->quote('%' . $s . '%'));
                }
            }
        }

        $values = $db->fetchAll($select);

        $users = array();
        foreach($values as $u)
        {
            if(!array_key_exists($u['id'], $users))
            {
                $users[$u['id']] = $u;
                unset($users[$u['id']]['iname'], $users[$u['id']]['iabbr']);
                $users[$u['id']]['internals'] = array();
                //$users[$u['id']]['internals'] = '';
            }
            $users[$u['id']]['internals'][] = $u['iabbr'];
            //$users[$u['id']]['internals'] .= $u['iabbr'] . ', ';
        }

        return $users;
    }

    public function save($data)
    {
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
        try
        {
            $edit = isset($data['id']) && $data['id'] != '';

            $userModel = new Model_Users();

            $user_id = $userModel->save($data);
            
            unset($userModel);
            
            $helper = new Maco_Db_Helper($db);
            
            // Collego l'utente al ruolo
            // TODO: si potrebbe fare meglio???
            $helper->linkNN_deleteFirst('acl_users_roles', 
                array('field' => 'id_role', 'value' => ((int)$data['role'])), 
                array('field' => 'id_user', 'value' => $user_id));

            // internals
            $helper->removeLinkNN('users_internals', 
                array('field' => 'id_user', 'value' => $user_id));
            
            if(isset($data['internals']))
            {
                foreach($data['internals'] as $internal_id)
                {
                    $safe = (int) $internal_id;
                    $helper->linkNN('users_internals', 
                        array('field' => 'id_user', 'value' => $user_id),
                        array('field' => 'id_internal', 'value' => $safe));
                }
            }
            
            
            $contactsModel = new Model_ContactsMapper();
            
            $contact_id = $contactsModel->save($data, 'contacts_');
            
            $helper->linkNN_deleteFirst('contacts_users', 
                        array('field' => 'id_user', 'value' => $user_id),
                        array('field' => 'id_contact', 'value' => $contact_id));

            $db->commit();
        
            return $user_id;
        }
        catch (Exception $e)
        {
            $db->rollBack();

            return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }
    }
    
    public function saveOld($data)
    {
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
        try
        {
            $edit = isset($data['id']) && $data['id'] != '';

            $userModel = new Model_Users();

            $user_id = $userModel->save($data);

            // Collego l'utente al ruolo
            // TODO: si potrebbe fare meglio???
            $user_role_data = array('id_role' => (int) $data['role']);
            if(!$edit)
            {
                $user_role_data['id_user'] = $user_id;
                $db->insert('acl_users_roles', $user_role_data);
            }
            else
            {
                $db->update('acl_users_roles',
                $user_role_data,
                array('id_user = ?' =>  $user_id));
            }

            unset($userModel);

            // informazioni personali
            $contactsMapper = new Model_ContactsMapper();
            $contactModel = new Model_Contacts();

            $cdata = array(
                'id' => $data['id_contact'],
                'nome' => $data['nome'],
                'cognome' => $data['cognome'],
            );

            $contact_id = $contactModel->save($cdata);

            unset($contactModel);

            // Collego l'utente e il contatto alle internals
            // 1. rimuovo tutti i collegamenti
            $db->delete('users_internals', 'id_user = ' . $db->quote($user_id));
            $db->delete('contacts_internals', 'id_contact = ' . $db->quote($contact_id));
            // 2. inserisco le passate
            if(isset($data['internals']))
            {
                foreach($data['internals'] as $internal_id)
                {
        $safe = (int) $internal_id;
        $db->insert('users_internals', array('id_user' => $user_id, 'id_internal' => $safe));
        $db->insert('contacts_internals', array('id_contact' => $contact_id, 'id_internal' => $safe));
                }
            }


            // informazioni personali
            // Carico l'utilit� per la gestione dei campi multipli
            $util = new Simplex_Utils_Input();

            $addressModel = new Model_Addresses();

            $this->_saveDependencies($util, array(
        'id' => 'id_address', 'via', 'cap', 'localita', 'numero', 'provincia', 'description' => 'address-description'
        ), $data, $db, $addressModel, 'addresses', 'addresses_contacts', 'id_address', 'id_contact', $contact_id);
        unset($addressModel);

        $numberModel = new Model_Telephones();

        $this->_saveDependencies($util, array(
        'id' => 'id_number', 'number', 'description' => 'telephone-description'
        ), $data, $db, $numberModel, 'telephones', 'telephones_contacts', 'id_telephone', 'id_contact', $contact_id);
        unset($numberModel);

        $mailsModel = new Model_Mails();

        $this->_saveDependencies($util, array(
        'id' => 'id_mail', 'mail', 'description' => 'email-description'
        ), $data, $db, $mailsModel, 'mails', 'mails_contacts', 'id_mail', 'id_contact', $contact_id);
        unset($numberModel);

        $db->commit();

        return $user_id;
        }
        catch (Exception $e)
        {
            $db->rollBack();

            return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }
    }

    protected function _saveDependencies(&$util, $fields, &$data, &$db, &$model, $table_main, $table_dep, $field_dep, $field_parent, $parent_id)
    {
        // Inserisco i diversi possibili indirizzi
        $partial = $util->formatDataForMultipleFields($fields, $data);

        $presents = array();
        if(!empty($partial))
        {
            foreach($partial as $k => $p)
            {
                $descr = $p['description'];
                unset($p['description']);

                $id = $model->save($p);

                $edit = !empty($p['id']);

                if(!$edit)
                {
        $db->insert($table_dep, array(
        $field_dep => $id,
        $field_parent => $parent_id,
            'description' => $descr
        ));
                }
                else
                {
        $db->update($table_dep,
        array('description' => $descr),
        array($field_dep . ' = ?' => $id, $field_parent . ' = ?' => $parent_id));
                }
                $presents[] = $id;
            }
        }

        // eliminiamo le non pi� presenti
        return $db->query('delete ' . $table_main . ', ' . $table_dep . ' ' .
'from ' . $table_dep . ', ' . $table_main . ' ' .
'where ' . $table_main . '.id = ' . $table_dep . '.' . $field_dep . ' ' .
'and ' . $field_parent . ' = ' . $db->quote($parent_id) .  
        ((empty($presents)) ? '' : ' and ' . $field_dep . ' not in (' . implode(', ', $presents) . ')'));
    }


    /**
     * Return the detail for the given user
     *
     * @param int $id user id
     * @return array
     */
    public function getDetail($id)
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();

        $select->from('users', array('username', 'active'))
            ->where('users.user_id = ?', $id);

        $userData = $db->fetchRow($select);

        unset($select);
        $select = $db->select();

        // internals
        $select->from('users_internals', array('id_internal'))
        ->joinLeft('internals', 'id_internal = internals.internal_id', array('full_name', 'abbr'))
        ->where('id_user = ?', $id);

        $userData['internals'] = $db->fetchAll($select);
        if(empty($userData['internals']))
        {
            $userData['internals'] = $this->_data['internals'];
        }

        return $userData;
    }

    /**
     * Save a new password
     *
     * @param array $data
     * @return array (true, '') or (false, 'error message')
     */
    public function saveNewPassword($data)
    {
        if($data['password'] != $data['cpassword'])
        {
            return array(false, 'Le password inserite non coincidono');
        }

        $user = Zend_Auth::getInstance()->getIdentity();

        $model = new Model_DbTables_Users();
        $old = $model->find($data['id']);

        if($old->count() == 0)
        {
            return array(false, 'Nessun utente con questo id');
        }
        $old = $old->current();
/*
        if(!$user->userIsAdmin)
        {
            if(!isset($data['oldpw']))
            {
                return array(false, 'Manca la vecchia password');
            }

            if(md5($data['oldpw'] . $old->password_salt) != $old->password)
            {
                return array(false, 'La vecchia password inserita non &egrave; corretta');
            }
        }
  */
        $ok = $model->update(
            array('password' => md5($data['password'] . $old->password_salt)),
            array('user_id = ?' => $data['id']));

        if(!$ok)
        {
            return array(false, 'Errore interno del sistema');
        }

        return array(true, 'La password &egrave; stata cambiata');
    }

    /**
     * Return a dummy user data array
     *
     * @return array
     */
    public function getEmptyDetail()
    {
        $data = $this->_data;
        
        $contacts = new Model_ContactsMapper();
        
        $cdata = $contacts->getEmptyDetail();
        $data['contact'] = $cdata;
        
        return $data;
    }

    /**
     * Delete a user from the database with all dependent data
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser($id)
    {
        $db = $this->_getDbAdapter();
        $db->beginTransaction();

        try
        {
            // account data
            $usersModel = new Model_Users();
            $usersModel->delete($id);
            
            $helper = new Maco_Db_Helper($db);
            
            $helper->removeLinkNN('acl_users_roles', 
            	array('field' => 'id_user', 'value' => $id));
            
            $helper->removeLinkNN('users_internals', 
            	array('field' => 'id_user', 'value' => $id));

            // contact data
            $contact_id = $db->fetchOne('select id_contact from contacts_users where id_user = ' . $db->quote($id));

            if($contact_id != null)
            {
                // esiste il contact nel db
                $contactsModel = new Model_ContactsMapper();                
                $contactsModel->delete($contact_id);
            }

            return $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollBack();
            return array('database_error' => $e->getMessage());
        }
    }

    /**
     * Returns the db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbAdapter()
    {
        return Zend_Registry::get('dbAdapter');
    }
}
