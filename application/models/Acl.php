<?php

class Model_Acl
{
	/**
	 * Db adapter
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_dbAdapter;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$db = Zend_Registry::get('dbAdapter');

		if(!$db)
		{
			throw new Exception('Unable to load db adapter from registry');
		}

		$this->_dbAdapter = $db;
	}

	/**
	 * Get all roles
	 */
	public function getRolesWithParents()
	{
		$select = $this->_dbAdapter->select();

		$select->from('roles', array('role_id', 'name'));

        return $this->_dbAdapter->fetchPairs($select);

		$dirtyRoles = $this->_dbAdapter->fetchAll($select);

		$roles = array();

		// TODO: Pu� essere ottimizzata
		foreach($dirtyRoles as $role)
		{
			if(!isset($roles[$role['id']]))
			{
				$roles[$role['id']] = array(
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'image' => $role['image'],
                    'parents' => array()
				);
			}

			if(isset($role['parent_id']))
			{
				$roles[$role['id']]['parents'][$role['parent_id']] = $role['parent_name'];
			}
		}

		return $roles;
	}
}
