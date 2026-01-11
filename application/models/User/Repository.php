<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 8-ott-2010
 * Time: 12.02.08
 * To change this template use File | Settings | File Templates.
 */

class Model_User_Repository
{
    /**
     * Users mysql mapper
     *
     * @var Model_User_Mapper
     */
    protected $_userMapper;

    public function __construct()
    {
        $this->_userMapper = new Model_User_Mapper();
    }

    public function getNewUser()
    {
        $user = new Model_User();
        $contactsRepo = Maco_Model_Repository_Factory::getRepository('contact');
        $user->contact = $contactsRepo->getNewContact();
        return $user;
    }

    public function find($id)
    {
        $user = $this->_userMapper->findWithRole($id);
        $contactsRepo = Maco_Model_Repository_Factory::getRepository('contact');
        $user->contact = $contactsRepo->find($user->id_contact);
        $user->internals = $this->_userMapper->findInternalsByUserId($user->user_id);
        
        $user->permissions = $this->_userMapper->findPermissionsByUserId($user->user_id);
        
        // TODO: potremmo aggiungere qui un controllo per vedere se l'utente
        //       associato ad un ufficio sia associato alla azienda interna
        //       relativa e solo ad essa.
        
        return $user;
    }
    
    public function findWithOnlyPermissions($id)
    {
        $user = $this->_userMapper->find($id);
        
        $user->permissions = $this->_userMapper->findPermissionsByUserId($user->user_id);
        
        // TODO: potremmo aggiungere qui un controllo per vedere se l'utente
        //       associato ad un ufficio sia associato alla azienda interna
        //       relativa e solo ad essa.
        
        return $user;
    }

    public function save($user)
    {
        return $this->_userMapper->save($user);
    }

    public function getUsers($sort = 'username', $dir = 'ASC', $search = array(), $deleted = null, $with_internal = false)
    {
        $db = $this->_userMapper->getDbAdapter();

        $select = $db->select();

        $select->from('users', array('user_id', 'username', 'active',
            'numbers' => new Zend_Db_Expr("group_concat(DISTINCT concat_ws(' ', telephones.description, telephones.number) SEPARATOR '<br />')"),
            'mails' => new Zend_Db_Expr("group_concat(DISTINCT concat_ws(' ', mails.description, mails.mail) SEPARATOR '<br />')"),
        ))
            ->joinLeft('contacts', 'contacts.contact_id = users.id_contact', array('nome', 'cognome'))

            ->joinLeft('telephones', 'telephones.id_contact = contacts.contact_id', array())
            ->joinLeft('mails', 'mails.id_contact = contacts.contact_id', array())

            ->joinLeft('roles', 'role_id = users.id_role', array('role_name' => 'roles.name', 'role_description' => 'roles.description'))
            ->joinLeft('users_internals', 'users_internals.id_user = user_id', array())
            ->joinLeft('internals', 'internal_id = users_internals.id_internal', array('iname' => 'full_name', 'iabbr' => 'abbr'))
            ->joinLeft('offices', 'office_id = users_internals.id_office', array('oname' => 'offices.name'))
            ->group('contact_id');

        if($with_internal)
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $select->where('users_internals.id_internal = ?', $user->internal_id);
        }

        $select->order($sort . ' ' . $dir);

        if(isset($deleted))
        {
            $select->where('users.deleted = ?', $db->quote($deleted));
        }

        if(isset($search['username']) && $search['username'] != '')
        {
            $select->where('username like \'%' . $search['username'] . '%\'');
        }
        if(isset($search['nome']) && $search['nome'] != '')
        {
            $select->where('nome like \'%' . $search['nome'] . '%\'');
        }
        if(isset($search['cognome']) && $search['cognome'] != '')
        {
            $select->where('cognome like \'%' . $search['cognome'] . '%\'');
        }
        if(isset($search['internals']) && $search['internals'] != '')
        {
            $select->where('abbr like \'%' . $search['internals'] . '%\'');
        }
        if(isset($search['active']) && $search['active'] != '')
        {
            $select->where('active = ' . $search['active']);
        }
        if(isset($search['role_name']) && $search['role_name'] != '')
        {
            $select->where('roles.name like \'%' . $search['role_name'] . '%\'');
        }

