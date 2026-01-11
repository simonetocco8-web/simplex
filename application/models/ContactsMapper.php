<?php

class Model_ContactsMapper
{
    /**
    * put your comment there...
    * 
    * @return Model_ContactsMapper
    */
    public function __construct()
    {

    }
    
    public function getList($sort = null, $dir = 'ASC', $search = array(), $searchOperand = 'AND')
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('contacts', array('contact_id', 'nome', 'cognome'))
        ->joinLeft('mails', 'mails.id_contact = contacts.contact_id', array('mail'))
        ->joinLeft('telephones', 'telephones.id_contact = contacts.contact_id', array('number'));


        if($sort)
        {
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('cognome ASC');
        }

        if(!empty($search))
        {
            foreach($search as $k => $s)
            {
                if($k == 'telephones' && $s != '')
                {
                    $select->where('number like ' . $db->quote('%' . $s . '%'));
                }
                else if($k == 'mails' && $s != '')
                {
                    $select->where('mail like ' . $db->quote('%' . $s . '%'));
                }
                else if($k == 'internals' && $s != '')
                {
                    $select->where('abbr like ' . $db->quote('%' . $s . '%'));
                }
                else
                if($s != '' && $k != 'page' && $k != 'format' && $k != 'perpage' && $k != 'sdl' && $k != 'sfl' && $k != '_s' && $k != '_d')
                {
                    $k = str_replace('|', '.', $k);
                    if($searchOperand == 'AND')
                    {
                        $select->where($k . ' like ' . $db->quote('%' . $s . '%'));
                    }
                    else
                    {
                        $select->orWhere($k . ' like ' . $db->quote('%' . $s . '%'));
                    }
                }
            }
        }

        $values = $db->fetchAll($select);

        $contacts = array();
        foreach($values as $c)
        {
            if(!array_key_exists($c['contact_id'], $contacts))
            {
                $contacts[$c['contact_id']] = $c;
                unset($contacts[$c['contact_id']]['mail']);
                $contacts[$c['contact_id']]['mails'] = array();
                $contacts[$c['contact_id']]['telephones'] = array();
                //$contacts[$u['id']]['internals'] = '';
            }
            if(!in_array($c['mail'], $contacts[$c['contact_id']]['mails']))
            {
                $contacts[$c['contact_id']]['mails'][] = $c['mail'];
            }
            if(!in_array($c['number'], $contacts[$c['contact_id']]['telephones']))
            {
                $contacts[$c['contact_id']]['telephones'][] = $c['number'];
            }
        }

