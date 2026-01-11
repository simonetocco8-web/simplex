<?php

class Maco_DaGrid_Render_Html_Cell_Links extends Maco_DaGrid_Render_Html_Renderer
{
	protected $_linksData = array();

	protected $_base = '/';

	protected $_links = array();

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

		$this->_links = $column->getOption('links');

		$this->_defaultTpl = 'cells/links.phtml';
	}
}
