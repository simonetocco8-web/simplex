<?php

class Maco_DaGrid_Render_Html_Search_Default extends Maco_DaGrid_Render_Html_Renderer
{
	/**
	 * Constructor
	 */
	public function __construct($template = 'default', $path_to_template = null)
	{
		parent::__construct($path_to_template);
		$this->_defaultTpl = 'search/' . $template . '.phtml';
	}

	public function setSource(&$source)
	{
		$this->_source = $source;
	}

	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}

	public function setColumn($column)
	{
		$this->_column = $column;
	}
	
	public function getColumnFieldName()
	{
		$name= $this->_column->getField();
		
		if(is_array($name))
		{
			$name = implode('|', $name);
		}
		
		return $name;
	}
}
