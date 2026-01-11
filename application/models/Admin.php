<?php

	// TODO: DELETE THIS FILE

class Model_Admin
{
	protected $_categories = array(
        'singolare' => 'Categoria',
        'plurale' => 'Categorie',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
	),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
	)
	)
	);

	protected $_fatturati = array(
        'singolare' => 'Fatturato',
        'plurale' => 'Fatturati',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
	),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
	)
	)
	);

	protected $_organici_medi = array(
        'singolare' => 'Organico Medio',
        'plurale' => 'Organici Medi',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
	),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
	)
	)
	);

	protected $_status = array(
        'singolare' => 'Stato',
        'plurale' => 'Stati',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
	),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
	)
	)
	);
    
    protected $_ea = array(
        'singolare' => 'EA',
        'plurale' => 'EA',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );
    
    protected $_conosciuto_come = array(
        'singolare' => 'Come',
        'plurale' => 'Come',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_interests = array(
        'singolare' => 'Programma di Interesse',
        'plurale' => 'Programmi di Interesse',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );
    
	protected $_internals = array(
        'singolare' => 'Azienda Interna',
        'plurale' => 'Aziende Interne',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
	),
            'abbr' => array(
                'label' => 'Abbr',
                'class' => 'input-medium required',
                'required' => true
	),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
	)
	)
	);
    
    protected $_services = array(
        'singolare' => 'Servizio',
        'plurale' => 'Servizi',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );
    
    protected $_subservices = array(
        'singolare' => 'Sotto-Servizio',
        'plurale' => 'Sotto-Servizi',
        'fields' => array(
            'id_service' => array(
                'label' => 'Servizio',
                'class' => 'input-medium required',
                'required' => true,
                'child' => 'services'
            ),
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );
    
    protected $_interests_levels = array(
        'singolare' => 'Livello di Interesse',
        'plurale' => 'Livelli di Interesse',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );
    
    protected $_pagamenti = array(
        'singolare' => 'Metodo di Pagamento',
        'plurale' => 'Metodi di Pagamento',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );
    
    protected $_offer_status = array(
        'singolare' => 'Stato Offerta',
        'plurale' => 'Stati Offerta',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    protected $_order_status = array(
        'singolare' => 'Stato Commessa',
        'plurale' => 'Stati Commessa',
        'fields' => array(
            'name' => array(
                'label' => 'Nome',
                'class' => 'input-medium required',
                'required' => true
            ),
            'description' => array(
                'label' => 'Descrizione',
                'class' => 'input-big',
                'required' => false
            )
        )
    );

    
	/**
	 * Returns the db adapter
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	protected function _getDbAdapter()
	{
		return Zend_Registry::get('dbAdapter');
	}

	public function getFieldsForTable($table)
	{
		$var = $this->_checkExists($table);
		$arr = $this->$var;
		return $arr['fields'];
	}

	public function getLabelsForTable($table)
	{
		$var = $this->_checkExists($table);
		$arr = $this->$var;
		return array('singolare' => $arr['singolare'], 'plurale' => $arr['plurale']);
	}

	public function getTableInfo($table)
	{
		$var = $this->_checkExists($table);
		return $this->$var;
	}

	protected function _checkExists($table)
	{
		$var = '_' . strtolower($table);
		if(!isset($this->$var))
		{
			throw new Exception('Table not found: ' . $table);
		}

		return $var;
	}

	public function getEmptyDetail($table)
	{
		$fields = $this->getFieldsForTable($table);
		$ret = array();
		$ret['id'] = '';
		foreach($fields as $k => $v)
		{
			$ret[$k] = '';
		}
		return $ret;
	}

	public function getDetail($table, $id)
	{
		$db = $this->_getDbAdapter();
		$select = $db->select();

		$select->from($table, array('*'))
		->where('id = ?', $id);

		$data = $db->fetchRow($select);

		return $data;
	}

	public function save($table, $data)
	{
		$filters = array(
            '*' => 'StringTrim',
		);

		// TODO : This validators could be splitted per each data model

		// TODO: Manca il controllo dell'uguaglianza delle 2 password

		$validators = array(
            'id' => array(
                'allowEmpty' => true
		),
            'name' => array(
                'presence' => 'required',
		),
            'description' => array(
                'allowEmpty' => true
		)
		);

		$validators = array(
            'id' => array(
                'allowEmpty' => true
		),
		);
		$fields = $this->getFieldsForTable($table);
		foreach($fields as $field => $info)
		{
			$validators[$field] = ($info['required'])
			? array('presence' => 'required')
			: array('allowEmpty' => true);
		}

		$input = new Zend_Filter_Input($filters, $validators);

		$input->setData($data);

		if($input->hasInvalid() || $input->hasMissing())
		{
			return $input->getMessages();
		}

		$db = $this->_getDbAdapter();

		$db->beginTransaction();
		try
		{
			$id = $input->id;
			$edit = ! empty($id);

			$safeData = array();

			foreach($fields as $field => $info)
			{
				$safeData[$field] = $input->$field;
			}

			$aut = Zend_Auth::getInstance()->getIdentity();

			if(!$edit)
			{
				$safeData['date_created'] = new Zend_Db_Expr('now()');
				$safeData['created_by'] = $aut->id;

				$db->insert($table, $safeData);
				$id = $db->lastInsertId();
			}
			else
			{
				$safeData['date_modified'] = new Zend_Db_Expr('now()');
				$safeData['modified_by'] = $aut->id;

				$db->update($table, $safeData, array('id = ?' => $id));
			}

			$db->commit();
			return $id;
		}
		catch (Exception $e)
		{
			$db->rollBack();
			return array('database_error' => $e->getFile() . ' - ' . $e->getLine() . ' - ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
		}
	}

	public function delete($table, $id)
	{
		$db = $this->_getDbAdapter();
		$db->beginTransaction();
		$safeId = $db->quote($id);

		try
		{
			// TODO: CONTROLLARE CHE NON CI SIANO ENTITA COLLEGATE
			$db->delete($table, 'id = ' . $safeId);
			return $db->commit();
		}
		catch (Exception $e)
		{
			$db->rollBack();
			return array('database_error' => $e->getMessage());
		}
	}
    
    public function getInternalsWithOffices()
    {
        $db = $this->_getDbAdapter();
        $select = $db->select();
        
        $select->from('internals', array('internal_id', 'internal_name' => 'internals.full_name', 'internal_abbr' => 'internals.abbr'))
            ->order('internals.abbr ASC');
        $ints = $db->fetchAll($select);
        
        $internals = array();
        
        foreach($ints as $k => $int)
        {
            $internals[$int['internal_id']] = $int;
            $internals[$int['internal_id']]['offices'] = array();
        }
        
        unset($ints, $select);
        $select = $db->select();
        
        $select->from('offices', array('office_id', 'office_name' => 'offices.name', 'id_internal'))
            ->order('offices.name ASC');
        
        $offices = $db->fetchAll($select);
        
        foreach($offices as $office)
        {
            $internals[$office['id_internal']]['offices'][$office['office_id']] = array(
                'office_id' => $office['office_id'],
                'office_name' => $office['office_name']
            );
        }
         
        return $internals;
    }
}