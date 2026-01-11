<?php

interface Maco_DaGrid_Source_Row_Interface
{
	/**
	 * Return the cells for this row
	 *
	 * @return array
	 */
	public function getCells();

	/**
	 * Return the cell value for the given field
	 *
	 * @param string $field
	 * @param string $default default value to return if no value for the field
	 *                        is found and strict is false
	 * @param bool   $strict  if true the field must exists in the row
	 * @return mixed
	 */
	public function getCellByFieldName($field, $default = NULL, $strict = false);
}
