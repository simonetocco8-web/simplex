<?php

class Maco_DaGrid_Render_Html extends Maco_DaGrid_Render_Html_Renderer implements Maco_DaGrid_Render_Interface
{
	/**
	 * The columns array
	 *
	 * @var array
	 */
	protected $_columns = array();

	/**
	 * The sort field label for the request (POST|GET)
	 *
	 * @var string
	 */
	protected $_sortFieldLabel = '_s';

    /**
	 * With export
	 *
	 * @var bool
	 */
	public $with_export = false;

	/**
	 * The sort dir label for the request (POST|GET)
	 *
	 * @var string
	 */
	protected $_sortDirLabel = '_d';

	/**
	 * The TR prefix
	 *
	 * @var string
	 */
	protected $_trIdPrefix = 'tr_';

	/**
	 * The paginator object
	 *
	 * @var Maco_DaGrid_Paginator_Default
	 */
	protected $_paginator;

	/**
	 * sort helper
	 *
	 * @var Maco_DaGrid_Render_Html_Helper_Sort_Default
	 */
	protected $_sortHelper;
    
    /**
     * advanced search obj
     *
     * @var object
     */
    protected $_advancedSearch;

    /**
     * With fast search ???
     *
     * @var bool
     */
    public $withFastSearch = true;

	/**
	 * Constructor
	 *
	 * @param string $path_to_template
	 * @return Maco_DaGrid_Render_Html
	 */
	public function __construct($path_to_template = NULL)
	{
		parent::__construct($path_to_template);
	}

    public function withFastSearch()
    {
        return $this->withFastSearch;
    }

	public function getSortFieldLabel()
	{
		return $this->getSortHelper()->getSortFieldLabel();
	}

	public function getSortDirLabel()
	{
		return $this->getSortHelper()->getSortDirLabel();
	}
    
    public function getSortField()
    {
        return $this->getSortHelper()->getSortField();
    }

    public function getSortDir()
    {
        return $this->getSortHelper()->getSortDir();
    }

	/**
	 * Return the source object taken from the parent grid
	 *
	 * @return Maco_DaGrid_Source_Interface
	 */
	public function getSource()
	{
		return $this->_grid->getSource();
	}

	/**
	 * Sets the label for this table
	 *
	 * @param array
	 */
	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}

	/**
	 * Return the sort helper
	 *
	 * @return Maco_DaGrid_Render_Html_Helper_Sort_Default
	 */
	public function getSortHelper()
	{
		if($this->_sortHelper == null)
		{
			$this->_sortHelper = new Maco_DaGrid_Render_Html_Helper_Sort_Default();
			$temp=$this->getRawUri();
			$this->_sortHelper->setRawUri($temp);
		}
		return $this->_sortHelper;
	}

	/**
	 * Sets the sort helper
	 *
	 * @param Maco_DaGrid_Render_Html_Helper_Sort_Default
	 */
	public function setSortHelper(&$sortHelper)
	{
		$this->_sortHelper = $sortHelper;
		$this->_sortHelper->setRawUri($this->getRawUri());
	}

	/**
	 * Set tr id prefix
	 *
	 * @param string
	 */
	public function setTrIdPrefix($prefix)
	{
		$this->_trIdPrefix = $prefix;
	}

	/**
	 * Return the tr id prefix
	 *
	 * @return string
	 */
	public function getTrIdPrefix($prefix)
	{
		return $this->_trIdPrefix;
	}

	/**
	 * Sets the source for the values
	 *
	 * @param Maco_DaGrid_Source_Interface
	 */
	public function setSource(&$source)
	{
		$this->_source = $source;
	}

	public function getSearchItemForColumn(&$column)
	{
		$type = $column->getSearchable();
        
		$cls = 'Maco_DaGrid_Render_Html_Search_' . ucfirst(strtolower($type));

		if(!class_exists($cls, false))
		{
			$ntype = 'default';
			$cls = 'Maco_DaGrid_Render_Html_Search_' . ucfirst(strtolower($ntype));
			$s = new $cls($type, $column->getPathToTemplate());
		}
		else
		{
			$s = new $cls($type, $column->getPathToTemplate());
		}

		$s->setColumn($column);

		return $s;
	}

	/**
	 * Render a row
	 *
	 * @param Maco_DaGrid_Row_Interface $row
	 */
	public function renderRow($row)
	{
		$ren = new Maco_DaGrid_Render_Html_Row_Default($row->getIndex());
		$ren->setView($this->_view);
		$ren->setSource($row);
		$ren->setColumns($this->_columns);
		$ren->setIdPrefix($this->_trIdPrefix);

		$ren->render();
	}

	/**
	 * Return a clean uri for this grid
	 *
	 * @return string
	 */
	public function getCleanUri($uri)
	{
		$uri = $this->getSortHelper()->getCleanUri($uri);

		if($this->_paginator !== NULL)
		{
			$pageLabel = $this->_paginator->getRenderer()->getPageLabel();
			$uri = preg_replace('/\/' . $pageLabel . '\/[0-9|a-z|A-Z|-|_]*/', '', $uri);
		}

		$uri = preg_replace('/\/$/', '', $uri);

		return $uri;
	}
    
    /**
    * Return the base url for this grid
    * 
    * @return string
    */
    public function getBaseUri()
    {
        return $this->_uri;
    }

	/**
	 * Return a well formatted uri for sorting with the given column
	 *
	 * @param Maco_DaGrid_Column_Default $column
	 */
	public function getSortUriForColumn($column)
	{
		return $this->getSortHelper()->getSortUriForColumn($column);
	}

	/**
	 * Return an img or html for sorting with the given column
	 *
	 * @param Maco_DaGrid_Column_Default $column
	 */
	public function getSortStatusImgForColumn($column)
	{
		return $this->getSortHelper()->getSortStatusImgForColumn($column);
	}

	public function setPaginator(&$paginator)
	{
		$this->_paginator = $paginator;
		$this->_paginator->getRenderer()->setView($this->_view);
	}
	 
	public function setGrid(Maco_DaGrid_Grid &$grid)
	{
		$this->_grid = $grid;
	}
    
    /**
    * Sets the advanced search for this renderer
    * 
    * @param mixed $search
    */
    public function setAdvancedSearch($search)
    {
        $this->_advancedSearch = $search;
    }
}
