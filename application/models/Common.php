<?php

class Model_Common
{
	/**
	 * Skip fields for common single tables
	 *
	 * @var array
	 */
	protected $_skipFields = array(
        'created_by',
        'date_created',
        'modified_by',
        'date_modified',
        'deleted',
	);

	/**
	 * Returns an array with id - value pairs from the given table
	 *
	 * @param string $table
	 * @param string $idField
	 * @param string $valueField
	 * @param string $where
	 * @return array
	 */
	public function getArrayForSelectElementSimple($table, $idField, $valueField = null, $where = null)
	{
		$db = $this->_getDbAdapter();

		$select = $db->select();

		$fields = array($idField);

		if(isset($valueField))
		{
			if(is_array($valueField))
			{
				$fields[] = new Zend_Db_Expr('concat_ws(\', \', ' . implode(', ', $valueField) . ')');
			}
			else
			{
				$fields[] = $valueField;
			}
		}

		$select->from($table, $fields);

		if(isset($where) && $where != '')
		{
			$select->where($where);
		}

		if(is_array($valueField))
		{
			foreach ($valueField as $vf)
			{
				$select->order($vf . ' asc');
			}
		}
		else
		{
			$select->order($valueField . ' asc');
		}

        if($table == 'subservices' || $table == 'services')
        {
            $user = Zend_Auth::getInstance()->getIdentity();
            $user_internal = $user->internal_id;
            if($table == 'subservices')
            {
                $select->join('subservice_internal', 'subservice_id = id_subservice and id_internal = ' . $db->quote($user_internal));
            }
            elseif($table == 'services')
            {
                $select->join('service_internal', 'service_id = id_service and id_internal = ' . $db->quote($user_internal));
            }
        }

        return (isset($valueField)) ? $db->fetchPairs($select) : $db->fetchCol($select);
	}

	public function fetchAllSingleTableDefault($table)
	{
		$db = $this->_getDbAdapter();

		$table = new Zend_Db_Table($table);
		$fields = $table->info(Zend_Db_Table_Abstract::METADATA);

		foreach($fields as $k => $f)
		{
			if(in_array($f, $this->_skipFields))
			{
				unset($fields[$k]);
			}
		}

		return $table->fetchAll()->toArray();

	}
	
	public function getValueFromId($table, $field, $id, $pk = 'id')
	{
		$db = $this->_getDbAdapter();
		$select = $db->select();
		$select->from($table, $field)
			->where($pk . ' = ?', $id);
		
		return $db->fetchOne($select);
	}

    public function getAddress($search)
    {
        $db = $this->_getDbAdapter();
		$select = $db->select();
        $select->from('comuni', array('localita' => 'nome', 'cap'))
                ->joinLeft('province', 'provincia_id = id_provincia', array('provincia' => 'province.nome'))
                ->limit(10, 0);

        $searchvals = explode(' ', $search);

        foreach($searchvals as $v)
        {
            $rv = trim($v);
            //$select->where('cap like \'%' . $rv . '%\' or comuni.nome like \'%' . $rv . '%\' or province.nome like \'%' . $rv . '%\'');
            // dall'inizio
            $select->where('cap like \'' . $rv . '%\' or comuni.nome like \'' . $rv . '%\' or province.nome like \'' . $rv . '%\'');
        }

        return $db->fetchAll($select);
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
