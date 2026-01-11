<?php
/**
 * Created by Marcello Stani.
 * User: marcello
 * Date: 6-set-2010
 * Time: 13.47.50
 * To change this template use File | Settings | File Templates.
 */
 
class Model_DbTables_Tasks extends Zend_Db_Table_Abstract
{
    /**
	 * Table name
	 */
	protected $_name = 'tasks';

	/**
	 * Primary key
	 */
	protected $_primary = 'task_id';

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
        if(!isset($data['done']))
        {
            $data['done'] = 0;
        }

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

		return parent::update($data, $where);
	}
}