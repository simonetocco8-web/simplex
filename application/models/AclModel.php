<?php

class Model_AclModel extends Zend_Acl
{
	/**
	 * Logged user role
	 *
	 * @var string
	 */
	protected $_userRole;

	/**
	 * Costruttore
	 *
	 * @param Zend_Db_Adapter_Abstract $db
	 * @param mixed $userId
	 * @return Auth_Model_AclModel
	 */
	public function __construct(Zend_Db_Adapter_Abstract $db, $userId)
	{
		if(!$db)
		{
			throw new Exception('Impossible to load db from ACL');
		}
		else
		{
			// 1. deny all
			$this->deny();

			// 2. carico il ruolo per questo utente -> se non c'� GUEST (ma ci deve essere)
			$select = $db->select();

			$select->from('acl_users_roles', array())
			    ->where('id_user = ?', $db->quote($userId))
			    ->joinLeft(array('acr' => 'acl_roles'), 'acr.id = id_role', array('id', 'name'));

			$roleRow = $db->fetchRow($select);
			unset($select);
			$this->_userRole = $roleRow['name'];
			$this->addRole($this->_userRole);

			// 4. carico le risorse e i privilegi relativi a questo utente e setto l'acl (WHITELIST)
			$select = $db->select();

			$select->from('acl_rules', array('id_action', 'value'))
			    ->joinLeft(array('aca' => 'acl_actions'), 'aca.id = id_action', array('action_name' => 'action', 'action_controller' => 'controller', 'id_resource'))
			    ->joinLeft(array('acr' => 'modules'), 'acr.id = aca.id_resource', array('resource_name' => 'name'))
			    ->where('id_role = ?', $roleRow['id']);

			$rules = $db->fetchAll($select);

			unset($select);

			$resource_actions = array();
			foreach($rules as $rule)
			{
				$resource_name = $rule['resource_name'] . '_' . $rule['action_controller'];
				if(!$this->has($resource_name))
				{
					$this->addResource($resource_name);
				}

				if($rule['value'] == 'Y')
				{
					$this->allow($roleRow['name'], $resource_name, $rule['action_name']);
				}
				else
				{
					//deny
				}
			}
		}
	}

	/**
	 * Returns the user role
	 *
	 * @returns string
	 */
	public function getLoggedUserRole()
	{
		return $this->_userRole;
	}
}
