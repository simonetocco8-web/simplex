<?php
  
class Model_CompaniesMapper
{
    /**
     * Return a dummy contact data array
     *
     * @return array
     */
    public function getEmptyDetail()
    {
        $model = new Model_Companies();
        $data = $model->getEmptyDetail();
        
         $data['internals'] = array(
        );
        
        $model = new Model_WebsitesMapper();
        $data['websites'] = array($model->getEmptyDetail());
        
        $model = new Model_AddressesMapper();
        $data['addresses'] = array($model->getEmptyDetail());
        
        $model = new Model_TelephonesMapper();
        $data['telephones'] = array($model->getEmptyDetail());
        
        $model = new Model_MailsMapper();
        $data['mails'] = array($model->getEmptyDetail());
        
        
        
        return $data;
    }
    
    public function hasOrders($id)
    {
		$db = $this->_getDbAdapter();

		$select = $db->select();
		
		$select->from('orders', new Zend_Db_Expr('count(orders.id)'))
			->joinLeft('offers', 'orders.id_offer = offers.id', array())
			->where('offers.id_company = ?', $id);
			
		$count = $db->fetchOne($select);
		
		return $count > 0;
    }
    
    public function getAddressesByCompanyId($id)
    {
    	$db = $this->_getDbAdapter();
    	
    	$select = $db->select();
    	
    	$select->from('addresses', array('address_id', 'via', 'localita', 'numero', 'provincia'))
    		->where('id_company = ?', $id);

    	return $db->fetchAll($select);
    }
    
    public function getDetail($id)
    {
    	$db = $this->_getDbAdapter();
    	
    	$select = $db->select();
    	
    	$select->from('companies', array(
    			'ragione_sociale',
    			'cf',
    			'partita_iva',
    			'iban',
    			'date_last_contact',
    			'segnalato_da',
    			'conosciuto_come',
    			'categoria',
    			'ea',
    			'rco',
    			'organico_medio',
    			'fatturato',
    			'conosciuto_come',
    			'id',
                'is_partner',
                'promotore_percent',
    		))
    		->joinLeft('status', 'status.id = companies.status', array('status' => 'status.name'))
    		->joinLeft('users', 'users.id = companies.rco', array('users.username'))
    		->joinLeft('contacts', 'contacts.id = companies.segnalato_da', array('nome', 'cognome'))
    		->joinLeft('categories', 'categories.id = companies.categoria', array('nome_categoria' => 'categories.name'))
    		->joinLeft('ea', 'ea.id = companies.ea', array('nome_ea' => 'name'))
    		->joinLeft('organici_medi', 'organici_medi.id = companies.organico_medio', array('nome_organico_medio' => 'organici_medi.name'))
    		->joinLeft('fatturati', 'fatturati.id = companies.fatturato', array('nome_fatturato' => 'fatturati.name'))
    		->joinLeft('conosciuto_come', 'conosciuto_come.id = companies.conosciuto_come', array('nome_conosciuto_come' => 'conosciuto_come.name'))
            ->where('companies.id = ?', $id);
    		
		$data = $db->fetchRow($select);
		
		        // extra data: indirizzi, numeri di telefono, mails
        $addressModel = new Model_AddressesMapper();
        
        $data['addresses'] = $addressModel->fetchByCompanyId($id);

        unset($addressModel);
        
        $telephonesModel = new Model_TelephonesMapper();
        
        $data['telephones'] = $telephonesModel->fetchByCompanyId($id);
        
        unset($telephonesModel);
        
        $mailsModel = new Model_MailsMapper();
        
        $data['mails'] = $mailsModel->fetchByCompanyId($id);
        
        unset($mailsModel);
        
        $websitesModel = new Model_WebsitesMapper();
        
        $data['websites'] = $websitesModel->fetchByCompanyId($id);
        
        unset($websitesModel);

        unset($select);
        $select = $db->select();
        // TODO: LE internals meritano un modello???
        $select->from('companies_internals', array())
            ->joinLeft('internals', 'id_internal = internals.id', array('id', 'name', 'abbr'))
            ->where('id_company = ?', $id);
        
        $data['internals'] = $db->fetchAssoc($select);
        
        
        return $data;
    }
    