        if(isset($search['numbers']) && $search['numbers'] != '')
        {
            $select->where('exists (select t2.number from telephones t2 where t2.id_contact = users.id_contact and t2.number like ' . $db->quote('%' . $search['numbers'] . '%') . ')');
        }

        if(isset($search['mails']) && $search['mails'] != '')
        {
            $select->where('exists (select m2.mail from mails m2 where m2.id_contact = users.id_contact and m2.mail like ' . $db->quote('%' . $search['mails'] . '%') . ')');
        }

        // type search
        if(isset($search['type']))
        {
            $types = $search['type'];
            if(is_string($types))
            {
                $types = array($types);
            }
            $type_where = '(';
            foreach($types as $type)
            {
                switch($type)
                {
                    case 'RAM':
                        $type_where .= 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'administration\' and action = \'receive_messages\') or';
                        break;
                    case 'DTG':
                        $type_where .= 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'orders\' and action = \'assign\') or';
                        break;
                    case 'RC':
                        $type_where .= 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'orders\' and action = \'incarico\') or';
                        break;
                    case 'SDMSR':
                        $type_where .= 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'sdm\' and action = \'responsabile\') or';
                        break;
                    default:
                        break;
                }
            }
            if($type_where != '(')
            {
                $type_where = substr($type_where, 0, -3) . ')';
                $select->where($type_where);
            }
        }

        if(isset($search['permissions']))
        {
            $select->joinLeft(array('up' => 'users_permissions'), 'up.id_user = users.user_id', array());
            foreach($search['permissions'] as $permission)
            {
                if(is_array($permission))
                {
                    $select->where('up.resource = ' . $db->quote($permission[0]) . ' and up.action = ' . $db->quote($permission[1]));
                }
                else
                {
                    $select->where('up.resource = ' . $db->quote($permission));
                }
            }
        }

        $values = $db->fetchAll($select);

        $users = array();
        foreach($values as $u)
        {
            if(!array_key_exists($u['user_id'], $users))
            {
                $users[$u['user_id']] = $u;
                unset($users[$u['user_id']]['iname'], $users[$u['user_id']]['iabbr']);
                $users[$u['user_id']]['internals'] = array();
            }
            $text = $u['iabbr'];
            if($u['oname'])
            {
                $text .= '<span class="info"> - ' . $u['oname'] . '</span>';
            }
            $users[$u['user_id']]['internals'][] = $text;
        }

