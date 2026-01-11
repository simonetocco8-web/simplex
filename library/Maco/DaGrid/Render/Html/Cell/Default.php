<?php

class Maco_DaGrid_Render_Html_Cell_Default extends Maco_DaGrid_Render_Html_Renderer
{	
	protected $_separator = ' ';
    
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
		$this->_defaultTpl = 'cells/' . $column->getRenderer() . '.phtml';
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