    public function save($data, $inputNamePrefix = '')
    {
    	$db = $this->_getDbAdapter();
        $db->beginTransaction();
    
        try
        {
            $edit = isset($data['id']) && $data['id'] != '';

            // informazioni personali
            $companyModel = new Model_Companies();

            $cdata = array(
                'id' => $data[$inputNamePrefix . 'id'],
                'ragione_sociale' => $data[$inputNamePrefix . 'ragione_sociale'],
                'cf' => $data[$inputNamePrefix . 'cf'],
                'partita_iva' => $data[$inputNamePrefix . 'partita_iva'],
            	'iban' => $data[$inputNamePrefix . 'iban'],
            	'status' => $data[$inputNamePrefix . 'status'],
            	'rco' => $data[$inputNamePrefix . 'rco'],
            	'segnalato_da' => $data[$inputNamePrefix . 'segnalato_da'],
            	'categoria' => $data[$inputNamePrefix . 'categoria'],
            	'ea' => $data[$inputNamePrefix . 'ea'],
            	'organico_medio' => $data[$inputNamePrefix . 'organico_medio'],
            	'fatturato' => $data[$inputNamePrefix . 'fatturato'],
            	'conosciuto_come' => $data[$inputNamePrefix . 'conosciuto_come'],
                'is_partner' => $data[$inputNamePrefix . 'is_partner'],
                'promotore_percent' => $data[$inputNamePrefix . 'promotore_percent'],
            );

            $company_id = $companyModel->save($cdata);

            unset($companyModel);
            
            $dbHelper = new Maco_Db_Helper($db);
            
            // 1. rimuovo tutti i collegamenti
            $dbHelper->removeLinkNN('companies_internals', 
                                    array('field' => 'id_company', 
                                          'value' => $company_id));

            // NOTA BENE: non usiamo il prefix
            // 2. inserisco le passate
            if(isset($data['internals']))
            {
                foreach($data['internals'] as $internal_id)
                {
                    $safe = (int) $internal_id;
                    $dbHelper->linkNN('companies_internals', 
                                    array('field' => 'id_company', 'value' => $company_id), 
                                    array('field' => 'id_internal', 'value' => $safe));
                }
            }

            // indirizzi

            $addressModel = new Model_Addresses();
            $fields = array(
                'id', 'via', 'cap', 'localita', 'numero', 'provincia', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'addresses_', $data, $addressModel, 'addresses', 'addresses_companies', 'id_address', 'id_company', $company_id);
            unset($addressModel);

            $telephonesModel = new Model_Telephones();
            $fields = array(
                'id', 'number', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'telephones_', $data, $telephonesModel, 'telephones', 'telephones_companies', 'id_telephone', 'id_company', $company_id);
            unset($telephonesModel);

            $mailsModel = new Model_Mails();
            $fields = array(
                'id', 'mail', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'mails_', $data, $mailsModel, 'mails', 'mails_companies', 'id_mail', 'id_company', $company_id);
            unset($mailsModel);

            $websiteModel = new Model_Websites();
            $fields = array(
                'id', 'url', 'description'
            );
            $dbHelper->saveDependencies($fields, $inputNamePrefix . 'websites_', $data, $websiteModel, 'websites', 'websites_companies', 'id_website', 'id_company', $company_id);
            unset($websiteModel);
                        
            $db->commit();

            return $company_id;
        }
        catch (Exception $e)
        {
            $db->rollBack();

            return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
        }    	
    }
    
    public function setStatus($id_company, $id_status)
    {
    	$db = $this->_getDbAdapter();
    	
    	return $db->update('companies', array('status' => $id_status), 'id = ' . $db->quote($id_company));
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
        throw new Exception('Metodo da implementare');
        
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
    
    public function saveContact($data)
    {
        $db = $this->_getDbAdapter();
        
        $db->beginTransaction();
        
        try
        {
        
            $id = $data['id'];
            $contactModel = new Model_ContactsMapper();
            
            $id_contact = $contactModel->save($data, 'contacts_');
            
            $db->insert('contacts_companies', array('id_contact' => $id_contact, 'id_company' => $id));
            
            $db->commit();
        }
        catch(Exception $e)
        {
            $db->rollBack();
            return false;
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