        return $contacts;
    }

    public function findCompanyIdForContact($id)
    {
        $db = $this->_getDbAdapter();

        return $db->fetchOne('select id_company from contacts_companies where id_contact = ?', $id);
    }
                    
    public function getListByCompanyId($id, $sort = null, $dir = 'ASC', $search = array(), $searchOperand = 'AND')
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('contacts', array('contact_id', 'nome', 'cognome'))
        ->joinLeft('mails', 'mails.id_contact = contact_id', array('mail'))
        ->joinLeft('telephones', 'telephones.id_contact = contact_id', array('number'))
        ->where('contacts.id_company = ' . $db->quote($id));


        if($sort)
        {
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('cognome ASC');
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

        $contacts = array();
        foreach($values as $c)
        {
            if(!array_key_exists($c['contact_id'], $contacts))
            {
                $contacts[$c['contact_id']] = $c;
                unset($contacts[$c['contact_id']]['mail']);
                $contacts[$c['contact_id']]['mails'] = array();
                $contacts[$c['contact_id']]['telephones'] = array();
                //$contacts[$u['id']]['internals'] = '';
            }
            if(!in_array($c['mail'], $contacts[$c['contact_id']]['mails']))
            {
                $contacts[$c['contact_id']]['mails'][] = $c['mail'];
            }
            if(!in_array($c['number'], $contacts[$c['contact_id']]['telephones']))
            {
                $contacts[$c['contact_id']]['telephones'][] = $c['number'];
            }
        }

        return $contacts;
        
    }

    public function getListOld($sort = null, $dir = 'ASC', $search = array())
    {
        $db = $this->_getDbAdapter();

        $select = $db->select();

        $select->from('contacts', array('id', 'nome', 'cognome'))
        ->joinLeft('contacts_internals', 'contacts_internals.id_contact = contacts.id', array())
        ->joinLeft('internals', 'internals.id = contacts_internals.id_internal', array('iname' => 'name', 'iabbr' => 'abbr'));


        if($sort)
        {
            $select->order($sort . ' ' . $dir);
        }
        else
        {
            $select->order('cognome ASC');
        }

        if(!empty($search))
        {
            foreach($search as $k => $s)
            {
                if($s != '' && $k != 'page' && $k != 'format' && $k != 'perpage' && $k != 'sdl' && $k != 'sfl' && $k != '_s' && $k != '_d')
                {
        $k = str_replace('|', '.', $k);
        $select->where($k . ' like ' . $db->quote('%' . $s . '%'));
                }
            }
        }

        $values = $db->fetchAll($select);

        $contacts = array();
        foreach($values as $u)
        {
            if(!array_key_exists($u['id'], $contacts))
            {
                $contacts[$u['id']] = $u;
                unset($contacts[$u['id']]['iname'], $contacts[$u['id']]['iabbr']);
                $contacts[$u['id']]['internals'] = array();
                //$contacts[$u['id']]['internals'] = '';
            }
            $contacts[$u['id']]['internals'][] = $u['iabbr'];
            //$contacts[$u['id']]['internals'] .= $u['iabbr'] . ', ';
        }

        return $contacts;
    }

    public function save($data, $inputNamePrefix = '')
    {
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
    
        try
        {
            $edit = isset($data['id']) && $data['id'] != '';

            // informazioni personali
            $contactModel = new Model_Contacts();

            $cdata = array(
                'id' => $data[$inputNamePrefix . 'id'],
                'nome' => $data[$inputNamePrefix . 'nome'],
                'cognome' => $data[$inputNamePrefix . 'cognome'],
                'description' => $data[$inputNamePrefix . 'description'],
            );

            $contact_id = $contactModel->save($cdata);

            unset($contactModel);
            
            $dbHelper = new Maco_Db_Helper($db);
            
            // 1. rimuovo tutti i collegamenti
            $dbHelper->removeLinkNN('contacts_internals', 
                                    array('field' => 'id_contact', 
                                          'value' => $contact_id));

            // NOTA BENE: non usiamo il prefix
            // 2. inserisco le passate
            if(isset($data['internals']))
            {
                foreach($data['internals'] as $internal_id)
                {
                    $safe = (int) $internal_id;
                    $dbHelper->linkNN('contacts_internals', 
                                    array('field' => 'id_contact', 'value' => $contact_id), 
                                    array('field' => 'id_internal', 'value' => $safe));
                }
            }

            // indirizzi

            $addressModel = new Model_Addresses();
            $fields = array(
                'id', 'via', 'cap', 'localita', 'numero', 'provincia', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'addresses_', $data, $addressModel, 'addresses', 'addresses_contacts', 'id_address', 'id_contact', $contact_id);
            unset($addressModel);

            $telephonesModel = new Model_Telephones();
            $fields = array(
                'id', 'number', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'telephones_', $data, $telephonesModel, 'telephones', 'telephones_contacts', 'id_telephone', 'id_contact', $contact_id);
            unset($telephonesModel);

            $mailsModel = new Model_Mails();
            $fields = array(
                'id', 'mail', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'mails_', $data, $mailsModel, 'mails', 'mails_contacts', 'id_mail', 'id_contact', $contact_id);
            unset($mailsModel);

            $db->commit();

            return $contact_id;
        }
        catch (Exception $e)
        {
            $db->rollBack();

            return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }
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

        $select->from('contacts', array('nome', 'cognome', 'description', 'contact_id'))
            ->where('contacts.contaid = ?', $id);

        $data = $db->fetchRow($select);

        // extra data: indirizzi, numeri di telefono, mails
        $addressModel = new Model_AddressesMapper();
        
        $data['addresses'] = $addressModel->fetchByContactId($id);

        unset($addressModel);
        
        $telephonesModel = new Model_TelephonesMapper();
        
        $data['telephones'] = $telephonesModel->fetchByContactId($id);
        
        unset($telephonesModel);
        
        $mailsModel = new Model_MailsMapper();
        
        $data['mails'] = $mailsModel->fetchByContactId($id);
        
        unset($mailsModel);

        $select = $db->select();

        // internals
        // TODO: LE internals meritano un modello???
        $select->from('contacts_internals', array())
            ->joinLeft('internals', 'id_internal = internals.id', array('id', 'name', 'abbr'))
            ->where('id_contact = ?', $id);
        
        $data['internals'] = $db->fetchAssoc($select);
        
        /*
        if(empty($data['internals']))
        {
            $data['internals'] = $this->_data['internals'];
        }
        */

        return $data;
    }

    /**
     * Return a dummy contact data array
     *
     * @return array
     */
    public function getEmptyDetail()
    {
        $model = new Model_Contacts();
        $data = $model->getEmptyDetail();
        
         $data['internals'] = array(
            //array()
            //'id_internal' => ''
        );
        
        $model = new Model_AddressesMapper();
        $data['addresses'] = array($model->getEmptyDetail());
        
        $model = new Model_TelephonesMapper();
        $data['telephones'] = array($model->getEmptyDetail());
        
        $model = new Model_MailsMapper();
        $data['mails'] = array($model->getEmptyDetail());
        
        return $data;
    }

    /**
     * Delete a contact from the database with all dependent data
     *
     * @param int $id
     * @return bool
     */
    public function delete($ids)
    {
        
        $db = $this->_getDbAdapter();
        $db->beginTransaction();
        
        try
        {
            if(is_array($ids))
            {
                foreach($ids as $id)
                {
                    $this->_delete($id);
                }
            }
            else
            {
                $this->_delete($ids);
            }
            
            return $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            return false;
        }
    }
    
    /**
    * Internal delete
    * 
    * @param int $id
    */
    protected function _delete($id)
    {
        $db = $this->_getDbAdapter();
        $helper = new Maco_Db_Helper($db);
        
        $model = new Model_Contacts();
        
        $model->delete($id);
        
        $inModel = new Model_AddressesMapper();
        $deps = $inModel->fetchByContactId($id);
        foreach($deps as $dep)
        {
            $inModel->delete($dep['id']);
        }
        $helper->removeLinkNN('addresses_contacts', array('field' => 'id_contact', 'value' => $id));
        
        $inModel = new Model_TelephonesMapper();
        $deps = $inModel->fetchByContactId($id);
        foreach($deps as $dep)
        {
            $inModel->delete($dep['id']);
        }
        $helper->removeLinkNN('telephones_contacts', array('field' => 'id_contact', 'value' => $id));
        
        $inModel = new Model_MailsMapper();
        $deps = $inModel->fetchByContactId($id);
        foreach($deps as $dep)
        {
            $inModel->delete($dep['id']);
        }
        $helper->removeLinkNN('mails_contacts', array('field' => 'id_contact', 'value' => $id));
        
        $helper->removeLinkNN('contacts_internals', array('field' => 'id_contact', 'value' => $id));
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
