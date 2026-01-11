<?php

class Model_Offers
{
    protected $_data = array(
        'id' => '',
    	'internal' => '',
        'id_company' => '',
        'id_service' => '',
        'id_subservice' => '',
        'luogo' => '',
        'id_partner' => '',
        'promotore_percent' => '',
        'date_offer' => '',
        'validita' => '',
        'scadenza' => '',
        'date_sent' => '',
        'date_accepted' => '',
        'subject' => '',
        'note' => '',
        'scadenze' => '',
        'id_company_contact' => '',
        'id_interest' => '',
        'sconto' => '',
        'id_pagamento' => '',
    	'id_rco' => '',
    	'segnalato_da' => ''
    );
    
    protected $_validators = array(
        'id' => array(
            'allowEmpty' => true,
        ),
        'internal' => array(
            'presence' => 'required',
        ),
        'id_company' => array(
            'presence' => 'required',
        ),
        'id_service' => array(
            'presence' => 'required',
        ),
        'id_subservice' => array(
            'presence' => 'required',
        ),
        'luogo' => array(
            'allowEmpty' => true,
        ),
        'id_partner' => array(
            'allowEmpty' => true,
        ),
        'promotore_percent' => array(
            'allowEmpty' => true,
        ),
        'date_offer' => array(
            'presence' => 'required',
        ),
        'validita' => array(
            'allowEmpty' => true,
        ),
        'scadenza' => array(
            'allowEmpty' => true,
        ),
        'subject' => array(
            'presence' => 'required',
        ),
        'note' => array(
            'allowEmpty' => true,
        ),
        'scadenze' => array(
            'allowEmpty' => true,
        ),
        'id_company_contact' => array(
            'allowEmpty' => true,
        ),
        'id_interest' => array(
            'allowEmpty' => true,
        ),
        'sconto' => array(
            'allowEmpty' => true,
        ),
        'id_pagamento' => array(
            'allowEmpty' => true,
        ),
        'id_rco' => array(
            'allowEmpty' => true,
        ),
        'segnalato_da' => array(
            'allowEmpty' => true,
        ),
    );
    
    protected $_filters = array(
        '*' => 'StringTrim',
    );
    
    public function getEmptyDetail()
    {
        $this->_data['year'] = date('Y');
        return $this->_data;
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
            $this->_table = new Model_DbTables_Offers(array('db' => 'dbAdapter'));
        }
        return $this->_table;
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

			$filter = new Zend_Filter_LocalizedToNormalized();
			
			$date_offer = $filter->filter($input->date_offer);
			$date_offer = $date_offer['year'] . '-' . $date_offer['month'] . '-' . $date_offer['day'];
			
			$scadenza = $filter->filter($input->scadenza);
			$scadenza = $scadenza['year'] . '-' . $scadenza['month'] . '-' . $scadenza['day'];
			
			$safeData = array(
				'internal' => $input->internal,
                'id_company' => $input->id_company,
                'id_service' => $input->id_service,
                'id_subservice' => $input->id_subservice,
                'luogo' => $input->luogo,
                'id_partner' => $input->id_partner,
                'promotore_percent' => $input->promotore_percent,
                'date_offer' => $date_offer,
                'validita' => $input->validita,
                'scadenza' => $scadenza,
                'subject' => $input->subject,
                'note' => $input->note,
                'scadenze' => $input->scadenze,
                'id_company_contact' => $input->id_company_contact,
                'id_interest' => $input->id_interest,
                'sconto' => $input->sconto,
                'id_pagamento' => $input->id_pagamento,
				'id_rco' => $input->id_rco,
				'segnalato_da' => $input->segnalato_da,
				'active' => 1
			);
			
			$new_revision = ($data['nr'] == 1);
			
			if(!$edit || $new_revision)
			{
				if(!$new_revision)
				{
					$db = $table->getDefaultAdapter();
					$id_offer = $db->fetchOne('select max(id_offer) from offers where internal = ' . $db->quote($safeData['internal']));
					$safeData['id_offer'] = $id_offer + 1;
				}
				else 
				{
					$id_offer = (int) $data['id_offer'];
					$db = $table->getDefaultAdapter();
					$revision = $db->fetchOne('select max(revision) from offers where id_offer = ' . $id_offer);
					
					$safeData['id_offer'] = $id_offer;
					$safeData['revision'] = ++$revision;
				}
				
				$id = $table->insert($safeData);
			}
			else
			{
				$table->update($safeData, array('id = ?' => $id));
			}
			/*
			$db = $table->getDefaultAdapter();
			$id_offer = $db->fetchOne('select id_offer from offers where id = ' . $db->quote($id));

			$db->update('offers', array('active' => 0), 'id_offer = '. $id_offer . ' and id <> ' . $db->quote($id));
			*/
			
			return $id;
    	
    }
    
    public function getDetail($id)
    {
    }
}
