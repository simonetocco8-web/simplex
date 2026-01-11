<?php

class Maco_DaGrid_Grid
{
	/**
	 * The renderer object
	 *
	 * @var Maco_DaGrid_Render_Interface
	 */
	protected $_renderer;

	/**
	 * The source object
	 *
	 * @var Maco_DaGrid_Source_Interface
	 */
	protected $_source;

	/**
	 * The id of this grid
	 *
	 * @var string
	 */
	protected $_id;

	/**
	 * The columns of this grid
	 *
	 * @var array
	 */
	protected $_columns = array();

	/**
	 * Grid Raw URI
	 *
	 * @var string
	 */
	protected $_rawUri;

	/**
	 * Grid URI
	 *
	 * @var string
	 */
	protected $_uri;

    /**
     * Grid PathInfo
     *
     * @var string
     */
    protected $_pathInfo;

	/**
	 * the paginator object
	 *
	 * @var Maco_DaGrid_Paginator_Default
	 */
	protected $_paginator;

	/**
	 * Grid Request Params
	 *
	 * @var array|bool
	 */
	protected $_reqParams = false;
    
    /**
    * Main searchable flag
    * 
    * @var bool
    */
    protected $_searchable = true;
    
    /**
    * Possible advanced search
    * 
    * @var object
    */
    protected $_advancedSearch;

	/**
	 * Sets the paginator for this grid
	 *
	 * @param array $paginator
	 */
	public function setPaginator($paginator)
	{
		$this->_paginator = $paginator;
	}

	/**
	 * Sets the row per page in this grid
	 *
	 * @param int $rows
	 */
	public function setRowsPerPage($rows)
	{
		$this->_paginator = new Maco_DaGrid_Paginator_Default($this);
		$this->_paginator->setPerPage($rows);
	}

	/**
	 * Sets the renderer
	 *
	 * @param Maco_DaGrid_Render_Interface $renderer
	 */
	public function setRenderer($renderer)
	{
		$this->_renderer = $renderer;
		$this->_renderer->setGrid($this);
        
        if($this->_advancedSearch)
        {
            $this->_renderer->setAdvancedSearch($this->_advancedSearch);
            unset($this->_advancedSearch);
        }
	}

	/**
	 * Sets the renderer
	 *
	 * @param Maco_DaGrid_Source_Interface $renderer
	 */
	public function setSource($source)
	{
		$this->_source = $source;
	}

	/**
	 * Sets the uri for this grid
	 *
	 * @var string
	 */
	public function setUri($uri)
	{
		$this->_uri = $uri;
	}

	/**
	 * Sets the raw uri for this grid
	 *
	 * @var string
	 */
	public function setRawUri($rawUri)
	{
		$this->_rawUri = $rawUri;
        $parts = explode('?', $this->_rawUri);
        $this->_pathInfo = $parts[0];
	}
    
    /**
    * Sets the main searchable flag.
    * 
    * Has to be called before adding the columns to the grid
    * 
    * @param bool $searchable
    * @return void
    */
    public function setSearchable($searchable)
    {
        $this->_searchable = $searchable;
    }
    
    /**
    * Sets the advanced search for this grid
    * 
    * @param mixed $search
    */
    public function setAdvancedSearch($search)
    {
        if($this->_renderer)
        {
            $this->_renderer->setAdvancedSearch($search);
        }
        else
        {
            $this->_advancedSearch = $search;
        }
    }

	/**
	 * Returns the uri for this Grid
	 *
	 * @return string
	 */
	public function getUri()
	{
		//if($this->_uri === NULL)
		{
			$uri = $this->getRawUri();

			if($this->_renderer !== NULL)
			{
				$uri = $this->_renderer->getCleanUri($uri);
			}

			$this->_uri = $uri;
		}

		return $this->_uri;
	}

	/**
	 * Returns the raw uri for this grid
	 *
	 * @return string
	 */
	public function getRawUri()
	{
		if($this->_rawUri === NULL)
		{
			$request = Zend_Controller_Front::getInstance()->getRequest();
			$this->_rawUri = $request->getRequestUri();
            $this->_pathInfo = $request->getPathInfo();
			if(substr($this->_rawUri, -1, 1) == '/')
			{
				$this->_rawUri = substr($this->_rawUri, 0, -1);
			}
		}

		return $this->_rawUri;
	}

