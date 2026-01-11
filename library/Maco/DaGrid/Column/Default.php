<?php

class Maco_DaGrid_Column_Default
{
	/**
	 * The field name  of this column
	 *
	 * @var string
	 */
	protected $_field;

	/**
	 * The label of this column
	 *
	 * @var string
	 */
	protected $_label;

	/**
	 * The renderer to be used vy this column
	 *
	 * @var string
	 */
	protected $_renderer = 'default';
    
    /**
     * The path where to search for the renderer
     *
     * @var string
     */
    protected $_path_to_template = null;

	/**
	 * The class name to be used by this column
	 *
	 * @var string
	 */
	protected $_class = 'default';

	/**
	 * Options of this column
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * Sortable flag
	 *
	 * @var bool
	 */
	protected $_sortable = true;

	/**
	 * Searchable value. False if this column is not searchable. Elsewhere a
	 * name for the sortable type or renderer
	 *
	 * @var bool|string
	 */
	protected $_searchable = 'default';

	/**
	 * Constructor
	 *
	 * @param string $field
	 * @param string $label
	 * @return Maco_DaGrid_Column_Default
	 */
	public function __construct($field, $label = NULL)
	{
		$this->_field = $field;

		$this->_label = ($label !== NULL) ? $label : $field;
	}

	/**
	 * Sets the renderer for this column
	 *
	 * @param string $renderer
	 */
	public function setRenderer($renderer, $path_to_template = null)
	{
		if($renderer === NULL)
		{
			throw new Exception('If you want to set a renderer, you must pass a renderer!');
		}
		$this->_renderer = $renderer;
        $this->_path_to_template = $path_to_template;
	}

	/**
	 * Returns the renderer string for this column
	 *
	 * @return string
	 */
	public function getRenderer()
	{
		return $this->_renderer;
	}

	/**
	 * Sets the class name for this column
	 *
	 * @param string $class
	 */
	public function setClass($class = NULL)
	{
		if($class !== NULL)
		{
			$this->_class = $class;
		}
	}

	/**
	 * Returns the class name for this column
	 *
	 * @return string
	 */
	public function getClass()
	{
		return $this->_class;
	}

	/**
	 * Returns the field name for this column
	 *
	 * @return string
	 */
	public function getField()
	{
		return $this->_field;
	}

	/**
	 * Returns the label for this column
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	/**
	 * Sets the options for this column
	 *
	 * @param array
	 */
	public function setOptions($options)
	{
		$this->_options = $options;
	}

	/**
	 * Returns the options for this column
	 *
	 * @return arrau
	 */
	public function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Return the option value for the passed key. Default if not found
	 *
	 * @param string $name
	 * @param mixed $def
	 */
	public function getOption($name, $def = null)
	{
		return (isset($this->_options[$name])) ? $this->_options[$name] : $def;
	}

	/**
	 * Set if the grid should be sortable by this column
	 *
	 * @param bool $sortable
	 */
	public function setSortable($sortable = true)
	{
		$this->_sortable = $sortable;
	}
    
    /**
     * GEt the path to the template
     *
     * @param bool $sortable
     */
    public function getPathToTemplate()
    {
        return $this->_path_to_template;
    }

	/**
	 * Returns if this grid is sortable by this column
	 *
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->_sortable;
	}

	/**
	 * Sets the searchable item for this column. False if not searchable
	 *
	 * @param bool|string $searchable
	 */
	public function setSearchable($searchable = 'default')
	{
		$this->_searchable = $searchable;
	}

	/**
	 * Returns the searchable type for this column. False if not searchable
	 *
	 * @return bool|string
	 */
	public function getSearchable()
	{
		return $this->_searchable;
	}
}
