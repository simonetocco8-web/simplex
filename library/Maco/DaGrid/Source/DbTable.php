<?php

class Maco_DaGrid_Source_DbTable implements Maco_DaGrid_Source_Interface
{
	/**
	 * Array data
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_tableName;

	/**
	 * Fields to load
	 *
	 * @var array
	 */
	protected $_fields;

	/**
	 * Paginator obj
	 *
	 * @var Zend_Pagination
	 */
	protected $_paginator;

	/**
	 * Costruttore
	 *
	 * @param string $tableName
	 * @param array $fields
	 * @return Maco_DaGrid_Source_DbTable
	 */
	public function __construct($tableName, $fields = null)
	{
		$this->_tableName = $tableName;

		if($fields !== NULL)
		{
			$this->_fields = $fields;
		}
	}

	public function getRows()
	{
		if($this->_data === NULL)
		{
			$this->load();
		}

		$ret = array();
		$index = 0;
		foreach($this->_data as $key => $row)
		{
			$ret[] = new Maco_DaGrid_Source_Row_Array(++$index, $key, $row);
		}
		return $ret;
	}

	public function getCount()
	{
		if($this->_data === NULL)
		{
			$this->load();
		}

		return count($this->_data);
	}

	protected function load()
	{
		$db = Zend_Registry::get('dbAdapter');
		// TODO: il db da dove lo prendo?

		$select = $db->select();

		$fields = ($this->_fields === NULL) ? '*' : $this->_fields;

		$select->from($this->_tableName, $fields);

		if($this->_paginator !== NULL)
		{
			$zpaginator = Zend_Paginator::factory($select);

			$zpaginator->setItemCountPerPage($this->_paginator->getPerPage());
			$zpaginator->setCurrentPageNumber($this->_paginator->getCurrentPage());

			$this->_data = $zpaginator;
			return;
		}


		$this->_data = $db->fetchAll($select);
	}

	public function getColumns()
	{
		if($this->_data === NULL)
		{
			$this->load();
		}

		$columns = array();

		foreach($this->_data as $row)
		{
			foreach($row as $k => $v)
			{
				$columns[] = array('field' => $k);
			}
			break;
		}

		$this->_grid->addColumns($columns);
	}

	public function setGrid(Maco_DaGrid_Grid &$grid)
	{
		$this->_grid = $grid;
	}

	public function setPaginator(&$paginator)
	{
		$this->_paginator = $paginator;
	}
}
