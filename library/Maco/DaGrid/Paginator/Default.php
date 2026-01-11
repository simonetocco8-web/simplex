<?php

class Maco_DaGrid_Paginator_Default
{
	protected $_previousPage;
	protected $_nextPage;
	protected $_totalPages;
	protected $_currentPage;

	protected $_perPage = 10;

	protected $_renderer;

	protected $_total;

	protected $_paginated;

	protected $_perPageOptions = array(
	   5=>5, 10 => 10, 25 => 25, 50 => 50, 100 => 100
	);

	/**
	 * The Grid object
	 *
	 * @var Maco_DaGrid_Grid
	 */
	protected $_grid;

	public function __construct(Maco_DaGrid_Grid &$grid)
	{
		$this->_grid = $grid;
		$this->_renderer = new Maco_DaGrid_Render_Html_Paginator_Default($this);

		// $this->_calculate();
	}

	protected function _calculate()
	{
		$this->_total = $this->_grid->getSource()->getCount();

		if($this->_total != 0)
		{
			$this->_totalPages = ceil($this->_total / $this->_perPage);
		}
		else
		{
			$this->_totalPages = 1;
		}

		$pageLabel = $this->_renderer->getPageLabel();

		$this->setCurrentPage($this->_grid->getParamInRequest($pageLabel, 1));

		if($this->_currentPage < $this->_totalPages)
		{
			$this->_paginated = $this->_perPage;
		}
		else
		{
			if($this->_currentPage == 1)
			{
				$this->_paginated = $this->_total;
			}
			else
			{
				$this->_paginated = /*$this->_perPage -*/ ($this->_total % $this->_perPage);
			}
		}
	}

	public function calculate()
	{
		$this->_calculate();
	}

	public function setPerPage($perPage)
	{
		$this->_perPage = $perPage;
		$this->_calculate();

		// TODO: Controllare se non si pu� evitare il primo controllo pagine che pu� essere a vuoto
	}

	public function setCurrentPage($page)
	{
		$page = (int) $page;
		if($page < 1)
		{
			$page = 1;
		}
		else if($page > $this->_totalPages)
		{
			$page = $this->_totalPages;
		}
		$this->_currentPage = $page;
	}

	public function getCurrentPage()
	{
		return $this->_currentPage;
	}

	public function setFirst($page)
	{
		$this->_firstPage = $page;
	}

	public function getFirst()
	{
		return $this->_firstPage;
	}

	public function getPrevious()
	{
		return $this->_previousPage;
	}

	public function getLast()
	{
		return $this->_lastPage;
	}

	public function getNext()
	{
		return $this->_nextPage;
	}

	public function getTotalPages()
	{
		return $this->_totalPages;
	}

	public function getRenderer()
	{
		return $this->_renderer;
	}

	public function setPaginated($paginated)
	{
		$this->_paginated = $paginated;
	}

	public function getPaginated()
	{
		return $this->_paginated;
	}

	public function setTotal($total)
	{
		$this->_total = $total;
	}

	public function getTotal()
	{
		return $this->_total;
	}

	public function getGridUri()
	{
		return $this->_grid->getUri();
	}

	public function getGridRawUri()
	{
		return $this->_grid->getRawUri();
	}

	public function getPerPage()
	{
		return $this->_perPage;
	}

	public function getPerPageOptions()
	{
		return $this->_perPageOptions;
	}
}

