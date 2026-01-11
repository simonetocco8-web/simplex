<?php

class Maco_PGrid_PGrid
{
	protected $_grid_id = 'grid';

	protected $_source;

	protected $_options = array();

	protected $_fields = array();

	protected $_callbacks = array();

	public function __call($name, array $arguments)
	{
		if(substr($name, 0, 3) == 'set')
		{
			$option = lcfirst(substr($name, 3));
			$this->_options[$option] = $arguments[0];
			return;
		}

		throw new Maco_PGrid_Exception('Invalid method call: ' . $name, 500);
	}

	public function __set(string $name, mixed $value)
	{

	}

	public function __get(string $name)
	{

	}

	/**
	 * Create the right data source
	 *
	 * @param mixed $data
	 */
	public function setData($data)
	{
		if(is_array($data))
		{
			$this->_source = new Maco_PGrid_Source_Array();
		}

		$this->_source->setData($data, $this->_fields);
	}

	public function setFields($array)
	{
		$this->_fields = $array;
	}

	public function setSortable($flag)
	{
		$this->_options['sortable'] = $flag;
	}

	public function setDetailOptions($params, $url = null)
	{
		$this->_options['detailOptions'] = array($params, $url);
	}

	public function setSelectable($flag)
	{
		$this->_options['selectable'] = $flag;
	}

	public function setSortableColumns($options)
	{
		$this->_options['sortableFields'] = $options;
	}

	public function setOrderBy($field, $dir)
	{
		$this->_options['orderBy'] = array($field => $dir);
	}

	public function setColumnCallback($field, $callback, $params)
	{
		$this->_options['callbacks'][$field] = array($callback, $params);
	}

	/**
	 * Enable ajax calls for the grid
	 *
	 * @param Zend_View $view
	 */
	public function useAjax(&$view)
	{
		$this->_options['useAjax'] = &$view;
	}

	/**
	 * Render the tables in the format given
	 *
	 * @param string $output
	 */
	public function render($output = 'htmlTable')
	{
		switch($output)
		{
			case 'htmlTable':
				$renderer = new Maco_PGrid_Renderer_HtmlTable();
				break;
		}

		$renderer->setSource($this->_source, $this->_fields);

		$renderer->setOptions($this->_options);

		return $renderer->execute();
	}
}
