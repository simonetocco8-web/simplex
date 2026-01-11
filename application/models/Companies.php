<?php

class Model_Companies
{
	protected $_data = array(
        'id' => '',
	//'deleted' => '',
        'ragione_sociale' => '',
        'cf' => '',
        'partita_iva' => '',
        'iban' => '',
        'status' => '',
        'segnalato_da' => '',
        'rco' => '',
        'categoria' => '',
        'ea' => '',
        'organico_medio' => '',
        'fatturato' => '',
        'conosciuto_come' => '',
        'is_partner' => '',
        'promotore_percent' => '',
        /*
        'websites' => array(
                array (
                'id_website' => '',
                'description' => '',
                'name' => '',
                'url' => ''
                )
                ),
        *//*
        'partners' => array(
            array(
                'id_partner' => '',
            )
        )*/
    );
    
    protected $_validators = array(
        'id' => array(
            'allowEmpty' => true
	    ),
        'ragione_sociale' => array(
            'presence' => 'required'
	    ),
        'cf' => array(
	        array('Alnum', true),
            'allowEmpty' => true
	    ),
	    'partita_iva' => array(
	        array('Alnum', true),
            'allowEmpty' => true
	    ),
	    'status' => array(
            'allowEmpty' => true
	    ),
	    'segnalato_da' => array(
            'allowEmpty' => true
	    ),
        'rco' => array(
            'allowEmpty' => true
	    ),
	    'categoria' => array(
            'allowEmpty' => true
	    ),
	    'ea' => array(
            'allowEmpty' => true
	    ),
        'organico_medio' => array(
            'allowEmpty' => true
	    ),
        'fatturato' => array(
            'allowEmpty' => true
	    ),
        'conosciuto_come' => array(
            'allowEmpty' => true
	    ),
        'is_partner' => array(
            'allowEmpty' => true
        ),
        'promotore_percent' => array(
            'allowEmpty' => true
        ),
	);

	protected $_filters = array(
        '*' => 'StringTrim',
	);
    

                public function __construct()
                {

                }

                public function getCompanies($sort = null, $dir = 'ASC', $search = array())
                {
                	$db = $this->_getDbAdapter();

                	$select = $db->select();

                	$select->from('companies', array('id', 'ragionesociale' => 'ragione_sociale', 'ispartner' => 'is_partner', 'partita_iva'))
                		->joinLeft('categories', 'categories.id=categoria', array('cname' => 'name', 'cdescription' => 'description'))
                		->joinLeft('status', 'status.id=status', array('stato' => 'name'));

                	if($sort)
                	{ 
                		$select->order($sort . ' ' . $dir);
                	}
                	else
                	{
                		$select->order('ragione_sociale ASC');
                	}
/*
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
  */
                    if(isset($search['ragionesociale']))
                    {
                        $select->where('ragione_sociale like ' . $db->quote('%' . $search['ragionesociale'] . '%'));
                    }
                    if(isset($search['cname']))
                    {
                        $select->where('categories.name like ' . $db->quote('%' . $search['cname'] . '%'));
                    }
                    if(isset($search['stato']))
                    {
                        $select->where('status.name like ' . $db->quote('%' . $search['stato'] . '%'));
                    }
                    if(isset($search['ispartner']))
                    {
                        $select->where('is_partner like ' . $db->quote('%' . $search['ispartner'] . '%'));
                    }
                    
                	$values = $db->fetchAll($select);
                    
                	return $values;
                }
                

		public function save($data)
        {
	       	// TODO : This validators could be splitted per each data model
			// TODO: Manca il controllo dell'uguaglianza delle 2 password

			$input = new Zend_Filter_Input($this->_filters, $this->_validators);

			$input->setData($data);

			if($input->hasInvalid() || $input->hasMissing())
			{
				return $input->getMessages();
			}
			$table = $this->_getTable();

			$id = $input->id;

			$edit = ! empty($id);

			$safeData = array(
            	'ragione_sociale' => $input->ragione_sociale,
            	'cf' => $input->cf,
            	'partita_iva' => $input->partita_iva,
				'iban' => $input->iban,
            	'status' => $input->status,
            	'rco' => $input->rco,
				'segnalato_da' => $input->segnalato_da,
            	'categoria' => $input->categoria,
            	'ea' => $input->ea,
				'organico_medio' => $input->organico_medio,
            	'fatturato' => $input->fatturato,
                'conosciuto_come' => $input->conosciuto_come,
                'is_partner' => $input->is_partner,
            	'promotore_percent' => $input->promotore_percent,
			);

			if(!$edit)
			{
				$id = $table->insert($safeData);
			}
			else
			{
				$table->update($safeData, array('id = ?' => $id));
			}

			return $id;
        }

                /**
                 * Return a dummy user data array
                 *
                 * @return array
                 */
                public function getEmptyDetail()
                {
                	return $this->_data;
                }

                /**
                 * Delete a user from the database with all dependent data
                 *
                 * @param int $id
                 * @return bool
                 */
                public function deleteUser($id)
                {
                	$db = $this->getDbAdapter();
                	$db->beginTransaction();
                	$safeId = $db->quote($id);

                	try
                	{
                		// account data
                		$accountsModel = new Model_DbTables_UserAccounts();

                		$accountsModel->delete('id = ' . $safeId);
                		$db->delete('acl_users_roles', 'id_user = ' . $safeId);

                		// contact data
                		$contact_id = $db->fetchOne('select id_contact from contacts_users where id_user = ' . $safeId);

                		if($contact_id != null)
                		{
                			// esiste il contact nel db
                			$contactModel = new Model_DbTables_Contacts();

                			$contactModel->delete('id = ' . $contact_id);
                			$db->delete('contacts_users', 'id_user = ' . $safeId);

                			// addresses data
                			$addresses = $db->fetchCol('select id_address from addresses_contacts where id_contact = ' . $contact_id);
                			$db->delete('addresses_contacts', 'id_contact = ' . $contact_id);
                			$addressModel = new Model_DbTables_Addresses();
                			foreach($addresses as $address)
                			{
                    $addressModel->delete('id = ' . $address);
                			}

                			// telephone numbers data
                			$numbers = $db->fetchCol('select id_telephone from telephones_contacts where id_contact = ' . $contact_id);
                			$db->delete('telephones_contacts', 'id_contact = ' . $contact_id);
                			$numbersModel = new Model_DbTables_Telephones();
                			foreach($numbers as $number)
                			{
                    $numbersModel->delete('id = ' . $number);
                			}

                			// mail data
                			$mails = $db->fetchCol('select id_mail from mails_contacts where id_contact = ' . $contact_id);
                			$db->delete('mails_contacts', 'id_contact = ' . $contact_id);
                			$mailsModel = new Model_DbTables_Mails();
                			foreach($mails as $mail)
                			{
                    $mailsModel->delete('id = ' . $mail);
                			}
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
                
    /**
	 * Returns the table db adapter
	 *
	 * @return Model_DbTables_Companies
	 */
	protected function _getTable()
	{
		if (null === $this->_table)
		{
			// since the dbTable is not a library item but an application item,
			// we must require it to use it
			$this->_table = new Model_DbTables_Companies(array('db' => 'dbAdapter'));
		}
		return $this->_table;
	}
}
