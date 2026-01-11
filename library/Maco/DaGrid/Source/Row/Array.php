<?php

class Maco_DaGrid_Source_Row_Array implements Maco_DaGrid_Source_Row_Interface
{
	/**
	 * The row index
	 *
	 * @var int
	 */
	protected $_index;

	/**
	 * The row key
	 *
	 * @var mixed
	 */
	protected $_key;

	/**
	 * The row data
	 *
	 * @var array
	 */
	protected $_row = array();

	/**
	 * Load data from array
	 *
	 * @param array $array
	 */
	public function __construct(&$index, &$key, &$row)
	{
		$this->_index = $index;
		$this->_key = $key;
		$this->_row = $row;
	}

	/**
	 * Return the row values
	 *
	 * @return array
	 */
	public function getCells()
	{
		return $this->_row;
	}

	/**
	 * Returns the index of this row
	 *
	 * @return int
	 */
	public function getIndex()
	{
		return $this->_index;
	}

	/**
	 * Returns the key of this row
	 *
	 * @return mixed
	 */
	public function getKey()
	{
		return $this->_key;
	}

	/**
	 * Return the row values
	 *
	 * @return array
	 */
	public function getCellByFieldName($field, $default = NULL, $strict = false)
	{
		if(!isset($this->_row[$field]))
		{
			if($strict)
			{
				throw new Exception('no value set in this row for the field: ' . $field);
			}
			else
			{
				return $default;
			}
		}

		return $this->_row[$field];
	}
}
