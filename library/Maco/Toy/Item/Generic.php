<?php

class Maco_Toy_Item_Generic extends Maco_Toy_Item_Abstract
{
	public function __construct($value, $label = '')
	{
		if($value === NULL)
		{
			throw new Exception('A value string must be supplied');
		}

		$this->_value = $value;

		$this->_label = $label;
	}

	public function renderDetail()
	{
		return '<dt>' . $this->_label . '</dt>' . '<dd>' . $this->_value . '</dd>';
	}

	public function renderEdit()
	{
		return '<dt>' . $this->_label . '</dt>'
		. '<dd>' . '<input type="text" name="TODO" value="' . $this->_value . '" />' . '</dd>';
	}

	public function renderInList()
	{
		return '<td>' . $this->_value . '</td>';
	}
}
