<?php

class Maco_DaGrid_Source_Array implements Maco_DaGrid_Source_Interface
{
	/**
	 * Array data
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * The grid object
	 *
	 * @var Maco_DaGrid_Grid
	 */
	protected $_grid;

	/**
	 * Load data from array
	 *
	 * @param array $array
	 */
	public function pushData(&$array)
	{
		if(!is_array($array) && !($array instanceof Zend_Paginator))
		{
			throw new Exception('Source must be of array type');
		}

		$this->_data = $array;
	}

	public function getRows()
	{
		$ret = array();
		$index = 0;
		foreach($this->_data as $key => $row)
		
		{
		    $index++;
			$ret[] = new Maco_DaGrid_Source_Row_Array($index, $key, $row);
		}
		return $ret;
	}

	public function getCount()
	{
        if(is_array($this->_data))
        {
		    return count($this->_data);
        }
        return $this->_data->getTotalItemCount();
	}

	public function getColumns()
	{
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
