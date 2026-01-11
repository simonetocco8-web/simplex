<?php

class Maco_DaGrid_Render_Html_Cell_Link extends Maco_DaGrid_Render_Html_Renderer
{
	protected $_linksData = array();

	protected $_base = '/';

	protected $_img;

	protected $_field;

	protected $_title;

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
		$this->_linksData = $column->getOption('linksData', array());
		$this->_base = $column->getOption('base', '/');

		$this->_field = $column->getField();


		$this->_img = $column->getOption('img', false);
		$this->_title = $column->getOption('title', '');

		$this->_defaultTpl = 'cells/link.phtml';
	}
	
	public function getValue()
	{
		$field = $this->_column->getField();
		$separator = $this->_column->getOption('separator', $this->_separator); 
		$ret = '';
		if(is_string($field))
		{
			$ret = $this->_source->getCellByFieldName($field);
		}
		else if(is_array($field))
		{
			foreach($field as $i => $f)
			{
				if($i == 0)
				{
					$ret .= $this->_source->getCellByFieldName($f);
				}
				else
				{
					$ret .= $separator . $this->_source->getCellByFieldName($f);
				}
			}
		}
		
		return $ret;
	}
}
