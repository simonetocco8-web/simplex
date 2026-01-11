<?php

class Maco_DaGrid_Render_Html_Helper_Sort_Default
{
	/**
	 * The raw uri
	 *
	 * @var string
	 */
	protected $_rawUri;

	/**
	 * The clean uri
	 *
	 * @var string
	 */
	protected $_uri;

	/**
	 * The sort field label for the request (POST|GET)
	 *
	 * @var string
	 */
	protected $_sortFieldLabel = '_s';

	/**
	 * The sort dir label for the request (POST|GET)
	 *
	 * @var string
	 */
	protected $_sortDirLabel = '_d';

    /**
     * The sort field
     *
     * @var string
     */
    protected $_sortField;

    /**
     * The sort dir 
     *
     * @var string
     */
    protected $_sortDir;

    
	public function setRawUri(&$rawUri)
	{
		$this->_rawUri = $rawUri;
		$temp=$this->getCleanUri($this->_rawUri);

		$this->setUri($temp);
	}

	public function setUri(&$uri)
	{
		$this->_uri = $uri;
        
        $this->_parseUri();
	}

	public function getSortFieldLabel()
	{
		return $this->_sortFieldLabel;
	}

	public function getSortDirLabel()
	{
		return $this->_sortDirLabel;
	}

	public function setSortFieldLabel($label)
	{
		$this->_sortFieldLabel =  $label;
	}

	public function setSortDirLabel($label)
	{
		$this->_sortDirLabel = $label;
	}
    
    public function getSortDir()
    {
        return $this->_sortDir;
    }
    
    public function getSortField()
    {
        return $this->_sortField;
    }

	/**
	 * Return a clean uri for this grid
	 *
	 * @return string
	 */
	public function getCleanUri($uri)
	{
		if($this->_uri === NULL)
		{
			$uri = preg_replace('/(\?|&)' . $this->getSortFieldLabel() . '=[0-9|a-z|A-Z|-|_]*/', '', $uri);
			$uri = preg_replace('/(\?|&)' . $this->getSortDirLabel() . '=[0-9|a-z|A-Z|-|_]*/', '', $uri);

			$this->_uri = preg_replace('/\/$/', '', $uri);
		}

		return $this->_uri;
	}

	/**
	 * Return a well formatted uri for sorting with the given column
	 *
	 * @param Maco_DaGrid_Column_Default $column
	 */
	public function getSortUriForColumn($column)
	{
		$test = $column->getField();
		if(is_array($test))
		{
			$test = implode('|', $test);
		}
		
		$reqParam = Maco_DaGrid_Grid::getRequestParam($this->getSortFieldLabel()); 
		
		if($reqParam == $test)
		{
			if(Maco_DaGrid_Grid::getRequestParam($this->getSortDirLabel()) == 'DESC')
			{
				return $this->_formatSortUri($column->getField(), 'ASC');
			}
			else
			{
				return $this->_formatSortUri($column->getField(), 'DESC');
			}
		}
		else
		{
			return $this->_formatSortUri($column->getField(), 'ASC');
		}
	}

	/**
	 * Return an img or html for sorting with the given column
	 *
	 * @param Maco_DaGrid_Column_Default $column
	 */
	public function getSortStatusImgForColumn($column)
	{
		$test = $column->getField();
		if(is_array($test))
		{
			$test = implode('|', $test);
		}
		
		$reqParam = Maco_DaGrid_Grid::getRequestParam($this->getSortFieldLabel());
		
		if($reqParam == $test)
		{
			if(Maco_DaGrid_Grid::getRequestParam($this->getSortDirLabel()) == 'DESC')
			{
				return '&uArr;';
			}
			else
			{
				return '&dArr;';
			}
		}
		else
		{
			return '';
			return '<span class="visibleOnOver">&dArr;</span>';
		}
	}

	protected function _formatSortUri($field, $dir)
	{
		if(is_array($field)){
			$field = implode('|', $field);
		}



        if(strpos($this->_uri, $this->getSortFieldLabel() . '='))
        {
            $pattern = '/(\?|&)(' . $this->getSortFieldLabel() . '=)(\w+)&('
                . $this->getSortDirLabel() . '=)(\w+)/';
            $replacement = '${1}${2}' . $field . '${4}' . $dir;

            return preg_replace($pattern, $replacement, $this->_uri);
        }

        $sep = strpos($this->_uri, '?') ? '&' : '?';

        return $this->_uri . $sep . $this->getSortFieldLabel() . '='
            . $field . '&' . $this->getSortDirLabel() . '=' . $dir;

		return $this->_uri . '/' . $this->getSortFieldLabel() . '/'
		. $field . '/' . $this->getSortDirLabel() . '/' . $dir;
	}
    
    protected function _parseUri()
    {
        $this->_sortField = Maco_DaGrid_Grid::getRequestParam($this->getSortFieldLabel(), null);
        $this->_sortDir = Maco_DaGrid_Grid::getRequestParam($this->getSortDirLabel(), null);
    }
}