        return $users;
    }

    public function getUsersWithPermissions($permissions, $search = array('active' => 1))
    {
        $search += array('permissions' => $permissions);
        return $this->getUsers('username', 'ASC', $search, 0, true);
    }

    public function getTypeWhere($type)
    {
        $type_where = '1=1';
        switch($type)
        {
            case 'RAM':
                $type_where = 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'administration\')';
                break;
            case 'DTG':
                $type_where = 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'orders\' and action = \'assign\')';
                break;
            case 'RC':
                $type_where = 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'orders\' and action = \'incarico\')';
                break;
            case 'SDMSR':
                $type_where .= 'exists (select id_user from users_permissions where id_user = users.user_id and resource = \'sdm\' and action = \'responsabile\')';
                break;
            default:
                break;
        }

        return $type_where;
    }

    /**
     * @param  string|array $type
     * @return array
     */
    public function getUsersOfType($type, $search = array('active' => 1))
    {
        $search['type'] = $type;
        return $this->getUsers('username', 'ASC', $search, null, true);
    }
    
    public function delete($id)
    {
        $db = $this->_userMapper->getDbAdapter();
        $db->beginTransaction();

        try
        {
            // account data
            $db->update('users', array('deleted' => 1), array('user_id = ?' => $id));
            
            /*
            $helper = new Maco_Db_Helper($db);
            
            //$helper->removeLinkNN('acl_users_roles', 
              //  array('field' => 'id_user', 'value' => $id));
            
            
            $helper->removeLinkNN('users_internals', 
                array('field' => 'id_user', 'value' => $id));

            // contact data
            $contact_id = $db->fetchOne('select id_contact from contacts_users where id_user = ' . $db->quote((int)$id));

            if($contact_id != null)
            {
                // esiste il contact nel db
                //$contactsModel = new Model_ContactsMapper();                
                //$contactsModel->delete($contact_id);
            }
*/
            return $db->commit();
        }
        catch (Exception $e)
        {
            $db->rollBack();
            return array('database_error' => $e->getMessage());
        }
    }
    
    public function saveLinks($data)
    {
        $db = $this->_userMapper->getDbAdapter();
        
        if(isset($data['office']))
        {                             
            if(count($data['internal']) != 1)
            {
                return array('Se si vuole associare l\'utente ad un ufficio bisogna scegliere una ed una sola azienda interna');
            }
        }
        
        $office = isset($data['office']) ? $data['office'] : null;
        $user_id = $data['user_id'];
        
        $db->delete('users_internals', 'id_user = ' . $db->quote($user_id));
        
        foreach($data['internal'] as $internal)
        {
            $db->insert('users_internals', array(
                'id_user'  => $user_id, 
                'id_internal' => $internal,
                'id_office' => $office));
        }
        
        return $user_id;
    }
    
    public function savePermissions($data, $permissions)
    {
        $db = $this->_userMapper->getDbAdapter();
        
        $user_id = $data['user_id'];
        
        $db->delete('users_permissions', 'id_user = ' . $db->quote($user_id));
        
        foreach($permissions as $rid => $resource)
        {
            foreach($resource['actions'] as $aid => $action)
            {
                if(isset($_POST[$rid . '__' . $aid]))
                {
                    $db->insert('users_permissions', array(
                        'id_user'  => $user_id, 
                        'resource' => $rid,
                        'action' => $aid));
                }
            }
        }
        
        return $user_id;
    }

    public function saveFromData($data, $uid, $prefix = '')
    {
        // fist the contact
        $contactRepo = Maco_Model_Repository_Factory::getRepository('contact');
        $contact_id = $contactRepo->saveFromData($data, $prefix . 'contacts_');
        if(is_array($contact_id))
        {
            // no good
            return $contact_id;
        }

        $data[$prefix . 'id_contact'] = $contact_id;

        $db = Zend_Registry::get('dbAdapter');

        $this_username = ($uid != 0)
                ? $db->fetchAll('select username from users where user_id = ' . $uid)
                : '';

        $exists = (int) $db->fetchOne(
            'select count(*) as cnt from users where username = ' . $db->quote($data['username'])
            . ' and username <> ' . $db->quote($this_username));
        if($exists > 0)
        {
            return array('Username esistente!');
        }

        $user = new Model_User();
        $user->setValidatorAndFilter(new Model_User_Validator());
        $user->setData($data, $prefix);
        if($user->isValid())
        {
            Maco_Model_TransactionManager::beginTransaction();

            try
            {
                $user_id = (int) $this->_userMapper->save($user);

                $db = $this->_userMapper->getDbAdapter();
                
                // aziende interne
                /*
                $db->delete('users_internals', 'id_user = ' . $db->quote($user_id));
                foreach($data['internals'] as $int)
                {
                    $db->insert('users_internals', array('id_user'  => $user_id, 'id_internal' => $int));
                }
                */

                Maco_Model_TransactionManager::commit();
                return $user_id;
            }
            catch(Exception $e)
            {
                Maco_Model_TransactionManager::rollback();
                return array($e->getMessage());
            }
        }
        else
        {
            return $user->getInvalidMessages();
        }
    }
}
