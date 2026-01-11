<?php

class Model_Contacts
{
	protected $_data = array(
        'id' => '',
        'nome' => '',
        'cognome' => '',
        'description' => ''
	);

	protected $_validators = array(
        'id' => array(
            'allowEmpty' => true
	    ),
        'nome' => array(
	        array('Alnum', true),
            'allowEmpty' => true
	    ),
        'cognome' => array(
	        array('Alnum', true),
            'presence' => 'required',
	    ),
        'description' => array(
            'allowEmpty' => true
        ),
	);

	protected $_filters = array(
        '*' => 'StringTrim',
	);
    
    protected $_table;

	public function __construct()
	{

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
            'nome' => $input->nome,
            'cognome' => $input->cognome,
            'description' => $input->description
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
	 * @return Model_DbTables_Contacts
	 */
	protected function _getTable()
	{
		if (null === $this->_table)
		{
			// since the dbTable is not a library item but an application item,
			// we must require it to use it
			$this->_table = new Model_DbTables_Contacts(array('db' => 'dbAdapter'));
		}
		return $this->_table;
	}
    
    /**
    * Delete a contact
    * 
    * @param int $id
    */
    public function delete($id)
    {
        $table = $this->_getTable();
        
        $table->delete(array('id = ?' => $id));
    }
}
