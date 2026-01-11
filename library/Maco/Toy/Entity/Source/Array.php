<?php

class Maco_Toy_Entity_Source_Array implements Maco_Toy_Entity_Source_Interface
{
	/**
	 * The array containg data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Populate the array data.
	 *
	 * @param mixed $rawArray the array could contain associative values or not!
	 */
	public function pushData($rawArray)
	{
		$this->_data = $rawArray;
	}

	public function load()
	{
		return $this->_data;
	}
}
