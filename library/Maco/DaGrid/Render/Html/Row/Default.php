<?php

class Maco_DaGrid_Render_Html_Row_Default extends Maco_DaGrid_Render_Html_Renderer
{
	/**
	 * Id Prefix
	 *
	 * @var string
	 */
	protected $_idPrefix = 'tr_';

	/**
	 * The index for this row
	 *
	 * @var int
	 */
	protected $_index;

	/**
	 * Constructor
	 */
	public function __construct($index)
	{
		parent::__construct();

		$this->_index = $index;
		$this->_defaultTpl = 'rows/default.phtml';
	}

	public function setSource(&$source)
	{
		$this->_source = $source;
	}

	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}

	public function setIdPrefix(&$pre)
	{
		$this->_idPrefix = $pre;
	}

	public function renderCell($column)
	{
		$colClass = $column->getClass();
		$class = 'Maco_DaGrid_Render_Html_Cell_' . ucfirst($colClass);

		if(!class_exists($class))
		{
			throw new Exception('class ' . $class . ' doesn\'t exists');
		}
        
		$ren = new $class($column->getPathToTemplate());

		$ren->setView($this->_view);
		$ren->setSource($this->_source);
		$ren->setColumns($this->_columns);
		$ren->setColumn($column);

		$ren->render();
	}
}
