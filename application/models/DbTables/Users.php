<?php
/**
 * This is the DbTable class for the Utenti table.
 */
class Model_DbTables_Users extends Zend_Db_Table_Abstract
{
	/**
	 * Table name
	 */
	protected $_name = 'users';

	/**
	 * Primary key
	 */
	protected $_primary = 'user_id';

	/**
	 * Dependencies
	 */


	/**
	 * Insert new row
	 *
	 * Ensure that a timestamp is set for the created field.
	 *
	 * @param array $data
	 * @return int
	 */
	public function insert(array $data)
	{
		$data['date_created'] = new Zend_Db_Expr('now()');
		$aut = Zend_Auth::getInstance()->getIdentity();
		$data['created_by'] = $aut->user_id;
		$data['active'] = 1;
		$data['deleted'] = 0;
		$data['password_salt'] = '1324576809';
		$data['password'] = md5($data['password'] . $data['password_salt']);

		return parent::insert($data);
	}

	/**
	 * Update a row
	 *
	 * Ensure that a timestamp is set for the created field.
	 *
	 * @param array $data
	 * @return int
	 */
	public function update(array $data, $where)
	{
		$data['date_modified'] = new Zend_Db_Expr('now()');
		$aut = Zend_Auth::getInstance()->getIdentity();
		$data['modified_by'] = $aut->user_id;
        unset($data['password_salt']);
        if($data['password'] == '')
            unset($data['password']);

		return parent::update($data, $where);
	}
}