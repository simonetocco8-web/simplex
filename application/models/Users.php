<?php

class Model_Users
{
	protected $_data = array(
        'id' => '',
        'username' => '',
        'password' => '',
        'cpassword' => '',
	);

	protected $_validators = array(
        'id' => array(
            'allowEmpty' => true
	    ),
        'username' => array(
            'presence' => 'required',
	        array('StringLength', 4),
	    ),
        'password' => array(
            'Alnum',    
	        array('StringLength', 6),
	    ),
        'cpassword' => array(
            'Alnum',
	        array('StringLength', 6),
	    ),
	);

	protected $_filters = array(
        '*' => 'StringTrim',
	);

	public function __construct()
	{
       
	}
	
	public function delete($id)
	{
		$table = $this->_getTable();

		$table->delete(array('id = ?' => $id));
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

		if(isset($input->password))
		{
			if(!isset($input->cpassword))
			{
				return array('pw_error' => 'Manca il campo conferma password');
			}
			if($input->password != $input->cpassword)
			{
				return array('pw_error' => 'La password non � confermata');
			}
		}

		$table = $this->_getTable();

		// Inserisco le informazioni relative all'account
		$safeData = array(
            'username' => $input->username
		);

		$id = $input->id;

		$edit = ! empty($id);

		if(!$edit)
		{
			$safeData['password'] = $input->password;
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
			$this->_table = new Model_DbTables_Users(array('db' => 'dbAdapter'));
		}
		return $this->_table;
	}
}