    public function getPathInfo()
    {
        return $this->_pathInfo;
    }

	/**
	 * Adds a column to the grid
	 *
	 * @param mixed $field
	 * @param mixed $label
	 * @param mixed $class
	 * @param mixed $renderer
	 * @param mixed $search
	 * @param mixed $options
	 */
	public function addColumn($field, $label = NULL, $class = 'default', $path_to_template = null,
	$renderer = 'default', $search = 'default', $sortable = true, $options = array())
	{
		$type = 'default';
		switch($type)
		{
			case 'TODO':
				break;
			default:
				$col = new Maco_DaGrid_Column_Default($field, $label);
				$col->setClass($class);
				$col->setRenderer($renderer, $path_to_template);
				$col->setOptions($options);
				$col->setSearchable($search);
				$col->setSortable($sortable);
				$this->_columns[] = $col;
				break;
		}
	}

	/**
	 * Add columns to the grid
	 *
	 * @param array $columns
	 */
	public function addColumns($columns)
	{
		foreach($columns as $col)
		{
			if(!isset($col['field']))
			{
				throw new Exception('You have to pass a value for $row[\'field\']: ' . print_r($col, true));
			}

			$field = $col['field'];
			$label = isset($col['label']) ? $col['label'] : NULL;
			$class = isset($col['class']) ? $col['class'] : NULL;
			$options = isset($col['options']) ? $col['options'] : array();

			$renderer = isset($col['renderer']) ? $col['renderer'] : 'default';
            if($this->_searchable)
            {
			    $search = isset($col['search']) ? $col['search'] : 'default';
            }
            else
            {
                $search = false;
            }
			$sortable = isset($col['sortable']) ? $col['sortable'] : true;

            $path_to_template = isset($col['path_to_template']) ? $col['path_to_template'] : null;
            
			$this->addColumn($field, $label, $class, $path_to_template, $renderer, $search, $sortable, $options);
		}
	}

	/**
	 * Returns the source object
	 *
	 * @return Maco_DaGrid_Source_Interface
	 */
	public function getSource()
	{
		return $this->_source;
	}

	/**
	 * Processes a view script and returns the output.
	 *
	 * @param string $name The script name to process.
	 * @return string The script output.
	 */
	public function deploy($name = 'default.phtml')
	{
		if($this->_renderer === NULL)
		{
			throw new Exception('No renderer set for this grid');
		}

		if($this->_source === NULL)
		{
			throw new Exception('No source set for this grid');
		}

		$this->_renderer->setGrid($this);
		$this->_source->setGrid($this);

		if(empty($this->_columns))
		{
			// try grabbing columns from the source
			$this->_source->getColumns();
		}

		if($this->_columns === FALSE || $this->_columns === NULL || empty($this->_columns))
		{
			throw new Exception('Columns not setted for this grid');
		}



		$this->_renderer->setColumns($this->_columns);
		//$this->_renderer->setSource($this->_source);
		$this->_renderer->setId($this->_id);
		$temp=$this->getRawUri();
		$this->_renderer->setRawUri($temp);
		$temp=$this->getUri();
		$this->_renderer->setUri($temp);
		$temp=$this->getPathInfo();
		$this->_renderer->setPathInfo($temp);

		if($this->_paginator !== NULL)
		{
			if($this->_paginator === TRUE)
			{
				$this->_paginator = new Maco_DaGrid_Paginator_Default($this);
			}
			$this->_source->setPaginator($this->_paginator);
			$this->_renderer->setPaginator($this->_paginator);
		}

		$this->_renderer->render($name);
	}

	/**
	 * Sets the id for this grid
	 *
	 * @param string
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * Returns this grid's id
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->_id;
	}

	public function getParamInRequest($field, $default = null)
	{
		if($this->_reqParams === FALSE)
		{
			$this->_parseReqParams();
		}

		if(isset($this->_reqParams[$field]))
		{
			return $this->_reqParams[$field];
		}

		return $default;
	}

	protected function _parseReqParams()
	{
		$this->_reqParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
	}

	public static function getRequestParams()
	{
		return Zend_Controller_Front::getInstance()->getRequest()->getParams();
	}

	public static function getRequestParam($field, $def = null)
	{
		return Zend_Controller_Front::getInstance()->getRequest()->getParam($field, $def);
	}
}